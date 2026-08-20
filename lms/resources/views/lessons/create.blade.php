@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Create Lesson</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                            <li class="breadcrumb-item active">Create Lesson</li>
                        </ul>
                    </div>
                </div>
            </div>

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
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Lesson Information</h3>
                                        <p class="text-muted mb-0">Fill in the details below to create a new lesson</p>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('lessons.index') }}" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-arrow-left"></i> Back to Lessons
                                        </a>
                                        <button type="submit" form="lessonForm" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-save"></i> <span id="submitText">Create Lesson</span>
                                            <span id="submitSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('lessons.store') }}" method="POST" id="lessonForm" enctype="multipart/form-data">
                                @csrf
                                
                                <!-- Lesson Details -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Title <span class="text-danger">*</span>
                                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Enter a clear, descriptive title for your lesson"></i>
                                                </label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Enter lesson title" required>
                                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                <div class="form-text">Keep it concise but descriptive</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Date <span class="text-danger">*</span>
                                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Select the date when this lesson will be taught"></i>
                                                </label>
                                                <input type="date" class="form-control @error('lesson_date') is-invalid @enderror" id="lesson_date" name="lesson_date" value="{{ old('lesson_date', now()->toDateString()) }}" required>
                                                @error('lesson_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Description <span class="text-danger">*</span>
                                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Describe the lesson objectives, content, and learning outcomes"></i>
                                                </label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Describe the lesson objectives, content, and learning outcomes" required>{{ old('description') }}</textarea>
                                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                <div class="form-text">Include learning objectives, key concepts, and expected outcomes</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subject & Section Selection -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Subject <span class="text-danger">*</span>
                                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Select the subject this lesson belongs to"></i>
                                                </label>
                                                <select class="form-control @error('subject_id') is-invalid @enderror" id="subject_id" name="subject_id" required>
                                                    <option value="">Select Subject</option>
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                                            {{ $subject->subject_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Section <span class="text-danger">*</span>
                                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Select the class section for this lesson"></i>
                                                </label>
                                                <select class="form-control @error('section_id') is-invalid @enderror" id="section_id" name="section_id" required>
                                                    <option value="">Select Section</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
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
                                                <label class="form-label">
                                                    Academic Year <span class="text-danger">*</span>
                                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Select the academic year for this lesson"></i>
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
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Semester <span class="text-danger">*</span>
                                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Select the semester for this lesson"></i>
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
                                </div>

                                <!-- File Upload -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Materials (Optional)
                                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Upload supporting materials like PDFs, documents, or presentations"></i>
                                                </label>
                                                <div class="file-upload-wrapper">
                                                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx">
                                                    <div class="file-upload-info mt-2" id="fileInfo" style="display: none;">
                                                        <div class="alert alert-info">
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
    border: 1px solid #e9ecef;
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
    transition: all 0.3s ease;
}

.student-group-form .form-control:focus {
    border-color: #3d5ee1;
    box-shadow: 0 0 0 0.2rem rgba(61, 94, 225, 0.25);
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
    resize: vertical;
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
    position: relative;
}

.btn-primary {
    background-color: #3d5ee1;
    border: 1px solid #3d5ee1;
}

.btn-primary:hover {
    background-color: #18aefa;
    border: 1px solid #18aefa;
    transform: translateY(-1px);
}

.btn-primary:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
    transform: none;
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
    border-left: 4px solid #dc3545;
}

/* Form text */
.form-text {
    font-size: 13px;
    color: #6c757d;
    margin-top: 5px;
}

/* File upload styling */
.file-upload-wrapper {
    position: relative;
}

.file-upload-info {
    transition: all 0.3s ease;
}

/* Tooltip styling */
.tooltip {
    font-size: 12px;
}

/* Loading spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
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
    
    .download-grp {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    
    .btn {
        width: 100%;
    }
}

/* Animation for form sections */
.student-group-form {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
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
    
    // Form validation with enhanced feedback
    $('#lessonForm').on('submit', function(e) {
        const title = $('#title').val().trim();
        const description = $('#description').val().trim();
        const subjectId = $('#subject_id').val();
        const sectionId = $('#section_id').val();
        const academicYearId = $('#academic_year_id').val();
        const semesterId = $('#semester_id').val();
        const lessonDate = $('#lesson_date').val();
        
        let hasErrors = false;
        let errorMessage = 'Please fix the following errors:\n';
        
        // Clear previous error styling
        $('.form-control').removeClass('is-invalid');
        
        if (!title) {
            $('#title').addClass('is-invalid');
            errorMessage += '• Lesson title is required\n';
            hasErrors = true;
        }
        
        if (!description) {
            $('#description').addClass('is-invalid');
            errorMessage += '• Lesson description is required\n';
            hasErrors = true;
        }
        
        if (!subjectId) {
            $('#subject_id').addClass('is-invalid');
            errorMessage += '• Subject selection is required\n';
            hasErrors = true;
        }
        
        if (!sectionId) {
            $('#section_id').addClass('is-invalid');
            errorMessage += '• Section selection is required\n';
            hasErrors = true;
        }
        
        if (!academicYearId) {
            $('#academic_year_id').addClass('is-invalid');
            errorMessage += '• Academic year selection is required\n';
            hasErrors = true;
        }
        
        if (!semesterId) {
            $('#semester_id').addClass('is-invalid');
            errorMessage += '• Semester selection is required\n';
            hasErrors = true;
        }
        
        if (!lessonDate) {
            $('#lesson_date').addClass('is-invalid');
            errorMessage += '• Lesson date is required\n';
            hasErrors = true;
        }
        
        if (hasErrors) {
            e.preventDefault();
            alert(errorMessage);
            return false;
        }
        
        // Check if lesson date is not in the past
        const selectedDate = new Date(lessonDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            if (!confirm('The selected lesson date is in the past. Are you sure you want to continue?')) {
                e.preventDefault();
                return false;
            }
        }
        
        // Show loading state
        $('#submitBtn').prop('disabled', true);
        $('#submitText').text('Creating...');
        $('#submitSpinner').show();
    });
    
    // File upload validation and preview
    $('#file').on('change', function() {
        const file = this.files[0];
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
        
        if (file) {
            if (file.size > maxSize) {
                alert('File size must be less than 10MB.');
                this.value = '';
                $('#fileInfo').hide();
                return;
            }
            
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid file type (PDF, DOC, DOCX, PPT, PPTX).');
                this.value = '';
                $('#fileInfo').hide();
                return;
            }
            
            // Show file info
            $('#fileName').text(file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)');
            $('#fileInfo').show();
        } else {
            $('#fileInfo').hide();
        }
    });
    
    // Remove file
    $('#removeFile').on('click', function() {
        $('#file').val('');
        $('#fileInfo').hide();
    });
    
    // Initialize preview
    updatePreview();
});
</script>
@endpush

@endsection 