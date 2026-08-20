@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Mark Attendance</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                            <li class="breadcrumb-item active">Mark Attendance</li>
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
                                        <h3 class="page-title">Attendance Entry Form</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-arrow-left"></i> Back to Summary
                                        </a>
                                        <button type="submit" form="attendanceForm" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Attendance
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('attendance.store') }}" method="POST" id="attendanceForm">
                                @csrf
                                
                                <!-- Filter Section -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Subject (Learning Area) *</label>
                                                <select name="subject_id" id="subject_id" class="form-control @error('subject_id') is-invalid @enderror">
                                                    <option value="">Select Subject</option>
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject->id }}" {{ old('subject_id', $subjectId ?? '') == $subject->id ? 'selected' : '' }}>
                                                            {{ $subject->subject_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                <small class="text-muted">Select a subject to view assigned students</small>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Section (Optional)</label>
                                                <select name="section_id" id="section_id" class="form-control @error('section_id') is-invalid @enderror">
                                                    <option value="">All Sections</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}" {{ old('section_id', $sectionId ?? '') == $section->id ? 'selected' : '' }}>
                                                            {{ $section->name }} (Grade {{ $section->grade_level }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                <small class="text-muted">Filter by section (optional)</small>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Date *</label>
                                                <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $date ?? now()->toDateString()) }}" required>
                                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    @if($students->isEmpty() && !$subjectId)
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Note:</strong> Please select a Subject to view all assigned students. All students from sections where you teach the selected subject will be displayed automatically.
                                        </div>
                                    @elseif($students->isEmpty() && $subjectId)
                                        <div class="alert alert-warning mt-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>No students found</strong> for the selected subject. Please verify that students are assigned to sections where you teach this subject.
                                        </div>
                                    @else
                                        <div class="alert alert-success mt-3">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <strong>{{ $students->count() }} student(s)</strong> found for the selected criteria.
                                        </div>
                                    @endif
                                </div>

                                <!-- Students Table -->
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>
                                                    <div class="form-check check-tables">
                                                        <input class="form-check-input" type="checkbox" value="something" id="selectAllStudents">
                                                    </div>
                                                </th>
                                                <th>Student Name</th>
                                                <th class="text-center">Present</th>
                                                <th class="text-center">Absent</th>
                                                <th>Remarks</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentsTableBody">
                                            @forelse($students as $student)
                                                <tr>
                                                    <td>
                                                        <div class="form-check check-tables">
                                                            <input class="form-check-input student-checkbox" type="checkbox" value="{{ $student->id }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <h2>
                                                            <a>{{ $student->first_name }} {{ $student->last_name }}</a>
                                                        </h2>
                                                        <small class="text-muted">Student ID: {{ $student->id }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" name="attendance[{{ $student->id }}][status]" value="present" 
                                                                class="form-check-input @error('attendance.' . $student->id . '.status') is-invalid @enderror"
                                                                {{ (old('attendance.' . $student->id . '.status', $existing[$student->id]['status'] ?? '') == 'present') ? 'checked' : '' }} required>
                                                            <label class="form-check-label">Present</label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" name="attendance[{{ $student->id }}][status]" value="absent" 
                                                                class="form-check-input @error('attendance.' . $student->id . '.status') is-invalid @enderror"
                                                                {{ (old('attendance.' . $student->id . '.status', $existing[$student->id]['status'] ?? '') == 'absent') ? 'checked' : '' }} required>
                                                            <label class="form-check-label">Absent</label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="attendance[{{ $student->id }}][remarks]" class="form-control form-control-sm"
                                                            value="{{ old('attendance.' . $student->id . '.remarks', $existing[$student->id]['remarks'] ?? '') }}"
                                                            placeholder="Optional remarks">
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="actions">
                                                            <a href="#" class="btn btn-sm bg-danger-light">
                                                                <i class="far fa-eye"></i>
                                                            </a>
                                                            <a href="#" class="btn btn-sm bg-danger-light">
                                                                <i class="far fa-edit"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-users fa-3x mb-3"></i>
                                                            <h5>No students found</h5>
                                                            <p>No students are enrolled in the selected section.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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

/* Table styling */
.table {
    color: #333;
    max-width: 100%;
    margin-bottom: 0;
    width: 100%;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
    color: #000;
    background-color: #f8f9fa;
    border-color: #eff2f7;
    padding: 15px;
}

.table tbody tr {
    border-bottom: 1px solid #dee2e6;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: #f7f7f7;
}

.table-hover tbody tr:hover td {
    color: #474648;
}

/* Form elements in table */
.table .form-control {
    border: 1px solid #ddd;
    border-radius: 5px;
    height: 40px;
    padding: 8px 12px;
    font-size: 14px;
}

.table .form-control:focus {
    border-color: #3d5ee1;
    box-shadow: none;
    outline: 0;
}

.table .form-control-sm {
    height: 35px;
    padding: 6px 10px;
    font-size: 13px;
}

/* Radio buttons */
.form-check-inline {
    margin-right: 0;
}

.form-check-input {
    width: 18px;
    height: 18px;
    margin-top: 0;
}

.form-check-input:checked {
    background-color: #3d5ee1;
    border-color: #3d5ee1;
}

.form-check-label {
    font-size: 14px;
    font-weight: 500;
    color: #333;
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

/* Actions */
.actions {
    display: flex;
    justify-content: end;
}

.actions a {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 5px;
}

.actions a:hover {
    background-color: #3d5ee1 !important;
    color: #fff !important;
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

/* Text styling */
.text-muted {
    color: #6c757d !important;
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

/* Responsive */
@media (max-width: 768px) {
    .student-group-form {
        padding: 15px;
    }
    
    .card-table .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .table th, .table td {
        padding: 10px 8px;
    }
    
    .form-check-inline {
        display: block;
        margin-bottom: 5px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const sectionSelect = document.getElementById('section_id');
    const subjectSelect = document.getElementById('subject_id');
    
    // When section changes, update available subjects
    sectionSelect.addEventListener('change', function() {
        const sectionId = this.value;
        if (!sectionId) {
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            return;
        }
        
        // Show loading state
        subjectSelect.innerHTML = '<option value="">Loading...</option>';
        subjectSelect.disabled = true;
        
        // Fetch subjects for the selected section
        fetch(`/api/sections/${sectionId}/subjects`)
            .then(response => response.json())
            .then(subjects => {
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                subjects.forEach(subject => {
                    subjectSelect.innerHTML += `<option value="${subject.id}">${subject.subject_name}</option>`;
                });
                subjectSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching subjects:', error);
                subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                subjectSelect.disabled = false;
            });
    });
    
    // Select all functionality
    $('#selectAllStudents').on('change', function() {
        $('.student-checkbox').prop('checked', $(this).is(':checked'));
    });
    
    // Auto-load students when subject changes (section is optional)
    $('#subject_id, #section_id, #date').on('change', function() {
        const sectionId = $('#section_id').val();
        const subjectId = $('#subject_id').val();
        const date = $('#date').val();
        
        if (subjectId && date) {
            // Build URL with parameters
            let url = `{{ route('attendance.create') }}?subject_id=${subjectId}&date=${date}`;
            if (sectionId) {
                url += `&section_id=${sectionId}`;
            }
            // Redirect to the same page with new parameters
            window.location.href = url;
        }
    });
    
    // Form validation
    $('#attendanceForm').on('submit', function(e) {
        const subjectId = $('#subject_id').val();
        const date = $('#date').val();
        
        if (!subjectId || !date) {
            e.preventDefault();
            alert('Please select Subject and Date before saving attendance.');
            return false;
        }
        
        // Check if at least one student has attendance marked
        const hasAttendance = $('input[name$="[status]"]:checked').length > 0;
        if (!hasAttendance) {
            e.preventDefault();
            alert('Please mark attendance for at least one student.');
            return false;
        }
    });
});
</script>
@endpush

@endsection 