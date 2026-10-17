@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Student preview</h3>
                    <p class="text-muted mb-0">{{ $activity->title }} · Answer keys stay hidden in this preview.</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('lessons.activities.edit', [$lesson, $activity]) }}" class="btn btn-outline-secondary">Back to quiz</a>
                </div>
            </div>
        </div>
        <div class="dir-card mb-3">
            <div class="p-3 p-md-4">
                <h5>{{ $activity->title }}</h5>
                <p style="white-space: pre-line;">{{ $activity->instructions }}</p>
                <p class="mb-0 text-muted">{{ $activity->points ?: 0 }} points
                    @if($activity->time_limit_minutes) · {{ $activity->time_limit_minutes }} minutes @endif
                    · {{ $activity->attempts_allowed ?: 1 }} attempt{{ ($activity->attempts_allowed ?: 1) === 1 ? '' : 's' }}
                    · Passing score {{ $activity->passing_score ?: '—' }}%
                </p>
            </div>
        </div>
        @forelse($questions as $index => $question)
            <div class="dir-card mb-3">
                <div class="p-3 p-md-4">
                    <h5>Question {{ $index + 1 }} of {{ $questions->count() }}</h5>
                    <p style="white-space: pre-line;">{{ $question['prompt'] }}</p>
                    <p class="text-muted small">{{ $question['type_label'] }} · {{ $question['points'] }} points</p>
                    @if($question['image'])
                        <img src="{{ $question['image'] }}" alt="" class="mb-3 rounded" style="max-width: 100%; max-height: 240px;">
                    @endif
                    @if(in_array($question['type'], ['short_answer', 'essay'], true))
                        <div class="form-control bg-light">{{ $question['type'] === 'essay' ? 'Students write a longer response here.' : 'Students write a short response here.' }}</div>
                    @else
                        @foreach($question['choices'] as $choice)
                            <label class="d-flex gap-2 mb-2"><input type="{{ $question['type'] === 'multiple_select' ? 'checkbox' : 'radio' }}" disabled> <span>{{ $choice['text'] }}</span></label>
                        @endforeach
                    @endif
                </div>
            </div>
        @empty
            <div class="alert alert-warning">Add questions before previewing the quiz.</div>
        @endforelse
    </div>
</div>
@endsection
