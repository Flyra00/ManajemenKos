<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Room;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_landing_page_can_be_rendered(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('KosFly');
        $response->assertSee('Kamar Kosong Siap Huni');
        $response->assertSee('Fasilitas Lengkap');
        $response->assertSee('Cara Sewa');
        $response->assertSee('Tanya Pengelola');
    }

    public function test_landing_page_displays_only_available_rooms(): void
    {
        $facility = Facility::create([
            'name'        => 'AC Dingin',
            'description' => 'Air Conditioner',
        ]);

        $availableRoom = Room::create([
            'room_number' => 'A-101',
            'price'       => 1500000,
            'floor'       => 1,
            'status'      => 'available',
            'is_active'   => true,
        ]);
        $availableRoom->facilities()->attach($facility);

        $occupiedRoom = Room::create([
            'room_number' => 'B-999',
            'price'       => 2000000,
            'floor'       => 2,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Kamar A-101');
        $response->assertSee('1.500.000');
        $response->assertSee('AC Dingin');
        $response->assertSee('Rekomendasi Kamar');
        $response->assertSee('Lihat Detail & Sewa Kamar Ini');

        // Kamar yang occupied TIDAK boleh muncul di showcase kamar kosong
        $response->assertDontSee('Kamar B-999');
    }

    public function test_floating_whatsapp_button_is_rendered(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('wa-float-btn');
        $response->assertSee('https://wa.me/', false);
        $response->assertSee('Tanya Pengelola');
    }

    public function test_login_page_renders_new_room_rental_branding(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Hunian Kos Nyaman');
        $response->assertSee('KosFly Residence');
        $response->assertDontSee('Manajemen Kos');
    }

    public function test_register_page_renders_new_room_rental_branding(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Mulai Tinggal Nyaman');
        $response->assertSee('KosFly Residence');
        $response->assertDontSee('Kelola Operasional Kos');
    }
}
