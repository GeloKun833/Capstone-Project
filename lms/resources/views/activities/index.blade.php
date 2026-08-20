@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Lesson Activities</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a></li>
                            <li class="breadcrumb-item active">Activities</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Activity Management</h3>
                                        <p class="text-muted">Manage activities for lesson: <strong>{{ $lesson->title }}</strong></p>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('lessons.show', $lesson) }}" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-arrow-left"></i> Back to Lesson
                                        </a>
                                        <a href="{{ route('lessons.activities.create', $lesson) }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Activity
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0">{{ $activities->count() }}</h4>
                                                    <p class="mb-0">Total Activities</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-tasks fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0">{{ $activities->where('allows_submission', true)->count() }}</h4>
                                                    <p class="mb-0">With Submissions</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-upload fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0">{{ $activities->where('is_overdue', true)->count() }}</h4>
                                                    <p class="mb-0">Overdue</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0">{{ $activities->where('due_date', '>', now())->count() }}</h4>
                                                    <p class="mb-0">Upcoming</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-calendar fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                    <thead class="student-thread">
                                        <tr>
                                            <th>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="something" id="selectAllActivities">
                                                </div>
                                            </th>
                                            <th>Activity Title</th>
                                            <th>Instructions</th>
                                            <th>Due Date</th>
                                            <th>Submissions</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="activityTableBody">
                                        @forelse($activities as $activity)
                                            <tr>
                                                <td>
                                                    <div class="form-check check-tables">
                                                        <input class="form-check-input" type="checkbox" value="{{ $activity->id }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <h2>
                                                        <a href="{{ route('lessons.activities.show', [$lesson, $activity]) }}">{{ $activity->title }}</a>
                                                    </h2>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ Str::limit($activity->instructions, 100) }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $activity->due_date->format('M d, Y') }}</strong>
                                                    @if($activity->is_overdue)
                                                        <br><span class="badge bg-danger">Overdue</span>
                                                    @elseif($activity->due_date->isToday())
                                                        <br><span class="badge bg-warning">Due Today</span>
                                                    @elseif($activity->due_date->isFuture())
                                                        <br><span class="badge bg-info">{{ $activity->due_date->diffForHumans() }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($activity->allows_submission)
                                                        <span class="badge bg-info">{{ $activity->submission_count }}</span> submitted
                                                        <br><span class="badge bg-success">{{ $activity->graded_count }}</span> graded
                                                        @if($activity->submission_count > 0)
                                                            <br><small class="text-muted">{{ round(($activity->graded_count / $activity->submission_count) * 100) }}% graded</small>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary">No Submission</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($activity->allows_submission)
                                                        <span class="badge bg-primary">Submission Allowed</span>
                                                    @else
                                                        <span class="badge bg-secondary">No Submission</span>
                                                    @endif
                                                    <br>
                                                    @if($activity->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="actions">
                                                        <a href="{{ route('lessons.activities.show', [$lesson, $activity]) }}" class="btn btn-sm bg-danger-light" title="View">
                                                            <i class="far fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('lessons.activities.edit', [$lesson, $activity]) }}" class="btn btn-sm bg-danger-light" title="Edit">
                                                            <i class="far fa-edit"></i>
                                                        </a>
                                                        @if($activity->allows_submission)
                                                            <a href="{{ route('lessons.activities.rubric', [$lesson, $activity]) }}" class="btn btn-sm bg-danger-light" title="Rubric">
                                                                <i class="fas fa-list-check"></i>
                                                            </a>
                                                        @endif
                                                        <form action="{{ route('lessons.activities.destroy', [$lesson, $activity]) }}" method="POST" style="display:inline-block;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm bg-danger-light" onclick="return confirm('Are you sure you want to delete this activity?')" title="Delete">
                                                                <i class="far fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-tasks fa-3x mb-3"></i>
                                                        <h5>No activities found</h5>
                                                        <p>No activities have been created for this lesson yet.</p>
                                                        <a href="{{ route('lessons.activities.create', $lesson) }}" class="btn btn-primary">
                                                            <i class="fas fa-plus"></i> Create Your First Activity
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('styles')
<style>
/* Admin-style form controls */
.student-group-form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 20px;
}

/* Card styling */
.card-table {
    border: 0;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 1.875rem;
}

.card-table .card-body {
    padding: 1.5rem;
}

.card {
    border: 0;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 1.875rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
    border-radius: 10px 10px 0 0;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c323f;
    margin-bottom: 0;
}

.card-body {
    padding: 1.5rem;
}

/* Summary cards */
.card.bg-primary {
    background-color: #3d5ee1 !important;
}

.card.bg-success {
    background-color: #7bb13c !important;
}

.card.bg-warning {
    background-color: #ffc107 !important;
}

.card.bg-info {
    background-color: #17a2b8 !important;
}

/* Buttons */
.btn {
    border-radius: 5px;
    font-weight: 600;
    transition: all .4s ease;
}

.btn-primary {
    background-color: #3d5ee1;
    border: 1px solid #3d5ee1;
}

.btn-primary:hover {
    background-color: #18aefa;
    border: 1px solid #18aefa;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #fff;
}

/* Page header */
.page-header {
    margin-bottom: 1.875rem;
}

.page-header .breadcrumb {
    background-color: transparent;
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 0;
    padding: 0;
    margin-left: auto;
}

.page-header .breadcrumb a {
    color: #333;
}

.page-title {
    font-size: 22px;
    font-weight: 500;
    color: #2c323f;
    margin-bottom: 5px;
}

/* Download group */
.download-grp {
    display: flex;
    align-items: center;
}

/* Table styling */
.table {
    color: #333;
    max-width: 100%;
    margin-bottom: 0;
    width: 100%;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
    color: #000;
    background-color: #f8f9fa;
    border-color: #eff2f7;
    padding: 15px;
}

.table tbody tr {
    border-bottom: 1px solid #dee2e6;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: #f7f7f7;
}

.table-hover tbody tr:hover td {
    color: #474648;
}

/* Actions */
.actions {
    display: flex;
    justify-content: end;
}

.actions a, .actions button {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 5px;
    border: none;
    background: transparent;
}

.actions a:hover, .actions button:hover {
    background-color: #3d5ee1 !important;
    color: #fff !important;
}

/* Checkbox styling */
.form-check-input {
    width: 18px;
    height: 18px;
    margin-top: 0;
}

.form-check-input:checked {
    background-color: #3d5ee1;
    border-color: #3d5ee1;
}

/* Badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.badge.bg-primary {
    background-color: #3d5ee1 !important;
}

.badge.bg-success {
    background-color: #7bb13c !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

/* Text styling */
.text-muted {
    color: #6c757d !important;
}

/* Responsive */
@media (max-width: 768px) {
    .card-table .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .table th, .table td {
        padding: 10px 8px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#selectAllActivities').on('change', function() {
        $('.form-check-input').prop('checked', $(this).is(':checked'));
    });
});
</script>
@endpush

@endsection 