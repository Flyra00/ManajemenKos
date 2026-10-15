<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\EmailSecurityService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailSecurityRateLimiterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Carbon::setTestNow(); // Reset time to actual now
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Pastikan waktu dikembalikan ke normal setelah test
        Cache::flush();
        parent::tearDown();
    }

    public function test_first_password_reset_request_succeeds_and_enforces_5_minute_cooldown(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'tenant1@kosfly.test']);

        // Percobaan ke-1: Harus berhasil
        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('status');
        Notification::assertSentToTimes($user, ResetPassword::class, 1);

        // Percobaan ke-2 langsung (dalam 5 menit): Harus ditolak oleh cooldown
        $blockedResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $blockedResponse->assertRedirect('/forgot-password');
        $blockedResponse->assertSessionHas('error');
        $blockedResponse->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('menit', session('error'));

        // Notification tidak boleh terkirim lagi
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_second_password_reset_request_after_5_minutes_succeeds_and_enforces_10_minute_cooldown(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'tenant2@kosfly.test']);

        // Percobaan ke-1
        $this->post('/forgot-password', ['email' => $user->email]);
        Notification::assertSentToTimes($user, ResetPassword::class, 1);

        // Majukan waktu 305 detik (5 menit 5 detik lewat)
        Carbon::setTestNow(now()->addSeconds(305));

        // Percobaan ke-2: Harus berhasil karena jeda 5 menit telah selesai
        $secondResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $secondResponse->assertRedirect('/forgot-password');
        $secondResponse->assertSessionHas('status');
        Notification::assertSentToTimes($user, ResetPassword::class, 2);

        // Percobaan ke-3 langsung: Sekarang harus diberi jeda 10 menit (600 detik)
        $blockedResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $blockedResponse->assertRedirect('/forgot-password');
        $blockedResponse->assertSessionHas('error');
        $this->assertStringContainsString('menit', session('error'));

        // Pastikan setelah 6 menit (360s), request ke-3 masih ditolak (karena batas sekarang 10 menit)
        Carbon::setTestNow(now()->addSeconds(360));
        $stillBlockedResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => $user->email,
        ]);
        $stillBlockedResponse->assertSessionHas('error');

        // Dan setelah total 10 menit lewat (misal 610s sejak percobaan ke-2), baru bisa kirim lagi
        Carbon::setTestNow(now()->addSeconds(260)); // 360 + 260 = 620 detik
        $thirdSuccessResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => $user->email,
        ]);
        $thirdSuccessResponse->assertSessionHas('status');
        Notification::assertSentToTimes($user, ResetPassword::class, 3);
    }

    public function test_daily_session_resets_on_next_day(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'daily@kosfly.test']);

        // Hari ini: Percobaan 1 (jeda 5 menit) & Percobaan 2 (jeda 10 menit)
        $this->post('/forgot-password', ['email' => $user->email]);
        Carbon::setTestNow(now()->addSeconds(305));
        $this->post('/forgot-password', ['email' => $user->email]);
        Notification::assertSentToTimes($user, ResetPassword::class, 2);

        // Majukan waktu ke besok hari (+1 day)
        Carbon::setTestNow(now()->addDay()->startOfDay()->addHours(9));

        // Pengiriman pertama di hari baru: Harus berhasil
        $newDayResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => $user->email,
        ]);
        $newDayResponse->assertSessionHas('status');
        Notification::assertSentToTimes($user, ResetPassword::class, 3);

        // Cek bahwa durasi jeda kembali ke tingkat 1 (5 menit, bukan 10 menit):
        // Setelah 305 detik, harus sudah bisa request lagi
        Carbon::setTestNow(now()->addSeconds(305));
        $nextResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => $user->email,
        ]);
        $nextResponse->assertSessionHas('status');
        Notification::assertSentToTimes($user, ResetPassword::class, 4);
    }

    public function test_email_verification_resend_enforces_tiered_cooldown(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['email' => 'unverified@kosfly.test']);

        // Kirim ulang verifikasi ke-1: Sukses
        $response = $this->actingAs($user)->from('/verify-email')->post('/email/verification-notification');
        $response->assertRedirect('/verify-email');
        $response->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);

        // Kirim ulang langsung ke-2: Ditolak cooldown 5 menit
        $blocked = $this->actingAs($user)->from('/verify-email')->post('/email/verification-notification');
        $blocked->assertRedirect('/verify-email');
        $blocked->assertSessionHas('error');
        $this->assertStringContainsString('menit', session('error'));
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);

        // Majukan waktu 305 detik
        Carbon::setTestNow(now()->addSeconds(305));

        // Kirim ulang ke-2: Sukses
        $response2 = $this->actingAs($user)->from('/verify-email')->post('/email/verification-notification');
        $response2->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentToTimes($user, VerifyEmail::class, 2);

        // Kirim ulang langsung ke-3: Ditolak cooldown 10 menit
        $blocked2 = $this->actingAs($user)->from('/verify-email')->post('/email/verification-notification');
        $blocked2->assertSessionHas('error');
        Notification::assertSentToTimes($user, VerifyEmail::class, 2);
    }

    public function test_registration_sets_initial_verification_cooldown(): void
    {
        Notification::fake();

        // Registrasi mengirim email verifikasi awal
        $this->post('/register', [
            'name'                  => 'Budi Baru',
            'email'                 => 'budibaru@kosfly.test',
            'phone'                 => '081234567888',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'budibaru@kosfly.test')->first();
        $this->assertNotNull($user);
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);

        // Jika user langsung klik "Kirim Ulang Email Verifikasi", harus ditolak karena registrasi sudah mengirim email
        $blocked = $this->actingAs($user)->from('/verify-email')->post('/email/verification-notification');
        $blocked->assertRedirect('/verify-email');
        $blocked->assertSessionHas('error');
        $this->assertStringContainsString('menit', session('error'));
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_admin_reset_password_enforces_tiered_cooldown(): void
    {
        $this->seed(RoleSeeder::class);
        Notification::fake();

        $admin = User::factory()->create(['email' => 'admin@kosfly.test']);
        $admin->assignRole('admin');

        $tenantUser = User::factory()->create(['email' => 'target@kosfly.test']);
        $tenantUser->assignRole('tenant');

        // Admin kirim reset link pertama: Sukses
        $response = $this->actingAs($admin)->from('/users')->put(route('users.reset-password', $tenantUser));
        $response->assertRedirect('/users');
        $response->assertSessionHas('success');
        Notification::assertSentToTimes($tenantUser, ResetPassword::class, 1);

        // Admin klik lagi langsung: Ditolak dengan pesan cooldown
        $blocked = $this->actingAs($admin)->from('/users')->put(route('users.reset-password', $tenantUser));
        $blocked->assertRedirect('/users');
        $blocked->assertSessionHas('error');
        $this->assertStringContainsString('menit', session('error'));
        Notification::assertSentToTimes($tenantUser, ResetPassword::class, 1);
    }
}
