@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">

        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Create Activity</h3>
                    <p class="dir-subtitle">Add a student task for {{ $lesson->title }}.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lessons.activities.index', $lesson) }}">Activities</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ul>
                    <a href="{{ route('lessons.activities.index', $lesson) }}" class="btn btn-outline-secondary dir-btn">Back</a>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('lessons.activities.store', $lesson) }}" method="POST" id="activityForm">
            @csrf
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="dir-card">
                        <div class="p-3 p-md-4">
                            <h5 class="mb-1">Activity details</h5>
                            <p class="text-muted small mb-3">Students will see this title and the instructions you write here.</p>

                            <div class="form-group mb-3">
                                <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                                       value="{{ old('title') }}" placeholder="e.g. Count and Match worksheet" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="instructions">Instructions <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('instructions') is-invalid @enderror" id="instructions" name="instructions"
                                          rows="6" placeholder="Tell students what to do, what to submit, and how you will check their work." required>{{ old('instructions') }}</textarea>
                                @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="due_date">Due date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date"
                                       value="{{ old('due_date', now()->addDays(7)->toDateString()) }}" min="{{ now()->toDateString() }}" required>
                                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <label class="act-toggle {{ old('allows_submission') ? 'is-on' : '' }}" id="submissionToggle">
                                <input class="form-check-input" type="checkbox" id="allows_submission" name="allows_submission" value="1"
                                       {{ old('allows_submission') ? 'checked' : '' }}>
                                <span>
                                    <strong>Allow file submissions</strong>
                                    <small>Students can upload PDF, DOCX, PPTX, or images. You can grade them with a rubric.</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary dir-btn" id="submitBtn">
                            <i class="fas fa-save me-1"></i> Create Activity
                        </button>
                        <a href="{{ route('lessons.activities.index', $lesson) }}" class="btn btn-outline-secondary dir-btn">Cancel</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="dir-card mb-3">
                        <div class="p-3 p-md-4">
                            <h5 class="mb-3">This lesson</h5>
                            <div class="act-fact"><span>Lesson</span><strong>{{ $lesson->title }}</strong></div>
                            <div class="act-fact"><span>Subject</span><strong>{{ $lesson->subject->subject_name ?? '—' }}</strong></div>
                            <div class="act-fact"><span>Section</span><strong>{{ $lesson->section->name ?? '—' }}</strong></div>
                            <div class="act-fact"><span>Lesson date</span><strong>{{ optional($lesson->lesson_date)->format('M j, Y') ?? '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="dir-card">
                        <div class="p-3 p-md-4">
                            <h5 class="mb-3">Preview</h5>
                            <div class="act-fact"><span>Title</span><strong id="previewTitle">—</strong></div>
                            <div class="act-fact"><span>Due</span><strong id="previewDueDate">—</strong></div>
                            <div class="act-fact"><span>Submission</span><strong id="previewSubmission">—</strong></div>
                            <p class="text-muted small mb-0 mt-2" id="previewInstructions">Instructions will appear here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260915a">
<style>
.act-toggle {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    cursor: pointer;
    background: #f8fafc;
}
.act-toggle.is-on {
    border-color: #c7d2fe;
    background: #eef2ff;
}
.act-toggle .form-check-input { margin-top: 0.2rem; }
.act-toggle strong { display: block; font-size: 0.95rem; }
.act-toggle small { display: block; color: #6b7280; margin-top: 0.15rem; }
.act-fact { margin-bottom: 0.75rem; }
.act-fact span { display: block; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #94a3b8; }
.act-fact strong { font-size: 0.95rem; color: #111827; font-weight: 650; }
.dir-page .form-control {
    min-height: 42px;
    border-radius: 10px;
    border-color: #e5e7eb;
}
.dir-page textarea.form-control { min-height: 140px; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    function updatePreview() {
        const title = $('#title').val();
        const dueDate = $('#due_date').val();
        const instructions = $('#instructions').val();
        const allowsSubmission = $('#allows_submission').is(':checked');

        $('#previewTitle').text(title || '—');
        $('#previewDueDate').text(dueDate ? new Date(dueDate + 'T00:00:00').toLocaleDateString() : '—');
        $('#previewInstructions').text(instructions || 'Instructions will appear here.');
        $('#previewSubmission').text(allowsSubmission ? 'File upload allowed' : 'No file upload');
        $('#submissionToggle').toggleClass('is-on', allowsSubmission);
    }

    $('#title, #due_date, #instructions').on('input change', updatePreview);
    $('#allows_submission').on('change', updatePreview);

    $('#activityForm').on('submit', function() {
        const btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creating...';
        }
    });

    updatePreview();
});
</script>
@endpush

@endsection
