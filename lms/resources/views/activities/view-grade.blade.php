@extends('layouts.master')
@section('content')
@php
    $isTeacher = auth()->user()?->role_name === 'Teacher';
    $percent = $submission->percentage;
    $bar = $percent === null ? 0 : max(0, min(100, (float) $percent));
    $passed = $percent !== null && (float) $percent >= 75;
    $studentName = trim(($submission->student->first_name ?? '').' '.($submission->student->last_name ?? '')) ?: 'Student';
    $initials = strtoupper(mb_substr($submission->student->first_name ?? '', 0, 1).mb_substr($submission->student->last_name ?? '', 0, 1)) ?: 'S';
@endphp
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="gd">
            <div class="gd-head">
                <div>
                    <p class="gd-kicker">Grade details</p>
                    <h1 class="gd-title">{{ $activity->title }}</h1>
                    <div class="gd-chips">
                        <span class="gd-chip">{{ $activity->typeLabel() }}</span>
                        @if($lesson->subject?->subject_name)
                            <span class="gd-chip">{{ $lesson->subject->subject_name }}</span>
                        @endif
                        @if($submission->submitted_at)
                            <span class="gd-chip">Submitted {{ $submission->submitted_at->timezone('Asia/Manila')->format('M j, Y g:i A') }}</span>
                        @endif
                        @if($submission->graded_at)
                            <span class="gd-chip">Graded {{ $submission->graded_at->timezone('Asia/Manila')->format('M j, Y') }}</span>
                        @endif
                    </div>
                </div>
                <div class="gd-actions">
                    @if($isTeacher)
                        <a class="gd-btn gd-btn-ghost" href="{{ route('lessons.activities.submissions', [$lesson, $activity]) }}">All submissions</a>
                        <a class="gd-btn gd-btn-primary" href="{{ route('lessons.activities.edit-grade', [$lesson, $activity, $submission]) }}">Edit grade</a>
                    @else
                        <a class="gd-btn gd-btn-ghost" href="{{ route('student.activities.show', [$lesson, $activity]) }}">Back to activity</a>
                    @endif
                </div>
            </div>

            <section class="gd-score {{ $percent === null ? '' : ($passed ? 'is-pass' : 'is-low') }}">
                <div class="gd-who">
                    <span class="gd-avatar" aria-hidden="true">{{ $initials }}</span>
                    <div>
                        <strong>{{ $studentName }}</strong>
                        <span>{{ $submission->student->admission_id ?: ($submission->student->roll ?: 'No student number') }}</span>
                    </div>
                </div>
                <div class="gd-figures">
                    <div>
                        <span>Percentage</span>
                        <strong>{{ $percent === null ? '—' : number_format((float) $percent, 1).'%' }}</strong>
                    </div>
                    <div>
                        <span>Score</span>
                        <strong>{{ $submission->total_score ?? '—' }}<small> / {{ $submission->max_possible_score ?? '—' }}</small></strong>
                    </div>
                    <div class="gd-letter" aria-label="Letter grade">
                        <span>Letter</span>
                        <strong>{{ $submission->letter_grade ?: '—' }}</strong>
                    </div>
                </div>
                <div class="gd-bar" aria-hidden="true"><i style="width: {{ $bar }}%"></i></div>
            </section>

            <section class="gd-card">
                <h2>Feedback</h2>
                @if($submission->feedback)
                    <p>{{ $submission->feedback }}</p>
                @else
                    <p class="gd-empty">No feedback yet.</p>
                @endif
            </section>

            @if($submission->grades->count())
                <section class="gd-card">
                    <h2>Rubric scores</h2>
                    @foreach($submission->grades as $grade)
                        <div class="gd-rubric">
                            <span>{{ $grade->rubric->name ?? 'Rubric' }}</span>
                            <strong>{{ $grade->score }}</strong>
                        </div>
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .gd { max-width: 760px; margin: 0 auto; }
    .gd-head { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 18px; }
    .gd-kicker { margin: 0 0 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #3d5ee1; }
    .gd-title { margin: 0; font-size: 1.7rem; font-weight: 700; letter-spacing: -0.03em; color: #1f2430; }
    .gd-chips, .gd-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .gd-chips { margin-top: 12px; }
    .gd-chip { background: #fff; border: 1px solid #e6e8ee; color: #3c4456; border-radius: 999px; padding: 5px 10px; font-size: 0.78rem; font-weight: 600; }
    .gd-btn { display: inline-flex; align-items: center; border-radius: 12px; padding: 10px 14px; font-weight: 700; text-decoration: none; }
    .gd-btn-ghost { background: #fff; color: #3c4456; border: 1px solid #e3e6ee; }
    .gd-btn-ghost:hover { color: #1f2430; }
    .gd-btn-primary { background: #3d5ee1; color: #fff; }
    .gd-btn-primary:hover { background: #314db8; color: #fff; }
    .gd-score, .gd-card { background: #fff; border: 1px solid #e6e8ee; border-radius: 18px; box-shadow: 0 1px 2px rgba(31,36,48,.04); }
    .gd-score { padding: 18px 20px 16px; margin-bottom: 12px; }
    .gd-who { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .gd-avatar { width: 46px; height: 46px; border-radius: 14px; background: #eef2ff; color: #24356e; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
    .gd-who strong, .gd-who span { display: block; }
    .gd-who strong { color: #1f2430; }
    .gd-who span, .gd-figures span, .gd-empty { color: #6b7287; }
    .gd-figures { display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: end; }
    .gd-figures span { display: block; font-size: 0.75rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .gd-figures strong { display: block; margin-top: 2px; font-size: 1.8rem; letter-spacing: -0.04em; color: #1f2430; line-height: 1.1; }
    .gd-figures small { font-size: 1rem; font-weight: 600; color: #6b7287; }
    .gd-letter { text-align: center; min-width: 72px; padding: 8px 12px; border-radius: 16px; background: #f4f6fb; }
    .gd-score.is-pass .gd-letter { background: #ecfdf3; }
    .gd-score.is-pass .gd-letter strong { color: #166534; }
    .gd-score.is-low .gd-letter { background: #fff7ed; }
    .gd-score.is-low .gd-letter strong { color: #c2410c; }
    .gd-bar { height: 8px; background: #eef1f6; border-radius: 999px; margin-top: 16px; overflow: hidden; }
    .gd-bar i { display: block; height: 100%; background: #3d5ee1; border-radius: inherit; }
    .gd-score.is-pass .gd-bar i { background: #16a34a; }
    .gd-score.is-low .gd-bar i { background: #ea580c; }
    .gd-card { padding: 18px 20px; margin-bottom: 12px; }
    .gd-card h2 { margin: 0 0 8px; font-size: 0.95rem; font-weight: 700; color: #1f2430; }
    .gd-card p { margin: 0; white-space: pre-line; color: #1f2430; }
    .gd-rubric { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-top: 1px solid #eef1f6; }
    .gd-rubric:first-of-type { border-top: 0; }
    .gd-rubric span { color: #3c4456; }
    .gd-rubric strong { color: #1f2430; }
    @media (max-width: 720px) {
        .gd-head { flex-direction: column; }
        .gd-figures { grid-template-columns: 1fr 1fr; }
        .gd-letter { grid-column: 1 / -1; text-align: left; }
    }
</style>
@endpush
