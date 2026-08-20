@extends('layouts.enrollment-registrar')

@section('title', 'Enrollment Applications')

@section('topbar-actions')
<a href="{{ route('enrollment.portal.index') }}" target="_blank" class="ep-btn ep-btn-outline ep-btn-sm"><i class="fas fa-external-link"></i> Portal</a>
@endsection

@section('content')
@php
    $pending = $applications->where('status', 'pending')->count();
    $approved = $applications->where('status', 'approved')->count();
    $rejected = $applications->where('status', 'rejected')->count();
    $review = $applications->where('status', 'under_review')->count();
@endphp

<div class="mb-4">
    <h1 class="ep-page-title">Enrollment Dashboard</h1>
    <p class="ep-page-subtitle">Manage applications, review documents, and approve enrollments.</p>
</div>

<div class="ep-stat-grid mb-4">
    <div class="ep-stat-card">
        <div class="ep-stat-icon blue"><i class="fas fa-inbox"></i></div>
        <div class="ep-stat-value">{{ $applications->total() }}</div>
        <div class="ep-stat-label">Total Applications</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon amber"><i class="fas fa-clock"></i></div>
        <div class="ep-stat-value">{{ $pending }}</div>
        <div class="ep-stat-label">Pending Review</div>
        @if($pending > 0)<div class="ep-stat-trend down"><i class="fas fa-arrow-up"></i> Needs attention</div>@endif
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="ep-stat-value">{{ $approved }}</div>
        <div class="ep-stat-label">Approved</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="ep-stat-value">{{ $rejected }}</div>
        <div class="ep-stat-label">Rejected</div>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header"><h3><i class="fas fa-filter me-2"></i>Search & Filter</h3></div>
    <div class="ep-card-body">
        <form method="GET" action="{{ route('enrollment.registrar.index') }}" class="row g-3 align-items-end">
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
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Submitted</option>
                    <option value="first_name" {{ request('sort_by') == 'first_name' ? 'selected' : '' }}>Name</option>
                    <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Status</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="ep-btn ep-btn-primary flex-fill"><i class="fas fa-search"></i></button>
                <a href="{{ route('enrollment.registrar.index') }}" class="ep-btn ep-btn-outline"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="ep-card">
    <div class="ep-card-header">
        <h3>Applications</h3>
        <div class="d-flex gap-2">
            <button class="ep-btn ep-btn-sm ep-btn-outline" onclick="window.print()"><i class="fas fa-print"></i></button>
            <button class="ep-btn ep-btn-sm ep-btn-outline" onclick="exportTable()"><i class="fas fa-download"></i> Export</button>
        </div>
    </div>
    <div class="ep-card-body p-0">
        @if($applications->count() > 0)
        <div class="ep-table-wrap">
            <table class="ep-table" id="applicationsTable">
                <thead>
                    <tr>
                        <th>Application #</th>
                        <th>Student</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Reviewer</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $application)
                    @php
                        $chips = ['pending'=>'ep-chip-pending','under_review'=>'ep-chip-review','approved'=>'ep-chip-approved','rejected'=>'ep-chip-rejected','needs_documents'=>'ep-chip-docs'];
                    @endphp
                    <tr>
                        <td><strong class="text-primary">{{ $application->application_number }}</strong></td>
                        <td>
                            <div class="fw-semibold">{{ $application->full_name }}</div>
                            <small class="text-muted">{{ $application->email }}</small>
                        </td>
                        <td><span class="ep-chip ep-chip-review">{{ $application->grade_level_applying_for }}</span></td>
                        <td><span class="ep-chip {{ $chips[$application->status] ?? 'ep-chip-docs' }}">{{ ucfirst(str_replace('_',' ',$application->status)) }}</span></td>
                        <td>{{ $application->created_at->format('M d, Y') }}</td>
                        <td>{{ $application->reviewer->name ?? '—' }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="ep-btn ep-btn-sm ep-btn-ghost dropdown-toggle" data-bs-toggle="dropdown">Actions</button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><a class="dropdown-item" href="{{ route('enrollment.registrar.show', $application->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                    @if($application->status === 'pending')
                                    <li><form action="{{ route('enrollment.registrar.mark-under-review', $application->id) }}" method="POST">@csrf<button class="dropdown-item"><i class="fas fa-search me-2 text-warning"></i>Mark Under Review</button></form></li>
                                    @endif
                                    @if($application->status === 'under_review')
                                    <li><form action="{{ route('enrollment.registrar.approve', $application->id) }}" method="POST">@csrf<button class="dropdown-item text-success"><i class="fas fa-check me-2"></i>Approve</button></form></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form action="{{ route('enrollment.registrar.destroy', $application->id) }}" method="POST" onsubmit="return confirm('Delete this application permanently?')">@csrf @method('DELETE')<button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button></form></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">Showing {{ $applications->firstItem() ?? 0 }}–{{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }}</small>
            {{ $applications->links() }}
        </div>
        @else
        <div class="ep-empty">
            <i class="fas fa-inbox"></i>
            <h5>No applications found</h5>
            <p>Try adjusting your filters or wait for new submissions.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportTable() {
    const table = document.getElementById('applicationsTable');
    if (!table) return;
    let csv = [];
    table.querySelectorAll('tr').forEach(row => {
        const cols = [...row.querySelectorAll('th,td')].slice(0, -1);
        csv.push(cols.map(c => '"' + c.innerText.replace(/"/g,'""').trim() + '"').join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
    a.download = 'enrollment_applications.csv'; a.click();
}
</script>
@endsection
