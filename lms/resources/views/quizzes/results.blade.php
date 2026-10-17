@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Quiz results</h3>
                    <p class="text-muted mb-0">{{ $activity->title }} · Scores stay on the quiz until you encode the term grade.</p>
                </div>
                <div class="col-auto d-flex gap-2">
                    <a href="{{ route('lessons.activities.quiz-preview', [$lesson, $activity]) }}" class="btn btn-outline-secondary">Preview</a>
                    <a href="{{ route('lessons.activities.edit', [$lesson, $activity]) }}" class="btn btn-outline-secondary">Edit quiz</a>
                </div>
            </div>
        </div>
        <div class="dir-card">
            <div class="table-responsive">
                <table class="table dir-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student number</th>
                            <th>Attempt</th>
                            <th>Submitted</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attempts as $attempt)
                            <tr>
                                <td>{{ trim(($attempt->student->last_name ?? '').', '.($attempt->student->first_name ?? '')) }}</td>
                                <td>{{ $attempt->student->admission_id ?: ($attempt->student->roll ?: '—') }}</td>
                                <td>{{ $attempt->attempt_number }}</td>
                                <td>{{ $attempt->submitted_at?->timezone('Asia/Manila')->format('M j, Y g:i A') ?: 'In progress' }}</td>
                                <td>
                                    @if($attempt->status === 'graded')
                                        {{ $attempt->score }} / {{ $attempt->max_score }} ({{ number_format((float) $attempt->percentage, 1) }}%)
                                    @elseif($attempt->status === 'submitted')
                                        Needs grading
                                    @else
                                        In progress
                                    @endif
                                </td>
                                <td>
                                    @if($attempt->status !== 'graded')
                                        —
                                    @elseif($attempt->passed === null)
                                        Recorded
                                    @elseif($attempt->passed)
                                        Passed
                                    @else
                                        Below passing score
                                    @endif
                                </td>
                                <td>
                                    @if($attempt->status !== 'in_progress')
                                        <a href="{{ route('lessons.activities.quiz-grade', [$lesson, $activity, $attempt]) }}">Review</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No attempts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
