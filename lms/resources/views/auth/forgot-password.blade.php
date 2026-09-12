@extends('layouts.app')
@section('content')

<div class="auth-shell">
    <div class="auth-form-panel">
        <div class="login-card">
            <header class="login-card__header">
                <img src="{{ URL::to('assets/img/Logo.jpg') }}" alt="School logo" class="login-card__logo" width="72" height="72">
                <h1 class="login-card__title">Forgot password?</h1>
                <p class="login-card__subtitle">Enter your account email and we will send a reset link. Works for Teacher, Registrar, Student, Parent, and Admin.</p>
            </header>

            @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="login-form">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">Email address <span class="login-danger">*</span></label>
                    <div class="input-field">
                        <span class="input-field__icon"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="you@school.edu" required autofocus>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <button class="btn-sign-in" type="submit">
                        <span class="btn-sign-in__text">
                            <i class="fas fa-paper-plane"></i> Send reset link
                        </span>
                    </button>
                </div>

                <p class="login-card__footer">
                    <a href="{{ route('login') }}" class="form-link">Back to sign in</a>
                </p>
                <p class="login-card__footer text-muted small mt-2 mb-0">
                    If email is not configured on the server, ask an Admin to reset your password from User Management.
                </p>
            </form>
        </div>
    </div>

    <aside class="auth-brand-panel" aria-label="School information">
        <div class="auth-brand-panel__content">
            <p class="auth-brand-panel__eyebrow">Password Recovery</p>
            <h2 class="auth-brand-panel__name">Panorama Montessori School INC.</h2>
            <p class="auth-brand-panel__address">Use the email registered to your LMS account.</p>
        </div>
    </aside>
</div>
@endsection
