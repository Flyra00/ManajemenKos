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
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
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
}
