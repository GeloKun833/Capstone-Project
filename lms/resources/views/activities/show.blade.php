@extends('layouts.master')
@section('content')
@php
    $statusLabel = $activity->isClosed() ? 'Closed' : ($activity->isPublished() ? 'Posted' : 'Draft');
    $questionCount = $activity->isQuiz() ? $activity->quizQuestionItems->count() : 0;
    $attempts = $activity->attempts_allowed ?: 1;
    $submissionCount = $activity->submissions->count();
@endphp
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="av">
            <div class="av-head">
                <div>
                    <p class="av-kicker">Activity</p>
                    <h1 class="av-title">{{ $activity->title }}</h1>
                    <div class="av-chips">
                        <span class="av-chip av-chip-{{ strtolower($statusLabel) }}">{{ $statusLabel }}</span>
                        <span class="av-chip">{{ $activity->typeLabel() }}</span>
                        @if($lesson->subject?->subject_name)
                            <span class="av-chip">{{ $lesson->subject->subject_name }}</span>
                        @endif
                        @if($lesson->section?->grade_level)
                            <span class="av-chip">{{ $lesson->section->grade_level }}</span>
                        @endif
                        @if($lesson->section?->name && $lesson->section->name !== $lesson->section->grade_level)
                            <span class="av-chip">{{ $lesson->section->name }}</span>
                        @endif
                        <span class="av-chip">Due {{ $activity->formattedDue() }}</span>
                        @if(!$activity->isClosed() && $activity->is_overdue)
                            <span class="av-chip av-chip-overdue">Overdue</span>
                        @endif
                        <span class="av-chip">{{ $activity->points ? $activity->points.' points' : 'Not scored' }}</span>
                    </div>
                </div>
                <div class="av-actions">
                    <a class="av-btn av-btn-ghost" href="{{ route('lessons.activities.edit', [$lesson, $activity]) }}">Edit</a>
                    @if($activity->isQuiz())
                        <a class="av-btn av-btn-ghost" href="{{ route('lessons.activities.quiz-preview', [$lesson, $activity]) }}">Preview</a>
                        <a class="av-btn av-btn-primary" href="{{ route('lessons.activities.quiz-results', [$lesson, $activity]) }}">Results</a>
                    @endif
                    <a class="av-btn {{ $activity->isQuiz() ? 'av-btn-ghost' : 'av-btn-primary' }}" href="{{ route('lessons.activities.submissions', [$lesson, $activity]) }}">Submissions</a>
                </div>
            </div>

            <div class="av-stats">
                <div class="av-stat">
                    <span>Submissions</span>
                    <strong>{{ $submissionCount }}</strong>
                </div>
                @if($activity->isQuiz())
                    <div class="av-stat">
                        <span>Questions</span>
                        <strong>{{ $questionCount }}</strong>
                    </div>
                    <div class="av-stat">
                        <span>Attempts</span>
                        <strong>{{ $attempts }}</strong>
                    </div>
                    <div class="av-stat">
                        <span>Passing score</span>
                        <strong>{{ $activity->passing_score ?: '—' }}%</strong>
                    </div>
                @else
                    <div class="av-stat">
                        <span>Turn-in</span>
                        <strong>{{ $activity->usesFileSubmission() || $activity->allows_submission ? 'File' : 'None' }}</strong>
                    </div>
                @endif
            </div>

            <section class="av-card">
                <h2>Instructions</h2>
                <p>{!! nl2br(e($activity->instructions)) !!}</p>
            </section>

            @if($activity->isQuiz())
                <p class="av-note">Answer keys stay on the grading page. This score is not entered into the term grade until you encode it.</p>
            @endif

            @if($activity->attachments)
                <section class="av-card">
                    <h2>Materials</h2>
                    @foreach($activity->attachments as $file)
                        <a class="av-file" href="{{ asset('storage/'.$file['path']) }}" target="_blank" rel="noopener">{{ $file['name'] ?? 'Material' }}</a>
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .av { width: 100%; }
    .av-head { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; margin-bottom: 22px; }
    .av-actions { flex: 0 0 auto; justify-content: flex-end; }
    .av-kicker { margin: 0 0 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #3d5ee1; }
    .av-title { margin: 0; font-size: 1.7rem; font-weight: 700; letter-spacing: -0.03em; color: #1f2430; }
    .av-chips, .av-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .av-chips { margin-top: 12px; }
    .av-chip { background: #fff; border: 1px solid #e6e8ee; color: #3c4456; border-radius: 999px; padding: 5px 10px; font-size: 0.78rem; font-weight: 600; }
    .av-chip-posted { background: #ecfdf3; border-color: #bbf7d0; color: #166534; }
    .av-chip-draft { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
    .av-chip-closed { background: #eef2ff; border-color: #c7d2fe; color: #24356e; }
    .av-chip-overdue { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
    .av-btn { display: inline-flex; align-items: center; border-radius: 12px; padding: 10px 14px; font-weight: 700; text-decoration: none; }
    .av-btn-ghost { background: #fff; color: #3c4456; border: 1px solid #e3e6ee; }
    .av-btn-ghost:hover { color: #1f2430; }
    .av-btn-primary { background: #3d5ee1; color: #fff; }
    .av-btn-primary:hover { background: #314db8; color: #fff; }
    .av-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 16px; }
    .av-stat, .av-card { background: #fff; border: 1px solid #e6e8ee; border-radius: 18px; box-shadow: 0 1px 2px rgba(31,36,48,.04); }
    .av-stat { padding: 22px 24px; }
    .av-stat span { display: block; color: #6b7287; font-size: 0.75rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .av-stat strong { display: block; margin-top: 8px; font-size: 2rem; letter-spacing: -0.03em; color: #1f2430; }
    .av-card { padding: 28px 32px; margin-bottom: 16px; min-height: 180px; }
    .av-card h2 { margin: 0 0 8px; font-size: 0.95rem; font-weight: 700; color: #1f2430; }
    .av-card p { margin: 0; color: #1f2430; }
    .av-note { color: #6b7287; font-size: 0.88rem; }
    .av-file { display: inline-flex; margin: 0 8px 8px 0; padding: 8px 12px; border-radius: 12px; background: #eef2ff; color: #24356e; font-weight: 700; text-decoration: none; }
    @media (max-width: 800px) {
        .av-head { flex-direction: column; }
        .av-stats { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush
