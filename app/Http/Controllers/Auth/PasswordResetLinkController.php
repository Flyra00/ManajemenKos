<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, EmailSecurityService $securityService): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($request->email));

        // Periksa jeda (cooldown) anti-spam bertingkat (5 menit ke-1, 10 menit ke-2+)
        if ($secondsRemaining = $securityService->getCooldownRemaining('password_reset', $email)) {
            $errorMessage = $securityService->getCooldownErrorMessage($secondsRemaining, 'tautan reset kata sandi');

            return back()
                ->withInput($request->only('email'))
                ->with('error', $errorMessage)
                ->withErrors(['email' => $errorMessage]);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            $securityService->recordSentAttempt('password_reset', $email);

            return back()->with('status', __($status));
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
