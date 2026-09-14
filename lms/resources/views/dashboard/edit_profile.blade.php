@extends('layouts.master')
@section('content')

@php
    $avatarUrl = \App\Support\AvatarUploader::url($user->avatar);
@endphp

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Edit Profile</h3>
                    <p class="dir-subtitle">Update the details stored on your account.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user/profile/page') }}">Profile</a></li>
                        <li class="breadcrumb-item active">Edit Profile</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="dir-card">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Account details</h5>
                    </div>
                    <div class="p-4">
                        <form action="{{ route('user/profile/update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Name</label>
                                @if($user->role_name === \App\Models\User::ROLE_STUDENT)
                                    <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <small class="text-muted">Students cannot change their registered name. Contact the administrator or registrar for corrections.</small>
                                @else
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                @endif
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Mobile</label>
                                <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $user->phone_number) }}">
                                @error('phone_number')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <input type="text" class="form-control" value="{{ $user->role_name }}" readonly>
                            </div>
                            @if($user->user_id)
                                <div class="form-group">
                                    <label>User ID</label>
                                    <input type="text" class="form-control" value="{{ $user->user_id }}" readonly>
                                </div>
                            @endif
                            <div class="form-group">
                                <label>Profile photo</label>
                                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                <small class="text-muted">JPG, PNG, GIF, or WEBP. Max 2MB.</small>
                                @error('avatar')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="mt-2">
                                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="dir-avatar">
                                </div>
                            </div>
                            @if($user->role_name === \App\Models\User::ROLE_STUDENT && $student)
                                <div class="form-group">
                                    <label>Student ID</label>
                                    <input type="text" class="form-control" value="{{ $student->admission_id ?? $student->student_id }}" readonly>
                                </div>
                            @endif
                            @if($user->role_name === \App\Models\User::ROLE_TEACHER && $teacher)
                                <div class="form-group">
                                    <label>Teacher ID</label>
                                    <input type="text" class="form-control" value="{{ $teacher->teacher_id }}" readonly>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-primary dir-btn">Update profile</button>
                            <a href="{{ route('user/profile/page') }}" class="btn btn-outline-secondary dir-btn">Cancel</a>
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
