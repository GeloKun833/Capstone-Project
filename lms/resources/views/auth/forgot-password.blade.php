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
        <h2 style="color: white; font-size: 1.8rem; margin-bottom: 10px;">Forgot Password?</h2>
        <p style="color: rgba(255, 255, 255, 0.9); font-size: 1rem;">
            Enter your email address and we'll send you a link to reset your password.
        </p>
    </div>
    
    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Email Address <span class="login-danger">*</span></label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required>
            <span class="profile-views"><i class="fas fa-envelope"></i></span>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        
        <div class="form-group">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-paper-plane me-2"></i>Send Reset Link
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
