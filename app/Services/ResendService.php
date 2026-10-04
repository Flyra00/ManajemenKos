<?php

namespace App\Services;

use Resend;
use Exception;

class ResendService
{
    /**
     * Send an email using Resend API.
     *
     * @param string $to
     * @param string $subject
     * @param string $html
     * @param string $from
     * @param string|null $apiKey
     * @return mixed
     * @throws Exception
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $html,
        string $from = 'onboarding@resend.dev',
        ?string $apiKey = null
    ) {
        $key = $apiKey ?: config('services.resend.key', env('RESEND_API_KEY'));

        if (empty($key) || $key === 're_xxxxxxxxx') {
            throw new Exception("Please replace 're_xxxxxxxxx' with your real Resend API key in the .env file (RESEND_API_KEY).");
        }

        $resend = Resend::client($key);

        return $resend->emails->send([
            'from'    => $from,
            'to'      => $to,
            'subject' => $subject,
            'html'    => $html,
        ]);
    }

    /**
     * Send a Password Reset email using Resend and the custom KosFly email template.
     *
     * @param string $to
     * @param string $userName
     * @param string $resetUrl
     * @param string|null $from
     * @return mixed
     * @throws Exception
     */
    public function sendPasswordResetEmail(
        string $to,
        string $userName,
        string $resetUrl,
        ?string $from = null,
        ?string $apiKey = null
    ) {
        $sender = $from ?: config('mail.from.address', 'onboarding@resend.dev');
        $senderName = config('mail.from.name', 'KosFly');
        $fullFrom = "{$senderName} <{$sender}>";

        $html = view('emails.forgot-password', [
            'userName' => $userName,
            'resetUrl' => $resetUrl,
        ])->render();

        return $this->sendEmail(
            to: $to,
            subject: 'Atur Ulang Kata Sandi Akun KosFly',
            html: $html,
            from: $fullFrom,
            apiKey: $apiKey
        );
    }
}
