<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_tenant_sees_personalized_dashboard_with_their_room_and_lease(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'user_id'    => $user->id,
            'ktp_number' => '3201123456780001',
        ]);

        $facility = Facility::create(['name' => 'WiFi Super Cepat']);
        $room = Room::create([
            'room_number' => 'C-01',
            'floor'       => '1',
            'price'       => 1500000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);
        $room->facilities()->attach($facility);

        Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->subDays(5)->toDateString(),
            'end_date'       => now()->addDays(25)->toDateString(),
            'monthly_price'  => 1500000,
            'deposit_amount' => 0,
            'status'         => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Halo, Budi Santoso!');
        $response->assertSee('Portal Penghuni');
        $response->assertSee('Kamar C-01');
        $response->assertSee('WiFi Super Cepat');
        $response->assertDontSee('Masa Aktif Kontrak');
        $response->assertSee('KosFly Penghuni');
    }

    public function test_tenant_does_not_see_admin_operational_metrics(): void
    {
        $user = User::factory()->create(['name' => 'Siti Aisyah']);
        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'user_id'    => $user->id,
            'ktp_number' => '3201123456780002',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        // Metrik operasional admin tidak boleh terlihat oleh tenant
        $response->assertDontSee('Total Kamar');
        $response->assertDontSee('Pendapatan Bulan Ini');
        $response->assertDontSee('dari 0 total penghuni');
    }

    public function test_tenant_with_unpaid_bill_sees_payment_call_to_action(): void
    {
        $user = User::factory()->create(['name' => 'Rian Pratama']);
        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'user_id'    => $user->id,
            'ktp_number' => '3201123456780003',
        ]);

        $room = Room::create([
            'room_number' => 'D-02',
            'floor'       => '2',
            'price'       => 1600000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->toDateString(),
            'end_date'       => now()->addMonths(1)->toDateString(),
            'monthly_price'  => 1600000,
            'deposit_amount' => 0,
            'status'         => 'active',
        ]);

        Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-202609-9999',
            'amount'         => 1600000,
            'billing_period' => now()->startOfMonth()->toDateString(),
            'due_date'       => now()->addDay()->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('INV-202609-9999');
        $response->assertSee('Ada Tagihan Sewa Menunggu Pembayaran');
        $response->assertSee('Bayar Sekarang / Lihat Invoice');
    }

    public function test_tenant_can_see_their_maintenance_complaints(): void
    {
        $user = User::factory()->create(['name' => 'Eko Susanto']);
        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'user_id'    => $user->id,
            'ktp_number' => '3201123456780004',
        ]);

        $room = Room::create([
            'room_number' => 'E-03',
            'floor'       => '1',
            'price'       => 1200000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'AC Kamar Menetes',
            'description' => 'Air AC menetes deras ke lantai',
            'priority'    => 'high',
            'status'      => 'in_progress',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('AC Kamar Menetes');
        $response->assertSee('Diproses');
        $response->assertSee('Tinggi');
    }

    public function test_admin_and_owner_still_see_operational_dashboard(): void
    {
        $admin = User::factory()->create(['name' => 'Administrator']);
        $admin->assignRole('admin');

        $responseAdmin = $this->actingAs($admin)->get(route('dashboard'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Total Kamar');
        $responseAdmin->assertSee('Pendapatan Bulan Ini');

        $owner = User::factory()->create(['name' => 'Pemilik Kos']);
        $owner->assignRole('owner');

        $responseOwner = $this->actingAs($owner)->get(route('dashboard'));
        $responseOwner->assertOk();
        $responseOwner->assertSee('Total Kamar');
        $responseOwner->assertSee('Pendapatan Bulan Ini');
    }

    public function test_tenant_with_low_days_sees_proactive_renewal_banner(): void
    {
        $user = User::factory()->create(['name' => 'Faisal']);
        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'user_id'    => $user->id,
            'ktp_number' => '3201123456789999',
        ]);

        $room = Room::create([
            'room_number' => 'E-01',
            'floor'       => '1',
            'price'       => 1500000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->subDays(50)->toDateString(),
            'end_date'       => now()->addDays(10)->toDateString(),
            'monthly_price'  => 1500000,
            'deposit_amount' => 0,
            'status'         => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Tersisa 10 Hari Lagi');
        $response->assertSee('Perpanjang Masa Aktif (+30 Hari)');
    }

    public function test_tenant_can_request_renewal_bill_directly(): void
    {
        $user = User::factory()->create(['name' => 'Dedi']);
        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'user_id'    => $user->id,
            'ktp_number' => '3201123456788888',
        ]);

        $room = Room::create([
            'room_number' => 'E-02',
            'floor'       => '1',
            'price'       => 1500000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->subDays(50)->toDateString(),
            'end_date'       => now()->addDays(10)->toDateString(),
            'monthly_price'  => 1500000,
            'deposit_amount' => 0,
            'status'         => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('tenant.leases.request-bill', $lease));

        $payment = Payment::where('lease_id', $lease->id)->first();
        $this->assertNotNull($payment);

        $response->assertRedirect($payment->public_url);
    }
}
