@extends('layouts.master')
@section('content')

@php
    $total = $activities->count();
@endphp

<div class="page-wrapper">
    <div class="content container-fluid dir-page">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Lesson Activities</h3>
                    <p class="dir-subtitle">
                        {{ $lesson->title }}
                        @if($lesson->subject)
                            · {{ $lesson->subject->subject_name }}
                        @endif
                        @if($lesson->section)
                            · {{ $lesson->section->grade_level ? $lesson->section->grade_level.' · ' : '' }}{{ $lesson->section->name }}
                        @endif
                    </p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a></li>
                        <li class="breadcrumb-item active">Activities</li>
                    </ul>
                    <div class="dir-header-actions">
                        <a href="{{ route('lessons.show', $lesson) }}" class="btn btn-outline-secondary dir-btn">Back</a>
                        <a href="{{ route('lessons.activities.create', $lesson) }}" class="btn btn-primary dir-btn">
                            <i class="fas fa-plus me-1"></i> Add Activity
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Activities</h5>
                    <span class="dir-count mt-1">{{ $total }} activit{{ $total === 1 ? 'y' : 'ies' }}</span>
                </div>
                <form method="GET" class="d-flex flex-wrap gap-2">
                    <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Search title" style="min-width: 180px;">
                    <select name="status" class="form-control" style="min-width: 140px;">
                        <option value="">Draft and posted</option>
                        <option value="draft" @selected($status === 'draft')>Draft</option>
                        <option value="published" @selected($status === 'published')>Posted</option>
                        <option value="closed" @selected($status === 'closed')>Closed</option>
                    </select>
                    <button class="btn btn-outline-secondary dir-btn" type="submit">Filter</button>
                </form>
            </div>

            @forelse($activities as $activity)
                @php
                    $due = $activity->due_date;
                    $dueLabel = $activity->formattedDue();
                    $dueState = 'muted';
                    $dueHint = '';
                    if ($activity->isClosed()) {
                        $dueState = 'closed';
                        $dueHint = 'Closed';
                    } elseif ($due) {
                        if ($activity->is_overdue) {
                            $dueState = 'danger';
                            $dueHint = 'Overdue';
                        } elseif ($due->isToday()) {
                            $dueState = 'warn';
                            $dueHint = 'Due today';
                        } else {
                            $dueState = 'ok';
                            $dueHint = $due->diffForHumans();
                        }
                    }
                    $submitted = (int) $activity->submission_count;
                    $graded = (int) $activity->graded_count;
                @endphp
                <article class="act-row">
                    <div class="act-row-main">
                        <a class="act-title" href="{{ route('lessons.activities.show', [$lesson, $activity]) }}">{{ $activity->title }}</a>
                        @if($activity->activity_type)
                            <div class="text-muted small">{{ $activity->typeLabel() }}</div>
                        @endif
                        <p class="act-copy">{{ Str::limit(strip_tags((string) $activity->instructions), 140) }}</p>
                        <div class="act-meta">
                            <span class="act-chip act-chip-{{ $dueState }}"><i class="far fa-calendar"></i> {{ $dueLabel }}@if($dueHint) · {{ $dueHint }}@endif</span>
                            @if($activity->isQuiz())
                                <span class="act-chip">Quiz · {{ $activity->points ?: 0 }} points</span>
                            @elseif($activity->allows_submission)
                                <span class="act-chip">{{ $submitted }} submitted · {{ $graded }} graded</span>
                                @if(! $activity->isClosed())
                                    <span class="act-chip act-chip-ok">Submissions on</span>
                                @endif
                            @else
                                <span class="act-chip">No file upload</span>
                            @endif
                            @if($activity->isClosed())
                                <span class="act-chip act-chip-closed">Closed</span>
                            @elseif($activity->isPublished())
                                <span class="act-chip act-chip-ok">Posted</span>
                            @else
                                <span class="act-chip act-chip-warn">Draft</span>
                            @endif
                            @if($activity->points)
                                <span class="act-chip">{{ $activity->points }} points</span>
                            @endif
                        </div>
                    </div>
                    <div class="act-row-actions">
                        @if($activity->isQuiz() && ($activity->isPublished() || $activity->isClosed()))
                            <a href="{{ route('lessons.activities.quiz-results', [$lesson, $activity]) }}" class="dir-icon-btn" title="Quiz results"><i class="fas fa-check-square"></i></a>
                        @elseif($activity->allows_submission && ($activity->isPublished() || $activity->isClosed()))
                            <a href="{{ route('lessons.activities.submissions', [$lesson, $activity]) }}" class="dir-icon-btn" title="Submissions"><i class="fas fa-upload"></i></a>
                        @endif
                        @if($activity->isPublished())
                            <form action="{{ route('lessons.activities.close', [$lesson, $activity]) }}" method="POST">
                                @csrf
                                <button type="submit" class="dir-icon-btn" title="Close">Close</button>
                            </form>
                        @elseif($activity->isClosed())
                            <form action="{{ route('lessons.activities.reopen', [$lesson, $activity]) }}" method="POST" onsubmit="return confirm('Reopen this activity? Students can submit again.')">
                                @csrf
                                <button type="submit" class="dir-icon-btn" title="Reopen">Reopen</button>
                            </form>
                        @endif
                        @if($activity->isPublished())
                            <form action="{{ route('lessons.activities.unpublish', [$lesson, $activity]) }}" method="POST" @if($activity->isQuiz()) onsubmit="return confirm('Unpublish this quiz? Students cannot start a new attempt. Existing results stay.')" @endif>
                                @csrf
                                <button type="submit" class="dir-icon-btn" title="Move to draft">Draft</button>
                            </form>
                        @elseif(! $activity->isClosed())
                            <form action="{{ route('lessons.activities.publish', [$lesson, $activity]) }}" method="POST">
                                @csrf
                                <button type="submit" class="dir-icon-btn" title="Post">Post</button>
                            </form>
                        @endif
                        <form action="{{ route('lessons.activities.duplicate', [$lesson, $activity]) }}" method="POST">
                            @csrf
                            <button type="submit" class="dir-icon-btn" title="Duplicate"><i class="far fa-copy"></i></button>
                        </form>
                        <a href="{{ route('lessons.activities.show', [$lesson, $activity]) }}" class="dir-icon-btn" title="View"><i class="far fa-eye"></i></a>
                        <a href="{{ route('lessons.activities.edit', [$lesson, $activity]) }}" class="dir-icon-btn" title="Edit"><i class="far fa-edit"></i></a>
                        <button type="button" class="dir-icon-btn is-danger delete-activity-btn" title="Delete"
                                data-title="{{ $activity->title }}"
                                data-action="{{ route('lessons.activities.destroy', [$lesson, $activity]) }}">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </div>
                </article>
            @empty
                <div class="dir-empty">
                    <i class="fas fa-tasks d-block"></i>
                    <h5 class="mt-2 mb-1">No activities yet</h5>
                    <p class="mb-3">Add a task, worksheet, or practice activity for this lesson.</p>
                    <a href="{{ route('lessons.activities.create', $lesson) }}" class="btn btn-primary dir-btn">
                        <i class="fas fa-plus me-1"></i> Create Activity
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="deleteActivityModal" tabindex="-1" aria-labelledby="deleteActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteActivityModalLabel">Delete activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Delete “<strong id="deleteActivityTitle"></strong>”?</p>
                <p class="dir-muted mb-0">The activity and its student submissions will be removed. This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteActivityForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger dir-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260915b">
