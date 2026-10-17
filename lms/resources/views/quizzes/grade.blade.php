@extends('layouts.master')
@section('content')
@php $responses = $attempt->responses->keyBy('quiz_question_id'); @endphp
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grade attempt</h3>
                    <p class="text-muted mb-0">
                        {{ trim(($attempt->student->last_name ?? '').', '.($attempt->student->first_name ?? '')) }}
                        · {{ $attempt->student->admission_id ?: ($attempt->student->roll ?: 'No student number') }}
                        · Attempt {{ $attempt->attempt_number }}
                        · {{ $attempt->submitted_at?->timezone('Asia/Manila')->format('M j, Y g:i A') }}
                    </p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('lessons.activities.quiz-results', [$lesson, $activity]) }}" class="btn btn-outline-secondary">All results</a>
                </div>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('lessons.activities.quiz-grade.save', [$lesson, $activity, $attempt]) }}" method="POST">
            @csrf
            @method('PUT')
            @foreach($activity->quizQuestionItems as $index => $question)
                @php $response = $responses->get($question->id); @endphp
                <div class="dir-card mb-3">
                    <div class="p-3 p-md-4">
                        <h5>{{ $index + 1 }}. {{ $question->prompt }}</h5>
                        <p class="text-muted small">{{ $question->typeLabel() }} · {{ $question->points }} points</p>
                        @if($question->attachment_path)
                            <img src="{{ asset('storage/'.$question->attachment_path) }}" alt="" class="mb-3 rounded" style="max-width: 100%; max-height: 220px;">
                        @endif
                        <p class="mb-2"><strong>Student answer.</strong>
                            @if(in_array($question->question_type, ['short_answer', 'essay'], true))
                                <span style="white-space: pre-line;">{{ data_get($response, 'answer.text', 'No answer') }}</span>
                            @else
                                @php $selected = data_get($response, 'answer.selected', []); @endphp
                                {{ collect($question->choices ?? [])->filter(fn ($choice, $choiceIndex) => in_array($choiceIndex, $selected, false))->implode(', ') ?: 'No answer' }}
                            @endif
                        </p>
                        @if($question->isAutoGraded())
                            <p class="mb-2"><strong>Answer key.</strong>
                                {{ collect($question->choices ?? [])->filter(fn ($choice, $choiceIndex) => in_array($choiceIndex, $question->answer_key ?? [], false))->implode(', ') }}
                            </p>
                            <p class="mb-0">Points earned: {{ data_get($response, 'points_earned', 0) }} / {{ $question->points }}</p>
                        @else
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label" for="score{{ $question->id }}">Points earned</label>
                                    <input class="form-control" id="score{{ $question->id }}" type="number" min="0" max="{{ $question->points }}" step="0.5"
                                           name="scores[{{ $question->id }}]" value="{{ $response->points_earned ?? '' }}">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label" for="feedback{{ $question->id }}">Feedback for this question</label>
                                    <input class="form-control" id="feedback{{ $question->id }}" name="question_feedback[{{ $question->id }}]" value="{{ $response->feedback ?? '' }}">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
            <div class="dir-card mb-3">
                <div class="p-3 p-md-4">
                    <label class="form-label" for="feedback">Feedback for the attempt</label>
                    <textarea class="form-control" id="feedback" name="feedback" rows="3">{{ $attempt->feedback }}</textarea>
                    @if($attempt->status === 'graded')
                        <p class="mt-3 mb-0">Total {{ $attempt->score }} / {{ $attempt->max_score }} · {{ number_format((float) $attempt->percentage, 1) }}%
                            · {{ $attempt->passed ? 'Passed' : 'Below the passing score' }}</p>
                    @endif
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save grading</button>
        </form>
    </div>
</div>
@endsection
