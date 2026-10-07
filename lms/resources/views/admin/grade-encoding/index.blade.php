@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grade Encoding Access</h3>
                    <p class="dir-subtitle">Choose who may encode and update grades. This does not change the user's role.</p>
                </div>
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
