@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm-12">
                    <div class="page-sub-header">
                        <h3 class="page-title">Activity Log</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">User Management</a></li>
                            <li class="breadcrumb-item active">Activity Log</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if($isAdmin)
            <div class="row mb-3">
                <div class="col-12">
                    <ul class="nav nav-pills activity-log-tabs">
                        <li class="nav-item">
                            <a
                                class="nav-link {{ $scope === 'all' ? 'active' : '' }}"
                                href="{{ route('activity.log', array_merge(request()->except('page', 'scope'), ['scope' => 'all'])) }}"
                            >
                                <i class="fas fa-clipboard-list me-1"></i> All System Activity
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link {{ $scope === 'mine' ? 'active' : '' }}"
                                href="{{ route('activity.log', array_merge(request()->except('page', 'scope', 'user_id'), ['scope' => 'mine'])) }}"
                            >
                                <i class="fas fa-user me-1"></i> My Activity
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        @endif

        <div class="student-group-form mb-4">
            <form method="GET" action="{{ route('activity.log') }}" class="row g-3 align-items-end">
                <input type="hidden" name="scope" value="{{ $scope }}">

                <div class="col-lg-3 col-md-6">
                    <label for="search" class="form-label mb-1">Search</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search action or description..."
                    >
                </div>

                @if($isAdmin && $scope === 'all')
                    <div class="col-lg-3 col-md-6">
                        <label for="user_id" class="form-label mb-1">User</label>
                        <select class="form-control select" id="user_id" name="user_id">
                            <option value="">All users</option>
                            @foreach($users as $filterUser)
                                <option value="{{ $filterUser->id }}" @selected(request('user_id') == $filterUser->id)>
                                    {{ $filterUser->name }} ({{ $filterUser->role_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-lg-2 col-md-4">
                    <label for="date_from" class="form-label mb-1">From</label>
                    <input
                        type="date"
                        class="form-control"
                        id="date_from"
                        name="date_from"
                        value="{{ request('date_from') }}"
                    >
                </div>

                <div class="col-lg-2 col-md-4">
                    <label for="date_to" class="form-label mb-1">To</label>
                    <input
                        type="date"
                        class="form-control"
                        id="date_to"
                        name="date_to"
                        value="{{ request('date_to') }}"
                    >
                </div>

                <div class="col-lg-2 col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('activity.log', ['scope' => $scope]) }}" class="btn btn-secondary">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table comman-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                @if($isAdmin && $scope === 'all')
                                    System-wide activity
                                @else
                                    Your recent activity
                                @endif
                            </h5>
                            <span class="text-muted small">{{ $activities->total() }} record(s)</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        @if($isAdmin && $scope === 'all')
                                            <th>User</th>
                                            <th>Role</th>
                                        @endif
                                        <th>Event</th>
                                        <th>Description</th>
                                        <th>Subject</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activities as $activity)
                                        <tr>
                                            <td>{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                                            @if($isAdmin && $scope === 'all')
                                                <td>{{ $activity->causer->name ?? 'System' }}</td>
                                                <td>{{ $activity->causer->role_name ?? '-' }}</td>
                                            @endif
                                            <td>
                                                @if($activity->event)
                                                    <span class="badge bg-light text-dark">{{ $activity->event }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $activity->description }}</td>
                                            <td>
                                                @if($activity->subject_type)
                                                    {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($activity->properties && count($activity->properties))
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#activity-details-{{ $activity->id }}"
                                                    >
                                                        View
                                                    </button>

                                                    <div class="modal fade" id="activity-details-{{ $activity->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Activity Details</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <pre class="activity-log-json mb-0">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ ($isAdmin && $scope === 'all') ? 7 : 5 }}" class="text-center py-4 text-muted">
                                                No activity found for the selected filters.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($activities->hasPages() || $activities->total() > 0)
                            <div class="pagination-wrapper mt-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div class="pagination-info">
                                        <p class="text-muted mb-0">
                                            Showing {{ $activities->firstItem() ?? 0 }} to {{ $activities->lastItem() ?? 0 }} of {{ $activities->total() }} results
                                        </p>
                                    </div>
                                    @if($activities->hasPages())
                                        <div class="pagination-controls">
                                            {{ $activities->links('pagination.bootstrap-limited') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .activity-log-tabs .nav-link {
        border-radius: 999px;
        padding: 0.55rem 1.1rem;
        color: #475569;
        font-weight: 500;
    }

    .activity-log-tabs .nav-link.active {
        background: #2563eb;
        color: #fff;
    }

    .activity-log-json {
        white-space: pre-wrap;
        word-break: break-word;
        background: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
        font-size: 0.85rem;
    }

    .pagination-wrapper {
        padding-top: 1.25rem;
        border-top: 1px solid #e9ecef;
    }

    .pagination-limited {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
    }

    .pagination-limited .page-item {
        list-style: none;
    }

    .pagination-limited .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0.4rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #fff;
        color: #495057;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .pagination-limited .page-link:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        color: #495057;
    }

    .pagination-limited .page-item.active .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .pagination-limited .page-item.disabled .page-link {
        background: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .pagination-limited .page-item:first-child .page-link,
    .pagination-limited .page-item:last-child .page-link {
        min-width: auto;
        font-weight: 600;
    }

    .pagination-limited .page-item:not(.disabled):not(.active):first-child .page-link,
    .pagination-limited .page-item:not(.disabled):not(.active):last-child .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .pagination-limited .page-item:not(.disabled):not(.active):first-child .page-link:hover,
    .pagination-limited .page-item:not(.disabled):not(.active):last-child .page-link:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }

    .pagination-info {
        font-size: 0.9rem;
    }
</style>
@endsection
