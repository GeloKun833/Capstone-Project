@extends('layouts.master')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid dir-page">

            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <h3 class="page-title mb-1">Create Lesson</h3>
                        <p class="dir-subtitle">Select your assigned class first, then fill in the lesson details.</p>
                    </div>
                    <div class="col-auto text-end">
                        <ul class="breadcrumb justify-content-end mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">My Lessons</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ul>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('lessons.index') }}" class="btn btn-outline-secondary dir-btn">Back</a>
                            <button type="submit" form="lessonForm" class="btn btn-primary dir-btn" id="submitBtn" @if($subjects->isEmpty() || $sections->isEmpty()) disabled @endif>
                                <i class="fas fa-save me-1"></i> <span id="submitText">Create Lesson</span>
                                <span id="submitSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-sm-12">
                    <div class="dir-card">
                        <div class="p-3 p-md-4">
                            @if($subjects->isEmpty() || $sections->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You have no subject/section assignment yet. Ask Admin to assign you under
                                    <strong>Classes &amp; Subjects</strong> before creating a lesson.
                                    <a href="{{ route('teacher.classes') }}" class="alert-link">View My Classes &amp; Subjects</a>
                                </div>
                            @endif

                            <form action="{{ route('lessons.store') }}" method="POST" id="lessonForm" enctype="multipart/form-data">
                                @csrf

                                {{-- Step 1: Section first, then matching subjects --}}
                                <div class="student-group-form">
                                    <div class="mb-3">
                                        <h5 class="mb-1">1. Class Assignment</h5>
                                        <p class="text-muted mb-0 small">Select your assigned section first, then choose a subject for that section</p>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Section <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control @error('section_id') is-invalid @enderror" id="section_id" name="section_id" required @if($sections->isEmpty()) disabled @endif>
                                                    <option value="">Select Section</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}"
                                                            data-grade="{{ $section->grade_level }}"
                                                            {{ (string) old('section_id') === (string) $section->id ? 'selected' : '' }}>
                                                            {{ $section->name }} ({{ $section->grade_level ?? 'N/A' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Subject <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control @error('subject_id') is-invalid @enderror" id="subject_id" name="subject_id" required disabled>
                                                    <option value="">Select section first</option>
                                                </select>
                                                @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                <div class="form-text">Subjects appear after you pick a section (same grade only)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Step 2: Lesson Information --}}
                                <div class="student-group-form">
                                    <div class="mb-3">
                                        <h5 class="mb-1">2. Lesson Information</h5>
                                        <p class="text-muted mb-0 small">Fill in the details below to create a new lesson</p>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Title <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Enter lesson title" required>
                                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Date <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" class="form-control @error('lesson_date') is-invalid @enderror" id="lesson_date" name="lesson_date" value="{{ old('lesson_date', now()->toDateString()) }}" required>
                                                @error('lesson_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Description <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Describe the lesson objectives, content, and learning outcomes" required>{{ old('description') }}</textarea>
                                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Academic Year <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                                    <option value="">Select Academic Year</option>
                                                    @foreach($academicYears as $year)
                                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                            {{ $year->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('academic_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Semester <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control @error('semester_id') is-invalid @enderror" id="semester_id" name="semester_id" required>
                                                    <option value="">Select Semester</option>
                                                    @foreach($semesters as $semester)
                                                        <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                                            {{ $semester->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('semester_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">Lesson Materials (Optional)</label>
                                                <div class="file-upload-wrapper">
                                                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx">
                                                    <div class="file-upload-info mt-2" id="fileInfo" style="display: none;">
                                                        <div class="alert alert-info mb-0">
                                                            <i class="fas fa-file me-2"></i>
                                                            <span id="fileName"></span>
                                                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="removeFile">
                                                                <i class="fas fa-times"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                <small class="form-text text-muted">Supported formats: PDF, DOC, DOCX, PPT, PPTX (Max: 10MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-eye me-2"></i>Lesson Preview
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Subject:</label>
                                                            <span class="info-value" id="previewSubject">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Section:</label>
                                                            <span class="info-value" id="previewSection">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Academic Period:</label>
                                                            <span class="info-value" id="previewPeriod">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Title:</label>
                                                            <span class="info-value" id="previewTitle">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914e">
<style>
.student-group-form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}
.student-group-form .form-control {
    border: 1px solid #ddd;
    border-radius: 5px;
    height: 45px;
    padding: 10px 15px;
    font-size: 15px;
}
.student-group-form .form-control:focus {
    border-color: #3d5ee1;
    box-shadow: 0 0 0 0.2rem rgba(61, 94, 225, 0.25);
}
.student-group-form .form-label {
    font-weight: 600;
    color: #2c323f;
    margin-bottom: 8px;
}
.student-group-form textarea.form-control {
    height: auto;
    min-height: 100px;
}
.student-group-form input[type="file"] {
    height: auto;
    padding: 8px 12px;
}
.info-label {
    display: block;
    font-weight: 600;
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 5px;
}
.info-value {
    display: block;
    font-weight: 500;
    color: #2c323f;
    font-size: 16px;
}
</style>
@endpush

@push('scripts')
@php
    $subjectsForJs = $subjects->map(function ($s) {
        return [
            'id' => $s->id,
            'label' => $s->subject_name . ($s->class ? ' (' . $s->class . ')' : ''),
            'grade' => $s->class,
        ];
    })->values();
@endphp
<script>
$(document).ready(function() {
    const subjectsBySection = @json($subjectsBySection ?? []);
    const allSubjects = @json($subjectsForJs);
    const oldSectionId = @json(old('section_id'));
    const oldSubjectId = @json(old('subject_id'));

    function filterSubjects() {
        const sectionId = $('#section_id').val();
        const $subject = $('#subject_id');
        const current = oldSubjectId || $subject.val();

        $subject.empty();

        if (!sectionId) {
            $subject.append('<option value="">Select section first</option>');
            $subject.prop('disabled', true);
            updatePreview();
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

        updatePreview();
    }

    function updatePreview() {
        const subjectSelect = $('#subject_id option:selected');
        const sectionSelect = $('#section_id option:selected');
        const academicYearSelect = $('#academic_year_id option:selected');
        const semesterSelect = $('#semester_id option:selected');

        $('#previewSubject').text(subjectSelect.val() ? subjectSelect.text() : '-');
        $('#previewSection').text(sectionSelect.val() ? sectionSelect.text() : '-');
        $('#previewPeriod').text(
            (academicYearSelect.val() && semesterSelect.val())
                ? `${academicYearSelect.text()} - ${semesterSelect.text()}`
                : '-'
        );
        $('#previewTitle').text($('#title').val().trim() || '-');
    }

    $('#section_id').on('change', function() {
        filterSubjects();
    });
    $('#subject_id, #academic_year_id, #semester_id').on('change', updatePreview);
    $('#title').on('input', updatePreview);

    if (oldSectionId) {
        $('#section_id').val(String(oldSectionId));
    }
    filterSubjects();

    $('#lessonForm').on('submit', function(e) {
        const requiredFields = [
            { id: '#section_id', label: 'Section' },
            { id: '#subject_id', label: 'Subject' },
            { id: '#title', label: 'Lesson title' },
            { id: '#description', label: 'Lesson description' },
            { id: '#lesson_date', label: 'Lesson date' },
            { id: '#academic_year_id', label: 'Academic year' },
            { id: '#semester_id', label: 'Semester' },
        ];

        let hasErrors = false;
        let errorMessage = 'Please fix the following errors:\n';
        $('.form-control').removeClass('is-invalid');

        requiredFields.forEach(function(field) {
            const val = $(field.id).val();
            if (!val || (typeof val === 'string' && !val.trim())) {
                $(field.id).addClass('is-invalid');
                errorMessage += '• ' + field.label + ' is required\n';
                hasErrors = true;
            }
        });

        if (hasErrors) {
            e.preventDefault();
            alert(errorMessage);
            return false;
        }

        $('#subject_id').prop('disabled', false);

        $('#submitBtn').prop('disabled', true);
        $('#submitText').text('Creating...');
        $('#submitSpinner').show();
    });

    $('#file').on('change', function() {
        const file = this.files[0];
        const maxSize = 10 * 1024 * 1024;
        if (!file) {
            $('#fileInfo').hide();
            return;
        }
        if (file.size > maxSize) {
            alert('File size must be less than 10MB.');
            this.value = '';
            $('#fileInfo').hide();
            return;
        }
        $('#fileName').text(file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)');
        $('#fileInfo').show();
    });

    $('#removeFile').on('click', function() {
        $('#file').val('');
        $('#fileInfo').hide();
    });
});
</script>
@endpush

@endsection
