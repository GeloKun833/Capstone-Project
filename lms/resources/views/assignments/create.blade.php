@extends('layouts.master')
@section('content')


{{-- Additional Success Notification --}}
@if(session('success'))
<script>
    // Immediate success notification
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎉 SUCCESS SESSION FOUND:', '{{ session('success') }}');
        
        // Show toastr immediately
        if (typeof toastr !== 'undefined') {
            toastr.success('{{ session('success') }}', 'Success!', {
                timeOut: 3000,
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right'
            });
        }
    });
</script>
@endif

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create New Assignment</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Assignments</a></li>
                        <li class="breadcrumb-item active">Create Assignment</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Assignment Details</h5>
                    </div>
                    <div class="card-body">
                        {{-- Error Display --}}
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-triangle"></i> Validation Errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('assignments.store') }}" method="POST" enctype="multipart/form-data" id="assignmentForm">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-8">
                                    {{-- Basic Information --}}
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="title" class="form-label">Assignment Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                                   id="title" name="title" value="{{ old('title') }}" required>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="max_score" class="form-label">Maximum Score <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('max_score') is-invalid @enderror" 
                                                   id="max_score" name="max_score" value="{{ old('max_score', 100) }}" min="1" max="1000" required>
                                            @error('max_score')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                            <select class="form-control @error('subject_id') is-invalid @enderror" id="subject_id" name="subject_id" required>
                                                <option value="">Select Subject</option>
                                                @php
                                                    $subjectsByGrade = $subjects->groupBy('class');
                                                @endphp
                                                @foreach($subjectsByGrade as $gradeLevel => $gradeSubjects)
                                                    <optgroup label="📚 {{ $gradeLevel }}">
                                                        @foreach($gradeSubjects as $subject)
                                                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                                                {{ $subject->subject_name }} ({{ $subject->subject_id }})
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            @error('subject_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>Only showing subjects assigned to you
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="section_id" class="form-label">Section <span class="text-danger">*</span></label>
                                            <select class="form-control @error('section_id') is-invalid @enderror" id="section_id" name="section_id" required>
                                                <option value="">Select Section</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                        {{ $section->name }} ({{ $section->grade_level }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>Only showing sections assigned to you
                                            </small>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                            <select class="form-control @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                                <option value="">Select Academic Year</option>
                                                @foreach($academicYears as $academicYear)
                                                    <option value="{{ $academicYear->id }}" {{ old('academic_year_id') == $academicYear->id ? 'selected' : '' }}>
                                                        {{ $academicYear->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('academic_year_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
                                            <select class="form-control @error('semester_id') is-invalid @enderror" id="semester_id" name="semester_id" required>
                                                <option value="">Select Semester</option>
                                                @foreach($semesters as $semester)
                                                    <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                                        {{ $semester->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('semester_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                                   id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                                            @error('due_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="due_time" class="form-label">Due Time</label>
                                            <input type="time" class="form-control @error('due_time') is-invalid @enderror" 
                                                   id="due_time" name="due_time" value="{{ old('due_time') }}">
                                            @error('due_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label">Assignment Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="submission_instructions" class="form-label">Submission Instructions</label>
                                        <textarea class="form-control @error('submission_instructions') is-invalid @enderror" 
                                                  id="submission_instructions" name="submission_instructions" rows="3">{{ old('submission_instructions') }}</textarea>
                                        @error('submission_instructions')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    {{-- Settings and Options --}}
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Assignment Settings</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="allows_late_submission" 
                                                           name="allows_late_submission" {{ old('allows_late_submission') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="allows_late_submission">
                                                        Allow Late Submissions
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3" id="late_penalty_div" style="display: none;">
                                                <label for="late_submission_penalty" class="form-label">Late Submission Penalty (%)</label>
                                                <input type="number" class="form-control" id="late_submission_penalty" 
                                                       name="late_submission_penalty" value="{{ old('late_submission_penalty', 10) }}" 
                                                       min="0" max="100">
                                            </div>

                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="requires_file_upload" 
                                                           name="requires_file_upload" {{ old('requires_file_upload') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="requires_file_upload">
                                                        Require File Upload
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3" id="file_settings_div" style="display: none;">
                                                <label for="allowed_file_types" class="form-label">Allowed File Types</label>
                                                <select class="form-control" id="allowed_file_types" name="allowed_file_types[]" multiple>
                                                    <option value="pdf">PDF</option>
                                                    <option value="doc">DOC</option>
                                                    <option value="docx">DOCX</option>
                                                    <option value="ppt">PPT</option>
                                                    <option value="pptx">PPTX</option>
                                                    <option value="txt">TXT</option>
                                                    <option value="jpg">JPG</option>
                                                    <option value="png">PNG</option>
                                                </select>
                                                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple types</small>
                                            </div>

                                            <div class="mb-3" id="file_size_div" style="display: none;">
                                                <label for="max_file_size" class="form-label">Maximum File Size (MB)</label>
                                                <input type="number" class="form-control" id="max_file_size" 
                                                       name="max_file_size" value="{{ old('max_file_size', 10) }}" 
                                                       min="1" max="50">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Assignment File Upload --}}
                                    <div class="card mt-3">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Assignment File</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="assignment_file" class="form-label">Upload Assignment File <span class="text-muted">(Optional)</span></label>
                                                <input type="file" class="form-control @error('assignment_file') is-invalid @enderror" 
                                                       id="assignment_file" name="assignment_file" 
                                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.txt">
                                                @error('assignment_file')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                <div id="fileError" class="alert alert-danger mt-2" style="display: none;">
                                                    <i class="fas fa-exclamation-circle me-2"></i>
                                                    <span id="fileErrorText"></span>
                                                </div>
                                                <div id="fileSuccess" class="alert alert-success mt-2" style="display: none;">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    <span id="fileSuccessText"></span>
                                                </div>
                                                <small class="form-text text-muted">Max size: 10MB. Supported: PDF, DOC, DOCX, PPT, PPTX, TXT</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('assignments.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-save me-2"></i>Create Assignment
                                        </button>
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

{{-- Success Modal --}}
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Assignment Created Successfully!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">🎉 Great Job!</h5>
                <p class="mb-2">Your assignment has been created successfully and is now <strong>published</strong>.</p>
                <p class="mb-0 text-muted">Students can now view and submit this assignment.</p>
            </div>
            <div class="modal-footer">
                <a href="{{ route('assignments.index') }}" class="btn btn-primary w-100">
                    <i class="fas fa-list me-2"></i>View All Assignments
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Show success modal if assignment was created successfully
        @if(session('success'))
        console.log('✅ Success session detected, showing modal...');
        showSuccessModal();
        @endif

        // Toggle late submission penalty field
        $('#allows_late_submission').change(function() {
            if ($(this).is(':checked')) {
                $('#late_penalty_div').show();
            } else {
                $('#late_penalty_div').hide();
            }
        });

        // Toggle file upload settings
        $('#requires_file_upload').change(function() {
            if ($(this).is(':checked')) {
                $('#file_settings_div').show();
                $('#file_size_div').show();
            } else {
                $('#file_settings_div').hide();
                $('#file_size_div').hide();
            }
        });

        // Show/hide fields on page load based on current values
        if ($('#allows_late_submission').is(':checked')) {
            $('#late_penalty_div').show();
        }
        if ($('#requires_file_upload').is(':checked')) {
            $('#file_settings_div').show();
            $('#file_size_div').show();
        }

        // File upload validation and preview
        $('#assignment_file').on('change', function() {
            const file = this.files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain'
            ];
            const allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt'];
            
            // Hide previous messages
            $('#fileError').hide();
            $('#fileSuccess').hide();
            $(this).removeClass('is-invalid');
            
            if (file) {
                // Check file size
                if (file.size > maxSize) {
                    $('#fileErrorText').text('File size must be less than 10MB. Your file is ' + (file.size / 1024 / 1024).toFixed(2) + ' MB.');
                    $('#fileError').show();
                    $(this).addClass('is-invalid');
                    this.value = '';
                    return;
                }
                
                // Check file type
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(fileExtension)) {
                    $('#fileErrorText').text('Invalid file type. Please select a PDF, DOC, DOCX, PPT, PPTX, or TXT file.');
                    $('#fileError').show();
                    $(this).addClass('is-invalid');
                    this.value = '';
                    return;
                }
                
                // If validation passes, show success
                $('#fileSuccessText').text(file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB) - Ready to upload!');
                $('#fileSuccess').show();
                $(this).removeClass('is-invalid');
            } else {
                $('#fileSuccess').hide();
            }
        });

        // Form submission - prevent double submit
        $('#assignmentForm').on('submit', function() {
            const submitBtn = $('#submitBtn');
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Creating Assignment...');
        });
    });

    // Function to show success modal with multiple fallback methods
    function showSuccessModal() {
        const modal = document.getElementById('successModal');
        
        if (!modal) {
            console.error('❌ Success modal element not found');
            // Fallback: Use toastr if available
            if (typeof toastr !== 'undefined') {
                toastr.success('Assignment created successfully!', 'Success', {
                    timeOut: 5000,
                    closeButton: true,
                    progressBar: true
                });
            } else {
                alert('✅ Assignment created successfully!');
            }
            return;
        }
        
        try {
            // Try Bootstrap 5 Modal first
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                console.log('✅ Using Bootstrap 5 Modal');
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
            // Try jQuery/Bootstrap 4 Modal
            else if (typeof $ !== 'undefined' && $.fn.modal) {
                console.log('✅ Using jQuery Modal');
                $('#successModal').modal('show');
            }
            // Manual fallback
            else {
                console.log('⚠️ Using manual modal display');
                modal.classList.add('show');
                modal.style.display = 'block';
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('role', 'dialog');
                
                // Add backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'successModalBackdrop';
                document.body.appendChild(backdrop);
                document.body.classList.add('modal-open');
                
                // Close on backdrop click
                backdrop.addEventListener('click', function() {
                    closeSuccessModal();
                });
                
                // Close on button click
                const closeBtn = modal.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        closeSuccessModal();
                    });
                }
            }
            console.log('✅ Success modal opened');
        } catch (e) {
            console.error('❌ Error opening success modal:', e);
            alert('✅ Assignment created successfully!');
        }
    }

    // Function to close success modal
    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        const backdrop = document.getElementById('successModalBackdrop');
        
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');
        }
        
        if (backdrop) {
            backdrop.remove();
        }
        
        document.body.classList.remove('modal-open');
    }
</script>
@endsection
