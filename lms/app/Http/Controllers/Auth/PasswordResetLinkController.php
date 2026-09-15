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
        }

        $request->session()->put('password_reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        return redirect()
            ->route('password.reset.continue')
            ->with('status', __('If that email exists in our system, you can set a new password on the next page.'));
    }

    /**
     * Show the reset form from a one-time session payload (token never goes in the URL).
     */
    public function continueReset(Request $request): View|RedirectResponse
    {
        $payload = $request->session()->get('password_reset');
        if (! is_array($payload) || empty($payload['token']) || empty($payload['email'])) {
            return redirect()
                ->route('password.request')
                ->with('status', __('If that email exists in our system, a reset link has been sent.'));
        }

        return view('auth.reset-password', [
            'token' => $payload['token'],
            'email' => $payload['email'],
        ]);
    }
}
