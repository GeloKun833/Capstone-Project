<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request form.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     * On success, open the reset form immediately (token is also emailed when mail works).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Soft message — do not reveal whether the email exists
        if (! $user) {
            return back()->with(
                'status',
                __('If that email exists in our system, a reset link has been sent.')
            );
        }

        $token = Password::broker()->createToken($user);

        try {
            $user->sendPasswordResetNotification($token);
        } catch (\Throwable $e) {
            report($e);
            // Still allow on-screen reset when mailer is unavailable (e.g. local/dummy email).
        }

        return redirect()
            ->route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ])
            ->with('status', __(Password::RESET_LINK_SENT));
    }
}
