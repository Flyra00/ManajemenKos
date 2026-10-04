<?php

namespace Tests\Feature;

use Tests\TestCase;

class ResendIntegrationTest extends TestCase
{
    public function test_resend_command_prompts_when_api_key_is_placeholder()
    {
        config(['services.resend.key' => 're_xxxxxxxxx']);

        $this->artisan('resend:test')
            ->expectsOutput("API Key is currently set to 're_xxxxxxxxx'!")
            ->assertExitCode(1);
    }

    public function test_resend_command_prompts_when_api_key_is_empty()
    {
        config(['services.resend.key' => '']);

        $this->artisan('resend:test')
            ->expectsOutput("API Key is currently set to 're_xxxxxxxxx'!")
            ->assertExitCode(1);
    }

    public function test_forgot_password_email_template_renders_properly()
    {
        $rendered = view('emails.forgot-password', [
            'userName' => 'Budi Santoso',
            'resetUrl' => 'https://kosfly.test/reset-password/sample-token?email=budi@example.com',
        ])->render();

        $this->assertStringContainsString('Budi Santoso', $rendered);
        $this->assertStringContainsString('https://kosfly.test/reset-password/sample-token?email=budi@example.com', $rendered);
        $this->assertStringContainsString('Atur Ulang Kata Sandi', $rendered);
        $this->assertStringContainsString('KosFly', $rendered);
        $this->assertStringContainsString('60 menit', $rendered);
    }

    public function test_resend_service_throws_exception_when_api_key_is_placeholder()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Please replace 're_xxxxxxxxx' with your real Resend API key");

        $service = new \App\Services\ResendService();
        $service->sendPasswordResetEmail(
            to: 'test@example.com',
            userName: 'Test User',
            resetUrl: 'https://kosfly.test/reset',
            apiKey: 're_xxxxxxxxx'
        );
    }
}
