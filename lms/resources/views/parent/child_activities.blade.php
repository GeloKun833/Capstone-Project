@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ $child->full_name }} - Activities</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $child->full_name }} - Activities</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <img src="{{ $child->upload ? asset('storage/' . $child->upload) : URL::to('assets/img/profiles/avatar-01.jpg') }}" 
                                         alt="Student Photo" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="col-md-6">
                                    <h4 class="mb-2">{{ $child->full_name }}</h4>
                                    <p class="text-muted mb-1">
                                        <strong>Student ID:</strong> {{ $child->admission_id }}
                                    </p>
                                    <p class="text-muted mb-1">
                                        <strong>Section:</strong> {{ $child->sections->first()->name ?? 'Not Assigned' }}
                                    </p>
                                    <p class="text-muted mb-0">
                                        <strong>Academic Period:</strong> {{ $academicYear->year ?? 'Not Set' }} - {{ $semester->name ?? 'Not Set' }}
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-primary mb-2">Activities Summary</h5>
                                        <h3 class="mb-0">{{ $activities->count() }} Total</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Activities Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="bg-success text-white rounded p-4">
                                        <h3>{{ $activities->where('due_date', '>', now())->count() }}</h3>
                                        <p class="mb-0">Pending</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-danger text-white rounded p-4">
                                        <h3>{{ $activities->where('due_date', '<', now())->whereDoesntHave('submissions', function($q) use ($child) { $q->where('student_id', $child->id); })->count() }}</h3>
                                        <p class="mb-0">Overdue</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-info text-white rounded p-4">
                                        <h3>{{ $submissions->count() }}</h3>
                                        <p class="mb-0">Submitted</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-warning text-white rounded p-4">
                                        <h3>{{ $submissions->where('status', 'graded')->count() }}</h3>
                                        <p class="mb-0">Graded</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">All Activities</h5>
                        </div>
                        <div class="card-body">
                            @if($activities->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Activity</th>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($activities as $activity)
                                                @php
                                                    $submission = $submissions->where('activity_id', $activity->id)->first();
                                                    $isOverdue = $activity->due_date < now() && !$submission;
                                                @endphp
                                                <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($isOverdue)
                                                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                                            @endif
                                                            <div>
                                                                <strong>{{ $activity->title }}</strong>
                                                                <br><small class="text-muted">{{ Str::limit($activity->instructions, 50) }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <i class="fas fa-book text-primary"></i>
                                                            </a>
                                                            <a href="#">{{ $activity->lesson->subject->subject_name ?? 'N/A' }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <img class="avatar-img rounded-circle" src="{{ $activity->lesson->teacher->avatar ?? URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="User Image">
                                                            </a>
                                                            <a href="#">{{ $activity->lesson->teacher->full_name ?? 'N/A' }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <span class="{{ $isOverdue ? 'text-danger' : '' }}">
                                                            {{ $activity->due_date->format('M d, Y') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($submission)
                                                            @if($submission->status == 'graded')
                                                                <span class="badge bg-success">Graded</span>
                                                            @else
                                                                <span class="badge bg-info">Submitted</span>
                                                            @endif
                                                        @elseif($isOverdue)
                                                            <span class="badge bg-danger">Overdue</span>
                                                        @else
                                                            <span class="badge bg-warning">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission && $submission->status == 'graded')
                                                            <strong>{{ $submission->total_score }}/{{ $submission->max_score }}</strong>
                                                            <br><small class="text-muted">{{ number_format(($submission->total_score / $submission->max_score) * 100, 1) }}%</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $activities->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-tasks text-muted" style="font-size: 4rem; opacity: 0.6;"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">No Activities Available</h4>
                                    <p class="text-muted mb-4">No activities have been assigned to this student yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Submissions -->
            @if($submissions->count() > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Recent Submissions</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Activity</th>
                                                <th>Subject</th>
                                                <th>Submitted</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                                <th>Feedback</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($submissions->take(10) as $submission)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $submission->activity->title }}</strong>
                                                    </td>
                                                    <td>{{ $submission->activity->lesson->subject->subject_name ?? 'N/A' }}</td>
                                                    <td>{{ $submission->submitted_at->format('M d, Y h:i A') }}</td>
                                                    <td>
                                                        @if($submission->status == 'graded')
                                                            <span class="badge bg-success">Graded</span>
                                                        @else
                                                            <span class="badge bg-info">Submitted</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission->status == 'graded')
                                                            <strong>{{ $submission->total_score }}/{{ $submission->max_score }}</strong>
                                                            <br><small class="text-muted">{{ number_format(($submission->total_score / $submission->max_score) * 100, 1) }}%</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission->comments)
                                                            <span class="text-muted">{{ Str::limit($submission->comments, 30) }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
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
@endsection 