@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="auth-form-panel">
        <div class="login-card">
            <header class="login-card__header">
                <h1 class="login-card__title">Verify your email</h1>
                <p class="login-card__subtitle">
                    Thanks for signing up. Please verify your email address using the link we sent you.
                    If you did not receive the email, we can send another.
                </p>
            </header>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success">
                    A new verification link has been sent to your email address.
                </div>
            @endif

            @if (Route::has('verification.send'))
                <form method="POST" action="{{ route('verification.send') }}" class="login-form">
                    @csrf
                    <div class="form-group">
                        <button class="btn-sign-in" type="submit">Resend verification email</button>
                    </div>
                </form>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="login-form">
                @csrf
                <p class="login-card__footer">
                    <button type="submit" class="form-link" style="background:none;border:none;padding:0;">Log out</button>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
