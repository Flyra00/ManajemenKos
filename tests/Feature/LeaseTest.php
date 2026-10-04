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

class LeaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_leases_index_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create(['name' => 'Doni Kusuma']);
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201112233445566',
        ]);
        $room = Room::create([
            'room_number' => 'L-101',
            'price' => 1200000,
            'status' => 'occupied',
        ]);

        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'm_price' => 1200000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('leases.index'));

        $response->assertOk();
        $response->assertSee('Doni Kusuma');
        $response->assertSee('Kamar L-101');
    }

    public function test_leases_create_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('leases.create'));

        $response->assertOk();
        $response->assertSee('Tambah Kontrak Sewa');
    }

    public function test_lease_can_be_stored_and_marks_room_occupied(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201112233445577',
        ]);
        $room = Room::create([
            'room_number' => 'L-102',
            'price' => 1500000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)->post(route('leases.store'), [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'monthly_price' => 1500000,
            'deposit_amount' => 500000,
            'status' => 'active',
            'note' => 'Perjanjian 6 bulan',
        ]);

        $response->assertRedirect(route('leases.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leases', [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'm_price' => 1500000,
            'status' => 'active',
        ]);

        $room->refresh();
        $this->assertEquals('occupied', $room->status);
    }

    public function test_lease_show_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create(['name' => 'Maya Indah']);
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201112233445588',
        ]);
        $room = Room::create([
            'room_number' => 'L-103',
            'price' => 1300000,
            'status' => 'occupied',
        ]);
        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'm_price' => 1300000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('leases.show', $lease));

        $response->assertOk();
        $response->assertSee('Maya Indah');
        $response->assertSee('Kamar L-103');
    }

    public function test_lease_edit_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201112233445599',
        ]);
        $room = Room::create([
            'room_number' => 'L-104',
            'price' => 1400000,
            'status' => 'occupied',
        ]);
        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'm_price' => 1400000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('leases.edit', $lease));

        $response->assertOk();
        $response->assertSee('Edit Kontrak Sewa');
    }

    public function test_lease_can_be_updated_and_syncs_room_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201112233445510',
        ]);
        $room = Room::create([
            'room_number' => 'L-105',
            'price' => 1600000,
            'status' => 'occupied',
        ]);
        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'm_price' => 1600000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put(route('leases.update', $lease), [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'monthly_price' => 1600000,
            'deposit_amount' => 0,
            'status' => 'completed',
        ]);

        $response->assertRedirect(route('leases.index'));
        $response->assertSessionHas('success');

        $lease->refresh();
        $this->assertEquals('completed', $lease->status);

        $room->refresh();
        $this->assertEquals('available', $room->status);
    }

    public function test_lease_can_be_deleted_safely_and_frees_room(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201112233445511',
        ]);
        $room = Room::create([
            'room_number' => 'L-106',
            'price' => 1700000,
            'status' => 'occupied',
        ]);
        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'm_price' => 1700000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('leases.destroy', $lease));

        $response->assertRedirect(route('leases.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('leases', ['id' => $lease->id]);

        $room->refresh();
        $this->assertEquals('available', $room->status);
    }

    public function test_lease_cannot_be_deleted_when_has_paid_payments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201112233445512',
        ]);
        $room = Room::create([
            'room_number' => 'L-107',
            'price' => 1800000,
            'status' => 'occupied',
        ]);
        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'm_price' => 1800000,
            'status' => 'active',
        ]);

        Payment::create([
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-2026-0001',
            'amount' => 1800000,
            'billing_period' => 'September 2026',
            'due_date' => now()->addDays(5)->toDateString(),
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_tf',
            'status' => 'paid',
        ]);


        $response = $this->actingAs($admin)->delete(route('leases.destroy', $lease));

        $response->assertRedirect(route('leases.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('leases', ['id' => $lease->id]);
    }
}

