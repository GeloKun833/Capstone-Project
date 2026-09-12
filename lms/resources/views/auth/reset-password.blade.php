@extends('layouts.app')
@section('content')

<div class="auth-shell">
    <div class="auth-form-panel">
        <div class="login-card">
            <header class="login-card__header">
                <img src="{{ URL::to('assets/img/Logo.jpg') }}" alt="School logo" class="login-card__logo" width="72" height="72">
                <h1 class="login-card__title">Reset password</h1>
                <p class="login-card__subtitle">Choose a new password for your LMS account.</p>
            </header>

            <form action="{{ route('password.update') }}" method="POST" class="login-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="email" class="form-label">Email address <span class="login-danger">*</span></label>
                    <div class="input-field">
                        <span class="input-field__icon"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ $email ?? old('email') }}" required>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">New password <span class="login-danger">*</span></label>
                    <div class="input-field">
                        <span class="input-field__icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required autocomplete="new-password">
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm new password <span class="login-danger">*</span></label>
                    <div class="input-field">
                        <span class="input-field__icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" required autocomplete="new-password">
                    </div>
                </div>

                <div class="form-group">
                    <button class="btn-sign-in" type="submit">
                        <span class="btn-sign-in__text">
                            <i class="fas fa-key"></i> Update password
                        </span>
                    </button>
                </div>

                <p class="login-card__footer">
                    <a href="{{ route('login') }}" class="form-link">Back to sign in</a>
                </p>
            </form>
        </div>
    </div>

    <aside class="auth-brand-panel" aria-label="School information">
        <div class="auth-brand-panel__content">
            <p class="auth-brand-panel__eyebrow">Secure Reset</p>
            <h2 class="auth-brand-panel__name">Panorama Montessori School INC.</h2>
            <p class="auth-brand-panel__address">This link expires after a short time for your security.</p>
        </div>
    </aside>
</div>
@endsection
