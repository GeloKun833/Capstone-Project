@extends('layouts.master')
@section('content')

@php
    $lessonTotal = method_exists($lessons, 'total') ? $lessons->total() : $lessons->count();
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
                    <h3 class="page-title mb-1">Lesson Planner</h3>
                    <p class="dir-subtitle">Create, publish, and track lessons for your assigned classes.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Lessons</li>
                    </ul>
                    <a href="{{ route('lessons.create') }}" class="btn btn-primary dir-btn">
                        <i class="fas fa-plus me-1"></i> Create Lesson
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Total lessons</span>
                    <div class="dir-stat-value" id="totalLessons">{{ $lessonTotal }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Published</span>
                    <div class="dir-stat-value" id="publishedLessons">{{ $lessons->where('status', 'published')->count() }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Draft</span>
                    <div class="dir-stat-value" id="draftLessons">{{ $lessons->where('status', 'draft')->count() }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Completed</span>
                    <div class="dir-stat-value" id="completedLessons">{{ $lessons->where('status', 'completed')->count() }}</div>
                </div>
            </div>
        </div>

        <div class="dir-card dir-filters">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label" for="searchLesson">Search</label>
                    <input type="text" class="form-control" id="searchLesson" value="{{ request('search') }}" placeholder="Title or description">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label" for="subject_filter">Subject</label>
                    <select class="form-control" id="subject_filter">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                                {{ $subject->subject_name }}{{ $subject->class ? ' (' . $subject->class . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label" for="status_filter">Status</label>
                    <select class="form-control" id="status_filter">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 pb-3">
                    <button type="button" class="btn btn-primary dir-btn w-100" id="filterLessons">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Lesson list</h5>
                    <span class="dir-count mt-1">{{ $lessonTotal }} lesson{{ $lessonTotal === 1 ? '' : 's' }}</span>
                </div>
            </div>

            @if($lessons->count() > 0)
                <div class="table-responsive">
                    <table class="table dir-table mb-0">
                        <thead>
                            <tr>
                                <th>Lesson</th>
                                <th>Subject</th>
                                <th>Section</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="lessonTableBody">
                            @foreach($lessons as $lesson)
                                @php
                                    $statusClass = match ($lesson->status) {
                                        'published' => 'dir-badge--active',
                                        'completed' => 'dir-badge--neutral',
                                        default => 'dir-badge--inactive',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('lessons.show', $lesson) }}" class="dir-person-name">{{ $lesson->title }}</a>
                                        <span class="dir-person-meta">{{ Str::limit($lesson->description, 90) }}</span>
                                    </td>
                                    <td><span class="dir-chip">{{ $lesson->subject->subject_name }}</span></td>
                                    <td>{{ $lesson->section->name }}</td>
                                    <td>{{ $lesson->lesson_date?->format('M d, Y') }}</td>
                                    <td><span class="dir-badge {{ $statusClass }}">{{ ucfirst($lesson->status) }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('lessons.show', $lesson) }}" class="dir-icon-btn" title="View"><i class="far fa-eye"></i></a>
                                            <a href="{{ route('lessons.edit', $lesson) }}" class="dir-icon-btn" title="Edit"><i class="far fa-edit"></i></a>
                                            <a href="{{ route('lessons.activities.index', $lesson) }}" class="dir-icon-btn" title="Activities"><i class="fas fa-tasks"></i></a>
                                            @if($lesson->status === 'draft')
                                                <button type="button" class="dir-icon-btn is-success publish-btn" data-lesson-id="{{ $lesson->id }}" data-lesson-title="{{ $lesson->title }}" title="Publish">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            @endif
                                            @if($lesson->status === 'published')
                                                <button type="button" class="dir-icon-btn complete-btn" data-lesson-id="{{ $lesson->id }}" data-lesson-title="{{ $lesson->title }}" title="Mark complete">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="dir-icon-btn is-danger delete-btn" data-lesson-id="{{ $lesson->id }}" data-lesson-title="{{ $lesson->title }}" title="Delete">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($lessons->hasPages())
                    <div class="p-3 d-flex justify-content-center">
                        {{ $lessons->links() }}
                    </div>
                @endif
            @else
                <div class="dir-empty">
                    <i class="fas fa-book d-block"></i>
                    <h5 class="mt-2 mb-1">No lessons found</h5>
                    <p class="mb-3">Create a lesson for one of your assigned classes to get started.</p>
                    <a href="{{ route('lessons.create') }}" class="btn btn-primary dir-btn">
                        <i class="fas fa-plus me-1"></i> Create Lesson
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete lesson</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Delete “<strong id="deleteLessonTitle"></strong>”?</p>
                <p class="dir-muted mb-0">This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger dir-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="publishModal" tabindex="-1" aria-labelledby="publishModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="publishModalLabel">Publish lesson</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Publish “<strong id="publishLessonTitle"></strong>”?</p>
                <p class="dir-muted mb-0">Published lessons become visible to students.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                <form id="publishForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary dir-btn">Publish</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeModalLabel">Mark complete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Mark “<strong id="completeLessonTitle"></strong>” as completed?</p>
                <p class="dir-muted mb-0">Completed lessons are archived from the active list.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                <form id="completeForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary dir-btn">Mark complete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914e">
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#filterLessons').on('click', function() {
        const searchTerm = $('#searchLesson').val();
        const subjectId = $('#subject_filter').val();
        const status = $('#status_filter').val();

        $(this).html('<i class="fas fa-spinner fa-spin me-1"></i> Filtering...').prop('disabled', true);

        let url = '{{ route("lessons.index") }}?';
        if (searchTerm) url += 'search=' + encodeURIComponent(searchTerm) + '&';
        if (subjectId) url += 'subject_id=' + subjectId + '&';
        if (status) url += 'status=' + status;

        window.location.href = url;
    });

    let searchTimeout;
    $('#searchLesson').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filterLessons').click();
        }, 500);
    });

    $('#subject_filter, #status_filter').on('change', function() {
        $('#filterLessons').click();
    });

    $('.delete-btn').on('click', function() {
        $('#deleteLessonTitle').text($(this).data('lesson-title'));
        $('#deleteForm').attr('action', '{{ route("lessons.index") }}/' + $(this).data('lesson-id'));
        $('#deleteModal').modal('show');
    });

    $('.publish-btn').on('click', function() {
        $('#publishLessonTitle').text($(this).data('lesson-title'));
        $('#publishForm').attr('action', '{{ route("lessons.index") }}/' + $(this).data('lesson-id') + '/publish');
        $('#publishModal').modal('show');
    });

    $('.complete-btn').on('click', function() {
        $('#completeLessonTitle').text($(this).data('lesson-title'));
        $('#completeForm').attr('action', '{{ route("lessons.index") }}/' + $(this).data('lesson-id') + '/complete');
        $('#completeModal').modal('show');
    });

    $('#deleteForm, #publishForm, #completeForm').on('submit', function() {
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...').prop('disabled', true);
    });
});
</script>
@endpush
