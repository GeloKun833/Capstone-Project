@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Lesson</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                            <li class="breadcrumb-item active">Edit Lesson</li>
                        </ul>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Update Lesson Information</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('lessons.index') }}" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-arrow-left"></i> Back to Lessons
                                        </a>
                                        <button type="submit" form="lessonForm" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Lesson
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('lessons.update', $lesson) }}" method="POST" id="lessonForm" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                
                                <!-- Lesson Details -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Lesson Title</label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $lesson->title) }}" placeholder="Enter lesson title" required>
                                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Lesson Date</label>
                                                <input type="date" class="form-control @error('lesson_date') is-invalid @enderror" id="lesson_date" name="lesson_date" value="{{ old('lesson_date', $lesson->lesson_date->toDateString()) }}" required>
                                                @error('lesson_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">Lesson Description</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Describe the lesson objectives, content, and learning outcomes" required>{{ old('description', $lesson->description) }}</textarea>
                                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subject & Section Selection -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Subject</label>
                                                <select class="form-control @error('subject_id') is-invalid @enderror" id="subject_id" name="subject_id" required>
                                                    <option value="">Select Subject</option>
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject->id }}" {{ old('subject_id', $lesson->subject_id) == $subject->id ? 'selected' : '' }}>
                                                            {{ $subject->subject_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Section</label>
                                                <select class="form-control @error('section_id') is-invalid @enderror" id="section_id" name="section_id" required>
                                                    <option value="">Select Section</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}" {{ old('section_id', $lesson->section_id) == $section->id ? 'selected' : '' }}>
                                                            {{ $section->name }} (Grade {{ $section->grade_level }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Academic Period & Curriculum -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Academic Year</label>
                                                <select class="form-control @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                                    <option value="">Select Academic Year</option>
                                                    @foreach($academicYears as $year)
                                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $lesson->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                            {{ $year->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('academic_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Semester</label>
                                                <select class="form-control @error('semester_id') is-invalid @enderror" id="semester_id" name="semester_id" required>
                                                    <option value="">Select Semester</option>
                                                    @foreach($semesters as $semester)
                                                        <option value="{{ $semester->id }}" {{ old('semester_id', $lesson->semester_id) == $semester->id ? 'selected' : '' }}>
                                                            {{ $semester->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('semester_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- File Upload -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">Lesson Materials</label>
                                                @if($lesson->file_path)
                                                    <div class="mb-3">
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-file me-2"></i>
                                                            <strong>Current File:</strong> {{ $lesson->file_name }}
                                                            <a href="{{ $lesson->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif
                                                <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx">
                                                <small class="form-text text-muted">Supported formats: PDF, DOC, DOCX, PPT, PPTX (Max: 10MB). Leave empty to keep current file.</small>
                                                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lesson Preview -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">
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

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Lesson Info -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">
                                                    <i class="fas fa-info-circle me-2"></i>Current Information
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Created:</label>
                                                            <span class="info-value">{{ $lesson->created_at ? $lesson->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Last Updated:</label>
                                                            <span class="info-value">{{ $lesson->updated_at ? $lesson->updated_at->format('M d, Y H:i') : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Status:</label>
                                                            <span class="badge {{ $lesson->status_badge }}">{{ ucfirst($lesson->status) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Record ID:</label>
                                                            <span class="info-value">#{{ $lesson->id }}</span>
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
<style>
/* Admin-style form controls */
.student-group-form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 20px;
}

.student-group-form .form-group {
    margin-bottom: 0;
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
    box-shadow: none;
    outline: 0;
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

/* Card styling */
.card-table {
    border: 0;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 1.875rem;
}

.card-table .card-body {
    padding: 1.5rem;
}

.card {
    border: 0;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 1.875rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
    border-radius: 10px 10px 0 0;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c323f;
    margin-bottom: 0;
}

.card-body {
    padding: 1.5rem;
}

/* Info items */
.info-item {
    margin-bottom: 15px;
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

/* Buttons */
.btn {
    border-radius: 5px;
    font-weight: 600;
    transition: all .4s ease;
}

.btn-primary {
    background-color: #3d5ee1;
    border: 1px solid #3d5ee1;
}

.btn-primary:hover {
    background-color: #18aefa;
    border: 1px solid #18aefa;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #fff;
}

.btn-outline-primary {
    color: #3d5ee1;
    border-color: #3d5ee1;
}

.btn-outline-primary:hover {
    background-color: #3d5ee1;
    border-color: #3d5ee1;
    color: #fff;
}

/* Page header */
.page-header {
    margin-bottom: 1.875rem;
}

.page-header .breadcrumb {
    background-color: transparent;
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 0;
    padding: 0;
    margin-left: auto;
}

.page-header .breadcrumb a {
    color: #333;
}

.page-title {
    font-size: 22px;
    font-weight: 500;
    color: #2c323f;
    margin-bottom: 5px;
}

/* Download group */
.download-grp {
    display: flex;
    align-items: center;
}

/* Alert styling */
.alert {
    border-radius: 5px;
    border: none;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

/* Form text */
.form-text {
    font-size: 13px;
    color: #6c757d;
    margin-top: 5px;
}

/* Badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.badge.bg-primary {
    background-color: #3d5ee1 !important;
}

.badge.bg-success {
    background-color: #7bb13c !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

/* Responsive */
@media (max-width: 768px) {
    .student-group-form {
        padding: 15px;
    }
    
    .card-table .card-body {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .info-item {
        margin-bottom: 10px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update preview on form changes
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

    }
    
    // Bind preview updates to form changes
    $('#subject_id, #section_id, #academic_year_id, #semester_id').on('change', updatePreview);
    $('#title, #description').on('input', updatePreview);
    
    // Form validation
    $('#lessonForm').on('submit', function(e) {
        const title = $('#title').val();
        const description = $('#description').val();
        const subjectId = $('#subject_id').val();
        const sectionId = $('#section_id').val();

        const academicYearId = $('#academic_year_id').val();
        const semesterId = $('#semester_id').val();
        const lessonDate = $('#lesson_date').val();
        
        if (!title || !description || !subjectId || !sectionId || !academicYearId || !semesterId || !lessonDate) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
    });
    
    // File upload validation
    $('#file').on('change', function() {
        const file = this.files[0];
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
        
        if (file) {
            if (file.size > maxSize) {
                alert('File size must be less than 10MB.');
                this.value = '';
                return;
            }
            
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid file type (PDF, DOC, DOCX, PPT, PPTX).');
                this.value = '';
                return;
            }
        }
    });
    
    // Initialize preview
    updatePreview();
});
</script>
@endpush

@endsection 