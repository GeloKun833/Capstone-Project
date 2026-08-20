@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Lesson Details</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                            <li class="breadcrumb-item active">Lesson Details</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Lesson Information Card -->
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">{{ $lesson->title }}</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('lessons.edit', $lesson) }}" class="btn btn-outline-primary me-2">
                                            <i class="fas fa-edit"></i> Edit Lesson
                                        </a>
                                        <a href="{{ route('lessons.activities.index', $lesson) }}" class="btn btn-primary">
                                            <i class="fas fa-tasks"></i> Manage Activities
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Lesson Status -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <span class="badge {{ $lesson->status_badge }} fs-6">{{ ucfirst($lesson->status) }}</span>
                                    <small class="text-muted ms-2">Lesson ID: #{{ $lesson->id }}</small>
                                </div>
                            </div>

                            <!-- Lesson Description -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5><i class="fas fa-info-circle me-2"></i>Description</h5>
                                    <p class="text-muted">{{ $lesson->description }}</p>
                                </div>
                            </div>

                            <!-- Lesson Details -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">Subject:</label>
                                        <span class="info-value">{{ $lesson->subject->subject_name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">Section:</label>
                                        <span class="info-value">{{ $lesson->section->name }} (Grade {{ $lesson->section->grade_level }})</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">Lesson Date:</label>
                                        <span class="info-value">{{ $lesson->lesson_date->format('F d, Y') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">Academic Period:</label>
                                        <span class="info-value">{{ $lesson->academicYear->name }} - {{ $lesson->semester->name }}</span>
                                    </div>
                                </div>
                            </div>



                            <!-- Lesson Materials -->
                            @if($lesson->file_path)
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5><i class="fas fa-file me-2"></i>Lesson Materials</h5>
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">{{ $lesson->file_name }}</h6>
                                                        <small class="text-muted">Uploaded on {{ $lesson->created_at->format('M d, Y') }}</small>
                                                    </div>
                                                    <a href="{{ $lesson->file_url }}" target="_blank" class="btn btn-outline-primary">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Lesson Summary Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-bar me-2"></i>Lesson Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-item text-center">
                                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                                            <i class="fas fa-tasks fa-lg"></i>
                                        </div>
                                        <h4 class="mb-0">{{ $lesson->activities->count() }}</h4>
                                        <small class="text-muted">Activities</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-item text-center">
                                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                                            <i class="fas fa-users fa-lg"></i>
                                        </div>
                                        <h4 class="mb-0">{{ $lesson->section->students->count() ?? 0 }}</h4>
                                        <small class="text-muted">Students</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lesson Timeline -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>Lesson Timeline
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Created</h6>
                                        <p class="timeline-text">{{ $lesson->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                @if($lesson->status === 'published')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Published</h6>
                                            <p class="timeline-text">{{ $lesson->updated_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($lesson->status === 'completed')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Completed</h6>
                                            <p class="timeline-text">{{ $lesson->updated_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Scheduled</h6>
                                        <p class="timeline-text">{{ $lesson->lesson_date->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($lesson->status === 'draft')
                                    <form action="{{ route('lessons.publish', $lesson) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-paper-plane"></i> Publish Lesson
                                        </button>
                                    </form>
                                @endif
                                @if($lesson->status === 'published')
                                    <form action="{{ route('lessons.complete', $lesson) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm">
                                            <i class="fas fa-check"></i> Mark Complete
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('lessons.activities.create', $lesson) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add Activity
                                </a>
                                <a href="{{ route('lessons.activities.index', $lesson) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-list"></i> View Activities
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities Section -->
            @if($lesson->activities->count() > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-table">
                            <div class="card-body">
                                <div class="page-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="page-title">Lesson Activities</h3>
                                        </div>
                                        <div class="col-auto text-end float-end ms-auto download-grp">
                                            <a href="{{ route('lessons.activities.create', $lesson) }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add Activity
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Activity Title</th>
                                                <th>Due Date</th>
                                                <th>Submissions</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lesson->activities as $activity)
                                                <tr>
                                                    <td>
                                                        <h2>
                                                            <a href="{{ route('lessons.activities.show', [$lesson, $activity]) }}">{{ $activity->title }}</a>
                                                        </h2>
                                                        <small class="text-muted">{{ Str::limit($activity->instructions, 100) }}</small>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $activity->due_date->format('M d, Y') }}</strong>
                                                        @if($activity->is_overdue)
                                                            <br><span class="badge bg-danger">Overdue</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $activity->submission_count }}</span> submitted
                                                        @if($activity->allows_submission)
                                                            <br><span class="badge bg-success">{{ $activity->graded_count }}</span> graded
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($activity->allows_submission)
                                                            <span class="badge bg-primary">Submission Allowed</span>
                                                        @else
                                                            <span class="badge bg-secondary">No Submission</span>
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
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
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

/* Info items */
.info-item {
    margin-bottom: 15px;
}

.info-label {
    display: block;
    font-weight: 600;
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 5px;
}

.info-value {
    display: block;
    font-weight: 500;
    color: #2c323f;
    font-size: 16px;
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

.btn-outline-primary {
    color: #3d5ee1;
    border-color: #3d5ee1;
}

.btn-outline-primary:hover {
    background-color: #3d5ee1;
    border-color: #3d5ee1;
    color: #fff;
}

.btn-success {
    background-color: #7bb13c;
    border: 1px solid #7bb13c;
}

.btn-info {
    background-color: #17a2b8;
    border: 1px solid #17a2b8;
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

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-left: 10px;
}

.timeline-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #2c323f;
}

.timeline-text {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 0;
}

/* Actions */
.actions {
    display: flex;
    justify-content: end;
}

.actions a {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 5px;
}

.actions a:hover {
    background-color: #3d5ee1 !important;
    color: #fff !important;
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

/* Text styling */
.text-muted {
    color: #6c757d !important;
}

/* Responsive */
@media (max-width: 768px) {
    .card-table .card-body {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .info-item {
        margin-bottom: 10px;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline::before {
        left: 10px;
    }
    
    .timeline-marker {
        left: -17px;
    }
}
</style>
@endpush

@endsection 