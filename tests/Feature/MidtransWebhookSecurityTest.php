<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji MidtransService::handleNotification ASLI (tanpa mock):
 * verifikasi signature, perpanjangan sewa, dan idempotensi.
 */
class MidtransWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'SB-Mid-server-test-123456';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        config(['midtrans.server_key' => self::SERVER_KEY]);
    }

    private function makePayment(): Payment
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'ktp_number' => '3201000000000001']);
        $room = Room::create(['room_number' => 'B-201', 'price' => 1000000, 'status' => 'occupied']);
        $lease = Lease::create([
            'tenant_id'  => $tenant->id,
            'room_id'    => $room->id,
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date'   => now()->addDays(10)->toDateString(),
            'm_price'    => 1000000,
            'status'     => 'active',
        ]);

        return Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-WEBHOOK-001',
            'amount'         => 1000000,
            'billing_period' => now()->toDateString(),
            'due_date'       => now()->addDays(5)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
        ]);
    }

    private function payload(string $orderId, string $status, bool $signed = true): array
    {
        $data = [
            'order_id'           => $orderId,
            'status_code'        => '200',
            'gross_amount'       => '1000000.00',
            'transaction_status' => $status,
            'payment_type'       => 'qris',
            'fraud_status'       => 'accept',
        ];
        if ($signed) {
            $data['signature_key'] = hash('sha512', $orderId . '200' . '1000000.00' . self::SERVER_KEY);
        }

        return $data;
    }

    public function test_valid_settlement_marks_paid_and_extends_lease_by_30_days(): void
    {
        $payment = $this->makePayment();
        $oldEnd = $payment->lease->end_date->copy();

        $this->postJson(route('midtrans.notification'), $this->payload('INV-WEBHOOK-001', 'settlement'))
            ->assertOk();

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertSame(
            $oldEnd->copy()->addDays(30)->toDateString(),
            $payment->lease->fresh()->end_date->toDateString()
        );
    }

    public function test_unsigned_notification_is_rejected(): void
    {
        $payment = $this->makePayment();

        $this->postJson(route('midtrans.notification'), $this->payload('INV-WEBHOOK-001', 'settlement', signed: false))
            ->assertStatus(500);

        $this->assertSame('unpaid', $payment->fresh()->status);
    }

    public function test_forged_signature_is_rejected(): void
    {
        $payment = $this->makePayment();
        $data = $this->payload('INV-WEBHOOK-001', 'settlement');
        $data['signature_key'] = str_repeat('a', 128);

        $this->postJson(route('midtrans.notification'), $data)->assertStatus(500);

        $this->assertSame('unpaid', $payment->fresh()->status);
    }

    public function test_duplicate_notifications_extend_lease_only_once(): void
    {
        $payment = $this->makePayment();
        $oldEnd = $payment->lease->end_date->copy();

        // capture (kartu) lalu settlement, plus retry dari Midtrans
        $this->postJson(route('midtrans.notification'), $this->payload('INV-WEBHOOK-001', 'capture'))->assertOk();
        $this->postJson(route('midtrans.notification'), $this->payload('INV-WEBHOOK-001', 'settlement'))->assertOk();
        $this->postJson(route('midtrans.notification'), $this->payload('INV-WEBHOOK-001', 'settlement'))->assertOk();

        $lease = $payment->lease->fresh();
        $this->assertSame($oldEnd->copy()->addDays(30)->toDateString(), $lease->end_date->toDateString());
        $this->assertSame(1, (int) $lease->renewal_count);
    }

    public function test_late_expire_notification_does_not_revert_paid_invoice(): void
    {
        $payment = $this->makePayment();

        $this->postJson(route('midtrans.notification'), $this->payload('INV-WEBHOOK-001', 'settlement'))->assertOk();
        $this->postJson(route('midtrans.notification'), $this->payload('INV-WEBHOOK-001', 'expire'))->assertOk();

        $this->assertSame('paid', $payment->fresh()->status);
    }
}
