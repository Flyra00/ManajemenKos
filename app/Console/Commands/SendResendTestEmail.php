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

        $this->info("Sending test email [{$type}] to: {$to} using Resend API...");

        try {
            $resend = Resend::client($apiKey);

            if ($type === 'forgot-password') {
                $fakeResetUrl = url('/reset-password/sample-test-token-12345?email=' . urlencode($to));
                $html = view('emails.forgot-password', [
                    'userName' => 'Pengguna KosFly',
                    'resetUrl' => $fakeResetUrl,
                ])->render();
                $subject = 'Atur Ulang Kata Sandi Akun KosFly';
            } else {
                $subject = 'Hello World';
                $html = '<p>Congrats on sending your <strong>first email</strong>!</p>';
            }

            $response = $resend->emails->send([
                'from'    => 'onboarding@resend.dev',
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
            return Command::FAILURE;
        }
    }
}