<style>
.act-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.15rem;
    border-top: 1px solid #eef2f7;
}
.act-row:first-of-type { border-top: 0; }
.act-title {
    display: block;
    font-weight: 750;
    color: #111827;
    text-decoration: none;
    margin-bottom: 0.2rem;
}
.act-title:hover { color: #3d5ee1; }
.act-copy { margin: 0 0 0.55rem; color: #6b7280; font-size: 0.9rem; }
.act-meta { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.act-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.72rem;
    font-weight: 650;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e8eaed;
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
}
.act-chip-ok { background: #ecfdf5; border-color: #d1fae5; color: #047857; }
.act-chip-warn { background: #fff7ed; border-color: #ffedd5; color: #c2410c; }
.act-chip-danger { background: #fef2f2; border-color: #fee2e2; color: #b91c1c; }
.act-chip-closed { background: #eef2ff; border-color: #c7d2fe; color: #24356e; }
.act-row-actions { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 0.25rem; }
.act-row-actions form { margin: 0; }
@media (max-width: 700px) {
    .act-row { flex-direction: column; }
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.delete-activity-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        document.getElementById('deleteActivityTitle').textContent = button.dataset.title || 'this activity';
        document.getElementById('deleteActivityForm').action = button.dataset.action;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteActivityModal')).show();
    });
});
</script>
@endpush

@endsection
