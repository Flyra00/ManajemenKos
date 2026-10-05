<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Payment;
use Carbon\Carbon;

/**
 * Sinkronisasi domain saat sebuah invoice dinyatakan lunas.
 *
 * Sebelumnya logika ini terduplikasi (dan berperilaku berbeda) di tiga tempat:
 * MidtransService::handleNotification, PaymentController::verify, dan
 * PaymentController::update. Service ini menjadi satu-satunya sumber kebenaran
 * agar hasil pelunasan konsisten apa pun kanal pembayarannya.
 */
class PaymentService
{
    /**
     * Sinkronkan kontrak sewa & status kamar setelah sebuah invoice lunas.
     *
     * WAJIB dipanggil hanya ketika status pembayaran BARU berubah menjadi 'paid'
     * (bukan setiap kali menyimpan), supaya masa sewa tidak diperpanjang ganda.
     *
     * Efek:
     *  - Kontrak pending -> diaktifkan (masa aktif awal sudah ditetapkan saat booking).
     *  - Kontrak aktif   -> diperpanjang +30 hari + renewal_count/last_renewed_at.
     *  - Kamar           -> ditandai 'occupied'.
     *  - Bila kontrak memakai deposit, sisa tagihan sewa otomatis diterbitkan.
     */
    public function synchronizeLeaseOnPaid(Payment $payment): void
    {
        $lease = $payment->lease()->first();

        if (! $lease) {
            return;
        }

        if ($lease->room) {
            $lease->room->update(['status' => 'occupied']);
        }

        if ($lease->status === 'pending') {
            // Aktivasi pertama: masa aktif awal sudah dihitung sejak booking.
            $lease->update(['status' => 'active']);
        } else {
            // Pembayaran sewa berjalan / pelunasan: tambah masa aktif +30 hari.
            $currentEnd = Carbon::parse($lease->end_date ?: now());
            $baseDate = $currentEnd->isPast() ? now() : $currentEnd;

            $lease->update([
                'status'          => 'active',
                'end_date'        => $baseDate->copy()->addDays(30)->toDateString(),
                'renewal_count'   => ($lease->renewal_count ?? 0) + 1,
                'last_renewed_at' => now(),
            ]);
        }

        $this->issueRemainingDepositInvoice($payment, $lease);
    }

    /**
     * Terbitkan invoice pelunasan sisa bila kontrak memakai uang muka (deposit)
     * dan masih ada sisa sewa yang belum tertagih.
     */
    protected function issueRemainingDepositInvoice(Payment $payment, Lease $lease): void
    {
        if ((float) $lease->deposit_amount <= 0) {
            return;
        }

        $totalRent = (float) $lease->deposit_amount * 2;
        $totalPaid = (float) $lease->payments()->where('status', 'paid')->sum('amount');
        $remaining = max(0, $totalRent - $totalPaid);

        // Jangan terbitkan sisa jika sudah ada tagihan yang menunggu pembayaran.
        $hasPendingRemaining = $lease->payments()
            ->where('id', '!=', $payment->id)
            ->whereIn('status', ['unpaid', 'pending'])
            ->exists();

        if ($remaining <= 0 || $hasPendingRemaining) {
            return;
        }

        Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'amount'         => $remaining,
            'billing_period' => $payment->billing_period ?: now()->startOfMonth(),
            'due_date'       => $lease->start_date
                ? Carbon::parse($lease->start_date)->toDateString()
                : now()->addDays(7)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
            'notes'          => "Tagihan Pelunasan Sewa (Sisa setelah Uang Muka Rp "
                . number_format($payment->amount, 0, ',', '.') . ") Kamar "
                . ($lease->room->room_number ?? '—') . ".",
        ]);
    }

    /**
     * Nomor invoice unik untuk tagihan yang diterbitkan sistem.
     */
    protected function generateInvoiceNumber(): string
    {
        $baseNumber = 'INV-' . now()->format('Ym') . '-';
        $counter = Payment::count() + 1;

        do {
            $invoiceNumber = $baseNumber . str_pad($counter, 4, '0', STR_PAD_LEFT);
            $counter++;
        } while (Payment::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }
}
