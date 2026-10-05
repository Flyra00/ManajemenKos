<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Email verification notification failed: ' . $e->getMessage());

            return back()->with('error', 'Layanan pengiriman email sedang dalam proses konfigurasi domain. Silakan hubungi Admin untuk verifikasi akun secara manual.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}
