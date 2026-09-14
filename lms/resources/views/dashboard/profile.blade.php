@extends('layouts.master')
@section('content')

@php
    $teacher = $teacher ?? null;
    $student = $student ?? null;
    $children = $children ?? collect();

    $avatarUrl = \App\Support\AvatarUploader::url($user->avatar);
    if ($user->role_name === \App\Models\User::ROLE_STUDENT && !empty($student?->upload)) {
        $avatarUrl = \Illuminate\Support\Facades\Storage::url('student-photos/'.$student->upload);
    }

    $displayName = $user->name;
    if ($user->role_name === \App\Models\User::ROLE_STUDENT && !empty($student?->full_name)) {
        $displayName = $student->full_name;
    } elseif ($user->role_name === \App\Models\User::ROLE_TEACHER && !empty($teacher?->full_name)) {
        $displayName = $teacher->full_name;
    }

    $phone = $user->phone_number
        ?: ($teacher->phone_number ?? null)
        ?: ($student->phone_number ?? null);

    $status = strtolower((string) ($user->status ?: 'active'));
    $statusClass = $status === 'active' ? 'dir-badge--active' : 'dir-badge--disabled';
    $joinDate = $user->join_date ? \Carbon\Carbon::parse($user->join_date)->format('M d, Y') : null;
    $dob = $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : null;
    if ($student && $student->date_of_birth) {
        $dob = \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y');
    } elseif ($teacher && $teacher->date_of_birth) {
        $dob = \Carbon\Carbon::parse($teacher->date_of_birth)->format('M d, Y');
    }

    $locationParts = [];
    if ($teacher) {
        foreach (['address', 'city', 'state', 'country'] as $locField) {
            if (!empty($teacher->{$locField})) {
                $locationParts[] = $teacher->{$locField};
            }
        }
    } elseif ($student && !empty($student->address)) {
        $locationParts[] = $student->address;
    }
    $location = implode(', ', $locationParts);
    $pwdErrors = $errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation');
@endphp

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Profile</h3>
                    <p class="dir-subtitle">Your account details from the school records.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="dir-card mb-3">
            <div class="dir-profile-hero">
                <img class="dir-avatar" src="{{ $avatarUrl }}" alt="{{ $displayName }}">
                <div class="flex-grow-1">
                    <h4 class="mb-1" style="font-weight:750;">{{ $displayName }}</h4>
                    <div class="d-flex flex-wrap gap-1 mb-1">
                        <span class="dir-chip">{{ $user->role_name }}</span>
                        @if($user->user_id)
                            <span class="dir-chip dir-chip--soft">ID {{ $user->user_id }}</span>
                        @endif
                        <span class="dir-badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                    </div>
                    <div class="dir-person-meta">{{ $user->email }}</div>
                    @if($location)
                        <div class="dir-person-meta"><i class="fas fa-map-marker-alt me-1"></i>{{ $location }}</div>
                    @endif
                </div>
                <a href="{{ route('user/profile/edit') }}" class="btn btn-primary dir-btn">
                    <i class="fas fa-pen me-1"></i> Edit
                </a>
            </div>
            <ul class="nav dir-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $pwdErrors ? '' : 'active' }}" data-bs-toggle="tab" href="#per_details_tab">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $pwdErrors ? 'active' : '' }}" data-bs-toggle="tab" href="#password_tab">Password</a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $pwdErrors ? '' : 'show active' }}" id="per_details_tab">
                @if($user->role_name === \App\Models\User::ROLE_STUDENT && isset($student))
                    @include('dashboard.partials.student_sis')
                @else
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="dir-card">
                                <div class="dir-toolbar">
                                    <h5 class="dir-toolbar-title mb-0">Personal details</h5>
                                </div>
                                <div class="p-4">
                                    <dl class="dir-dl">
                                        <dt>Full name</dt>
                                        <dd>{{ $displayName }}</dd>
                                        <dt>Email</dt>
                                        <dd>{{ $user->email }}</dd>
                                        <dt>Mobile</dt>
                                        <dd>{{ $phone ?: '—' }}</dd>
                                        <dt>Role</dt>
                                        <dd>{{ $user->role_name }}</dd>
                                        @if($user->position)
                                            <dt>Position</dt>
                                            <dd>{{ $user->position }}</dd>
                                        @endif
                                        @if($user->department)
                                            <dt>Department</dt>
                                            <dd>{{ $user->department }}</dd>
                                        @endif
                                        @if($user->user_id)
                                            <dt>User ID</dt>
                                            <dd>{{ $user->user_id }}</dd>
                                        @endif
                                        @if($joinDate)
                                            <dt>Joined</dt>
                                            <dd>{{ $joinDate }}</dd>
                                        @endif
                                        @if($dob)
                                            <dt>Date of birth</dt>
                                            <dd>{{ $dob }}</dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>

                            @if($teacher)
                                <div class="dir-card mt-3">
                                    <div class="dir-toolbar">
                                        <h5 class="dir-toolbar-title mb-0">Teacher record</h5>
                                    </div>
                                    <div class="p-4">
                                        <dl class="dir-dl">
                                            @if($teacher->teacher_id)
                                                <dt>Teacher ID</dt>
                                                <dd>{{ $teacher->teacher_id }}</dd>
                                            @endif
                                            @if($teacher->qualification)
                                                <dt>Qualification</dt>
                                                <dd>{{ $teacher->qualification }}</dd>
                                            @endif
                                            @if($teacher->experience)
                                                <dt>Experience</dt>
                                                <dd>{{ $teacher->experience }}</dd>
                                            @endif
                                            @if($teacher->gender)
                                                <dt>Gender</dt>
                                                <dd>{{ $teacher->gender }}</dd>
                                            @endif
                                            @if($location)
                                                <dt>Address</dt>
                                                <dd>{{ $location }}</dd>
                                            @endif
                                        </dl>
                                    </div>
                                </div>
                            @endif

                            @if($user->role_name === \App\Models\User::ROLE_PARENT && $children->count())
                                <div class="dir-card mt-3">
                                    <div class="dir-toolbar">
                                        <h5 class="dir-toolbar-title mb-0">Linked children</h5>
                                    </div>
                                    <div class="p-4">
                                        @foreach($children as $child)
                                            <div class="mb-2">
                                                <span class="dir-person-name">{{ $child->full_name }}</span>
                                                <span class="dir-person-meta">{{ $child->year_level }} · {{ $child->admission_id ?? $child->student_id }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-4">
                            <div class="dir-card">
                                <div class="dir-toolbar">
                                    <h5 class="dir-toolbar-title mb-0">Account status</h5>
                                </div>
                                <div class="p-4">
                                    <span class="dir-badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                                    <p class="dir-person-meta mt-3 mb-0">This status comes from your user account, not a placeholder.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div id="password_tab" class="tab-pane fade {{ $pwdErrors ? 'show active' : '' }}">
                <div class="dir-card">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Change password</h5>
                    </div>
                    <div class="p-4">
                        <form action="{{ route('user/password/update') }}" method="POST" class="col-lg-6 px-0">
                            @csrf
                            <div class="form-group">
                                <label>Current password</label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" autocomplete="current-password">
                                @error('current_password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>New password</label>
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" autocomplete="new-password">
                                @error('new_password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Confirm new password</label>
                                <input type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" name="new_password_confirmation" autocomplete="new-password">
                                @error('new_password_confirmation')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary dir-btn">Save password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914c">
@endpush
