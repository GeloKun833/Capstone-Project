@extends('layouts.app')
@section('content')

<div class="auth-shell">
    {{-- Login form panel --}}
    <div class="auth-form-panel">
        <div class="login-card">
            <header class="login-card__header">
                <img
                    src="{{ URL::to('assets/img/Logo.jpg') }}"
                    alt="Panorama Montessori School logo"
                    class="login-card__logo"
                    width="72"
                    height="72"
                >
                <h1 class="login-card__title">Welcome back</h1>
                <p class="login-card__subtitle">Sign in to your LMS account to continue</p>
            </header>

            <form action="{{ route('login') }}" method="POST" class="login-form" id="login-form" novalidate>
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">
                        Email address <span class="login-danger" aria-hidden="true">*</span>
                    </label>
                    <div class="input-field">
                        <span class="input-field__icon" aria-hidden="true">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="you@school.edu"
                            autocomplete="email"
                            inputmode="email"
                            required
                            autofocus
                        >
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password" class="form-label">
                            Password <span class="login-danger" aria-hidden="true">*</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="form-link form-link--inline">
                            Forgot password?
                        </a>
                    </div>
                    <div class="input-field">
                        <span class="input-field__icon" aria-hidden="true">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                        <button
                            type="button"
                            class="input-field__toggle toggle-password"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group form-group--compact">
                    <label class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Keep me signed in</span>
                    </label>
                </div>

                <div class="form-group">
                    <button class="btn-sign-in" type="submit" id="login-submit">
                        <span class="btn-sign-in__text">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            Sign in
                        </span>
                        <span class="btn-sign-in__loading" aria-hidden="true">
                            <i class="fas fa-circle-notch fa-spin"></i>
                            Signing in…
                        </span>
                    </button>
                </div>

                <p class="login-card__footer">
                    Need access? Contact your administrator.
                </p>
            </form>
        </div>
    </div>

    {{-- Brand panel --}}
    <aside class="auth-brand-panel" aria-label="School information">
        <div class="auth-brand-panel__content">
            <p class="auth-brand-panel__eyebrow">Learning Management System</p>
            <h2 class="auth-brand-panel__name">Panorama Montessori School INC.</h2>
            <p class="auth-brand-panel__address">
                Panorama Ville, Brgy. Dita,<br>
                City of Santa Rosa, Laguna
            </p>
            <div class="auth-brand-panel__divider" aria-hidden="true"></div>
            <p class="auth-brand-panel__tagline">
                <span class="auth-brand-panel__tagline-prefix">Fostering a</span>
                <span class="auth-brand-panel__tagline-accent">Passion for Excellence</span>
            </p>
            <p class="auth-brand-panel__motto">Nurturing Minds, Building Futures</p>
        </div>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('login-form');
    const submitBtn = document.getElementById('login-submit');

    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.classList.add('is-loading');
            submitBtn.disabled = true;
        });
    }
});
</script>
@endsection
