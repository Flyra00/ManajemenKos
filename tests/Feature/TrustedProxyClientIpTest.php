<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Regresi keamanan: di balik proxy hosting, IP klien harus diambil dari header
 * yang ditimpa edge (X-Real-Ip), bukan dari X-Forwarded-For yang bisa dipalsukan
 * klien untuk melewati rate limit (`throttle`).
 */
class TrustedProxyClientIpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_test/client-ip', fn (Request $request) => response()->json([
            'ip' => $request->ip(),
        ]));
    }

    public function test_client_ip_uses_x_real_ip_instead_of_spoofed_x_forwarded_for(): void
    {
        $response = $this->get('/_test/client-ip', [
            'X-Real-Ip'       => '203.0.113.10',
            'X-Forwarded-For' => '1.2.3.4',
        ]);

        $response->assertOk();
        $response->assertJson(['ip' => '203.0.113.10']);
    }

    public function test_invalid_x_real_ip_is_ignored(): void
    {
        $response = $this->get('/_test/client-ip', [
            'X-Real-Ip'       => 'not-an-ip',
            'X-Forwarded-For' => '1.2.3.4',
        ]);

        $response->assertOk();
        // Nilai sampah diabaikan, rantai X-Forwarded-For dibiarkan apa adanya.
        $response->assertJson(['ip' => '1.2.3.4']);
    }

    public function test_private_x_real_ip_is_ignored(): void
    {
        $response = $this->get('/_test/client-ip', [
            'X-Real-Ip'       => '10.0.0.5',
            'X-Forwarded-For' => '203.0.113.77',
        ]);

        $response->assertOk();
        $response->assertJson(['ip' => '203.0.113.77']);
    }
}
