@extends('layouts.app')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}

<div class="glass-form">
    <div class="school-logo-top">
        <img src="{{ URL::to('assets/img/Logo.jpg') }}" alt="School Logo">
        <h1 class="school-name">Panorama Montessori School</h1>
        <p class="school-motto">Nurturing Minds, Building Futures</p>
    </div>
    
    <div class="text-center mb-4">
        <h2 style="color: white; font-size: 1.8rem; margin-bottom: 10px;">Reset Password</h2>
        <p style="color: rgba(255, 255, 255, 0.9); font-size: 1rem;">
            Enter your new password below.
        </p>
    </div>
    
    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        
        <div class="form-group">
            <label>Email Address <span class="login-danger">*</span></label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" placeholder="Enter your email address" required>
            <span class="profile-views"><i class="fas fa-envelope"></i></span>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        
        <div class="form-group">
            <label>New Password <span class="login-danger">*</span></label>
            <input type="password" class="form-control pass-input @error('password') is-invalid @enderror" name="password" placeholder="Enter your new password" required>
            <span class="profile-views feather-eye toggle-password"></span>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        
        <div class="form-group">
            <label>Confirm New Password <span class="login-danger">*</span></label>
            <input type="password" class="form-control pass-confirm @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder="Confirm your new password" required>
            <span class="profile-views feather-eye reg-toggle-password"></span>
            @error('password_confirmation')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        
        <div class="form-group">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-key me-2"></i>Reset Password
            </button>
        </div>
        
        <div class="text-center">
            <p style="color: rgba(255, 255, 255, 0.9); margin-bottom: 0;">
                Remember your password? 
                <a href="{{ route('login') }}" style="color: white; text-decoration: none; font-weight: 600;">Sign in here</a>
            </p>
        </div>
    </form>
</div>
@endsection
