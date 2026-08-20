@extends('layouts.enrollment-registrar')

@section('title', 'Enrollment Archive')

@section('topbar-actions')
<a href="{{ route('enrollment.registrar.index') }}" class="ep-btn ep-btn-outline ep-btn-sm"><i class="fas fa-arrow-left"></i> Active Applications</a>
@endsection

@section('content')
<div class="mb-4">
    <h1 class="ep-page-title">Enrollment Archive</h1>
    <p class="ep-page-subtitle">View and restore deleted applications organized by final status.</p>
</div>

<div class="ep-stat-grid mb-4" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="ep-stat-card">
        <div class="ep-stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="ep-stat-value">{{ $statusCounts['approved'] }}</div>
        <div class="ep-stat-label">Approved (Archived)</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="ep-stat-value">{{ $statusCounts['rejected'] }}</div>
        <div class="ep-stat-label">Rejected (Archived)</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon amber"><i class="fas fa-box-archive"></i></div>
        <div class="ep-stat-value">{{ $statusCounts['deleted'] }}</div>
        <div class="ep-stat-label">Total Archived</div>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header"><h3><i class="fas fa-filter me-2"></i>Search & Filter Archive</h3></div>
    <div class="ep-card-body">
        <form method="GET" action="{{ route('enrollment.registrar.archive') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="ep-label">Search</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name, email, or application #">
            </div>
            <div class="col-md-3">
                <label class="ep-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    @foreach(['pending','under_review','approved','rejected','needs_documents'] as $st)
                    <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="ep-label">Sort By</label>
                <select class="form-select" name="sort_by">
                    <option value="deleted_at" {{ request('sort_by') == 'deleted_at' ? 'selected' : '' }}>Date Deleted</option>
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Submitted</option>
                    <option value="first_name" {{ request('sort_by') == 'first_name' ? 'selected' : '' }}>Name</option>
                    <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Status</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="ep-btn ep-btn-primary flex-fill"><i class="fas fa-search"></i></button>
                <a href="{{ route('enrollment.registrar.archive') }}" class="ep-btn ep-btn-outline"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="ep-card">
    <div class="ep-card-header">
        <h3><i class="fas fa-archive me-2"></i>Archived Applications</h3>
    </div>
    <div class="ep-card-body p-0">
        @if($applications->count() > 0)
            <div class="ep-table-wrap">
                <table class="ep-table">
                    <thead>
                        <tr>
                            <th>Application #</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Grade</th>
                            <th>Final Status</th>
                            <th>Deleted</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $chipMap = ['pending'=>'ep-chip-pending','under_review'=>'ep-chip-review','approved'=>'ep-chip-approved','rejected'=>'ep-chip-rejected','needs_documents'=>'ep-chip-docs'];
                        @endphp
                        @foreach($applications as $application)
                            <tr>
                                <td><strong>{{ $application->application_number }}</strong></td>
                                <td>{{ $application->full_name }}</td>
                                <td>{{ $application->email }}</td>
                                <td>{{ $application->grade_level_applying_for }}</td>
                                <td><span class="ep-chip {{ $chipMap[$application->status] ?? 'ep-chip-docs' }}">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span></td>
                                <td class="text-muted">{{ $application->deleted_at ? $application->deleted_at->format('M d, Y') : 'N/A' }}</td>
                                <td class="text-muted">{{ $application->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="ep-btn ep-btn-outline ep-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('enrollment.registrar.show', $application->id) }}">
                                                    <i class="fas fa-eye me-2"></i>View Details
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('enrollment.registrar.restore', $application->id) }}" method="POST" onsubmit="return confirm('Restore this application?')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success"><i class="fas fa-undo me-2"></i>Restore</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('enrollment.registrar.force-delete', $application->id) }}" method="POST" onsubmit="return confirm('Permanently delete? This cannot be undone.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Delete Forever</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="ep-card-body border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    Showing {{ $applications->firstItem() ?? 0 }}–{{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }}
                </span>
                {{ $applications->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-archive fa-3x text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Archived Applications</h5>
                <p class="text-muted mb-4">Deleted applications will appear here for review and restoration.</p>
                <a href="{{ route('enrollment.registrar.index') }}" class="ep-btn ep-btn-primary"><i class="fas fa-arrow-left me-2"></i>Back to Applications</a>
            </div>
        @endif
    </div>
</div>
@endsection
