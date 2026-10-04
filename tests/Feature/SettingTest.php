<?php

namespace Tests\Feature;

use App\Models\KosSetting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guests_cannot_access_settings_page(): void
    {
        $response = $this->get(route('settings.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_settings_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan & Konfigurasi', false);
        $response->assertSee('Informasi Operasional Kos');
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name'  => 'Nama Lama',
            'email' => 'lama@example.com',
            'phone' => '081234567890',
        ]);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->put(route('settings.profile'), [
            'name'  => 'Nama Baru',
            'email' => 'baru@example.com',
            'phone' => '081299998888',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Nama Baru',
            'email' => 'baru@example.com',
            'phone' => '081299998888',
        ]);
    }

    public function test_authenticated_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->put(route('settings.password'), [
            'current_password'      => 'old-password',
            'password'              => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-strong-password', $user->fresh()->password));
    }

    public function test_authenticated_user_can_update_kos_info(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->put(route('settings.kos'), [
            'name'         => 'Kos Mawar Indah',
            'address'      => 'Jl. Teratai No. 45',
            'phone'        => '081233445566',
            'email'        => 'info@kosmawar.com',
            'latitude'     => -6.9745,
            'longitude'    => 107.6310,
            'map_zoom'     => 17,
            'billing_due'  => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kos_settings', [
            'name'        => 'Kos Mawar Indah',
            'address'     => 'Jl. Teratai No. 45',
            'phone'       => '081233445566',
            'email'       => 'info@kosmawar.com',
            'map_zoom'    => 17,
            'billing_due' => 5,
        ]);

        $settings = KosSetting::current();
        $this->assertEquals(-6.9745, $settings->latitude);
        $this->assertEquals(107.6310, $settings->longitude);
    }

    public function test_kos_settings_defaults_are_returned_before_any_save(): void
    {
        $this->assertDatabaseCount('kos_settings', 0);

        $settings = KosSetting::settings();

        $this->assertSame('KosFly Residence', $settings['name']);
        $this->assertSame(10, $settings['billing_due']);
        $this->assertSame(16, $settings['map_zoom']);
    }

    public function test_kos_settings_are_persisted_and_read_back_from_database(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->put(route('settings.kos'), [
            'name'        => 'Kos Melati',
            'address'     => 'Jl. Melati No. 7',
            'phone'       => '081200001111',
            'email'       => 'halo@kosmelati.com',
            'latitude'    => -6.9,
            'longitude'   => 107.6,
            'map_zoom'    => 15,
            'billing_due' => 20,
        ]);

        // Dibaca ulang tanpa state apa pun: sumbernya harus database, bukan file.
        $settings = KosSetting::settings();

        $this->assertSame('Kos Melati', $settings['name']);
        $this->assertSame('Jl. Melati No. 7', $settings['address']);
        $this->assertSame(20, $settings['billing_due']);
        $this->assertSame(15, $settings['map_zoom']);
    }

    public function test_kos_settings_stay_in_a_single_row(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->put(route('settings.kos'), ['name' => 'Kos Pertama', 'billing_due' => 5]);
        $this->actingAs($admin)->put(route('settings.kos'), ['name' => 'Kos Kedua', 'billing_due' => 7]);

        $this->assertDatabaseCount('kos_settings', 1);
        $this->assertDatabaseHas('kos_settings', ['name' => 'Kos Kedua', 'billing_due' => 7]);
    }

    public function test_kos_settings_ignore_the_legacy_json_file(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->put(route('settings.kos'), [
            'name'        => 'Kos Dari Database',
            'billing_due' => 12,
        ]);

        // File lama diisi nilai berbeda: nilai database harus tetap menang.
        $path = storage_path('app/kos_settings.json');
        file_put_contents($path, json_encode(['name' => 'Kos Dari File Lama', 'billing_due' => 99]));

        try {
            $settings = KosSetting::settings();

            $this->assertSame('Kos Dari Database', $settings['name']);
            $this->assertSame(12, $settings['billing_due']);
        } finally {
            @unlink($path);
        }
    }

    public function test_user_role_can_be_updated(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('tenant');

        $response = $this->actingAs($admin)->put(route('settings.users.role', $targetUser), [
            'role' => 'owner',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue($targetUser->fresh()->hasRole('owner'));
    }
}
