@extends('layouts.master')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Assignment</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Assignments</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assignments.show', $assignment->id) }}">{{ $assignment->title }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Edit Assignment: {{ $assignment->title }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('assignments.update', $assignment->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-8">
                                    {{-- Basic Information --}}
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="title" class="form-label">Assignment Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                                   id="title" name="title" value="{{ old('title', $assignment->title) }}" required>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="max_score" class="form-label">Maximum Score <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('max_score') is-invalid @enderror" 
                                                   id="max_score" name="max_score" value="{{ old('max_score', $assignment->max_score) }}" min="1" max="1000" required>
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
                                                @foreach($subjects as $subject)
                                                    <option value="{{ $subject->id }}" {{ old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : '' }}>
                                                        {{ $subject->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('subject_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="section_id" class="form-label">Section <span class="text-danger">*</span></label>
                                            <select class="form-control @error('section_id') is-invalid @enderror" id="section_id" name="section_id" required>
                                                <option value="">Select Section</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{ old('section_id', $assignment->section_id) == $section->id ? 'selected' : '' }}>
                                                        {{ $section->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                            <select class="form-control @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                                <option value="">Select Academic Year</option>
                                                @foreach($academicYears as $academicYear)
                                                    <option value="{{ $academicYear->id }}" {{ old('academic_year_id', $assignment->academic_year_id) == $academicYear->id ? 'selected' : '' }}>
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
                                                    <option value="{{ $semester->id }}" {{ old('semester_id', $assignment->semester_id) == $semester->id ? 'selected' : '' }}>
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
                                                   id="due_date" name="due_date" value="{{ old('due_date', $assignment->due_date) }}" required>
                                            @error('due_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="due_time" class="form-label">Due Time</label>
                                            <input type="time" class="form-control @error('due_time') is-invalid @enderror" 
                                                   id="due_time" name="due_time" value="{{ old('due_time', $assignment->due_time) }}">
                                            @error('due_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label">Assignment Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="5" required>{{ old('description', $assignment->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="submission_instructions" class="form-label">Submission Instructions</label>
                                        <textarea class="form-control @error('submission_instructions') is-invalid @enderror" 
                                                  id="submission_instructions" name="submission_instructions" rows="3">{{ old('submission_instructions', $assignment->submission_instructions) }}</textarea>
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
                                                           name="allows_late_submission" {{ old('allows_late_submission', $assignment->allows_late_submission) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="allows_late_submission">
                                                        Allow Late Submissions
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3" id="late_penalty_div" style="display: {{ $assignment->allows_late_submission ? 'block' : 'none' }};">
                                                <label for="late_submission_penalty" class="form-label">Late Submission Penalty (%)</label>
                                                <input type="number" class="form-control" id="late_submission_penalty" 
                                                       name="late_submission_penalty" value="{{ old('late_submission_penalty', $assignment->late_submission_penalty) }}" 
                                                       min="0" max="100">
                                            </div>

                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="requires_file_upload" 
                                                           name="requires_file_upload" {{ old('requires_file_upload', $assignment->requires_file_upload) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="requires_file_upload">
                                                        Require File Upload
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3" id="file_settings_div" style="display: {{ $assignment->requires_file_upload ? 'block' : 'none' }};">
                                                <label for="allowed_file_types" class="form-label">Allowed File Types</label>
                                                <select class="form-control" id="allowed_file_types" name="allowed_file_types[]" multiple>
                                                    @php
                                                        $allowedTypes = is_array($assignment->allowed_file_types) ? $assignment->allowed_file_types : [];
                                                    @endphp
                                                    <option value="pdf" {{ in_array('pdf', $allowedTypes) ? 'selected' : '' }}>PDF</option>
                                                    <option value="doc" {{ in_array('doc', $allowedTypes) ? 'selected' : '' }}>DOC</option>
                                                    <option value="docx" {{ in_array('docx', $allowedTypes) ? 'selected' : '' }}>DOCX</option>
                                                    <option value="ppt" {{ in_array('ppt', $allowedTypes) ? 'selected' : '' }}>PPT</option>
                                                    <option value="pptx" {{ in_array('pptx', $allowedTypes) ? 'selected' : '' }}>PPTX</option>
                                                    <option value="txt" {{ in_array('txt', $allowedTypes) ? 'selected' : '' }}>TXT</option>
                                                    <option value="jpg" {{ in_array('jpg', $allowedTypes) ? 'selected' : '' }}>JPG</option>
                                                    <option value="png" {{ in_array('png', $allowedTypes) ? 'selected' : '' }}>PNG</option>
                                                </select>
                                                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple types</small>
                                            </div>

                                            <div class="mb-3" id="file_size_div" style="display: {{ $assignment->requires_file_upload ? 'block' : 'none' }};">
                                                <label for="max_file_size" class="form-label">Maximum File Size (MB)</label>
                                                <input type="number" class="form-control" id="max_file_size" 
                                                       name="max_file_size" value="{{ old('max_file_size', $assignment->max_file_size) }}" 
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
                                            @if($assignment->assignment_file)
                                                <div class="mb-3">
                                                    <label class="form-label">Current File</label>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-file me-2"></i>
                                                        <span>{{ basename($assignment->assignment_file) }}</span>
                                                        <a href="{{ Storage::url($assignment->assignment_file) }}" class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <div class="mb-3">
                                                <label for="assignment_file" class="form-label">Upload New File (Optional)</label>
                                                <input type="file" class="form-control @error('assignment_file') is-invalid @enderror" 
                                                       id="assignment_file" name="assignment_file" 
                                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.txt">
                                                @error('assignment_file')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Max size: 10MB. Supported: PDF, DOC, DOCX, PPT, PPTX, TXT</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('assignments.show', $assignment->id) }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Assignment
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

@endsection

@section('script')
<script>
    $(document).ready(function() {
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
    });
</script>
@endsection
