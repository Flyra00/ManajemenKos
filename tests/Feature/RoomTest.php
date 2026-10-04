<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Lease;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_rooms_index_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Room::create([
            'room_number' => 'A-101',
            'floor' => '1',
            'price' => 1500000,
            'status' => 'available',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('rooms.index'));

        $response->assertOk();
        $response->assertSee('A-101');
    }

    public function test_rooms_can_be_filtered_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Room::create([
            'room_number' => 'A-101',
            'floor' => '1',
            'price' => 1500000,
            'status' => 'available',
        ]);
        Room::create([
            'room_number' => 'B-201',
            'floor' => '2',
            'price' => 1800000,
            'status' => 'occupied',
        ]);

        $response = $this->actingAs($admin)->get(route('rooms.index', ['status' => 'available']));

        $response->assertOk();
        $response->assertSee('A-101');
        $response->assertDontSee('B-201');
    }

    public function test_rooms_create_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('rooms.create'));

        $response->assertOk();
        $response->assertSee('Tambah Kamar');
    }

    public function test_room_can_be_stored_with_facilities_and_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $facility = Facility::create(['name' => 'WiFi']);

        $image = UploadedFile::fake()->image('room.jpg');

        $response = $this->actingAs($admin)->post(route('rooms.store'), [
            'room_number' => 'C-301',
            'floor' => '3',
            'price' => 2000000,
            'status' => 'available',
            'is_active' => '1',
            'description' => 'Kamar luas di lantai 3',
            'image' => $image,
            'facilities' => [$facility->id],
        ]);

        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'room_number' => 'C-301',
            'floor' => '3',
            'price' => 2000000,
        ]);

        $room = Room::where('room_number', 'C-301')->first();
        $this->assertTrue($room->facilities->contains($facility));
        Storage::disk('public')->assertExists($room->image);
    }

    public function test_room_number_must_be_unique(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Room::create([
            'room_number' => 'A-101',
            'floor' => '1',
            'price' => 1500000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)->post(route('rooms.store'), [
            'room_number' => 'A-101',
            'price' => 1200000,
            'status' => 'available',
        ]);

        $response->assertSessionHasErrors('room_number');
    }

    public function test_room_show_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $room = Room::create([
            'room_number' => 'A-102',
            'floor' => '1',
            'price' => 1500000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)->get(route('rooms.show', $room));

        $response->assertOk();
        $response->assertSee('A-102');
    }

    public function test_room_edit_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $room = Room::create([
            'room_number' => 'A-103',
            'floor' => '1',
            'price' => 1500000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)->get(route('rooms.edit', $room));

        $response->assertOk();
        $response->assertSee('A-103');
    }

    public function test_room_can_be_updated(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $room = Room::create([
            'room_number' => 'A-104',
            'floor' => '1',
            'price' => 1500000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)->put(route('rooms.update', $room), [
            'room_number' => 'A-104-Renovated',
            'floor' => '1',
            'price' => 1700000,
            'status' => 'occupied',
            'is_active' => '1',
            'description' => 'Sudah direnovasi',
        ]);

        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'room_number' => 'A-104-Renovated',
            'price' => 1700000,
            'status' => 'occupied',
        ]);
    }

    public function test_room_can_be_deleted_safely(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $room = Room::create([
            'room_number' => 'D-401',
            'floor' => '4',
            'price' => 2500000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)->delete(route('rooms.destroy', $room));

        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('rooms', [
            'id' => $room->id,
        ]);
    }

    public function test_room_cannot_be_deleted_when_has_active_lease(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201111122223333',
        ]);

        $room = Room::create([
            'room_number' => 'D-402',
            'floor' => '4',
            'price' => 2500000,
            'status' => 'occupied',
        ]);

        Lease::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'm_price' => 2500000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('rooms.destroy', $room));

        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
        ]);
    }
}

