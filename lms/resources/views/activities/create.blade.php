@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Create Activity</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.activities.index', $lesson) }}">Activities</a></li>
                            <li class="breadcrumb-item active">Create Activity</li>
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
                                        <h3 class="page-title">Activity Information</h3>
                                        <p class="text-muted">Create a new activity for lesson: <strong>{{ $lesson->title }}</strong></p>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('lessons.activities.index', $lesson) }}" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-arrow-left"></i> Back to Activities
                                        </a>
                                        <button type="submit" form="activityForm" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Activity
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('lessons.activities.store', $lesson) }}" method="POST" id="activityForm">
                                @csrf
                                
                                <!-- Activity Details -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Activity Title</label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Enter activity title" required>
                                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Due Date</label>
                                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', now()->addDays(7)->toDateString()) }}" required>
                                                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">Activity Instructions</label>
                                                <textarea class="form-control @error('instructions') is-invalid @enderror" id="instructions" name="instructions" rows="4" placeholder="Provide detailed instructions for students on how to complete this activity" required>{{ old('instructions') }}</textarea>
                                                @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="allows_submission" name="allows_submission" value="1" {{ old('allows_submission') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="allows_submission">
                                                        Allow students to submit files for this activity
                                                    </label>
                                                </div>
                                                <small class="form-text text-muted">If checked, students can upload files and you can grade their submissions using rubrics.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activity Preview -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">
                                                    <i class="fas fa-eye me-2"></i>Activity Preview
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="info-item">
                                                            <label class="info-label">Title:</label>
                                                            <span class="info-value" id="previewTitle">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="info-item">
                                                            <label class="info-label">Due Date:</label>
                                                            <span class="info-value" id="previewDueDate">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="info-item">
                                                            <label class="info-label">Submission:</label>
                                                            <span class="info-value" id="previewSubmission">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="info-item">
                                                            <label class="info-label">Instructions:</label>
                                                            <span class="info-value" id="previewInstructions">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lesson Information -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">
                                                    <i class="fas fa-info-circle me-2"></i>Lesson Information
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Lesson:</label>
                                                            <span class="info-value">{{ $lesson->title }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Subject:</label>
                                                            <span class="info-value">{{ $lesson->subject->subject_name }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Section:</label>
                                                            <span class="info-value">{{ $lesson->section->name }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Lesson Date:</label>
                                                            <span class="info-value">{{ $lesson->lesson_date->format('M d, Y') }}</span>
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

.student-group-form .form-check {
    margin-top: 10px;
}

.student-group-form .form-check-input {
    width: 18px;
    height: 18px;
    margin-top: 0;
}

.student-group-form .form-check-input:checked {
    background-color: #3d5ee1;
    border-color: #3d5ee1;
}

.student-group-form .form-check-label {
    font-weight: 500;
    color: #2c323f;
    margin-left: 8px;
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

/* Form text */
.form-text {
    font-size: 13px;
    color: #6c757d;
    margin-top: 5px;
}

/* Text styling */
.text-muted {
    color: #6c757d !important;
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
        const title = $('#title').val();
        const dueDate = $('#due_date').val();
        const instructions = $('#instructions').val();
        const allowsSubmission = $('#allows_submission').is(':checked');
        
        $('#previewTitle').text(title || '-');
        $('#previewDueDate').text(dueDate ? new Date(dueDate).toLocaleDateString() : '-');
        $('#previewInstructions').text(instructions || '-');
        $('#previewSubmission').text(allowsSubmission ? 'Allowed' : 'Not Allowed');
    }
    
    // Bind preview updates to form changes
    $('#title, #due_date, #instructions').on('input change', updatePreview);
    $('#allows_submission').on('change', updatePreview);
    
    // Form validation
    $('#activityForm').on('submit', function(e) {
        const title = $('#title').val();
        const instructions = $('#instructions').val();
        const dueDate = $('#due_date').val();
        
        if (!title || !instructions || !dueDate) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        // Check if due date is not in the past
        const selectedDate = new Date(dueDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            if (!confirm('The selected due date is in the past. Are you sure you want to continue?')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Initialize preview
    updatePreview();
});
</script>
@endpush

@endsection 