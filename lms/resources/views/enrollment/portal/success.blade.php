@extends('layouts.enrollment-portal')

@section('title', 'Enrollment Successful')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="text-center mb-4">
            <div class="ep-success-icon"><i class="fas fa-check"></i></div>
            <h1 class="ep-page-title">Application Submitted!</h1>
            <p class="ep-page-subtitle">Your enrollment application has been received.</p>
        </div>

        @if(!empty($accountDetails))
        <div class="ep-alert ep-alert-danger mb-4">
            <i class="fas fa-camera me-2"></i>
            <strong>IMPORTANT:</strong> Screenshot your login credentials below before leaving this page.
        </div>

        {{-- Student Credentials --}}
        <div class="ep-card mb-4">
            <div class="ep-card-header">
                <h3><i class="fas fa-graduation-cap me-2 text-primary"></i>Student Account Credentials</h3>
            </div>
            <div class="ep-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Student Name</div><div class="value">{{ $accountDetails['student_name'] }}</div></div>
                        <div class="ep-info-item"><div class="label">Grade Level</div><div class="value">{{ $accountDetails['grade_level'] }}</div></div>
                        <div class="ep-info-item">
                            <div class="label">Section</div>
                            <div class="ep-credential {{ $accountDetails['assigned_section'] !== 'To be assigned after approval' ? 'success-bg' : '' }}">
                                <i class="fas fa-users text-success"></i>
                                <strong>{{ $accountDetails['assigned_section'] }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Application No.</div><div class="value">{{ $accountDetails['application_number'] }}</div></div>
                        <div class="ep-info-item"><div class="label">Email / Username</div><div class="ep-credential"><i class="fas fa-envelope"></i>{{ $accountDetails['email'] }}</div></div>
                        <div class="ep-info-item">
                            <div class="label">Temporary Password</div>
                            <div class="ep-credential highlight">
                                <i class="fas fa-key text-warning"></i>
                                <span id="password-display" class="fw-bold">{{ $accountDetails['password'] }}</span>
                                <button type="button" class="ep-btn ep-btn-sm ep-btn-ghost ms-auto" onclick="togglePassword()"><i class="fas fa-eye" id="toggle-icon"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($accountDetails['parent_account']))
        <div class="ep-card mb-4">
            <div class="ep-card-header">
                <h3><i class="fas fa-user-friends me-2 text-primary"></i>Parent Account Credentials</h3>
            </div>
            <div class="ep-card-body">
                <div class="ep-alert ep-alert-info mb-3">A parent portal account was created so you can monitor your child's progress.</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Parent Name</div><div class="value">{{ $accountDetails['parent_account']['name'] }}</div></div>
                        <div class="ep-info-item"><div class="label">Email</div><div class="ep-credential"><i class="fas fa-envelope"></i>{{ $accountDetails['parent_account']['email'] }}</div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="ep-info-item">
                            <div class="label">Temporary Password</div>
                            <div class="ep-credential highlight">
                                <i class="fas fa-key"></i>
                                <span id="parent-password-display" class="fw-bold">{{ $accountDetails['parent_account']['password'] ?: 'Use the existing parent password' }}</span>
                                @if(!empty($accountDetails['parent_account']['password']))
                                <button type="button" class="ep-btn ep-btn-sm ep-btn-ghost ms-auto" onclick="toggleParentPassword()"><i class="fas fa-eye" id="parent-toggle-icon"></i></button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif

        <div class="ep-card mb-4">
            <div class="ep-card-header"><h3>Next Steps</h3></div>
            <div class="ep-card-body">
                <div class="ep-process-list">
                    @if(!empty($accountDetails))
                    <div class="ep-process-item"><div class="ep-process-num">1</div><span>Screenshot all credentials above</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">2</div><span><a href="{{ route('login') }}" target="_blank">Login to the LMS</a> and change your password</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">3</div><span>Wait 3–5 business days for registrar approval</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">4</div><span>Track status anytime via Check Status</span></div>
                    @else
                    <div class="ep-process-item"><div class="ep-process-num">1</div><span>Wait 3–5 business days for registrar approval</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">2</div><span>Track status anytime via Check Status</span></div>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4 justify-content-center">
                    <a href="{{ route('login') }}" class="ep-btn ep-btn-primary ep-btn-lg" target="_blank"><i class="fas fa-sign-in-alt"></i> Go to Login</a>
                    <a href="{{ route('enrollment.portal.status') }}" class="ep-btn ep-btn-outline ep-btn-lg"><i class="fas fa-search"></i> Check Status</a>
                    <a href="{{ route('enrollment.portal.index') }}" class="ep-btn ep-btn-outline ep-btn-lg"><i class="fas fa-home"></i> Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(!empty($accountDetails))
<script>
function togglePassword() {
    const el = document.getElementById('password-display');
    const icon = document.getElementById('toggle-icon');
    if (el.textContent === '{{ $accountDetails['password'] }}') { el.textContent = '••••••••'; icon.className = 'fas fa-eye-slash'; }
    else { el.textContent = '{{ $accountDetails['password'] }}'; icon.className = 'fas fa-eye'; }
}
@if(!empty($accountDetails['parent_account']['password']))
function toggleParentPassword() {
    const el = document.getElementById('parent-password-display');
    const icon = document.getElementById('parent-toggle-icon');
    if (el.textContent === '{{ $accountDetails['parent_account']['password'] }}') { el.textContent = '••••••••'; icon.className = 'fas fa-eye-slash'; }
    else { el.textContent = '{{ $accountDetails['parent_account']['password'] }}'; icon.className = 'fas fa-eye'; }
}
@endif
setTimeout(function() {
    ['password-display','parent-password-display'].forEach(id => {
        const el = document.getElementById(id);
        if (el && el.textContent !== '••••••••' && el.textContent !== 'Use the existing parent password') el.textContent = '••••••••';
    });
}, 30000);
</script>
@endif
@endsection
