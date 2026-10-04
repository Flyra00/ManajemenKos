<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function createActiveLeaseFixture(float $monthlyPrice = 1500000, string $phone = '081234567890'): array
    {
        $tenantUser = User::factory()->create([
            'name'  => 'Agus Prasetyo',
            'phone' => $phone,
        ]);

        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201123456789001',
        ]);

        $room = Room::create([
            'room_number' => 'B-101',
            'price'       => $monthlyPrice,
            'floor'       => 1,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->startOfMonth()->toDateString(),
            'end_date'       => now()->addMonths(6)->toDateString(),
            'm_price'        => $monthlyPrice,
            'deposit_amount' => 500000,
            'status'         => 'active',
        ]);

        return [$tenantUser, $tenant, $room, $lease];
    }

    public function test_billing_service_generates_monthly_bills_for_active_leases(): void
    {
        [$tenantUser, $tenant, $room, $lease] = $this->createActiveLeaseFixture(1750000);

        $service = app(BillingService::class);
        $nextMonth = now()->addMonth();
        $result = $service->generateMonthlyBills($nextMonth->year, $nextMonth->month);

        $this->assertEquals(1, $result['generated']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEquals(1, $result['total']);

        $this->assertDatabaseHas('payments', [
            'lease_id' => $lease->id,
            'amount'   => 1750000,
            'status'   => 'unpaid',
        ]);

        $payment = Payment::where('lease_id', $lease->id)->first();
        $this->assertNotNull($payment);
        $this->assertStringStartsWith('INV-', $payment->invoice_number);
        $this->assertEquals($nextMonth->startOfMonth()->toDateString(), $payment->billing_period->toDateString());
    }

    public function test_billing_service_skips_leases_that_already_have_bills_for_the_period(): void
    {
        [$tenantUser, $tenant, $room, $lease] = $this->createActiveLeaseFixture();

        $service = app(BillingService::class);

        // Run pertama: buat 1 tagihan
        $firstRun = $service->generateMonthlyBills();
        $this->assertEquals(1, $firstRun['generated']);
        $this->assertEquals(0, $firstRun['skipped']);

        // Run kedua pada bulan yang sama: harus melewati kontrak ini
        $secondRun = $service->generateMonthlyBills();
        $this->assertEquals(0, $secondRun['generated']);
        $this->assertEquals(1, $secondRun['skipped']);

        // Pastikan tidak ada duplikasi tagihan di database
        $this->assertEquals(1, Payment::where('lease_id', $lease->id)->count());
    }

    public function test_billing_service_ignores_inactive_or_expired_leases(): void
    {
        [$tenantUser, $tenant, $room, $lease] = $this->createActiveLeaseFixture();
        $lease->update(['status' => 'completed']);

        $service = app(BillingService::class);
        $result = $service->generateMonthlyBills();

        $this->assertEquals(0, $result['generated']);
        $this->assertEquals(0, $result['total']);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_artisan_command_generates_monthly_bills(): void
    {
        $this->createActiveLeaseFixture();

        $this->artisan('kos:generate-monthly-bills')
            ->expectsOutputToContain('Memulai pembuatan tagihan bulanan KosFly...')
            ->expectsOutputToContain('1 tagihan baru berhasil diterbitkan')
            ->assertSuccessful();

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_admin_can_generate_bills_via_web_post(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->createActiveLeaseFixture();

        $response = $this->actingAs($admin)->post(route('payments.generate_bills'), [
            'month' => date('Y-m'),
        ]);

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_whatsapp_reminder_url_formatting(): void
    {
        [$tenantUser, $tenant, $room, $lease] = $this->createActiveLeaseFixture(1500000, '081298765432');

        $service = app(BillingService::class);
        $service->generateMonthlyBills();

        $payment = Payment::where('lease_id', $lease->id)->first();
        $url = $service->buildWhatsAppReminderUrl($payment);

        $this->assertNotNull($url);
        // Pastikan nomor diawali 62 dan angka 0 awal dihilangkan
        $this->assertStringStartsWith('https://wa.me/6281298765432?text=', $url);

        // Pastikan pesan mengandung nama, nomor kamar, nomor invoice, dan link invoice
        $decoded = urldecode($url);
        $this->assertStringContainsString('Agus Prasetyo', $decoded);
        $this->assertStringContainsString('B-101', $decoded);
        $this->assertStringContainsString($payment->invoice_number, $decoded);
        $this->assertStringContainsString('Rp 1.500.000', $decoded);
        $this->assertStringContainsString($payment->public_url, $decoded);
    }

    public function test_payments_index_displays_generate_bills_and_wa_buttons(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [$tenantUser, $tenant, $room, $lease] = $this->createActiveLeaseFixture(1500000, '081234567890');

        $service = app(BillingService::class);
        $service->generateMonthlyBills();

        $response = $this->actingAs($admin)->get(route('payments.index'));

        $response->assertOk();
        $response->assertSee('Terbitkan Tagihan Bulanan');
        $response->assertSee('openGenerateBillsModal');
        $response->assertSee('https://wa.me/6281234567890', false);
    }
}
