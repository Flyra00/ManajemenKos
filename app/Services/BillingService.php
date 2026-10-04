<?php

namespace App\Services;

use App\Models\KosSetting;
use App\Models\Lease;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BillingService
{
    /**
     * Membaca pengaturan operasional dan rekening bank kos.
     */
    public function getKosSettings(): array
    {
        return KosSetting::settings();
    }

    /**
     * Generate tagihan sewa bulanan secara otomatis untuk semua kontrak sewa yang aktif.
     */
    public function generateMonthlyBills(?int $year = null, ?int $month = null): array
    {
        $now = now();
        $year = $year ?: (int) $now->year;
        $month = $month ?: (int) $now->month;

        $targetDate = Carbon::create($year, $month, 1)->startOfDay();
        $periodDate = $targetDate->toDateString();
        $settings = $this->getKosSettings();

        // Cari seluruh kontrak sewa aktif yang mencakup periode bulan ini
        $activeLeases = Lease::with(['tenant.user', 'room'])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $targetDate->copy()->endOfMonth())
            ->where(function ($q) use ($targetDate) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $targetDate->copy()->startOfMonth());
            })
            ->get();

        $generated = 0;
        $skipped = 0;

        foreach ($activeLeases as $lease) {
            // Cek apakah tagihan untuk periode bulan ini sudah pernah diterbitkan
            $alreadyBilled = Payment::where('lease_id', $lease->id)
                ->whereYear('billing_period', $year)
                ->whereMonth('billing_period', $month)
                ->exists();

            if ($alreadyBilled) {
                $skipped++;
                continue;
            }

            // Tentukan tanggal jatuh tempo berdasarkan setting kos (default: tgl 10)
            $dueDay = (int) ($settings['billing_due'] ?? 10);
            $actualDay = min(max(1, $dueDay), $targetDate->daysInMonth);
            $dueDate = Carbon::create($year, $month, $actualDay)->toDateString();

            // Status: jika tanggal jatuh tempo sudah lewat, beri status overdue, sebaliknya unpaid
            $status = ($dueDate < now()->toDateString()) ? 'overdue' : 'unpaid';

            // Generate nomor invoice unik
            do {
                $invoiceNumber = 'INV-' . $targetDate->format('Ymd') . '-' . strtoupper(Str::random(4));
            } while (Payment::where('invoice_number', $invoiceNumber)->exists());

            Payment::create([
                'lease_id'       => $lease->id,
                'invoice_number' => $invoiceNumber,
                'amount'         => $lease->monthly_price,
                'billing_period' => $periodDate,
                'due_date'       => $dueDate,
                'payment_method' => 'bank_tf',
                'status'         => $status,
                'notes'          => 'Tagihan sewa bulanan otomatis (' . $targetDate->translatedFormat('F Y') . ')',
            ]);

            $generated++;
        }

        return [
            'generated' => $generated,
            'skipped'   => $skipped,
            'total'     => $activeLeases->count(),
            'period'    => $targetDate,
        ];
    }

    /**
     * Terbitkan tagihan sewa tunggal untuk suatu kontrak pada periode bulan tertentu.
     */
    public function generateBillForLease(Lease $lease, ?Carbon $targetDate = null): ?Payment
    {
        $targetDate = $targetDate ? $targetDate->copy()->startOfMonth() : now()->startOfMonth();
        $year = (int) $targetDate->year;
        $month = (int) $targetDate->month;
        $periodDate = $targetDate->toDateString();
        $settings = $this->getKosSettings();

        $alreadyBilled = Payment::where('lease_id', $lease->id)
            ->whereYear('billing_period', $year)
            ->whereMonth('billing_period', $month)
            ->first();

        if ($alreadyBilled) {
            return $alreadyBilled;
        }

        $dueDay = (int) ($settings['billing_due'] ?? 10);
        $actualDay = min(max(1, $dueDay), $targetDate->daysInMonth);
        $dueDate = Carbon::create($year, $month, $actualDay)->toDateString();
        $status = ($dueDate < now()->toDateString()) ? 'overdue' : 'unpaid';

        do {
            $invoiceNumber = 'INV-' . $targetDate->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Payment::where('invoice_number', $invoiceNumber)->exists());

        return Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => $invoiceNumber,
            'amount'         => $lease->monthly_price,
            'billing_period' => $periodDate,
            'due_date'       => $dueDate,
            'payment_method' => 'bank_tf',
            'status'         => $status,
            'notes'          => 'Tagihan sewa perpanjangan kontrak (' . $targetDate->translatedFormat('F Y') . ')',
        ]);
    }

    /**
     * Normalisasi nomor telepon ke format internasional Indonesia (62xxx).
     */
    public function formatPhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (empty($digits)) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return strlen($digits) >= 9 ? $digits : null;
    }

    /**
     * Buat URL WhatsApp resmi berisikan template pesan pengingat tagihan.
     */
    public function buildWhatsAppReminderUrl(Payment $payment): ?string
    {
        $payment->loadMissing(['lease.tenant.user', 'lease.room']);

        $tenantUser = $payment->lease?->tenant?->user;
        $phone = $tenantUser?->phone ?? $payment->lease?->tenant?->emergency_contact;
        $cleanPhone = $this->formatPhoneNumber($phone);

        if (!$cleanPhone) {
            return null;
        }

        $settings   = $this->getKosSettings();
        $tenantName = $tenantUser?->name ?? 'Penghuni';
        $roomNumber = $payment->lease?->room?->room_number ?? '—';
        $periodStr  = $payment->billing_period ? Carbon::parse($payment->billing_period)->translatedFormat('F Y') : '—';
        $amountStr  = 'Rp ' . number_format($payment->amount, 0, ',', '.');
        $dueDateStr = $payment->due_date ? Carbon::parse($payment->due_date)->translatedFormat('d F Y') : '—';
        $invoiceUrl = $payment->public_url;

        $kosName = $settings['name'] ?? 'KosFly';

        $msg  = "Halo kak {$tenantName},\n";
        $msg .= "Ini adalah pengingat tagihan sewa kamar *{$roomNumber}* di *{$kosName}* untuk periode *{$periodStr}*.\n\n";
        $msg .= "📋 *Rincian Tagihan:*\n";
        $msg .= "• No. Invoice: `{$payment->invoice_number}`\n";
        $msg .= "• Jumlah Tagihan: *{$amountStr}*\n";
        $msg .= "• Jatuh Tempo: *{$dueDateStr}*\n\n";
        $msg .= "💳 *Bayar Online Praktis (QRIS / Virtual Account):*\n";
        $msg .= "Klik tautan invoice resmi berikut untuk langsung melakukan pembayaran online otomatis:\n";
        $msg .= "{$invoiceUrl}\n\n";
        $msg .= "Pembayaran otomatis terverifikasi lunas tanpa perlu konfirmasi manual. Terima kasih! 🙏";

        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($msg);
    }

    /**
     * Konversi angka nominal ke kalimat terbilang bahasa Indonesia.
     */
    public function terbilang(float $number): string
    {
        $number = (int) abs($number);
        if ($number === 0) {
            return 'Nol Rupiah';
        }

        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $temp = '';

        if ($number < 12) {
            $temp = ' ' . $huruf[$number];
        } elseif ($number < 20) {
            $temp = $this->terbilangWithoutSuffix($number - 10) . ' Belas';
        } elseif ($number < 100) {
            $temp = $this->terbilangWithoutSuffix((int) ($number / 10)) . ' Puluh ' . $this->terbilangWithoutSuffix($number % 10);
        } elseif ($number < 200) {
            $temp = ' Seratus ' . $this->terbilangWithoutSuffix($number - 100);
        } elseif ($number < 1000) {
            $temp = $this->terbilangWithoutSuffix((int) ($number / 100)) . ' Ratus ' . $this->terbilangWithoutSuffix($number % 100);
        } elseif ($number < 2000) {
            $temp = ' Seribu ' . $this->terbilangWithoutSuffix($number - 1000);
        } elseif ($number < 1000000) {
            $temp = $this->terbilangWithoutSuffix((int) ($number / 1000)) . ' Ribu ' . $this->terbilangWithoutSuffix($number % 1000);
        } elseif ($number < 1000000000) {
            $temp = $this->terbilangWithoutSuffix((int) ($number / 1000000)) . ' Juta ' . $this->terbilangWithoutSuffix($number % 1000000);
        } elseif ($number < 1000000000000) {
            $temp = $this->terbilangWithoutSuffix((int) ($number / 1000000000)) . ' Miliar ' . $this->terbilangWithoutSuffix(fmod($number, 1000000000));
        }

        return trim(preg_replace('/\s+/', ' ', $temp)) . ' Rupiah';
    }

    protected function terbilangWithoutSuffix(int $number): string
    {
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($number < 12) {
            return $huruf[$number];
        } elseif ($number < 20) {
            return $this->terbilangWithoutSuffix($number - 10) . ' Belas';
        } elseif ($number < 100) {
            return $this->terbilangWithoutSuffix((int) ($number / 10)) . ' Puluh ' . $this->terbilangWithoutSuffix($number % 10);
        } elseif ($number < 200) {
            return 'Seratus ' . $this->terbilangWithoutSuffix($number - 100);
        } elseif ($number < 1000) {
            return $this->terbilangWithoutSuffix((int) ($number / 100)) . ' Ratus ' . $this->terbilangWithoutSuffix($number % 100);
        } elseif ($number < 2000) {
            return 'Seribu ' . $this->terbilangWithoutSuffix($number - 1000);
        } elseif ($number < 1000000) {
            return $this->terbilangWithoutSuffix((int) ($number / 1000)) . ' Ribu ' . $this->terbilangWithoutSuffix($number % 1000);
        } elseif ($number < 1000000000) {
            return $this->terbilangWithoutSuffix((int) ($number / 1000000)) . ' Juta ' . $this->terbilangWithoutSuffix($number % 1000000);
        } elseif ($number < 1000000000000) {
            return $this->terbilangWithoutSuffix((int) ($number / 1000000000)) . ' Miliar ' . $this->terbilangWithoutSuffix(fmod($number, 1000000000));
        }

        return '';
    }
}
