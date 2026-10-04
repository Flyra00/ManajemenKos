<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Pendapatan 6 Bulan Terakhir');
        $response->assertSee('Okupansi Kamar');
    }

    public function test_dashboard_displays_real_aggregated_metrics(): void
    {
        $user = User::factory()->create();

        // Kamar
        $room = Room::create([
            'room_number' => '101',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        // Tenant & Lease
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201123456789999',
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => '2026-09-01',
            'monthly_price'  => 1500000,
            'deposit_amount' => 500000,
            'status'         => 'active',
        ]);

        // Pembayaran Lunas
        Payment::create([
            'lease_id'        => $lease->id,
            'invoice_number'  => 'INV-202609-001',
            'amount'          => 1500000,
            'billing_period'  => '2026-09-01',
            'due_date'        => '2026-09-10',
            'payment_date'    => '2026-09-02',
            'payment_method'  => 'bank_tf',
            'status'          => 'paid',
        ]);

        // Pembayaran Belum Lunas
        Payment::create([
            'lease_id'        => $lease->id,
            'invoice_number'  => 'INV-202609-002',
            'amount'          => 750000,
            'billing_period'  => '2026-09-01',
            'due_date'        => '2026-09-10',
            'payment_method'  => 'bank_tf',
            'status'          => 'unpaid',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('1.500.000'); // Pendapatan
        $response->assertSee('750.000');   // Tagihan belum dibayar
    }

    public function test_dashboard_displays_recent_payments_and_maintenance_requests(): void
    {
        $user = User::factory()->create();

        $room = Room::create([
            'room_number' => '202',
            'floor'       => 2,
            'price'       => 1200000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $tenantUser = User::factory()->create(['name' => 'Budi Santoso']);
        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201123456788888',
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => '2026-09-01',
            'monthly_price'  => 1200000,
            'deposit_amount' => 0,
            'status'         => 'active',
        ]);

        Payment::create([
            'lease_id'        => $lease->id,
            'invoice_number'  => 'INV-202609-999',
            'amount'          => 1200000,
            'billing_period'  => '2026-09-01',
            'due_date'        => '2026-09-10',
            'payment_date'    => '2026-09-02',
            'payment_method'  => 'bank_tf',
            'status'          => 'paid',
        ]);

        MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Lampu Kamar Putus',
            'description' => 'Perlu diganti bohlam LED',
            'priority'    => 'high',
            'status'      => 'reported',
            'reported_at' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Lampu Kamar Putus');
    }
}
