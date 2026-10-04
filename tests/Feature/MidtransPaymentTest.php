<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MidtransService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        config([
            'midtrans.server_key' => 'SB-Mid-server-test-123456',
            'midtrans.client_key' => 'SB-Mid-client-test-123456',
            'midtrans.is_production' => false,
        ]);
    }

    private function createPaymentFixture(): Payment
    {
        $tenantUser = User::factory()->create([
            'name'  => 'Andi Pratama',
            'email' => 'andi@example.com',
            'phone' => '081298765432',
        ]);
        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201998877665544',
        ]);
        $room = Room::create([
            'room_number' => 'A-101',
            'price'       => 1500000,
            'status'      => 'occupied',
        ]);
        $lease = Lease::create([
            'tenant_id'  => $tenant->id,
            'room_id'    => $room->id,
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addMonths(3)->toDateString(),
            'm_price'    => 1500000,
            'status'     => 'active',
        ]);

        return Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-TEST-MIDTRANS-001',
            'amount'         => 1500000,
            'billing_period' => now()->toDateString(),
            'due_date'       => now()->addDays(3)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
        ]);
    }

    public function test_invoice_page_renders_midtrans_button_and_script(): void
    {
        $payment = $this->createPaymentFixture();

        $mockMidtrans = Mockery::mock(MidtransService::class);
        $mockMidtrans->shouldReceive('getSnapToken')
            ->once()
            ->with(Mockery::on(fn($p) => $p->id === $payment->id))
            ->andReturn('dummy-snap-token-xyz-123');
        $this->app->instance(MidtransService::class, $mockMidtrans);

        $signedUrl = URL::signedRoute('invoices.show', ['invoice_number' => $payment->invoice_number]);
        $response = $this->get($signedUrl);

        $response->assertOk();
        $response->assertSee('Pembayaran Online');
        $response->assertSee('Bayar Online Sekarang');
        $response->assertSee('dummy-snap-token-xyz-123');
        $response->assertSee(config('midtrans.snap_url'));
    }

    public function test_get_snap_token_endpoint_returns_token(): void
    {
        $payment = $this->createPaymentFixture();

        $mockMidtrans = Mockery::mock(MidtransService::class);
        $mockMidtrans->shouldReceive('getSnapToken')
            ->once()
            ->andReturn('generated-snap-token-abc');
        $this->app->instance(MidtransService::class, $mockMidtrans);

        $url = URL::signedRoute('invoices.snap-token', ['invoice_number' => $payment->invoice_number]);
        $response = $this->getJson($url);

        $response->assertOk();
        $response->assertJson([
            'success'    => true,
            'snap_token' => 'generated-snap-token-abc',
        ]);
    }

    public function test_midtrans_notification_webhook_marks_payment_as_paid_on_settlement(): void
    {
        $payment = $this->createPaymentFixture();

        $mockMidtrans = Mockery::mock(MidtransService::class);
        $mockMidtrans->shouldReceive('handleNotification')
            ->once()
            ->andReturnUsing(function () use ($payment) {
                $payment->update([
                    'status'            => 'paid',
                    'payment_date'      => now(),
                    'payment_method'    => 'qris',
                    'midtrans_response' => ['transaction_status' => 'settlement'],
                ]);

                return [
                    'payment'            => $payment,
                    'transaction_status' => 'settlement',
                    'status'             => 'paid',
                ];
            });
        $this->app->instance(MidtransService::class, $mockMidtrans);

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id'           => $payment->invoice_number,
            'transaction_status' => 'settlement',
            'gross_amount'       => '1500000.00',
            'payment_type'       => 'qris',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'data'   => [
                'invoice_number' => $payment->invoice_number,
                'payment_status' => 'paid',
            ],
        ]);

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('qris', $payment->payment_method);
        $this->assertNotNull($payment->payment_date);
    }
}
