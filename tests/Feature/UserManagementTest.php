<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guests_cannot_access_users_index(): void
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_tenants_cannot_access_users_index(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $response = $this->actingAs($tenant)->get(route('users.index'));
        $response->assertStatus(403);
    }

    public function test_staff_cannot_access_users_index(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $response = $this->actingAs($staff)->get(route('users.index'));
        $response->assertStatus(403);
    }

    public function test_admin_and_owner_can_view_users_page(): void
    {
        $admin = User::factory()->create(['name' => 'Budi Admin']);
        $admin->assignRole('admin');

        $owner = User::factory()->create(['name' => 'Siti Owner']);
        $owner->assignRole('owner');

        // Admin check
        $adminResponse = $this->actingAs($admin)->get(route('users.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Manajemen Pengguna');
        $adminResponse->assertSee('Budi Admin');
        $adminResponse->assertSee('Siti Owner');

        // Owner check
        $ownerResponse = $this->actingAs($owner)->get(route('users.index'));
        $ownerResponse->assertOk();
        $ownerResponse->assertSee('Manajemen Pengguna');
    }

    public function test_admin_can_update_user_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create(['name' => 'Calon Staff']);
        $targetUser->assignRole('tenant');

        $response = $this->actingAs($admin)->put(route('users.role', $targetUser), [
            'role' => 'staff',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue($targetUser->fresh()->hasRole('staff'));
    }

    public function test_users_can_be_filtered_by_role_and_keyword(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Utama', 'email' => 'adminutama@example.com']);
        $admin->assignRole('admin');

        $tenant = User::factory()->create(['name' => 'Anak Kos', 'email' => 'anakkos@example.com']);
        $tenant->assignRole('tenant');

        // Filter search keyword
        $response = $this->actingAs($admin)->get(route('users.index', ['q' => 'Anak Kos']));
        $response->assertOk();
        $users = $response->viewData('users');
        $this->assertTrue($users->contains('name', 'Anak Kos'));
        $this->assertFalse($users->contains('name', 'Admin Utama'));

        // Filter role
        $roleResponse = $this->actingAs($admin)->get(route('users.index', ['role' => 'admin']));
        $roleResponse->assertOk();
        $roleUsers = $roleResponse->viewData('users');
        $this->assertTrue($roleUsers->contains('name', 'Admin Utama'));
        $this->assertFalse($roleUsers->contains('name', 'Anak Kos'));
    }

    public function test_admin_can_reset_user_password_to_default(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'password' => \Illuminate\Support\Facades\Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($admin)->put(route('users.reset-password', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password123', $user->fresh()->password));
    }

    public function test_admin_can_manually_verify_user_email(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $this->assertFalse($user->hasVerifiedEmail());

        $response = $this->actingAs($admin)->put(route('users.verify-email', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
