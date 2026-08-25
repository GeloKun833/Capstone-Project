@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="auth-form-panel">
        <div class="login-card">
            <header class="login-card__header">
                <h1 class="login-card__title">Confirm password</h1>
                <p class="login-card__subtitle">This is a secure area. Please confirm your password before continuing.</p>
            </header>

            <form method="POST" action="{{ route('password.confirm') }}" class="login-form">
                @csrf

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <button class="btn-sign-in" type="submit">Confirm password</button>
                </div>

                @if (Route::has('password.request'))
                    <p class="login-card__footer">
                        <a href="{{ route('password.request') }}" class="form-link">Forgot your password?</a>
                    </p>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
