@extends('layouts.master')
@section('content')
{!! Toastr::message() !!}
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <h3 class="page-title">{{ $activity->title }}</h3>
            <p class="text-muted">Lesson: {{ $lesson->title }} | Due: {{ $activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('M d, Y') : 'N/A' }}</p>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <h5>Instructions</h5>
                <p>{!! nl2br(e($activity->instructions)) !!}</p>
            </div>
        </div>
        @if($activity->allows_submission)
            @if($existingSubmission)
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-success">You submitted this activity on {{ $existingSubmission->submitted_at?->format('M d, Y H:i') ?? $existingSubmission->created_at->format('M d, Y H:i') }}.</div>
                        @if($existingSubmission->status === 'graded')
                            <a href="{{ route('student.activities.view-grade', [$lesson, $activity, $existingSubmission]) }}" class="btn btn-primary">View Grade & Feedback</a>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('student.activities.submit', [$lesson, $activity]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Upload File (PDF, DOC, PPT, JPG, PNG — max 10MB)</label>
                                <input type="file" name="file" class="form-control" required>
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
