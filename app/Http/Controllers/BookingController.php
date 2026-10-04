<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BookingController extends Controller
{
    /**
     * Proses pengajuan sewa kamar, pendaftaran akun otomatis, dan penerbitan invoice.
     */
    public function store(Request $request, Room $room)
    {
        // 1. Validasi ketersediaan kamar
        if (! $room->is_active || $room->status !== 'available') {
            return back()->with('error', 'Maaf, kamar ini baru saja dipesan atau tidak sedang tersedia.');
        }

        // 2. Validasi input formulir
        $rules = [
            'start_date'      => ['required', 'date', 'after_or_equal:today'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:24'],
            'payment_type'    => ['nullable', 'in:full,deposit_50'],
        ];

        if (! Auth::check()) {
            $rules['name']     = ['required', 'string', 'max:255'];
            $rules['email']    = ['required', 'email', 'max:255'];
            $rules['phone']    = ['required', 'string', 'max:20'];
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        $validated = $request->validate($rules);

        // 3. Autentikasi / Auto-Register Calon Tenant
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            // Cek apakah email sudah terdaftar sebelumnya
            $user = User::where('email', $validated['email'])->first();

            // Cek apakah nomor telepon sudah terdaftar pada akun lain
            $existingPhoneUser = User::where('phone', $validated['phone'])->first();
            if ($existingPhoneUser && (! $user || $existingPhoneUser->id !== $user->id)) {
                return back()
                    ->withInput()
                    ->withErrors(['phone' => 'Nomor WhatsApp / HP ini sudah terdaftar pada akun lain. Silakan gunakan nomor lain atau login ke akun Anda.']);
            }

            if ($user) {
                // Jika sudah ada tapi belum login, login jika password cocok atau langsung hubungkan
                if (! Hash::check($validated['password'], $user->password)) {
                    return back()
                        ->withInput()
                        ->withErrors(['email' => 'Email ini sudah terdaftar. Masukkan password yang sesuai atau silakan login terlebih dahulu.']);
                }

                // Jika user lama belum memiliki nomor telepon, simpan nomor dari booking
                if (empty($user->phone)) {
                    $user->update(['phone' => $validated['phone']]);
                }

                if (!$user->hasRole('admin') && !$user->hasRole('owner') && !$user->hasRole('staff') && !$user->hasRole('tenant')) {
                    Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
                    $user->assignRole('tenant');
                }
            } else {
                try {
                    $user = User::create([
                        'name'     => $validated['name'],
                        'email'    => $validated['email'],
                        'phone'    => $validated['phone'],
                        'password' => Hash::make($validated['password']),
                    ]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    return back()
                        ->withInput()
                        ->withErrors(['phone' => 'Nomor WhatsApp atau email ini sudah terdaftar. Silakan gunakan data lain atau login ke akun Anda.']);
                }

                // Pastikan role tenant tersedia
                Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
                $user->assignRole('tenant');
            }

            Auth::login($user);
        }

        if (!$user->hasRole('admin') && !$user->hasRole('owner') && !$user->hasRole('staff') && !$user->hasRole('tenant')) {
            Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
            $user->assignRole('tenant');
        }

        // 4. Pastikan record Tenant tersedia
        $tenant = Tenant::firstOrCreate(
            ['user_id' => $user->id],
            [
                'ktp_number'        => $request->ktp_number ?? ('KTP-' . $user->id . '-' . time()),
                'emergency_name'    => $user->name,
                'emergency_contact' => $user->phone ?? '-',
                'job'               => 'Penyewa Kos',
            ]
        );

        // 5. Buat Kontrak Sewa (Lease) berstatus pending dengan masa aktif awal 60 hari (2 bulan)
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = (clone $startDate)->addDays(60);

        // Tagihan invoice awal tetap senilai 1 bulan sewa
        $totalRent = $room->price;
        $isDeposit = ($validated['payment_type'] ?? 'full') === 'deposit_50';
        $depositAmount = $isDeposit ? round($totalRent * 0.5) : 0;
        $initialInvoiceAmount = $isDeposit ? $depositAmount : $totalRent;

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => $startDate->toDateString(),
            'end_date'       => $endDate->toDateString(),
            'monthly_price'  => $room->price,
            'deposit_amount' => $depositAmount,
            'status'         => 'pending',
            'note'           => "Masa aktif sewa awal 60 hari (2 bulan). " . ($isDeposit
                ? "Tagihan Uang Muka (Deposit 50%) Rp " . number_format($depositAmount, 0, ',', '.') . ". Sisa pelunasan Rp " . number_format($totalRent - $depositAmount, 0, ',', '.') . "."
                : "Tagihan sewa 1 bulan."),
        ]);

        // 6. Terbitkan Invoice Resmi (Payment) dengan tenggat 1x24 jam
        $baseNumber = 'INV-' . now()->format('Ym') . '-';
        $counter = Payment::count() + 1;
        $invoiceNumber = $baseNumber . str_pad($counter, 4, '0', STR_PAD_LEFT);

        while (Payment::where('invoice_number', $invoiceNumber)->exists()) {
            $counter++;
            $invoiceNumber = $baseNumber . str_pad($counter, 4, '0', STR_PAD_LEFT);
        }

        $paymentNotes = $isDeposit
            ? "Tagihan Uang Muka (Deposit 50%) sewa kamar {$room->room_number}. Sisa pelunasan: Rp " . number_format($totalRent - $depositAmount, 0, ',', '.') . "."
            : "Tagihan sewa kamar {$room->room_number} (1 bulan).";

        $payment = Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => $invoiceNumber,
            'amount'         => $initialInvoiceAmount,
            'billing_period' => $startDate->startOfMonth()->toDateString(),
            'due_date'       => now()->addDay()->toDateString(), // Tenggat 1x24 jam
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
            'notes'          => $paymentNotes,
        ]);


        return redirect()->to($payment->public_url)
            ->with('success', 'Pengajuan sewa berhasil! Invoice resmi Anda telah terbit. Silakan lakukan pembayaran dalam batas waktu 1x24 jam.');
    }
}
