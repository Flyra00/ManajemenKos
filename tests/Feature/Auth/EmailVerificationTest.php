<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/verify-email');
    }

    public function test_registration_sends_verification_notification(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $this->post('/register', [
            'name'                  => 'Verify User',
            'email'                 => 'verify@example.com',
            'phone'                 => '081299998888',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'verify@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->hasVerifiedEmail());

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    public function test_admin_can_access_dashboard_without_manual_email_verification(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->unverified()->create(['email' => 'admin@gmail.com']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_management_email_addresses_no_longer_bypass_verification(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);

        // Email yang dulu di-hardcode kini tidak lagi mem-bypass verifikasi:
        // status diambil dari database (email_verified_at atau peran pengelola).
        $user = User::factory()->unverified()->create(['email' => 'owner@gmail.com']);
        $user->assignRole('tenant');

        $this->assertFalse($user->hasVerifiedEmail());
        $this->actingAs($user)->get('/dashboard')->assertRedirect('/verify-email');
    }

    public function test_management_role_alone_bypasses_verification_from_database(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $staff = User::factory()->unverified()->create(['email' => 'somebody@example.com']);
        $staff->assignRole('staff');

        $this->assertTrue($staff->hasVerifiedEmail());
        $this->actingAs($staff)->get('/dashboard')->assertStatus(200);
    }
}
