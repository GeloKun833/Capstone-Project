@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">

        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Edit Class Post</h3>
                    <p class="dir-subtitle">Update this announcement, resource, or reminder.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('class-posts.index') }}">Class Posts</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                    <a href="{{ route('class-posts.show', $classPost) }}" class="btn btn-outline-secondary dir-btn">Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="dir-card">
                    <div class="p-3 p-md-4">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('class-posts.update', $classPost) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-1">Class</h5>
                                    <p class="text-muted small mb-3">Select a section first. Subjects then show only for that section.</p>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Section <span class="text-danger">*</span></label>
                                        <select id="section_id" name="section_id" class="form-control @error('section_id') is-invalid @enderror" required @if($sections->isEmpty()) disabled @endif>
                                            <option value="">Select Section</option>
                                            @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ (string) old('section_id', $classPost->section_id) === (string) $section->id ? 'selected' : '' }}>
                                                    {{ $section->name }}{{ $section->grade_level ? ' (' . $section->grade_level . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('section_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select id="subject_id" name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required disabled>
                                            <option value="">Select section first</option>
                                        </select>
                                        @error('subject_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Subjects appear after you pick a section.</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                        <select name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}" {{ old('academic_year_id', $classPost->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                    {{ $year->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('academic_year_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                                        <select name="semester_id" class="form-control @error('semester_id') is-invalid @enderror" required>
                                            <option value="">Select Semester</option>
                                            @foreach($semesters as $semester)
                                                <option value="{{ $semester->id }}" {{ old('semester_id', $classPost->semester_id) == $semester->id ? 'selected' : '' }}>
                                                    {{ $semester->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('semester_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                                            <option value="">Select Type</option>
                                            @foreach(['announcement' => 'Announcement', 'resource' => 'Resource', 'discussion' => 'Discussion', 'reminder' => 'Reminder'] as $value => $label)
                                                <option value="{{ $value }}" {{ old('type', $classPost->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                            <option value="">Select Priority</option>
                                            @foreach(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                                                <option value="{{ $value }}" {{ old('priority', $classPost->priority) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <h5 class="mb-3 mt-3">Post Information</h5>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                               value="{{ old('title', $classPost->title) }}" placeholder="Enter post title" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Content <span class="text-danger">*</span></label>
                                        <textarea name="content" rows="6" class="form-control @error('content') is-invalid @enderror"
                                                  placeholder="Enter post content" required>{{ old('content', $classPost->content) }}</textarea>
                                        @error('content')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Expires At (Optional)</label>
                                        <input type="date" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror"
                                               value="{{ old('expires_at', optional($classPost->expires_at)->format('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
                                        @error('expires_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Attachment (Optional)</label>
                                        <input type="file" name="post_file" class="form-control @error('post_file') is-invalid @enderror"
                                               accept=".pdf,.docx,.pptx,.txt,.jpg,.jpeg,.png">
                                        @if($classPost->file_name)
                                            <small class="form-text text-muted d-block">Current file: {{ $classPost->file_name }}</small>
                                        @endif
                                        <small class="form-text text-muted">Max size: 10MB. Allowed: PDF, Word (DOCX), PowerPoint (PPTX), TXT, JPG, PNG</small>
                                        @error('post_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <h5 class="mb-3 mt-3">Post Options</h5>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned"
                                               {{ old('is_pinned', $classPost->is_pinned) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_pinned">
                                            <i class="fas fa-thumbtack"></i> Pin this post
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allows_comments" id="allows_comments"
                                               {{ old('allows_comments', $classPost->allows_comments) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allows_comments">
                                            <i class="fas fa-comments"></i> Allow comments
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requires_confirmation" id="requires_confirmation"
                                               {{ old('requires_confirmation', $classPost->requires_confirmation) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_confirmation">
                                            <i class="fas fa-check-circle"></i> Require confirmation
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <a href="{{ route('class-posts.show', $classPost) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914k">
@endpush

@push('scripts')
@php
    $subjectsForJs = $subjects->map(function ($s) {
        return [
            'id' => $s->id,
            'label' => $s->subject_name . ($s->class ? ' (' . $s->class . ')' : ''),
        ];
    })->values();
@endphp
<script>
$(document).ready(function() {
    const subjectsBySection = @json($subjectsBySection ?? []);
    const allSubjects = @json($subjectsForJs);
    let restoreSubjectId = @json(old('subject_id', $classPost->subject_id));

    function filterSubjects() {
        const sectionId = $('#section_id').val();
        const $subject = $('#subject_id');
        const current = restoreSubjectId || $subject.val();

        $subject.empty();

        if (!sectionId) {
            $subject.append('<option value="">Select section first</option>');
            $subject.prop('disabled', true);
            return;
        }

        const allowedIds = (subjectsBySection[sectionId] || []).map(String);
        $subject.append('<option value="">Select Subject</option>');

        let matched = 0;
        allSubjects.forEach(function(subject) {
            if (allowedIds.includes(String(subject.id))) {
                matched++;
                const selected = String(current) === String(subject.id) ? ' selected' : '';
                $subject.append('<option value="' + subject.id + '"' + selected + '>' + subject.label + '</option>');
            }
        });

        if (matched === 0) {
            $subject.empty().append('<option value="">No subjects for this section</option>');
            $subject.prop('disabled', true);
        } else {
            $subject.prop('disabled', false);
        }
    }

    $('#section_id').on('change', function() {
        restoreSubjectId = null;
        filterSubjects();
    });

    filterSubjects();
});

document.querySelector('input[name="post_file"]')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const maxSize = 10240 * 1024;
        if (file.size > maxSize) {
            alert('File size must not exceed 10MB.');
            this.value = '';
            return;
        }

        const allowedExtensions = ['pdf', 'docx', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];
        const fileExtension = file.name.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(fileExtension)) {
            alert('Invalid file type. Allowed: PDF, Word (DOCX), PowerPoint (PPTX), TXT, JPG, PNG.');
            this.value = '';
        }
    }
});

document.querySelector('form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
});
</script>
@endpush

@endsection
