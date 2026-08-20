@extends('layouts.enrollment-portal')

@section('title', 'Old Student Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="text-center mb-4">
            <div class="ep-stat-icon green d-inline-flex mb-3" style="width:64px;height:64px;font-size:1.5rem;"><i class="fas fa-user-check"></i></div>
            <h1 class="ep-page-title">Returning Student Login</h1>
            <p class="ep-page-subtitle">Sign in to view your subjects, schedule, and select your section.</p>
        </div>

        <div class="ep-card">
            <div class="ep-card-body p-4">
                <form action="{{ route('enrollment.old-student.authenticate') }}" method="POST">
                    @csrf
                    <div class="ep-form-group">
                        <label class="ep-label" for="email"><i class="fas fa-envelope me-1 text-primary"></i> Email <span class="required">*</span></label>
                        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}" placeholder="student@email.com" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-label" for="password"><i class="fas fa-lock me-1 text-primary"></i> Password <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                                   id="password" name="password" placeholder="Your password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fas fa-eye" id="toggleIcon"></i></button>
                        </div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="ep-btn ep-btn-success ep-btn-lg ep-btn-block">
                        <i class="fas fa-sign-in-alt"></i> Login & Continue Enrollment
                    </button>
                </form>
                <p class="text-center text-muted mt-4 mb-0 small">
                    New student? <a href="{{ route('enrollment.portal.create', ['type' => 'new']) }}">Apply here</a>
                </p>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('enrollment.portal.index') }}" class="ep-btn ep-btn-outline ep-btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const p = document.getElementById('password');
    const i = document.getElementById('toggleIcon');
    p.type = p.type === 'password' ? 'text' : 'password';
    i.classList.toggle('fa-eye'); i.classList.toggle('fa-eye-slash');
});
</script>
@endsection
