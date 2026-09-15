@extends('layouts.master')
@section('content')

@php
    $enrollment = null;
    if (auth()->user()?->student && $lesson->subject_id) {
        $enrollment = auth()->user()->student->enrollments()
            ->where('subject_id', $lesson->subject_id)
            ->where('status', 'active')
            ->first();
    }
@endphp

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $activity->title }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        @if($enrollment)
                            <li class="breadcrumb-item">
                                <a href="{{ route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'lessons']) }}">
                                    {{ $lesson->subject->subject_name ?? 'Class' }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('student.lessons.show', [$enrollment->id, $lesson->id]) }}">
                                    {{ $lesson->title }}
                                </a>
                            </li>
                        @else
                            <li class="breadcrumb-item">{{ $lesson->title }}</li>
                        @endif
                        <li class="breadcrumb-item active">{{ $activity->title }}</li>
                    </ul>
                    <p class="text-muted mb-0">
                        Lesson: {{ $lesson->title }}
                        | Due: {{ $activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('M d, Y') : 'N/A' }}
                    </p>
                </div>
                <div class="col-auto">
                    @if($enrollment)
                        <a href="{{ route('student.lessons.show', [$enrollment->id, $lesson->id]) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Lesson
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5>Instructions</h5>
                <p>{!! nl2br(e($activity->instructions)) !!}</p>
            </div>
        </div>

        @if($activity->allows_submission)
            @if($existingSubmission)
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="alert alert-success mb-3">
                            You submitted this activity on
                            {{ $existingSubmission->submitted_at?->format('M d, Y H:i') ?? $existingSubmission->created_at->format('M d, Y H:i') }}.
                        </div>

                        @if($existingSubmission->status === 'graded')
                            <h5 class="mb-3">Your Grade</h5>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Score</small>
                                        <strong class="fs-4">
                                            {{ $existingSubmission->total_score }}{{ $existingSubmission->max_possible_score ? ' / ' . $existingSubmission->max_possible_score : '' }}
                                        </strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Percentage</small>
                                        <strong class="fs-4">{{ number_format((float) ($existingSubmission->percentage ?? 0), 1) }}%</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Letter Grade</small>
                                        <span class="badge bg-{{ $existingSubmission->letter_grade_color }} fs-6">
                                            {{ $existingSubmission->letter_grade ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Graded On</small>
                                        <strong>{{ optional($existingSubmission->graded_at)->format('M d, Y') ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light border rounded p-3">
                                <h6 class="mb-2"><i class="fas fa-comment me-1"></i> Teacher Comments</h6>
                                @if($existingSubmission->feedback)
                                    <p class="mb-0" style="white-space: pre-line;">{{ $existingSubmission->feedback }}</p>
                                @else
                            <p class="text-muted mb-0">No teacher comments provided.</p>
                        @endif
                    </div>
                        @else
                            <span class="badge bg-warning text-dark">Submitted — waiting for teacher grade</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('student.activities.submit', [$lesson, $activity]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Upload File (PDF, DOCX, PPT, PPTX, JPG, PNG — max 10MB)</label>
                                <input type="file" name="file" class="form-control" accept=".pdf,.docx,.ppt,.pptx,.jpg,.jpeg,.png" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comments (optional)</label>
                                <textarea name="comments" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Submit Activity</button>
                        </form>
                    </div>
                </div>
            @endif
        @else
            <div class="alert alert-info">This activity does not require an online submission.</div>
        @endif
    </div>
</div>
@endsection
