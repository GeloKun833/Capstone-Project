@extends('layouts.master')
@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">My Assignments</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Assignments</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('student.assignments.index') }}">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <select name="subject_id" class="form-control">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->subject_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">All Assignments</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Not Submitted</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Submitted</option>
                                    <option value="due_soon" {{ request('status') == 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <button type="submit" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <a href="{{ route('student.assignments.index') }}" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-redo"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Assignments List --}}
        <div class="row">
            <div class="col-md-12">
                @forelse($assignments as $assignment)
                    @php
                        $submission = $assignment->submissions()->where('student_id', auth()->user()->student->id)->first();
                        $isSubmitted = $submission !== null;
                        $isOverdue = !$isSubmitted && now() > $assignment->dueDateTime;
                        $isDueSoon = !$isSubmitted && now()->diffInHours($assignment->dueDateTime) <= 24 && now() < $assignment->dueDateTime;
                    @endphp
                    
                    <div class="card mb-3 {{ $isOverdue ? 'border-danger' : ($isDueSoon ? 'border-warning' : '') }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="card-title mb-0 me-3">
                                            <a href="{{ route('student.assignments.show', $assignment->id) }}">
                                                {{ $assignment->title }}
                                            </a>
                                        </h5>
                                        
                                        @if($isSubmitted)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Submitted
                                            </span>
                                        @elseif($isOverdue)
                                            <span class="badge bg-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Overdue
                                            </span>
                                        @elseif($isDueSoon)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Due Soon
                                            </span>
                                        @else
                                            <span class="badge bg-info">
                                                <i class="fas fa-hourglass-half"></i> Pending
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <p class="text-muted mb-2">{{ Str::limit($assignment->description, 150) }}</p>
                                    
                                    <div class="row text-muted small">
                                        <div class="col-md-6">
                                            <i class="fas fa-book"></i> <strong>Subject:</strong> {{ $assignment->subject->subject_name ?? 'N/A' }}<br>
                                            <i class="fas fa-user"></i> <strong>Teacher:</strong> {{ $assignment->teacher->full_name ?? 'N/A' }}
                                        </div>
                                        <div class="col-md-6">
                                            <i class="fas fa-calendar"></i> <strong>Due:</strong> {{ $assignment->dueDateTime->format('M d, Y h:i A') }}<br>
                                            <i class="fas fa-star"></i> <strong>Points:</strong> {{ $assignment->max_score }}
                                        </div>
                                    </div>
                                    
                                    @if($isSubmitted)
                                        <div class="mt-2">
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-calendar-check"></i> Submitted: {{ $submission->submitted_at->format('M d, Y h:i A') }}
                                            </span>
                                            @if($submission->score !== null)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Score: {{ $submission->score }}/{{ $submission->max_score }}
                                                </span>
                                            @endif
                                            @if($submission->is_late)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock"></i> Late Submission
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="ms-3">
                                    <a href="{{ route('student.assignments.show', $assignment->id) }}" class="btn btn-primary">
                                        @if($isSubmitted)
                                            <i class="fas fa-eye"></i> View
                                        @else
                                            <i class="fas fa-upload"></i> Submit
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h5>No Assignments Found</h5>
                            <p class="text-muted">You don't have any assignments at the moment. Check back later!</p>
                        </div>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if($assignments->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $assignments->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

