@extends('layouts.master')
@section('content')
@php
    $graded = $attempts->where('status', 'graded');
    $average = $graded->count() ? round((float) $graded->avg('percentage'), 1) : null;
    $passedCount = $graded->filter(fn ($attempt) => $attempt->passed === true || $attempt->passed === 1)->count();
    $belowCount = $graded->filter(fn ($attempt) => $attempt->passed === false || $attempt->passed === 0)->count();
    $pendingCount = $attempts->where('status', 'submitted')->count();
@endphp
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="qr">
            <div class="qr-head">
                <div>
                    <p class="qr-kicker">Quiz results</p>
                    <h1 class="qr-title">{{ $activity->title }}</h1>
                    <div class="qr-chips">
                        @if($lesson->section?->grade_level)
                            <span class="qr-chip">{{ $lesson->section->grade_level }}</span>
                        @endif
                        @if($lesson->section?->name && $lesson->section->name !== $lesson->section->grade_level)
                            <span class="qr-chip">{{ $lesson->section->name }}</span>
                        @endif
                        <span class="qr-chip">{{ $lesson->subject->subject_name ?? 'Subject' }}</span>
                        <span class="qr-chip">{{ $activity->points ?: 0 }} points</span>
                        @if($activity->passing_score)
                            <span class="qr-chip">Passing {{ $activity->passing_score }}%</span>
                        @endif
                    </div>
                    <p class="qr-note">Scores stay on the quiz until you encode the term grade.</p>
                </div>
                <div class="qr-head-actions">
                    <a href="{{ route('lessons.activities.quiz-preview', [$lesson, $activity]) }}" class="qr-btn qr-btn-ghost">Preview</a>
                    <a href="{{ route('lessons.activities.edit', [$lesson, $activity]) }}" class="qr-btn qr-btn-ghost">Edit quiz</a>
                </div>
            </div>

            <div class="qr-stats">
                <div class="qr-stat">
                    <span>Attempts</span>
                    <strong>{{ $attempts->count() }}</strong>
                </div>
                <div class="qr-stat">
                    <span>Average</span>
                    <strong>{{ $average === null ? '—' : number_format($average, 1).'%' }}</strong>
                </div>
                <div class="qr-stat">
                    <span>Passed</span>
                    <strong>{{ $passedCount }}</strong>
                </div>
                <div class="qr-stat">
                    <span>{{ $pendingCount ? 'Needs grading' : 'Below passing' }}</span>
                    <strong>{{ $pendingCount ?: $belowCount }}</strong>
                </div>
            </div>

            @forelse($attempts as $attempt)
                @php
                    $first = $attempt->student->first_name ?? '';
                    $last = $attempt->student->last_name ?? '';
                    $initials = strtoupper(mb_substr($first, 0, 1).mb_substr($last, 0, 1)) ?: 'S';
                    $number = $attempt->student->admission_id ?: ($attempt->student->roll ?: 'No student number');
                    $passed = $attempt->passed === true || $attempt->passed === 1;
                    $below = $attempt->status === 'graded' && ($attempt->passed === false || $attempt->passed === 0);
                    $percent = $attempt->status === 'graded' ? max(0, min(100, (float) $attempt->percentage)) : 0;
                @endphp
                <article class="qr-row">
                    <div class="qr-who">
                        <span class="qr-avatar" aria-hidden="true">{{ $initials }}</span>
                        <div>
                            <strong>{{ trim($last.', '.$first, ' ,') ?: 'Student' }}</strong>
                            <span>{{ $number }} · Attempt {{ $attempt->attempt_number }}</span>
                        </div>
                    </div>
                    <div class="qr-when">{{ $attempt->submitted_at?->timezone('Asia/Manila')->format('M j, Y g:i A') ?: 'In progress' }}</div>
                    <div class="qr-score">
                        @if($attempt->status === 'graded')
                            <strong>{{ number_format((float) $attempt->percentage, 1) }}%</strong>
                            <span>{{ $attempt->score }} / {{ $attempt->max_score }}</span>
                            <div class="qr-bar {{ $passed ? 'is-pass' : ($below ? 'is-low' : '') }}" aria-hidden="true"><i style="width: {{ $percent }}%"></i></div>
                        @elseif($attempt->status === 'submitted')
                            <strong>Pending</strong>
                            <span>Needs grading</span>
                        @else
                            <strong>—</strong>
                            <span>In progress</span>
                        @endif
                    </div>
                    @if($attempt->status !== 'graded')
                        <span class="qr-badge">{{ $attempt->status === 'submitted' ? 'Needs grading' : 'In progress' }}</span>
                    @elseif($attempt->passed === null)
                        <span class="qr-badge">Recorded</span>
                    @elseif($passed)
                        <span class="qr-badge is-pass">Passed</span>
                    @else
                        <span class="qr-badge is-low">Below passing score</span>
                    @endif
                    @if($attempt->status !== 'in_progress')
                        <a class="qr-btn qr-btn-review" href="{{ route('lessons.activities.quiz-grade', [$lesson, $activity, $attempt]) }}">Review</a>
                    @else
                        <span class="qr-spacer"></span>
                    @endif
                </article>
            @empty
                <div class="qr-empty">
                    <strong>No attempts yet</strong>
                    <p>Results appear here after a student submits this quiz.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .qr { max-width: 980px; margin: 0 auto; }
    .qr-head { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 18px; }
    .qr-kicker { margin: 0 0 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #3d5ee1; }
    .qr-title { margin: 0; font-size: 1.7rem; font-weight: 700; letter-spacing: -0.03em; color: #1f2430; }
    .qr-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
    .qr-chip { background: #fff; border: 1px solid #e6e8ee; color: #3c4456; border-radius: 999px; padding: 5px 10px; font-size: 0.78rem; font-weight: 600; }
    .qr-note { margin: 12px 0 0; color: #6b7287; font-size: 0.88rem; }
    .qr-head-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .qr-btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; padding: 10px 14px; font-weight: 700; text-decoration: none; }
    .qr-btn-ghost { background: #fff; color: #3c4456; border: 1px solid #e3e6ee; }
    .qr-btn-ghost:hover { color: #1f2430; border-color: #c9d4f8; }
    .qr-btn-review { background: #3d5ee1; color: #fff; min-width: 92px; }
    .qr-btn-review:hover { background: #314db8; color: #fff; }
    .qr-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 14px; }
    .qr-stat, .qr-row, .qr-empty { background: #fff; border: 1px solid #e6e8ee; border-radius: 18px; box-shadow: 0 1px 2px rgba(31,36,48,.04); }
    .qr-stat { padding: 14px 16px; }
    .qr-stat span { display: block; color: #6b7287; font-size: 0.75rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .qr-stat strong { display: block; margin-top: 4px; font-size: 1.35rem; letter-spacing: -0.03em; color: #1f2430; }
    .qr-row { display: flex; flex-wrap: wrap; align-items: center; gap: 12px 18px; padding: 16px 18px; margin-bottom: 10px; }
    .qr-who { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1 1 220px; }
    .qr-when { flex: 1 1 150px; }
    .qr-score { flex: 0 1 120px; }
    .qr-btn-review { margin-left: auto; }
    .qr-avatar { width: 42px; height: 42px; border-radius: 14px; background: #eef2ff; color: #24356e; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex: 0 0 42px; }
    .qr-who strong, .qr-who span { display: block; }
    .qr-who strong { color: #1f2430; }
    .qr-who span, .qr-when, .qr-score span { color: #6b7287; font-size: 0.82rem; }
    .qr-score strong { display: block; color: #1f2430; font-size: 1.05rem; }
    .qr-bar { height: 6px; background: #eef1f6; border-radius: 999px; margin-top: 6px; overflow: hidden; }
    .qr-bar i { display: block; height: 100%; background: #3d5ee1; border-radius: inherit; }
    .qr-bar.is-pass i { background: #16a34a; }
    .qr-bar.is-low i { background: #ea580c; }
    .qr-badge { justify-self: start; border-radius: 999px; padding: 6px 10px; background: #f4f6fb; color: #3c4456; font-size: 0.78rem; font-weight: 700; white-space: nowrap; }
    .qr-badge.is-pass { background: #ecfdf3; color: #166534; }
    .qr-badge.is-low { background: #fff7ed; color: #c2410c; }
    .qr-empty { padding: 36px 20px; text-align: center; }
    .qr-empty strong { color: #1f2430; }
    .qr-empty p { margin: 6px 0 0; color: #6b7287; }
    @media (max-width: 860px) {
        .qr-head, .qr-row { display: flex; flex-direction: column; align-items: stretch; }
        .qr-stats { grid-template-columns: 1fr 1fr; }
        .qr-btn-review { align-self: flex-start; }
    }
</style>
@endpush
