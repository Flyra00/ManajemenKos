<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Konfigurasi kredensial dan mode Midtrans.
     */
    protected function configure(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production', false);
        Config::$isSanitized = (bool) config('midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('midtrans.is_3ds', true);
    }

    /**
     * Dapatkan atau generate Snap Token untuk Payment yang diberikan.
     */
    public function getSnapToken(Payment $payment): string
    {
        // Jika token sudah pernah di-generate sebelumnya dan payment belum dibayar, gunakan yang ada
        if (!empty($payment->snap_token) && $payment->status !== 'paid') {
            return $payment->snap_token;
        }

        $payment->loadMissing(['lease.room', 'lease.tenant.user']);

        $user = $payment->lease?->tenant?->user;
        $room = $payment->lease?->room;

        $params = [
            'transaction_details' => [
                'order_id'     => $payment->invoice_number,
                'gross_amount' => (int) round($payment->amount),
            ],
            'item_details' => [
                [
                    'id'       => (string) $payment->id,
                    'price'    => (int) round($payment->amount),
                    'quantity' => 1,
                    'name'     => 'Sewa Kamar ' . ($room->room_number ?? 'Kos'),
                ],
            ],
            'customer_details' => [
                'first_name' => $user->name ?? 'Penyewa',
                'email'      => $user->email ?? 'tenant@kosfly.com',
                'phone'      => $user->phone ?? '08123456789',
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        $payment->update([
            'snap_token' => $snapToken,
        ]);

        return $snapToken;
    }

    /**
     * Proses notifikasi webhook resmi dari Midtrans.
     *
     * @param array|null $payload
     * @return array [Payment|null $payment, string $status, string $message]
     */
    public function handleNotification(?array $payload = null): array
    {
        if ($payload === null) {
            $rawInput = file_get_contents('php://input');
            $payload = json_decode($rawInput, true) ?? [];
        }

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'qris';
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        // Cek jika ini adalah simulasi / test ping dari Dashboard Midtrans ("Test notification URL")
        if ($orderId && str_starts_with($orderId, 'payment_notif_test_')) {
            return [
                'payment'            => null,
                'transaction_status' => $transactionStatus ?? 'settlement',
                'status'             => 'test_ok',
                'is_test'            => true,
            ];
        }

        // Verifikasi Signature Key WAJIB. Tanpa ini, siapa pun bisa memalsukan
        // notifikasi "settlement" dan menandai tagihan lunas tanpa membayar.
        $serverKey = (string) config('midtrans.server_key');
        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey || $serverKey === '') {
            throw new \Exception('Midtrans notification is missing required signature fields.');
        }
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if (!hash_equals($expectedSignature, (string) $signatureKey)) {
            throw new \Exception('Invalid Midtrans signature key.');
        }

        $mappedPaymentMethod = match ($paymentType) {
            'qris', 'gopay', 'shopeepay' => 'qris',
            'bank_transfer', 'echannel' => 'bank_tf',
            'cstore' => 'cash',
            default => 'e_wallet',
        };

        $isPaid = ($transactionStatus === 'settlement')
            || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        // Transaksi + row lock: Midtrans bisa mengirim notifikasi berulang
        // (retry, atau capture lalu settlement untuk kartu kredit). Kunci baris
        // agar masa sewa hanya diperpanjang SEKALI per invoice.
        $payment = DB::transaction(function () use ($orderId, $isPaid, $transactionStatus, $mappedPaymentMethod, $payload) {
            $payment = Payment::where('invoice_number', $orderId)->lockForUpdate()->first();
            if (!$payment) {
                throw new \Exception("Payment with invoice {$orderId} not found.");
            }

            // Invoice yang sudah lunas tidak boleh diubah lagi oleh notifikasi apa pun
            // (mencegah perpanjangan ganda dan mencegah status lunas kembali ke unpaid).
            if ($payment->status === 'paid') {
                return $payment;
            }

            if ($isPaid) {
                $payment->update([
                    'status'            => 'paid',
                    'payment_date'      => now(),
                    'payment_method'    => $mappedPaymentMethod,
                    'midtrans_response' => $payload,
                ]);

                // Sinkronisasi kontrak sewa & kamar lewat service terpusat agar
                // hasilnya konsisten dengan verifikasi admin dan update manual.
                // Ini juga menerbitkan invoice sisa bila pembayaran ini uang muka.
                app(PaymentService::class)->synchronizeLeaseOnPaid($payment);
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $payment->update([
                    'status'            => 'unpaid',
                    'midtrans_response' => $payload,
                ]);
            } elseif ($transactionStatus === 'pending') {
                $payment->update([
                    'status'            => 'pending',
                    'payment_method'    => $mappedPaymentMethod,
                    'midtrans_response' => $payload,
                ]);
            }

            return $payment;
        });

        return [
            'payment'            => $payment,
            'transaction_status' => $transactionStatus,
            'status'             => $payment->status,
            'is_test'            => false,
        ];
    }
}
