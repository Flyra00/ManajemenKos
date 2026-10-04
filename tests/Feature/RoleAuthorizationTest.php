<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_can_access_all_routes_and_create(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('rooms.index'))->assertOk();
        $this->actingAs($admin)->get(route('rooms.create'))->assertOk();
        $this->actingAs($admin)->get(route('facilities.index'))->assertOk();
        $this->actingAs($admin)->get(route('facilities.create'))->assertOk();
        $this->actingAs($admin)->get(route('tenants.index'))->assertOk();
        $this->actingAs($admin)->get(route('tenants.create'))->assertOk();
        $this->actingAs($admin)->get(route('leases.index'))->assertOk();
        $this->actingAs($admin)->get(route('leases.create'))->assertOk();
        $this->actingAs($admin)->get(route('payments.index'))->assertOk();
        $this->actingAs($admin)->get(route('payments.create'))->assertOk();
        $this->actingAs($admin)->get(route('expenses.index'))->assertOk();
        $this->actingAs($admin)->get(route('expenses.create'))->assertOk();
        $this->actingAs($admin)->get(route('reports.index'))->assertOk();
        $this->actingAs($admin)->get(route('settings.index'))->assertOk();
    }

    public function test_owner_can_access_all_view_routes(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $room = Room::create([
            'room_number' => '101',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('rooms.index'))->assertOk();
        $this->actingAs($owner)->get(route('rooms.show', $room))->assertOk();
        $this->actingAs($owner)->get(route('facilities.index'))->assertOk();
        $this->actingAs($owner)->get(route('tenants.index'))->assertOk();
        $this->actingAs($owner)->get(route('leases.index'))->assertOk();
        $this->actingAs($owner)->get(route('payments.index'))->assertOk();
        $this->actingAs($owner)->get(route('maintenance.index'))->assertOk();
        $this->actingAs($owner)->get(route('expenses.index'))->assertOk();
        $this->actingAs($owner)->get(route('reports.index'))->assertOk();
        $this->actingAs($owner)->get(route('settings.index'))->assertOk();
    }

    public function test_owner_is_forbidden_from_mutating_data(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $room = Room::create([
            'room_number' => '102',
            'floor'       => 1,
            'price'       => 1200000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        // Owner dilarang membuka form create atau mengirim request store
        $this->actingAs($owner)->get(route('rooms.create'))->assertForbidden();
        $this->actingAs($owner)->post(route('rooms.store'), [
            'room_number' => '103',
            'floor'       => 1,
            'price'       => 1000000,
            'status'      => 'available',
        ])->assertForbidden();

        // Owner dilarang membuka form edit atau mengirim update
        $this->actingAs($owner)->get(route('rooms.edit', $room))->assertForbidden();
        $this->actingAs($owner)->put(route('rooms.update', $room), [
            'room_number' => '102-B',
            'floor'       => 1,
            'price'       => 1300000,
            'status'      => 'available',
        ])->assertForbidden();

        // Owner dilarang menghapus data
        $this->actingAs($owner)->delete(route('rooms.destroy', $room))->assertForbidden();

        // Owner dilarang mengubah informasi kos atau peran user di pengaturan
        $this->actingAs($owner)->put(route('settings.kos'), [
            'name' => 'Kos Diubah Owner',
        ])->assertForbidden();

        $targetUser = User::factory()->create();
        $this->actingAs($owner)->put(route('settings.users.role', $targetUser), [
            'role' => 'admin',
        ])->assertForbidden();
    }

    public function test_owner_views_do_not_render_creation_or_delete_buttons(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Room::create([
            'room_number' => '105',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $response = $this->actingAs($owner)->get(route('rooms.index'));
        $response->assertOk();
        // Link pembuatan kamar tidak boleh muncul
        $response->assertDontSee(route('rooms.create'));

        $responseDashboard = $this->actingAs($owner)->get(route('dashboard'));
        $responseDashboard->assertOk();
        $responseDashboard->assertDontSee(route('payments.create'));
        $responseDashboard->assertDontSee(route('rooms.create'));
        $responseDashboard->assertDontSee(route('maintenance.create'));
    }


    public function test_tenant_is_forbidden_from_accessing_administrative_routes(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $this->actingAs($tenant)->get(route('rooms.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('facilities.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('tenants.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('leases.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('payments.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('expenses.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('settings.index'))->assertForbidden();
    }

    public function test_tenant_can_access_dashboard_and_maintenance(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $this->actingAs($tenant)->get(route('dashboard'))->assertOk();
        $this->actingAs($tenant)->get(route('maintenance.index'))->assertOk();
    }

    public function test_user_without_role_is_forbidden_from_administrative_routes(): void
    {
        // User tanpa role apa pun tidak boleh menembus area pengelola.
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('rooms.index'))->assertForbidden();
        $this->actingAs($user)->get(route('rooms.create'))->assertForbidden();
        $this->actingAs($user)->get(route('facilities.index'))->assertForbidden();
        $this->actingAs($user)->get(route('tenants.index'))->assertForbidden();
        $this->actingAs($user)->get(route('leases.index'))->assertForbidden();
        $this->actingAs($user)->get(route('payments.index'))->assertForbidden();
        $this->actingAs($user)->get(route('expenses.index'))->assertForbidden();
        $this->actingAs($user)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($user)->get(route('settings.index'))->assertForbidden();

        // Aksi tulis admin juga harus ditolak.
        $this->actingAs($user)->post(route('rooms.store'), [
            'room_number' => 'X-001',
            'floor'       => 1,
            'price'       => 1000000,
            'status'      => 'available',
        ])->assertForbidden();
    }

    public function test_user_without_role_is_forbidden_from_admin_only_maintenance_actions(): void
    {
        $user = User::factory()->create();

        $tenantUser = User::factory()->create();
        $tenant = \App\Models\Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201000000000099',
        ]);
        $room = Room::create([
            'room_number' => 'R-001',
            'floor'       => 1,
            'price'       => 1000000,
            'status'      => 'available',
            'is_active'   => true,
        ]);
        $maint = \App\Models\MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Uji Akses',
            'description' => 'Uji akses middleware.',
            'priority'    => 'low',
            'status'      => 'reported',
            'reported_at' => now(),
        ]);

        $this->actingAs($user)->get(route('maintenance.edit', $maint))->assertForbidden();
    }

    public function test_staff_can_access_maintenance_payments_expenses_and_reports_and_mutate(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $tenantUser = User::factory()->create();
        $tenant = \App\Models\Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201000000000088',
        ]);
        $room = Room::create([
            'room_number' => 'S-101',
            'floor'       => 1,
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);
        $maint = \App\Models\MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Pintu Rusak',
            'description' => 'Engsel patah.',
            'priority'    => 'medium',
            'status'      => 'reported',
            'reported_at' => now(),
        ]);

        // Staff can access dashboard
        $this->actingAs($staff)->get(route('dashboard'))->assertOk();

        // Staff can access maintenance index, create, edit
        $this->actingAs($staff)->get(route('maintenance.index'))->assertOk();
        $this->actingAs($staff)->get(route('maintenance.create'))->assertOk();
        $this->actingAs($staff)->get(route('maintenance.edit', $maint))->assertOk();

        // Staff can access payments index, create
        $this->actingAs($staff)->get(route('payments.index'))->assertOk();
        $this->actingAs($staff)->get(route('payments.create'))->assertOk();

        // Staff can access expenses index, create
        $this->actingAs($staff)->get(route('expenses.index'))->assertOk();
        $this->actingAs($staff)->get(route('expenses.create'))->assertOk();

        // Staff can access reports index, export
        $this->actingAs($staff)->get(route('reports.index'))->assertOk();
        $this->actingAs($staff)->get(route('reports.export'))->assertOk();
    }

    public function test_staff_is_forbidden_from_rooms_facilities_tenants_leases_and_settings(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        // Staff dilarang mengakses modul Kamar
        $this->actingAs($staff)->get(route('rooms.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('rooms.create'))->assertForbidden();

        // Staff dilarang mengakses modul Fasilitas
        $this->actingAs($staff)->get(route('facilities.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('facilities.create'))->assertForbidden();

        // Staff dilarang mengakses modul Penghuni
        $this->actingAs($staff)->get(route('tenants.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('tenants.create'))->assertForbidden();

        // Staff dilarang mengakses modul Kontrak Sewa
        $this->actingAs($staff)->get(route('leases.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('leases.create'))->assertForbidden();

        // Staff dilarang mengakses Pengaturan Sistem
        $this->actingAs($staff)->get(route('settings.index'))->assertForbidden();
    }

    public function test_staff_sidebar_only_renders_allowed_menus(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $response = $this->actingAs($staff)->get(route('dashboard'));
        $response->assertOk();

        // Menu yang diizinkan untuk staff
        $response->assertSee(route('maintenance.index'));
        $response->assertSee(route('payments.index'));
        $response->assertSee(route('expenses.index'));
        $response->assertSee(route('reports.index'));
        $response->assertSee('KosFly Staff');

        // Menu yang disembunyikan dari staff
        $response->assertDontSee(route('rooms.index'));
        $response->assertDontSee(route('facilities.index'));
        $response->assertDontSee(route('tenants.index'));
        $response->assertDontSee(route('leases.index'));
        $response->assertDontSee(route('settings.index'));
    }
}
