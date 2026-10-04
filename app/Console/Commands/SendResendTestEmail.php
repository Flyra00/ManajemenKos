<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Resend;

class SendResendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:test 
                            {--to=xiktronz@gmail.com : Recipient email address}
                            {--from= : Optional sender email address (default: onboarding@resend.dev)}
                            {--key= : Optional Resend API key to override .env}
                            {--type=forgot-password : Email type (greeting or forgot-password)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email using Resend API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = $this->option('key') ?: config('services.resend.key', env('RESEND_API_KEY', 're_xxxxxxxxx'));
        $to = $this->option('to');
        $type = $this->option('type');

        if (empty($apiKey) || $apiKey === 're_xxxxxxxxx') {
            $this->error("API Key is currently set to 're_xxxxxxxxx'!");
            $this->warn("Please replace 're_xxxxxxxxx' with your real Resend API key in your .env file:");
            $this->line("  RESEND_API_KEY=re_your_actual_key_here\n");
            $this->line("Or pass it directly using --key option:");
            $this->line("  php artisan resend:test --key=re_your_actual_key_here\n");
            return Command::FAILURE;
        }

        $fromOpt = $this->option('from') ?: config('mail.from.address', 'onboarding@resend.dev');
        if (empty($fromOpt) || $fromOpt === 'hello@example.com') {
            $fromOpt = 'onboarding@resend.dev';
        }
        $fromName = config('mail.from.name', 'KosFly');
        $from = str_contains($fromOpt, '<') ? $fromOpt : "{$fromName} <{$fromOpt}>";

        $this->info("Sending test email [{$type}] to: {$to} (from: {$from}) using Resend API...");

        try {
            $resend = Resend::client($apiKey);

            if ($type === 'forgot-password') {
                $user = \App\Models\User::where('email', $to)->first();
                if ($user) {
                    $token = \Illuminate\Support\Facades\Password::createToken($user);
                    $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $to], false));
                    $userName = $user->name;
                    $this->info("Generated genuine password reset token for registered user: {$user->email}");
                } else {
                    $resetUrl = url(route('password.reset', ['token' => 'sample-test-token-12345', 'email' => $to], false));
                    $userName = 'Pengguna KosFly';
                    $this->warn("User {$to} not found in database. Using mock reset token.");
                }

                $html = view('emails.forgot-password', [
                    'userName' => $userName,
                    'resetUrl' => $resetUrl,
                ])->render();
                $subject = 'Atur Ulang Kata Sandi Akun KosFly';
            } else {
                $subject = 'Hello World';
                $html = '<p>Congrats on sending your <strong>first email</strong>!</p>';
            }

            $response = $resend->emails->send([
                'from'    => $from,
                'to'      => $to,
                'subject' => $subject,
                'html'    => $html,
            ]);

            $this->info("Email sent successfully!");
            if (isset($response->id)) {
                $this->line("Resend Email ID: " . $response->id);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            if (str_contains(strtolower($e->getMessage()), 'domain') || str_contains(strtolower($e->getMessage()), 'verify') || str_contains(strtolower($e->getMessage()), 'validation')) {
                $this->warn("\nCatatan: Pengirim selain onboarding@resend.dev mengharuskan domain Anda sudah diverifikasi di Resend Dashboard (Domains -> Add Domain).");
            }
            return Command::FAILURE;
        }
    }
}
