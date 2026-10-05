<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Atur Ulang Kata Sandi Akun KosFly')
                ->view('emails.forgot-password', [
                    'userName' => $notifiable->name ?? 'Pengguna',
                    'resetUrl' => $resetUrl,
                ]);
        });

        \Illuminate\Auth\Notifications\VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Verifikasi Alamat Email Anda — KosFly')
                ->view('emails.verify-email', [
                    'userName' => $notifiable->name ?? 'Pengguna',
                    'verifyUrl' => $url,
                ]);
        });
    }
}
