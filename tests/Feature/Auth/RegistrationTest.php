<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('data-toggle-pw="regPassword"', false);
        $response->assertSee('data-toggle-pw="regConfirm"', false);
        $response->assertSee('id="strengthBar"', false);
        $response->assertSee('app', false);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'phone'                 => '081234567899',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_user_registration_saves_phone_number(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Dafa User',
            'email'                 => 'dafa@example.com',
            'phone'                 => '081234567890',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'dafa@example.com',
            'phone' => '081234567890',
        ]);
    }

    public function test_registration_fails_when_email_already_exists(): void
    {
        \App\Models\User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name'                  => 'New User',
            'email'                 => 'existing@example.com',
            'phone'                 => '081234567800',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_fails_when_phone_already_exists(): void
    {
        \App\Models\User::factory()->create(['phone' => '081234567890']);

        $response = $this->post('/register', [
            'name'                  => 'Another User',
            'email'                 => 'another@example.com',
            'phone'                 => '081234567890',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertGuest();
    }
}
