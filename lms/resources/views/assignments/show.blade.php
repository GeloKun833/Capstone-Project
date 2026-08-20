@extends('layouts.master')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Assignment Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Assignments</a></li>
                        <li class="breadcrumb-item active">{{ $assignment->title }}</li>
                    </ul>
                </div>
                <div class="col-auto text-right float-right ml-auto">
                    <div class="btn-group" role="group">
                        <a href="{{ route('assignments.edit', $assignment->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('assignments.submissions', $assignment->id) }}" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Grade Submissions
                        </a>
                        <a href="{{ route('assignments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                {{-- Assignment Details --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Assignment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Title</h6>
                                <p class="fw-bold">{{ $assignment->title }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                @switch($assignment->status)
                                    @case('draft')
                                        <span class="badge bg-warning">Draft</span>
                                        @break
                                    @case('published')
                                        <span class="badge bg-success">Published</span>
                                        @break
                                    @case('closed')
                                        <span class="badge bg-danger">Closed</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($assignment->status) }}</span>
                                @endswitch
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Subject</h6>
                                <p class="fw-bold">{{ $assignment->subject->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Section</h6>
                                <p class="fw-bold">{{ $assignment->section->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Academic Year</h6>
                                <p class="fw-bold">{{ $assignment->academicYear->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Semester</h6>
                                <p class="fw-bold">{{ $assignment->semester->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Due Date</h6>
                                <p class="fw-bold">
                                    @if($assignment->due_date)
                                        {{ \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') }}
                                        @if($assignment->due_time)
                                            at {{ $assignment->due_time }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Maximum Score</h6>
                                <p class="fw-bold">{{ $assignment->max_score }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted">Description</h6>
                            <p>{{ $assignment->description }}</p>
                        </div>

                        @if($assignment->submission_instructions)
                            <div class="mb-4">
                                <h6 class="text-muted">Submission Instructions</h6>
                                <p>{{ $assignment->submission_instructions }}</p>
                            </div>
                        @endif

                        {{-- Assignment Settings --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Late Submissions</h6>
                                <p class="fw-bold">
                                    @if($assignment->allows_late_submission)
                                        <span class="text-success">Allowed</span>
                                        @if($assignment->late_submission_penalty)
                                            <br><small class="text-muted">Penalty: {{ $assignment->late_submission_penalty }}%</small>
                                        @endif
                                    @else
                                        <span class="text-danger">Not Allowed</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">File Upload</h6>
                                <p class="fw-bold">
                                    @if($assignment->requires_file_upload)
                                        <span class="text-success">Required</span>
                                        @if($assignment->max_file_size)
                                            <br><small class="text-muted">Max size: {{ $assignment->max_file_size }}MB</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Optional</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($assignment->assignment_file)
                            <div class="mb-4">
                                <h6 class="text-muted">Assignment File</h6>
                                <a href="{{ Storage::url($assignment->assignment_file) }}" class="btn btn-outline-primary" target="_blank">
                                    <i class="fas fa-download me-2"></i>Download Assignment File
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Quick Stats --}}
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Quick Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="mb-3">
                                    <h4 class="text-primary">{{ $assignment->submissions()->count() }}</h4>
                                    <small class="text-muted">Total Submissions</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <h4 class="text-success">{{ $assignment->submissions()->where('status', 'graded')->count() }}</h4>
                                    <small class="text-muted">Graded</small>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="mb-3">
                                    <h4 class="text-warning">{{ $assignment->submissions()->where('status', 'submitted')->count() }}</h4>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <h4 class="text-danger">{{ $assignment->submissions()->where('status', 'late')->count() }}</h4>
                                    <small class="text-muted">Late</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($assignment->status === 'draft')
                                <form action="{{ route('assignments.publish', $assignment->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-publish me-2"></i>Publish Assignment
                                    </button>
                                </form>
                            @elseif($assignment->status === 'published')
                                <form action="{{ route('assignments.close', $assignment->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-lock me-2"></i>Close Assignment
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('assignments.submissions', $assignment->id) }}" class="btn btn-primary">
                                <i class="fas fa-check-circle me-2"></i>Grade Submissions
                            </a>

                            <a href="{{ route('assignments.edit', $assignment->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit Assignment
                            </a>

                            <form action="{{ route('assignments.destroy', $assignment->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100" 
                                        onclick="return confirm('Are you sure you want to delete this assignment? This action cannot be undone.')">
                                    <i class="fas fa-trash me-2"></i>Delete Assignment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Export Options --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Export</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('assignments.export-pdf', $assignment->id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                            </a>
                            <a href="{{ route('assignments.export-excel') }}" class="btn btn-outline-success">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
