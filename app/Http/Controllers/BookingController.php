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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class BookingController extends Controller
{
    /**
     * Proses pengajuan sewa kamar, pendaftaran akun otomatis, dan penerbitan invoice.
     */
    public function store(Request $request, Room $room)
    {
        // 1. Validasi ketersediaan kamar.
        //    Ini cek awal (ramah pengguna) supaya akun tidak dibuat saat kamar sudah dipegang
        //    kontrak/booking lain. Pengecekan atomik diulang di dalam transaction saat membuat kontrak.
        if (! $room->is_active || $room->status !== 'available' || $room->hasBlockingLease()) {
            return back()->with('error', 'Maaf, kamar ini baru saja dipesan atau tidak sedang tersedia.');
        }

        // 2. Validasi input formulir
        $targetUser = Auth::user() ?? User::where('email', $request->email)->first();
        $targetTenantId = $targetUser?->tenant?->id;

        $rules = [
            'ktp_number'      => [
                'required',
                'string',
                'regex:/^[0-9]{16}$/',
                Rule::unique('tenants', 'ktp_number')->ignore($targetTenantId),
            ],
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

        $messages = [
            'ktp_number.required' => 'Nomor KTP / NIK (16 digit) wajib diisi.',
            'ktp_number.regex'    => 'Nomor KTP / NIK harus terdiri dari tepat 16 digit angka.',
            'ktp_number.unique'   => 'Nomor KTP / NIK ini sudah terdaftar pada sistem hunian.',
        ];

        $validated = $request->validate($rules, $messages);

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

        // 4. Pastikan data record Tenant tersimpan dengan nomor KTP resmi
        $tenant = $user->tenant;
        if ($tenant) {
            $tenant->update([
                'ktp_number'        => $validated['ktp_number'],
                'emergency_name'    => $tenant->emergency_name ?: $user->name,
                'emergency_contact' => $tenant->emergency_contact ?: ($user->phone ?? '-'),
                'job'               => $tenant->job ?: 'Penyewa Kos',
            ]);
        } else {
            $tenant = Tenant::create([
                'user_id'           => $user->id,
                'ktp_number'        => $validated['ktp_number'],
                'emergency_name'    => $user->name,
                'emergency_contact' => $user->phone ?? '-',
                'job'               => 'Penyewa Kos',
            ]);
        }

        // 5 & 6. Kunci kamar lalu terbitkan Kontrak (Lease) + Invoice secara atomik.
        //
        // Lock baris kamar (lockForUpdate) mencegah dua calon penyewa membuat
        // kontrak untuk kamar yang sama pada saat yang bersamaan (double-booking).
        $startDate = Carbon::parse($validated['start_date']);

        $payment = DB::transaction(function () use ($room, $tenant, $validated, $startDate) {
            $lockedRoom = Room::whereKey($room->id)->lockForUpdate()->first();

            if (! $lockedRoom || ! $lockedRoom->is_active || $lockedRoom->status !== 'available') {
                return null;
            }

            // Bebaskan booking kedaluwarsa: kontrak pending yang seluruh invoicenya
            // sudah lewat jatuh tempo dan tidak ada yang berstatus dibayar/menunggu bayar.
            $lockedRoom->leases()
                ->where('status', 'pending')
                ->whereDoesntHave('payments', fn ($p) => $p->whereIn('status', ['paid', 'pending']))
                ->whereHas('payments', fn ($p) => $p->whereIn('status', ['unpaid', 'overdue'])
                    ->whereDate('due_date', '<', now()->toDateString()))
                ->update(['status' => 'cancelled']);

            // Cegah double-booking: kamar yang dipegang kontrak aktif / booking menunggu bayar.
            if ($lockedRoom->hasBlockingLease()) {
                return null;
            }

            // Hitung durasi dan masa aktif sewa berdasarkan pilihan durasi bulan
            $durationMonths = (int) ($validated['duration_months'] ?? 1);
            if ($durationMonths < 1) {
                $durationMonths = 1;
            }
            $endDate = (clone $startDate)->addMonths($durationMonths);
            $totalRent = $lockedRoom->price * $durationMonths;
            $isDeposit = ($validated['payment_type'] ?? 'full') === 'deposit_50';
            $depositAmount = $isDeposit ? round($totalRent * 0.5) : 0;
            $initialInvoiceAmount = $isDeposit ? $depositAmount : $totalRent;

            $lease = Lease::create([
                'tenant_id'      => $tenant->id,
                'room_id'        => $lockedRoom->id,
                'start_date'     => $startDate->toDateString(),
                'end_date'       => $endDate->toDateString(),
                'monthly_price'  => $lockedRoom->price,
                'deposit_amount' => $depositAmount,
                'status'         => 'pending',
                'note'           => "Durasi sewa {$durationMonths} bulan. " . ($isDeposit
                    ? "Tagihan Uang Muka (Deposit 50%) Rp " . number_format($depositAmount, 0, ',', '.') . ". Sisa pelunasan Rp " . number_format($totalRent - $depositAmount, 0, ',', '.') . "."
                    : "Tagihan sewa ({$durationMonths} bulan)."),
            ]);

            // Terbitkan Invoice Resmi (Payment) dengan tenggat 1x24 jam
            $baseNumber = 'INV-' . now()->format('Ym') . '-';
            $counter = Payment::count() + 1;
            $invoiceNumber = $baseNumber . str_pad($counter, 4, '0', STR_PAD_LEFT);

            while (Payment::where('invoice_number', $invoiceNumber)->exists()) {
                $counter++;
                $invoiceNumber = $baseNumber . str_pad($counter, 4, '0', STR_PAD_LEFT);
            }

            $paymentNotes = $isDeposit
                ? "Tagihan Uang Muka (Deposit 50%) sewa kamar {$lockedRoom->room_number} ({$durationMonths} bulan). Sisa pelunasan: Rp " . number_format($totalRent - $depositAmount, 0, ',', '.') . "."
                : "Tagihan sewa kamar {$lockedRoom->room_number} ({$durationMonths} bulan).";

            return Payment::create([
                'lease_id'       => $lease->id,
                'invoice_number' => $invoiceNumber,
                'amount'         => $initialInvoiceAmount,
                'billing_period' => $startDate->startOfMonth()->toDateString(),
                'due_date'       => now()->addDay()->toDateString(), // Tenggat 1x24 jam
                'payment_method' => 'bank_tf',
                'status'         => 'unpaid',
                'notes'          => $paymentNotes,
            ]);
        });

        if (! $payment) {
            return back()
                ->withInput()
                ->with('error', 'Maaf, kamar ini baru saja dipesan oleh calon penyewa lain. Silakan pilih kamar lain.');
        }

        return redirect()->to($payment->public_url)
            ->with('success', 'Pengajuan sewa berhasil! Invoice resmi Anda telah terbit. Silakan lakukan pembayaran dalam batas waktu 1x24 jam.');
    }
}
