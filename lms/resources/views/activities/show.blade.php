@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $activity->title }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lessons.activities.index', $lesson) }}">Activities</a></li>
                        <li class="breadcrumb-item active">{{ $activity->title }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('lessons.activities.edit', [$lesson, $activity]) }}" class="btn btn-primary me-2"><i class="fas fa-edit"></i> Edit</a>
                    <a href="{{ route('lessons.activities.submissions', [$lesson, $activity]) }}" class="btn btn-info"><i class="fas fa-inbox"></i> Submissions</a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p><strong>Status:</strong> {{ $activity->isPublished() ? 'Posted' : 'Draft' }}</p>
                <p><strong>Due:</strong> {{ $activity->formattedDue() }}</p>
                <p><strong>Type:</strong> {{ $activity->typeLabel() }}</p>
                <p><strong>Points:</strong> {{ $activity->points ?: 'Not scored' }}</p>
                <p><strong>Student response:</strong>
                    @if($activity->isQuiz())
                        Quiz
                        · {{ $activity->quizQuestionItems()->count() }} questions
                        · {{ $activity->attempts_allowed ?: 1 }} attempt{{ ($activity->attempts_allowed ?: 1) === 1 ? '' : 's' }}
                        · Passing score {{ $activity->passing_score ?: '—' }}%
                    @elseif($activity->usesFileSubmission())
                        File submission
                    @else
                        {{ $activity->allows_submission ? 'File turn-in' : 'No file turn-in' }}
                    @endif
                </p>
                @if($activity->isQuiz())
                    <p>
                        <a href="{{ route('lessons.activities.quiz-preview', [$lesson, $activity]) }}">Preview</a>
                        · <a href="{{ route('lessons.activities.quiz-results', [$lesson, $activity]) }}">Results and grading</a>
                    </p>
                    <p class="text-muted">Answer keys stay on the grading page. This score is not entered into the term grade until you encode it.</p>
                @endif
                @foreach($activity->attachments ?? [] as $file)
                    <p><a href="{{ asset('storage/'.$file['path']) }}" target="_blank" rel="noopener">{{ $file['name'] ?? 'Material' }}</a></p>
                @endforeach
                <hr>
                <h5>Instructions</h5>
                <p>{!! nl2br(e($activity->instructions)) !!}</p>
                <p><strong>Submissions:</strong> {{ $activity->submissions->count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
