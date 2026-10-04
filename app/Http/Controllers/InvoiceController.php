<?php

namespace App\Http\Controllers;

use App\Models\KosSetting;
use App\Models\Payment;
use App\Services\BillingService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Tampilkan halaman invoice publik/tenant.
     */
    public function show($invoiceNumber, MidtransService $midtransService)
    {
        $payment = Payment::with(['lease.room', 'lease.tenant.user'])
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();

        // Ambil data rekening tujuan dari pengaturan kos
        $kosSettings = KosSetting::settings();

        // Generate Snap Token jika kredensial Midtrans tersedia dan belum lunas
        $snapToken = null;
        if ($payment->status !== 'paid' && !empty(config('midtrans.server_key')) && !str_starts_with(config('midtrans.server_key'), 'SB-Mid-server-xxxx')) {
            try {
                $snapToken = $midtransService->getSnapToken($payment);
            } catch (\Throwable $e) {
                Log::warning("Midtrans Snap token generation failed for invoice {$invoiceNumber}: " . $e->getMessage());
            }
        }

        return view('invoices.show', compact('payment', 'kosSettings', 'snapToken'));
    }

    /**
     * Upload bukti pembayaran transfer bank oleh penyewa.
     */
    public function uploadProof(Request $request, $invoiceNumber)
    {
        $payment = Payment::with('lease.room')
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();

        if ($payment->status === 'paid') {
            return back()->with('info', 'Tagihan ini telah lunas dan diverifikasi.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:bank_tf,qris,cash,e_wallet'],
            'proof_image'    => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        if ($payment->proof_image && Storage::disk('public')->exists($payment->proof_image)) {
            Storage::disk('public')->delete($payment->proof_image);
        }

        $proofPath = $request->file('proof_image')->store('payment_proofs', 'public');

        $payment->update([
            'payment_method' => $validated['payment_method'],
            'proof_img'      => $proofPath,
            'status'         => 'pending', // Menunggu verifikasi admin
        ]);


        return back()->with('success', 'Bukti pembayaran berhasil diunggah! Status pembayaran kini menunggu verifikasi pengelola kos.');
    }

    /**
     * Cetak kuitansi / invoice resmi dari halaman publik.
     */
    public function receipt($invoiceNumber, BillingService $billingService)
    {
        $payment = Payment::with(['lease.room', 'lease.tenant.user', 'verifier'])
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();

        $kosSettings = $billingService->getKosSettings();
        $terbilang = $billingService->terbilang((float) $payment->amount);

        return view('payments.receipt', compact('payment', 'kosSettings', 'terbilang'));
    }

    /**
     * Cek status pembayaran secara real-time via API/AJAX.
     */
    public function status($invoiceNumber)
    {
        $payment = Payment::where('invoice_number', $invoiceNumber)->firstOrFail();

        return response()->json([
            'status'      => $payment->status,
            'is_paid'     => $payment->status === 'paid',
            'receipt_url' => $payment->public_receipt_url,
        ]);
    }

    /**
     * Request Snap Token secara dinamis via AJAX.
     */
    public function getSnapToken($invoiceNumber, MidtransService $midtransService)
    {
        $payment = Payment::where('invoice_number', $invoiceNumber)->firstOrFail();

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan sudah lunas.',
                'is_paid' => true,
            ], 400);
        }

        try {
            $snapToken = $midtransService->getSnapToken($payment);

            return response()->json([
                'success'    => true,
                'snap_token' => $snapToken,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to generate Midtrans Snap token for {$invoiceNumber}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungkan ke Midtrans. Silakan periksa kredensial Server Key Midtrans Anda atau gunakan transfer manual.',
            ], 500);
        }
    }

    /**
     * Webhook notifikasi pembayaran otomatis dari Midtrans.
     */
    public function midtransNotification(Request $request, MidtransService $midtransService)
    {
        try {
            $result = $midtransService->handleNotification($request->all());

            return response()->json([
                'status'  => 'success',
                'message' => 'Notifikasi Midtrans berhasil diproses.',
                'data'    => [
                    'invoice_number'     => $result['payment']?->invoice_number ?? 'TEST-NOTIFICATION',
                    'transaction_status' => $result['transaction_status'],
                    'payment_status'     => $result['status'],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error processing Midtrans notification: ' . $e->getMessage(), [
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
