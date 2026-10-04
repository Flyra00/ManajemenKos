<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantMaintenanceScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_tenant_is_directed_to_tenant_dashboard_with_their_real_name(): void
    {
        $tenantUser = User::factory()->create([
            'name'  => 'Domas Salim',
            'email' => 'dims@gmail.com',
        ]);
        $tenantUser->assignRole('tenant');

        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201999988880001',
        ]);

        $response = $this->actingAs($tenantUser)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard-tenant');
        $response->assertSee('Halo, Domas Salim!');
        $response->assertSee('Portal Penghuni');
        $response->assertSee('Domas Salim');
    }

    public function test_tenant_only_sees_their_own_maintenance_requests_on_index(): void
    {
        // Tenant 1 (Target)
        $user1 = User::factory()->create(['name' => 'Domas Salim']);
        $user1->assignRole('tenant');
        $tenant1 = Tenant::create(['user_id' => $user1->id, 'ktp_number' => '1111111111111111']);

        // Tenant 2 (Other)
        $user2 = User::factory()->create(['name' => 'Penghuni Lain']);
        $user2->assignRole('tenant');
        $tenant2 = Tenant::create(['user_id' => $user2->id, 'ktp_number' => '2222222222222222']);

        $room1 = Room::create(['room_number' => 'A-01', 'floor' => 1, 'price' => 1000000, 'status' => 'occupied']);
        $room2 = Room::create(['room_number' => 'B-02', 'floor' => 2, 'price' => 1200000, 'status' => 'occupied']);

        // Request milik Tenant 1
        $maint1 = MaintenanceRequest::create([
            'room_id'     => $room1->id,
            'tenant_id'   => $tenant1->id,
            'title'       => 'AC Kamar Domas Bocor',
            'description' => 'Tetesan air AC membasahi lantai.',
            'priority'    => 'high',
            'status'      => 'reported',
            'cost'        => 0,
            'reported_at' => now(),
        ]);

        // Request milik Tenant 2 (tidak boleh terlihat oleh Tenant 1)
        $maint2 = MaintenanceRequest::create([
            'room_id'     => $room2->id,
            'tenant_id'   => $tenant2->id,
            'title'       => 'Pintu Kamar Mandi Rusak Orang Lain',
            'description' => 'Engsel pintu kamar mandi patah.',
            'priority'    => 'medium',
            'status'      => 'reported',
            'cost'        => 0,
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($user1)->get(route('maintenance.index'));

        $response->assertOk();
        $response->assertSee('AC Kamar Domas Bocor');
        $response->assertDontSee('Pintu Kamar Mandi Rusak Orang Lain');
    }

    public function test_tenant_cannot_view_other_tenants_maintenance_details(): void
    {
        $user1 = User::factory()->create(['name' => 'Domas Salim']);
        $user1->assignRole('tenant');
        $tenant1 = Tenant::create(['user_id' => $user1->id, 'ktp_number' => '1111111111111111']);

        $user2 = User::factory()->create(['name' => 'Penghuni Lain']);
        $user2->assignRole('tenant');
        $tenant2 = Tenant::create(['user_id' => $user2->id, 'ktp_number' => '2222222222222222']);

        $room2 = Room::create(['room_number' => 'B-02', 'floor' => 2, 'price' => 1200000, 'status' => 'occupied']);

        $otherMaint = MaintenanceRequest::create([
            'room_id'     => $room2->id,
            'tenant_id'   => $tenant2->id,
            'title'       => 'Keluhan Rahasia Penghuni Lain',
            'description' => 'Deskripsi sensitif.',
            'priority'    => 'high',
            'status'      => 'reported',
            'cost'        => 0,
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($user1)->get(route('maintenance.show', $otherMaint));
        $response->assertForbidden();
    }

    public function test_tenant_submitting_complaint_is_automatically_assigned_their_tenant_id(): void
    {
        $user = User::factory()->create(['name' => 'Domas Salim']);
        $user->assignRole('tenant');
        $tenant = Tenant::create(['user_id' => $user->id, 'ktp_number' => '1111111111111111']);

        $room = Room::create(['room_number' => 'A-01', 'floor' => 1, 'price' => 1000000, 'status' => 'occupied']);

        Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->toDateString(),
            'end_date'       => now()->addMonth()->toDateString(),
            'monthly_price'  => 1000000,
            'deposit_amount' => 0,
            'status'         => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('maintenance.store'), [
            'room_id'     => $room->id,
            'tenant_id'   => 999,
            'title'       => 'Lampu Kamar Putus',
            'priority'    => 'medium',
            'description' => 'Lampu utama kamar tidur tiba-tiba padam.',
        ]);

        $response->assertRedirect(route('maintenance.index'));

        $this->assertDatabaseHas('maintenance_requests', [
            'title'     => 'Lampu Kamar Putus',
            'tenant_id' => $tenant->id,
            'room_id'   => $room->id,
            'status'    => 'reported',
            'cost'      => 0,
        ]);
    }

    public function test_tenant_cannot_delete_other_tenants_maintenance_request(): void
    {
        $user1 = User::factory()->create(['name' => 'Domas Salim']);
        $user1->assignRole('tenant');
        $tenant1 = Tenant::create(['user_id' => $user1->id, 'ktp_number' => '1111111111111111']);

        $user2 = User::factory()->create(['name' => 'Penghuni Lain']);
        $user2->assignRole('tenant');
        $tenant2 = Tenant::create(['user_id' => $user2->id, 'ktp_number' => '2222222222222222']);

        $room = Room::create(['room_number' => 'C-03', 'floor' => 1, 'price' => 1000000, 'status' => 'occupied']);

        $otherMaint = MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant2->id,
            'title'       => 'Tiket Orang Lain',
            'description' => 'Jangan dihapus.',
            'priority'    => 'low',
            'status'      => 'reported',
            'cost'        => 0,
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($user1)->delete(route('maintenance.destroy', $otherMaint));
        $response->assertForbidden();

        $this->assertDatabaseHas('maintenance_requests', ['id' => $otherMaint->id]);
    }

    public function test_admin_can_still_see_all_maintenance_requests_and_manage_them(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Kos']);
        $admin->assignRole('admin');

        $tenantUser = User::factory()->create(['name' => 'Budi Santoso']);
        $tenantUser->assignRole('tenant');
        $tenant = Tenant::create(['user_id' => $tenantUser->id, 'ktp_number' => '3333333333333333']);

        $room = Room::create(['room_number' => 'D-04', 'floor' => 1, 'price' => 1000000, 'status' => 'occupied']);

        $maint = MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Kerusakan untuk Admin',
            'description' => 'Admin harus melihat ini.',
            'priority'    => 'high',
            'status'      => 'reported',
            'cost'        => 150000,
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('maintenance.index'));
        $response->assertOk();
        $response->assertSee('Kerusakan untuk Admin');

        $detailResponse = $this->actingAs($admin)->get(route('maintenance.show', $maint));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Kerusakan untuk Admin');
    }
}
