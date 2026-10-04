<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_facilities_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Facility::create([
            'name' => 'WiFi Cepat',
            'description' => 'Koneksi internet stabil',
        ]);

        $response = $this->actingAs($user)->get(route('facilities.index'));

        $response->assertOk();
        $response->assertSee('WiFi Cepat');
    }

    public function test_facilities_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('facilities.create'));

        $response->assertOk();
        $response->assertSee('Simpan Fasilitas');
    }

    public function test_facility_can_be_stored(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post(route('facilities.store'), [
            'name' => 'AC Inverter',
            'description' => 'Hemat listrik dan dingin',
        ]);

        $response->assertRedirect(route('facilities.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('facilities', [
            'name' => 'AC Inverter',
        ]);
    }

    public function test_facility_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Facility::create([
            'name' => 'AC Inverter',
        ]);

        $response = $this->actingAs($user)->post(route('facilities.store'), [
            'name' => 'AC Inverter',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_facility_show_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $facility = Facility::create([
            'name' => 'Dapur Bersama',
            'description' => 'Lengkap kompor dan gas',
        ]);

        $response = $this->actingAs($user)->get(route('facilities.show', $facility));

        $response->assertOk();
        $response->assertSee('Dapur Bersama');
    }

    public function test_facility_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $facility = Facility::create([
            'name' => 'Meja Belajar',
            'description' => 'Kayu jati',
        ]);

        $response = $this->actingAs($user)->get(route('facilities.edit', $facility));

        $response->assertOk();
        $response->assertSee('Meja Belajar');
    }

    public function test_facility_can_be_updated(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $facility = Facility::create([
            'name' => 'Lemari 2 Pintu',
            'description' => 'Ada cermin',
        ]);

        $response = $this->actingAs($user)->put(route('facilities.update', $facility), [
            'name' => 'Lemari 3 Pintu',
            'description' => 'Ada cermin dan laci kunci',
        ]);

        $response->assertRedirect(route('facilities.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'name' => 'Lemari 3 Pintu',
        ]);
    }

    public function test_facility_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $facility = Facility::create([
            'name' => 'Kasur Busa',
        ]);

        $response = $this->actingAs($user)->delete(route('facilities.destroy', $facility));

        $response->assertRedirect(route('facilities.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('facilities', [
            'id' => $facility->id,
        ]);
    }
}


