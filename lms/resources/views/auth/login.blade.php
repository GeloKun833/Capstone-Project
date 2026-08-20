@extends('layouts.app')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}

<div class="glass-form">
    <div class="school-logo-top">
        <img src="{{ URL::to('assets/img/Logo.jpg') }}" alt="School Logo">
        <h1 class="school-name">Panorama Montessori School INC.</h1>
        <p class="school-motto">Nurturing Minds, Building Futures</p>
    </div>
    
    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group input-with-icon">
            <label>Email Address <span class="login-danger">*</span></label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required>
            <span class="input-icon-left"><i class="fas fa-envelope"></i></span>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        
        <div class="form-group input-with-icon">
            <label>Password <span class="login-danger">*</span></label>
            <input type="password" class="form-control pass-input @error('password') is-invalid @enderror" name="password" id="password-input" placeholder="Enter your password" required>
            <span class="input-icon-left"><i class="fas fa-lock"></i></span>
            <span class="input-icon-right toggle-password" onclick="togglePassword()">
                <i class="fas fa-eye" id="toggle-icon"></i>
            </span>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        
        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember" style="color: rgba(255, 255, 255, 0.9); font-size: 0.9rem;">
                        Remember me
                    </label>
                </div>
                <a href="{{ route('password.request') }}" style="color: rgba(255, 255, 255, 0.9); text-decoration: none; font-size: 0.9rem;">
                    Forgot Password?
                </a>
            </div>
        </div>
        
        <div class="form-group">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-arrow-right me-2"></i>Sign In
            </button>
        </div>
        
        <div class="text-center">
            <p style="color: rgba(255, 255, 255, 0.9); margin-bottom: 0;">
                Need access? Contact your administrator.
            </p>
        </div>
    </form>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password-input');
    const toggleIcon = document.getElementById('toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
@endsection         
