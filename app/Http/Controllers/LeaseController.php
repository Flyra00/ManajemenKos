<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Lease;
use App\Models\Room;
use App\Models\Tenant;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lease::with(['tenant.user', 'room'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('tenant.user', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('room', function ($rq) use ($search) {
                      $rq->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'Segera Berakhir') {
                $query->where('status', 'active')
                      ->whereNotNull('end_date')
                      ->whereBetween('end_date', [now(), now()->addDays(30)]);
            } else {
                $query->where('status', $status);
            }
        }

        if ($month = $request->input('month')) {
            $query->whereYear('end_date', substr($month, 0, 4))
                  ->whereMonth('end_date', substr($month, 5, 2));
        }

        $leases = $query->paginate(10)->withQueryString();

        $stats = [
            'total'   => Lease::count(),
            'aktif'   => Lease::where('status', 'active')->count(),
            'segera'  => Lease::where('status', 'active')
                             ->whereNotNull('end_date')
                             ->whereBetween('end_date', [now(), now()->addDays(30)])
                             ->count(),
            'selesai' => Lease::whereIn('status', ['completed', 'cancelled'])->count(),
        ];

        return view('leases.index', compact('leases', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $tenants = Tenant::with('user')->get();
        $rooms = Room::all();
        $selectedTenantId = $request->query('tenant_id');
        $selectedRoomId = $request->query('room_id');

        return view('leases.create', compact('tenants', 'rooms', 'selectedTenantId', 'selectedRoomId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id'      => ['required', 'exists:tenants,id'],
            'room_id'        => ['required', 'exists:rooms,id'],
            'start_date'     => ['required', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'monthly_price'  => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'status'         => ['required', 'in:pending,active,completed,cancelled'],
            'note'           => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $lease = Lease::create([
                'tenant_id'      => $validated['tenant_id'],
                'room_id'        => $validated['room_id'],
                'start_date'     => $validated['start_date'],
                'end_date'       => $validated['end_date'] ?? null,
                'm_price'        => $validated['monthly_price'],
                'deposit_amount' => $validated['deposit_amount'] ?? 0,
                'status'         => $validated['status'],
                'note'           => $validated['note'] ?? null,
            ]);

            if ($lease->status === 'active') {
                $lease->room->update(['status' => 'occupied']);
            }
        });

        return redirect()
            ->route('leases.index')
            ->with('success', 'Kontrak sewa berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lease $lease)
    {
        $lease->load(['tenant.user', 'room', 'payments']);
        return view('leases.show', compact('lease'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lease $lease)
    {
        $tenants = Tenant::with('user')->get();
        $rooms = Room::all();

        return view('leases.edit', compact('lease', 'tenants', 'rooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lease $lease)
    {
        $validated = $request->validate([
            'tenant_id'      => ['required', 'exists:tenants,id'],
            'room_id'        => ['required', 'exists:rooms,id'],
            'start_date'     => ['required', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'monthly_price'  => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'status'         => ['required', 'in:pending,active,completed,cancelled'],
            'note'           => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $lease) {
            $oldRoomId = $lease->room_id;

            $lease->update([
                'tenant_id'      => $validated['tenant_id'],
                'room_id'        => $validated['room_id'],
                'start_date'     => $validated['start_date'],
                'end_date'       => $validated['end_date'] ?? null,
                'm_price'        => $validated['monthly_price'],
                'deposit_amount' => $validated['deposit_amount'] ?? 0,
                'status'         => $validated['status'],
                'note'           => $validated['note'] ?? null,
            ]);

            if ($lease->status === 'active') {
                $lease->room->update(['status' => 'occupied']);
            } else {
                if (!Lease::where('room_id', $lease->room_id)->where('status', 'active')->exists()) {
                    $lease->room->update(['status' => 'available']);
                }
            }

            if ($oldRoomId != $lease->room_id) {
                if (!Lease::where('room_id', $oldRoomId)->where('status', 'active')->exists()) {
                    Room::where('id', $oldRoomId)->update(['status' => 'available']);
                }
            }
        });

        return redirect()
            ->route('leases.index')
            ->with('success', 'Kontrak sewa berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lease $lease)
    {
        if ($lease->payments()->where('status', 'paid')->exists()) {
            return redirect()
                ->route('leases.index')
                ->with('error', 'Tidak dapat menghapus kontrak yang sudah memiliki pembayaran lunas.');
        }

        DB::transaction(function () use ($lease) {
            $roomId = $lease->room_id;
            $lease->delete();

            if (!Lease::where('room_id', $roomId)->where('status', 'active')->exists()) {
                Room::where('id', $roomId)->update(['status' => 'available']);
            }
        });

        return redirect()
            ->route('leases.index')
            ->with('success', 'Kontrak sewa berhasil dihapus');
    }

    /**
     * Tampilkan Surat Perjanjian Sewa Kamar (SPK / Kontrak) siap cetak.
     */
    public function contract(Lease $lease, BillingService $billingService)
    {
        $lease->loadMissing(['tenant.user', 'room.facilities']);
        $kosSettings = $billingService->getKosSettings();
        $monthlyTerbilang = $billingService->terbilang((float) $lease->m_price);
        $depositTerbilang = $billingService->terbilang((float) $lease->deposit_amount);

        return view('leases.contract', compact('lease', 'kosSettings', 'monthlyTerbilang', 'depositTerbilang'));
    }

    /**
     * Perpanjang masa sewa kontrak (Lease Renewal).
     */
    public function renew(Request $request, Lease $lease, BillingService $billingService)
    {
        if (auth()->user() && auth()->user()->hasRole('owner')) {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Admin.');
        }

        $validated = $request->validate([
            'duration_type'    => ['required', 'in:1_month,3_months,6_months,12_months,custom'],
            'new_end_date'     => ['nullable', 'required_if:duration_type,custom', 'date'],
            'monthly_price'    => ['required', 'numeric', 'min:0'],
            'generate_invoice' => ['nullable', 'boolean'],
            'renewal_note'     => ['nullable', 'string'],
        ]);

        // Tentukan tanggal acuan awal perpanjangan
        $baseDate = ($lease->end_date && $lease->end_date->isFuture())
            ? $lease->end_date->copy()
            : now()->startOfDay();

        $newEndDate = match ($validated['duration_type']) {
            '1_month'   => $baseDate->copy()->addMonth(),
            '3_months'  => $baseDate->copy()->addMonths(3),
            '6_months'  => $baseDate->copy()->addMonths(6),
            '12_months' => $baseDate->copy()->addYear(),
            'custom'    => Carbon::parse($validated['new_end_date'])->startOfDay(),
        };

        if ($newEndDate->lessThanOrEqualTo($baseDate)) {
            return back()->withErrors(['new_end_date' => 'Tanggal akhir baru harus lebih besar dari tanggal akhir sebelumnya.']);
        }

        DB::transaction(function () use ($lease, $validated, $newEndDate, $baseDate, $billingService, $request) {
            $formattedOldDate = $lease->end_date ? $lease->end_date->translatedFormat('d M Y') : 'Fleksibel';
            $formattedNewDate = $newEndDate->translatedFormat('d M Y');
            $logNote = "[" . now()->translatedFormat('d M Y H:i') . "] Diperpanjang dari " . $formattedOldDate . " sampai " . $formattedNewDate;
            if (!empty($validated['renewal_note'])) {
                $logNote .= " (Catatan: " . $validated['renewal_note'] . ")";
            }

            $currentNote = $lease->note ? rtrim($lease->note) . "\n" . $logNote : $logNote;

            $lease->update([
                'end_date'        => $newEndDate->toDateString(),
                'm_price'         => $validated['monthly_price'],
                'status'          => 'active',
                'renewal_count'   => $lease->renewal_count + 1,
                'last_renewed_at' => now(),
                'note'            => $currentNote,
            ]);

            // Pastikan kamar berstatus occupied
            $lease->room->update(['status' => 'occupied']);

            // Jika admin memilih untuk otomatis menerbitkan tagihan sewa
            if ($request->boolean('generate_invoice')) {
                $billingTarget = ($lease->end_date && $lease->end_date->isFuture())
                    ? $baseDate->copy()->addDay()->startOfMonth()
                    : now()->startOfMonth();
                $billingService->generateBillForLease($lease, $billingTarget);
            }
        });

        return redirect()
            ->route('leases.show', $lease)
            ->with('success', 'Kontrak sewa berhasil diperpanjang hingga ' . $newEndDate->translatedFormat('d F Y') . '.');
    }

    /**
     * Proses Check-Out & Pengakhiran Sewa Penghuni.
     */
    public function checkout(Request $request, Lease $lease)
    {
        if (auth()->user() && auth()->user()->hasRole('owner')) {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Admin.');
        }

        $validated = $request->validate([
            'checkout_date'     => ['required', 'date'],
            'room_condition'    => ['required', 'in:good,needs_cleaning,damaged'],
            'deposit_deduction' => ['nullable', 'numeric', 'min:0', 'lte:' . $lease->deposit_amount],
            'deposit_refunded'  => ['nullable', 'numeric', 'min:0'],
            'room_status'       => ['required', 'in:available,maintenance'],
            'record_expense'    => ['nullable', 'boolean'],
            'checkout_notes'    => ['nullable', 'string'],
        ]);

        $deduction = (float) ($validated['deposit_deduction'] ?? 0);
        $refunded = (float) ($validated['deposit_refunded'] ?? max(0, (float) $lease->deposit_amount - $deduction));

        DB::transaction(function () use ($lease, $validated, $deduction, $refunded, $request) {
            $lease->update([
                'status'            => 'completed',
                'checkout_date'     => $validated['checkout_date'],
                'deposit_deduction' => $deduction,
                'deposit_refunded'  => $refunded,
                'room_condition'    => $validated['room_condition'],
                'checkout_notes'    => $validated['checkout_notes'] ?? null,
            ]);

            // Update status kamar (available atau maintenance)
            $lease->room->update(['status' => $validated['room_status']]);

            // Jika dicatat ke kas pengeluaran kos
            if ($request->boolean('record_expense') && $refunded > 0) {
                $tenantName = $lease->tenant?->user?->name ?? 'Penghuni';
                $roomNo = $lease->room?->room_number ?? $lease->room_id;

                Expense::create([
                    'title'        => "Pengembalian Deposit Kamar {$roomNo} ({$tenantName})",
                    'description'  => "Pengembalian uang jaminan sewa (Kontrak #LS-" . str_pad($lease->id, 4, '0', STR_PAD_LEFT) . ")" . (!empty($validated['checkout_notes']) ? ". Catatan: {$validated['checkout_notes']}" : ''),
                    'amount'       => $refunded,
                    'expense_date' => $validated['checkout_date'],
                    'user_id'      => auth()->id(),
                ]);
            }
        });

        return redirect()
            ->route('leases.show', $lease)
            ->with('success', 'Proses check-out berhasil diselesaikan. Kamar kini berstatus ' . ($validated['room_status'] === 'maintenance' ? 'Perbaikan' : 'Tersedia') . '.');
    }

    /**
     * Tampilkan Berita Acara Check-Out & Kuitansi Pengembalian Deposit siap cetak.
     */
    public function checkoutReceipt(Lease $lease, BillingService $billingService)
    {
        $lease->loadMissing(['tenant.user', 'room.facilities']);
        $kosSettings = $billingService->getKosSettings();
        $refundTerbilang = $billingService->terbilang((float) $lease->deposit_refunded);
        $depositTerbilang = $billingService->terbilang((float) $lease->deposit_amount);
        $deductionTerbilang = $billingService->terbilang((float) $lease->deposit_deduction);

        return view('leases.checkout_receipt', compact(
            'lease',
            'kosSettings',
            'refundTerbilang',
            'depositTerbilang',
            'deductionTerbilang'
        ));
    }

    /**
     * Layanan mandiri bagi penghuni untuk menerbitkan invoice perpanjangan (+30 hari).
     */
    public function requestBill(Request $request, Lease $lease, BillingService $billingService)
    {
        $user = auth()->user();

        // Pastikan hanya penyewa yang bersangkutan yang dapat meminta tagihan
        if ($lease->tenant?->user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Cari apakah sudah ada tagihan belum lunas
        $pendingPayment = $lease->payments()
            ->whereIn('status', ['unpaid', 'pending', 'overdue'])
            ->latest('due_date')
            ->first();

        if ($pendingPayment) {
            return redirect()->to($pendingPayment->public_url)
                ->with('info', 'Tagihan sewa Anda sudah tersedia. Silakan lakukan pembayaran.');
        }

        // Tentukan periode bulan target berikutnya
        $targetDate = ($lease->end_date && $lease->end_date->isFuture())
            ? $lease->end_date->copy()->startOfMonth()
            : now()->startOfMonth();

        $payment = $billingService->generateBillForLease($lease, $targetDate);

        return redirect()->to($payment->public_url)
            ->with('success', 'Tagihan perpanjangan sewa berhasil diterbitkan! Lakukan pembayaran untuk menambah masa aktif sewa Anda +30 hari.');
    }
}

