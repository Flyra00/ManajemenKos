<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LeaseService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaseExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function makeTenant(string $ktp): Tenant
    {
        $user = User::factory()->create();
        $user->assignRole('tenant');

        return Tenant::create(['user_id' => $user->id, 'ktp_number' => $ktp]);
    }

    public function test_expired_active_lease_is_closed_and_room_freed(): void
    {
        $tenant = $this->makeTenant('3201000000000101');
        $room = Room::create([
            'room_number' => 'EXP-A1',
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id'   => $room->id,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date'  => now()->subDay()->toDateString(),
            'm_price'   => 1000000,
            'status'    => 'active',
        ]);

        $closed = app(LeaseService::class)->expireOverdueLeases();

        $this->assertSame(1, $closed);
        $this->assertSame('completed', $lease->fresh()->status);
        $this->assertSame('available', $room->fresh()->status);
    }

    public function test_future_lease_is_not_closed(): void
    {
        $tenant = $this->makeTenant('3201000000000102');
        $room = Room::create([
            'room_number' => 'EXP-A2',
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id'   => $room->id,
            'start_date' => now()->toDateString(),
            'end_date'  => now()->addDays(30)->toDateString(),
            'm_price'   => 1000000,
            'status'    => 'active',
        ]);

        $closed = app(LeaseService::class)->expireOverdueLeases();

        $this->assertSame(0, $closed);
        $this->assertSame('active', $lease->fresh()->status);
        $this->assertSame('occupied', $room->fresh()->status);
    }

    public function test_expired_lease_does_not_free_room_that_still_has_another_active_lease(): void
    {
        $tenantA = $this->makeTenant('3201000000000103');
        $tenantB = $this->makeTenant('3201000000000104');
        $room = Room::create([
            'room_number' => 'EXP-A3',
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $expired = Lease::create([
            'tenant_id' => $tenantA->id,
            'room_id'   => $room->id,
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date'  => now()->subDay()->toDateString(),
            'm_price'   => 1000000,
            'status'    => 'active',
        ]);

        $current = Lease::create([
            'tenant_id' => $tenantB->id,
            'room_id'   => $room->id,
            'start_date' => now()->toDateString(),
            'end_date'  => now()->addDays(30)->toDateString(),
            'm_price'   => 1000000,
            'status'    => 'active',
        ]);

        app(LeaseService::class)->expireOverdueLeases();

        $this->assertSame('completed', $expired->fresh()->status);
        $this->assertSame('active', $current->fresh()->status);
        $this->assertSame('occupied', $room->fresh()->status, 'Kamar tetap terisi karena masih ada kontrak aktif lain.');
    }

    public function test_expired_lease_does_not_change_maintenance_room(): void
    {
        $tenant = $this->makeTenant('3201000000000105');
        $room = Room::create([
            'room_number' => 'EXP-A4',
            'price'       => 1000000,
            'status'      => 'maintenance',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id'   => $room->id,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date'  => now()->subDay()->toDateString(),
            'm_price'   => 1000000,
            'status'    => 'active',
        ]);

        app(LeaseService::class)->expireOverdueLeases();

        $this->assertSame('completed', $lease->fresh()->status);
        $this->assertSame('maintenance', $room->fresh()->status);
    }

    public function test_command_closes_expired_leases(): void
    {
        $tenant = $this->makeTenant('3201000000000106');
        $room = Room::create([
            'room_number' => 'EXP-A5',
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id'   => $room->id,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date'  => now()->subDays(3)->toDateString(),
            'm_price'   => 1000000,
            'status'    => 'active',
        ]);

        $this->artisan('kos:expire-leases')->assertExitCode(0);

        $this->assertSame('completed', $lease->fresh()->status);
        $this->assertSame('available', $room->fresh()->status);
    }

    public function test_visiting_dashboard_closes_expired_leases(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $tenant = $this->makeTenant('3201000000000107');
        $room = Room::create([
            'room_number' => 'EXP-A6',
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id'   => $room->id,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date'  => now()->subDay()->toDateString(),
            'm_price'   => 1000000,
            'status'    => 'active',
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->assertSame('completed', $lease->fresh()->status);
        $this->assertSame('available', $room->fresh()->status);
    }
}
