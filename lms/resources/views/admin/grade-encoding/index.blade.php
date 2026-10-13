@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grade Encoding Access</h3>
                    <p class="dir-subtitle">Choose who may encode grades, and which grade levels are open for encoding.</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="dir-card mb-4">
            <div class="p-3 p-md-4">
                <h5 class="mb-1">Open grade levels</h5>
                <p class="text-muted small">Teachers can encode grades only for the levels you leave checked. Nursery and Kindergarten use descriptive letters. Grade 1 through Grade 3 also use letters. Grade 4 through Grade 10 use scores from 0 to 100.</p>
                <form method="POST" action="{{ route('admin.grade-encoding.levels') }}">
                    @csrf
                    <div class="row g-2">
                        @foreach($gradeLevels as $grade)
                            @php
                                $checked = $openLevels === null || in_array($grade, $openLevels, true);
                            @endphp
                            <div class="col-6 col-md-4 col-lg-3">
                                <label class="d-flex align-items-center gap-2 mb-0">
                                    <input type="checkbox" name="grade_levels[]" value="{{ $grade }}" {{ $checked ? 'checked' : '' }}>
                                    <span>{{ $grade }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary dir-btn mt-3">Save grade levels</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Account</th>
                        <th>Grade Encoding Access</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php $allowed = $user->permissions->contains('name', $permission); @endphp
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->role_name }}</td>
                            <td>{{ $user->isActiveAccount() ? 'Active' : 'Inactive' }}</td>
                            <td>{{ $allowed ? 'Allowed' : 'Not Allowed' }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.grade-encoding.update', $user) }}">
                                    @csrf
                                    <input type="hidden" name="allow" value="{{ $allowed ? 0 : 1 }}">
                                    <button type="submit" class="btn btn-sm {{ $allowed ? 'btn-outline-danger' : 'btn-primary' }}">
                                        {{ $allowed ? 'Revoke' : 'Allow' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No teachers or registrars are available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914m">
@endpush
