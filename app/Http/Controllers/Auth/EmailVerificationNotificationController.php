<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request, EmailSecurityService $securityService): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $email = $request->user()->email;

        // Periksa jeda (cooldown) anti-spam bertingkat (5 menit ke-1, 10 menit ke-2+)
        if ($secondsRemaining = $securityService->getCooldownRemaining('email_verification', $email)) {
            $errorMessage = $securityService->getCooldownErrorMessage($secondsRemaining, 'email verifikasi');

            return back()->with('error', $errorMessage);
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            $securityService->recordSentAttempt('email_verification', $email);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Email verification notification failed: ' . $e->getMessage());

            return back()->with('error', 'Layanan pengiriman email sedang dalam proses konfigurasi domain. Silakan hubungi Admin untuk verifikasi akun secara manual.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}
