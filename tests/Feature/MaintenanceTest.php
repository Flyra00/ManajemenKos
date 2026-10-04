<?php

namespace Tests\Feature;

use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function createFixture(): array
    {
        $tenantUser = User::factory()->create(['name' => 'Budi Santoso']);
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201889900112233',
        ]);
        $room = Room::create([
            'room_number' => 'M-201',
            'price' => 1200000,
            'floor' => 2,
            'status' => 'occupied',
        ]);
        $technician = User::factory()->create(['name' => 'Pak Joko Teknisi']);

        return [$tenant, $room, $technician];
    }

    public function test_maintenance_index_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        MaintenanceRequest::create([
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'title' => 'Kran Kamar Mandi Bocor',
            'description' => 'Air terus menetes dari kran wastafel.',
            'priority' => 'medium',
            'status' => 'reported',
            'cost' => 50000,
            'handled_by' => $technician->id,
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('maintenance.index'));

        $response->assertOk();
        $response->assertSee('Kran Kamar Mandi Bocor');
        $response->assertSee('Budi Santoso');
        $response->assertSee('M-201');
    }

    public function test_maintenance_can_be_filtered_by_priority_and_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        MaintenanceRequest::create([
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'title' => 'AC Rusak Total',
            'description' => 'Tidak dingin sama sekali dan berisik.',
            'priority' => 'high',
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        MaintenanceRequest::create([
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'title' => 'Ganti Lampu Bohlam',
            'description' => 'Lampu balkon mati.',
            'priority' => 'low',
            'status' => 'completed',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('maintenance.index', ['priority' => 'high']));
        $response->assertOk();
        $response->assertSee('AC Rusak Total');
        $response->assertDontSee('Ganti Lampu Bohlam');

        $response2 = $this->actingAs($admin)->get(route('maintenance.index', ['status' => 'completed']));
        $response2->assertOk();
        $response2->assertSee('Ganti Lampu Bohlam');
        $response2->assertDontSee('AC Rusak Total');
    }

    public function test_maintenance_create_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->createFixture();

        $response = $this->actingAs($admin)->get(route('maintenance.create'));

        $response->assertOk();
        $response->assertSee('Buat Laporan Perbaikan');
    }

    public function test_maintenance_can_be_stored_with_image_upload(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();
        $image = UploadedFile::fake()->image('broken_ac.jpg');

        $response = $this->actingAs($admin)->post(route('maintenance.store'), [
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'title' => 'Pipa Pembuangan Mampet',
            'description' => 'Air tidak mengalir lancar di kamar mandi.',
            'priority' => 'high',
            'status' => 'in_progress',
            'cost' => 150000,
            'handled_by' => $technician->id,
            'image_path' => $image,
            'reported_at' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('maintenance.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('maintenance_requests', [
            'title' => 'Pipa Pembuangan Mampet',
            'cost' => 150000,
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        $maint = MaintenanceRequest::where('title', 'Pipa Pembuangan Mampet')->first();
        $this->assertNotNull($maint->image_path);
        Storage::disk('public')->assertExists($maint->image_path);
    }

    public function test_maintenance_show_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $maint = MaintenanceRequest::create([
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'title' => 'Kunci Pintu Macet',
            'description' => 'Kunci sulit diputar dari luar.',
            'priority' => 'medium',
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('maintenance.show', $maint));

        $response->assertOk();
        $response->assertSee('Kunci Pintu Macet');
    }

    public function test_maintenance_edit_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $maint = MaintenanceRequest::create([
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'title' => 'Cat Tembok Mengelupas',
            'description' => 'Dinding lembab dekat kamar mandi.',
            'priority' => 'low',
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('maintenance.edit', $maint));

        $response->assertOk();
        $response->assertSee('Edit Laporan: Cat Tembok Mengelupas');
    }

    public function test_maintenance_can_be_updated_and_resolved(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $maint = MaintenanceRequest::create([
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'title' => 'Genteng Bocor',
            'description' => 'Tetesan air saat hujan deras.',
            'priority' => 'high',
            'status' => 'in_progress',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put(route('maintenance.update', $maint), [
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'title' => 'Genteng Bocor',
            'description' => 'Tetesan air saat hujan deras - telah ditambal silikon.',
            'priority' => 'high',
            'status' => 'completed',
            'cost' => 75000,
            'handled_by' => $technician->id,
            'reported_at' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('maintenance.index'));
        $response->assertSessionHas('success');

        $maint->refresh();
        $this->assertEquals('completed', $maint->status);
        $this->assertEquals(75000, $maint->cost);
        $this->assertNotNull($maint->resolved_at);
    }

    public function test_maintenance_can_be_deleted_safely(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $maint = MaintenanceRequest::create([
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'title' => 'Laporan Dibatalkan',
            'description' => 'Salah lapor.',
            'priority' => 'low',
            'status' => 'cancelled',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('maintenance.destroy', $maint));

        $response->assertRedirect(route('maintenance.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('maintenance_requests', ['id' => $maint->id]);
    }

    public function test_maintenance_cost_is_automatically_synced_to_expenses_on_creation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $response = $this->actingAs($admin)->post(route('maintenance.store'), [
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Pintu Rusak',
            'description' => 'Engsel pintu rusak.',
            'priority'    => 'medium',
            'status'      => 'reported',
            'cost'        => 65000,
            'handled_by'  => $technician->id,
            'reported_at' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('maintenance.index'));

        $this->assertDatabaseHas('expenses', [
            'amount' => 65000,
            'title'  => "Biaya Perbaikan: Pintu Rusak (Kamar {$room->room_number})",
        ]);
    }

    public function test_maintenance_image_can_be_removed_via_update(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('kerusakan.jpg');
        $storedPath = $file->store('maintenance', 'public');

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $maint = MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Wastafel Mampet',
            'description' => 'Air tidak mengalir lancar.',
            'priority'    => 'medium',
            'status'      => 'reported',
            'image_path'  => $storedPath,
            'cost'        => 0,
            'reported_at' => now(),
        ]);

        $this->assertTrue(Storage::disk('public')->exists($storedPath));

        $response = $this->actingAs($admin)->put(route('maintenance.update', $maint), [
            'room_id'      => $room->id,
            'tenant_id'    => $tenant->id,
            'title'        => 'Wastafel Mampet',
            'description'  => 'Air tidak mengalir lancar.',
            'priority'     => 'medium',
            'status'       => 'reported',
            'remove_image' => 1,
            'cost'         => 0,
            'reported_at'  => now()->toDateString(),
        ]);

        $response->assertRedirect(route('maintenance.index'));
        $maint->refresh();
        $this->assertNull($maint->image_path);
        $this->assertFalse(Storage::disk('public')->exists($storedPath));
    }

    public function test_admin_can_update_maintenance_status_via_quick_action(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $maintenance = MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Kran bocor',
            'description' => 'Air menetes kencang',
            'priority'    => 'medium',
            'status'      => 'reported',
            'cost'        => 0,
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put(route('maintenance.update-status', $maintenance), [
            'status'     => 'in_progress',
            'handled_by' => $technician->id,
            'cost'       => 75000,
        ]);

        $response->assertRedirect(route('maintenance.index'));
        $response->assertSessionHas('success');

        $maintenance->refresh();
        $this->assertEquals('in_progress', $maintenance->status);
        $this->assertEquals($technician->id, $maintenance->handled_by);
        $this->assertEquals(75000, (int)$maintenance->cost);

        // Pastikan biaya perbaikan tersinkron ke modul Pengeluaran
        $this->assertDatabaseHas('expenses', [
            'amount' => 75000,
        ]);
    }

    public function test_updating_status_to_in_progress_requires_staff_and_rejects_tenant(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $maintenance = MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Saklar Lampu Rusak',
            'description' => 'Saklar berbunyi kresek-kresek',
            'priority'    => 'high',
            'status'      => 'reported',
            'reported_at' => now(),
        ]);

        // Coba tanpa handled_by -> harus error validasi
        $resNoStaff = $this->actingAs($admin)->put(route('maintenance.update-status', $maintenance), [
            'status' => 'in_progress',
        ]);
        $resNoStaff->assertSessionHasErrors('handled_by');

        // Coba tunjuk tenant sebagai staff -> harus error validasi
        $resTenantAsStaff = $this->actingAs($admin)->put(route('maintenance.update-status', $maintenance), [
            'status'     => 'in_progress',
            'handled_by' => $tenant->user_id,
        ]);
        $resTenantAsStaff->assertSessionHasErrors('handled_by');

        // Tunjuk teknisi valid -> sukses
        $resSuccess = $this->actingAs($admin)->put(route('maintenance.update-status', $maintenance), [
            'status'     => 'in_progress',
            'handled_by' => $technician->id,
        ]);
        $resSuccess->assertRedirect(route('maintenance.index'));

        $maintenance->refresh();
        $this->assertEquals('in_progress', $maintenance->status);
        $this->assertEquals($technician->id, $maintenance->handled_by);
    }

    public function test_admin_can_complete_maintenance_with_cost_and_completion_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $maintenance = MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Pintu Geser Macet',
            'description' => 'Rel pintu kotor dan berkarat',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'handled_by'  => $technician->id,
            'reported_at' => now(),
        ]);

        $completionPhoto = UploadedFile::fake()->image('hasil_perbaikan.jpg');

        $response = $this->actingAs($admin)->put(route('maintenance.update-status', $maintenance), [
            'status'           => 'completed',
            'cost'             => 120000,
            'completion_image' => $completionPhoto,
        ]);

        $response->assertRedirect(route('maintenance.index'));
        $response->assertSessionHas('success');

        $maintenance->refresh();
        $this->assertEquals('completed', $maintenance->status);
        $this->assertEquals(120000, (int)$maintenance->cost);
        $this->assertNotNull($maintenance->resolved_at);
        $this->assertNotNull($maintenance->completion_image);

        Storage::disk('public')->assertExists($maintenance->completion_image);

        // Pastikan biaya tercatat di tabel expenses
        $this->assertDatabaseHas('expenses', [
            'amount' => 120000,
        ]);
    }

    public function test_tenants_are_excluded_from_staff_list_in_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        [$tenant, $room, $technician] = $this->createFixture();

        $response = $this->actingAs($admin)->get(route('maintenance.index'));
        $response->assertOk();

        // Pastikan list $users di view tidak menyertakan tenant
        $viewUsers = $response->viewData('users');
        $this->assertFalse($viewUsers->contains('id', $tenant->user_id));
        $this->assertTrue($viewUsers->contains('id', $technician->id));
    }
}

