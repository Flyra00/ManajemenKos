<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Services\BillingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, BillingService $billingService)
    {
        // Sinkronisasi otomatis status tagihan yang telah melewati batas jatuh tempo
        Payment::where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $query = Payment::with(['lease.tenant.user', 'lease.room', 'verifier'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('lease.tenant.user', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('lease.room', function ($rq) use ($search) {
                      $rq->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($month = $request->input('month')) {
            $query->whereYear('billing_period', substr($month, 0, 4))
                  ->whereMonth('billing_period', substr($month, 5, 2));
        }

        $payments = $query->paginate(10)->withQueryString();

        $stats = [
            'total'   => Payment::count(),
            'paid'    => Payment::where('status', 'paid')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'unpaid'  => Payment::where('status', 'unpaid')->count(),
            'overdue' => Payment::where('status', 'overdue')->count(),
            'nominal' => Payment::where('status', 'paid')->sum('amount'),
        ];

        return view('payments.index', compact('payments', 'stats', 'billingService'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $leases = Lease::with(['tenant.user', 'room'])
            ->where('status', 'active')
            ->get();

        if ($leases->isEmpty()) {
            $leases = Lease::with(['tenant.user', 'room'])->latest()->get();
        }

        $selectedLeaseId = $request->input('lease_id');
        $suggestedInvoice = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        return view('payments.create', compact('leases', 'selectedLeaseId', 'suggestedInvoice'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lease_id'       => ['required', 'exists:leases,id'],
            'invoice_number' => ['required', 'string', 'max:50', 'unique:payments,invoice_number'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', 'date'],
            'due_date'       => ['required', 'date'],
            'payment_date'   => ['nullable', 'date'],
            'payment_method' => ['required', 'in:cash,e_wallet,bank_tf,qris'],
            'status'         => ['required', 'in:paid,pending,unpaid,overdue'],
            'proof_img'      => ['nullable', 'image', 'max:2048'],
            'notes'          => ['nullable', 'string'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_img')) {
            $proofPath = $request->file('proof_img')->store('payments/proofs', 'public');
        }

        $paymentDate = $validated['payment_date'] ?? null;
        $verifiedBy = null;

        if ($validated['status'] === 'paid') {
            $paymentDate = $paymentDate ?: now();
            $verifiedBy = auth()->id();
        }

        $payment = Payment::create([
            'lease_id'       => $validated['lease_id'],
            'invoice_number' => $validated['invoice_number'],
            'amount'         => $validated['amount'],
            'billing_period' => $validated['billing_period'],
            'due_date'       => $validated['due_date'],
            'payment_date'   => $paymentDate,
            'payment_method' => $validated['payment_method'],
            'status'         => $validated['status'],
            'proof_img'      => $proofPath,
            'verified_by'    => $verifiedBy,
            'notes'          => $validated['notes'] ?? null,
        ]);

        // Bila admin langsung mencatat pembayaran lunas, sinkronkan kontrak & kamar
        // dengan logika yang sama seperti verifikasi/notifikasi pembayaran lainnya.
        if ($validated['status'] === 'paid') {
            $this->paymentService->synchronizeLeaseOnPaid($payment);
        }

        return redirect()
            ->route('payments.index')
            ->with('success', 'Tagihan/Pembayaran berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['lease.tenant.user', 'lease.room', 'verifier']);
        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        $leases = Lease::with(['tenant.user', 'room'])->get();
        return view('payments.edit', compact('payment', 'leases'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'lease_id'       => ['required', 'exists:leases,id'],
            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('payments', 'invoice_number')->ignore($payment->id),
            ],
            'amount'         => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', 'date'],
            'due_date'       => ['required', 'date'],
            'payment_date'   => ['nullable', 'date'],
            'payment_method' => ['required', 'in:cash,e_wallet,bank_tf,qris'],
            'status'         => ['required', 'in:paid,pending,unpaid,overdue'],
            'proof_img'      => ['nullable', 'image', 'max:2048'],
            'notes'          => ['nullable', 'string'],
        ]);

        $proofPath = $payment->proof_img;

        if ($request->hasFile('proof_img')) {
            if ($payment->proof_img && Storage::disk('public')->exists($payment->proof_img)) {
                Storage::disk('public')->delete($payment->proof_img);
            }
            $proofPath = $request->file('proof_img')->store('payments/proofs', 'public');
        }

        $paymentDate = $validated['payment_date'] ?? $payment->payment_date;
        $verifiedBy = $payment->verified_by;

        if ($validated['status'] === 'paid') {
            $paymentDate = $paymentDate ?: now();
            $verifiedBy = $verifiedBy ?: auth()->id();
        }

        $previousStatus = $payment->getOriginal('status');
        $payment->update([
            'lease_id'       => $validated['lease_id'],
            'invoice_number' => $validated['invoice_number'],
            'amount'         => $validated['amount'],
            'billing_period' => $validated['billing_period'],
            'due_date'       => $validated['due_date'],
            'payment_date'   => $paymentDate,
            'payment_method' => $validated['payment_method'],
            'status'         => $validated['status'],
            'proof_img'      => $proofPath,
            'verified_by'    => $verifiedBy,
            'notes'          => $validated['notes'] ?? null,
        ]);

        // Hanya sinkronkan saat status BARU berubah menjadi lunas, agar masa sewa
        // tidak diperpanjang berkali-kali ketika admin menyimpan ulang.
        if ($validated['status'] === 'paid' && $previousStatus !== 'paid') {
            $this->paymentService->synchronizeLeaseOnPaid($payment);
        }

        return redirect()
            ->route('payments.index')
            ->with('success', 'Pembayaran berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        if ($payment->proof_img && Storage::disk('public')->exists($payment->proof_img)) {
            Storage::disk('public')->delete($payment->proof_img);
        }

        $payment->delete();

        return redirect()
            ->route('payments.index')
            ->with('success', 'Catatan pembayaran berhasil dihapus');
    }

    /**
     * Verifikasi instan pelunasan invoice oleh Admin.
     */
    public function verify(Payment $payment)
    {
        $payment->update([
            'status'       => 'paid',
            'payment_date' => $payment->payment_date ?: now(),
            'verified_by'  => auth()->id(),
        ]);

        // Aktifkan/perpanjang kontrak + sinkronkan kamar & invoice sisa deposit
        // lewat service terpusat (sumber kebenaran tunggal untuk semua kanal bayar).
        $this->paymentService->synchronizeLeaseOnPaid($payment);

        return back()->with('success', "Pembayaran invoice {$payment->invoice_number} berhasil diverifikasi Lunas! Kontrak aktif dan kamar resmi terisi.");
    }

    /**
     * Generate tagihan sewa bulanan otomatis via Web UI (Admin).
     */
    public function generateBills(Request $request, BillingService $billingService)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $year = null;
        $month = null;

        if (!empty($validated['month'])) {
            $parts = explode('-', $validated['month']);
            $year = (int) $parts[0];
            $month = (int) $parts[1];
        }

        $result = $billingService->generateMonthlyBills($year, $month);

        $periodStr = $result['period']->translatedFormat('F Y');

        if ($result['generated'] > 0) {
            $msg = "Berhasil menerbitkan {$result['generated']} tagihan sewa baru untuk periode {$periodStr}.";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} kontrak dilewati karena sudah memiliki tagihan).";
            }
            return redirect()->route('payments.index')->with('success', $msg);
        }

        return redirect()->route('payments.index')->with('info', "Tidak ada tagihan baru yang diterbitkan untuk periode {$periodStr}. Seluruh {$result['total']} kontrak sewa aktif sudah memiliki tagihan.");
    }

    /**
     * Tampilkan halaman kuitansi / invoice siap cetak (print view).
     */
    public function receipt(Payment $payment, BillingService $billingService)
    {
        $payment->loadMissing(['lease.tenant.user', 'lease.room', 'verifier']);
        $kosSettings = $billingService->getKosSettings();
        $terbilang = $billingService->terbilang((float) $payment->amount);

        return view('payments.receipt', compact('payment', 'kosSettings', 'terbilang'));
    }
}



