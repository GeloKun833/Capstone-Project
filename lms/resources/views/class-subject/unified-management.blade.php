@extends('layouts.master')

@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Academic Management</h3>
                    <p class="text-muted mb-0">Manage teachers, sections, and subject assignments. Students are automatically assigned to sections through the enrollment portal.</p>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Academic Management</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Teacher Assignment</h5>
                    </div>
                    <div class="card-body">
                <!-- Quick Setup Buttons -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <a href="{{ route('subject/list/page') }}" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-book fa-2x mb-2"></i>
                            <span class="fw-bold">Manage Subjects</span>
                            <small class="text-muted">Add & edit subjects</small>
                                </a>
                            </div>
                    <div class="col-md-3">
                        <a href="{{ route('sections.index') }}" class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-layer-group fa-2x mb-2"></i>
                            <span class="fw-bold">Manage Sections</span>
                            <small class="text-muted">Add & edit sections</small>
                        </a>
                    </div>
                                        <div class="col-md-3">
                        <a href="{{ route('teacher/list/page') }}" class="btn btn-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                            <span class="fw-bold">Manage Teachers</span>
                            <small class="text-muted">Add & edit teachers</small>
                        </a>
                                        </div>
                                        <div class="col-md-3">
                        <a href="{{ route('academic_years.index') }}" class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-calendar fa-2x mb-2"></i>
                            <span class="fw-bold">Academic Years</span>
                            <small class="text-muted">Manage academic periods</small>
                        </a>
                            </div>
                        </div>

                        <hr>

                        <!-- Quick Setup Information -->
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                            <strong>Quick Setup:</strong> Need to create new sections or subjects first? Use the buttons above to add them, then come back here to assign teachers.
        </div>
    </div>
</div>

                <!-- Teacher Assignment Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chalkboard-teacher text-primary me-2"></i>Assign Teacher to Subject
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('class-subject.unified-management') }}" id="teacherAssignmentForm">
            @csrf
            <input type="hidden" name="operation_type" value="teacher_subject">
            
            <div class="row">
                <!-- Subject Selection -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="subject_id">Subject <span class="text-danger">*</span></label>
                        <select class="form-control @error('subject_id') is-invalid @enderror" name="subject_id" id="subject_id" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->subject_name }} ({{ $subject->class }})
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Academic Year -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="academic_year_id">Academic Year <span class="text-danger">*</span></label>
                        <select class="form-control @error('academic_year_id') is-invalid @enderror" name="academic_year_id" id="academic_year_id" required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Semester -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="semester_id">Semester <span class="text-danger">*</span></label>
                        <select class="form-control @error('semester_id') is-invalid @enderror" name="semester_id" id="semester_id" required>
                            <option value="">Select Semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('semester_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                                <!-- Section -->
                <div class="col-md-6">
                    <div class="form-group">
                                        <label for="section_id">Section <span class="text-danger">*</span></label>
                                        <select class="form-control @error('section_id') is-invalid @enderror" name="section_id" id="section_id" required>
                                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                    {{ $section->name }} ({{ $section->class }})
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Teacher Selection -->
            <div class="form-group">
                <label for="teacher_ids">Teachers <span class="text-danger">*</span></label>
                @if($teachers->isEmpty())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>No teachers available!</strong> Please create teacher users in 
                        <a href="{{ route('list/users') }}" class="alert-link">User Management</a> first, 
                        or click the yellow <strong>Sync</strong> button on the 
                        <a href="{{ route('teacher/list/page') }}" class="alert-link">Teachers page</a>.
                    </div>
                @else
                    <div class="row">
                        @foreach($teachers as $teacher)
                            <div class="col-md-4 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="teacher_ids[]" value="{{ $teacher->id }}" 
                                           id="teacher_{{ $teacher->id }}" {{ in_array($teacher->id, old('teacher_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="teacher_{{ $teacher->id }}">
                                        <strong>{{ $teacher->full_name ?: ($teacher->user->name ?? 'Unknown Teacher') }}</strong>
                                        <br><small class="text-muted">ID: {{ $teacher->user_id ?? 'N/A' }}</small>
                                        @if($teacher->user && $teacher->user->department)
                                            <br><small class="text-muted">{{ $teacher->user->department }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        {{ $teachers->count() }} teacher(s) available for assignment
                    </small>
                @endif
            </div>
                        
                        @error('teacher_ids')
                            <div class="alert alert-danger mt-3">{{ $message }}</div>
                        @enderror
            </div>

            <div class="text-end mt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Assign Teachers to Subject
                </button>
            </div>
        </form>
                    </div>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#teacherAssignmentForm').on('submit', function(e) {
        const teacherIds = $('input[name="teacher_ids[]"]:checked').length;
        if (teacherIds === 0) {
            e.preventDefault();
            alert('Please select at least one teacher.');
            return false;
        }
        
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');
    });
});
</script>
@endsection