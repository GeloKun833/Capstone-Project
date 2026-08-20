@extends('layouts.master')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Quarterly Grade Entry</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Grade Entry</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Select Learning Area & Academic Year</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('teacher.grading.grade-entry') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><strong>Learning Area (Subject) *</strong></label>
                                    <select class="form-control form-select" name="subject_id" id="selected_subject" required>
                                        <option value="">-- Select Learning Area --</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ $selectedSubjectId == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->subject_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><strong>Academic Year *</strong></label>
                                    <select class="form-control form-select" name="academic_year_id" id="selected_academic_year" required>
                                        <option value="">-- Select Academic Year --</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ $currentAcademicYear && $currentAcademicYear->id == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-search me-2"></i>Load Students
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grade Entry Table - DepEd Report Card Format -->
            @if($selectedSubjectId && $currentAcademicYear && $students->count() > 0)
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>
                        Quarterly Grades - {{ $subjects->where('id', $selectedSubjectId)->first()->subject_name ?? 'N/A' }} 
                        ({{ $currentAcademicYear->name }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle me-2"></i>Instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Enter quarterly grades (Q1, Q2, Q3, Q4) for each student</li>
                            <li>Final Grade is automatically computed as the average of all quarters</li>
                            <li>Enter remarks manually for each student, or click the <i class="fas fa-magic"></i> button to auto-fill based on final grade</li>
                            <li>Click "Save All Grades" when finished</li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="gradesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 20%;">Student Name</th>
                                    <th style="width: 12%;">Quarter 1</th>
                                    <th style="width: 12%;">Quarter 2</th>
                                    <th style="width: 12%;">Quarter 3</th>
                                    <th style="width: 12%;">Quarter 4</th>
                                    <th style="width: 12%;">Final Grade</th>
                                    <th style="width: 15%;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $index => $student)
                                    @php
                                        $quarterlyGrade = $quarterlyGrades->get($student->id);
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name ?? '' }}</strong></td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control quarter-input" 
                                                   data-student-id="{{ $student->id }}"
                                                   data-quarter="1"
                                                   name="grades[{{ $student->id }}][quarter_1]"
                                                   value="{{ $quarterlyGrade ? $quarterlyGrade->quarter_1 : '' }}"
                                                   min="0" 
                                                   max="100"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control quarter-input" 
                                                   data-student-id="{{ $student->id }}"
                                                   data-quarter="2"
                                                   name="grades[{{ $student->id }}][quarter_2]"
                                                   value="{{ $quarterlyGrade ? $quarterlyGrade->quarter_2 : '' }}"
                                                   min="0" 
                                                   max="100"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control quarter-input" 
                                                   data-student-id="{{ $student->id }}"
                                                   data-quarter="3"
                                                   name="grades[{{ $student->id }}][quarter_3]"
                                                   value="{{ $quarterlyGrade ? $quarterlyGrade->quarter_3 : '' }}"
                                                   min="0" 
                                                   max="100"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control quarter-input" 
                                                   data-student-id="{{ $student->id }}"
                                                   data-quarter="4"
                                                   name="grades[{{ $student->id }}][quarter_4]"
                                                   value="{{ $quarterlyGrade ? $quarterlyGrade->quarter_4 : '' }}"
                                                   min="0" 
                                                   max="100"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary final-grade-display" data-student-id="{{ $student->id }}">
                                                {{ $quarterlyGrade && $quarterlyGrade->final_grade ? number_format($quarterlyGrade->final_grade, 2) : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control remarks-input" 
                                                       data-student-id="{{ $student->id }}"
                                                       name="grades[{{ $student->id }}][remarks]"
                                                       value="{{ $quarterlyGrade && $quarterlyGrade->remarks ? $quarterlyGrade->remarks : '' }}"
                                                       placeholder="Enter remarks"
                                                       list="remarks-suggestions-{{ $student->id }}">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary auto-fill-remarks" 
                                                        data-student-id="{{ $student->id }}"
                                                        title="Auto-fill based on final grade">
                                                    <i class="fas fa-magic"></i>
                                                </button>
                                            </div>
                                            <datalist id="remarks-suggestions-{{ $student->id }}">
                                                <option value="Outstanding">
                                                <option value="Very Satisfactory">
                                                <option value="Satisfactory">
                                                <option value="Fairly Satisfactory">
                                                <option value="Did Not Meet Expectations">
                                            </datalist>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-success btn-lg" id="saveGradesBtn">
                            <i class="fas fa-save me-2"></i>Save All Grades
                        </button>
                    </div>
                </div>
            </div>
            @elseif($selectedSubjectId && $currentAcademicYear && $students->count() == 0)
            <div class="card mt-4">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-user-slash text-muted" style="font-size: 5rem; opacity: 0.6;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">No Students Found</h4>
                    <p class="text-muted mb-4">No students are assigned to your sections for the selected subject and academic year.</p>
                </div>
            </div>
            @else
            <div class="card mt-4">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-clipboard-list text-primary" style="font-size: 5rem; opacity: 0.6;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">Ready to Enter Grades</h4>
                    <p class="text-muted mb-4">Please select a Learning Area (Subject) and Academic Year above to view and enter student grades.</p>
                </div>
            </div>
            @endif

        </div>
    </div>

@push('styles')
<style>
.card {
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 20px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    padding: 15px 20px;
}

.table th {
    background-color: #343a40;
    color: white;
    font-weight: 600;
    padding: 15px;
    text-align: center;
    vertical-align: middle;
}

.table td {
    padding: 12px;
    vertical-align: middle;
}

.quarter-input {
    width: 100%;
    padding: 10px;
    border: 2px solid #ddd;
    border-radius: 5px;
    text-align: center;
    font-size: 16px;
    font-weight: 600;
}

.quarter-input:focus {
    border-color: #3d5ee1;
    box-shadow: 0 0 0 0.2rem rgba(61, 94, 225, 0.25);
}

.badge {
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 600;
}

.remarks-input {
    font-size: 14px;
}

.auto-fill-remarks {
    border-left: none;
}

/* DepEd Grading Scale Colors */
.remarks-outstanding { background-color: #28a745 !important; color: white; }
.remarks-very-satisfactory { background-color: #17a2b8 !important; color: white; }
.remarks-satisfactory { background-color: #007bff !important; color: white; }
.remarks-fairly-satisfactory { background-color: #ffc107 !important; color: #212529; }
.remarks-did-not-meet { background-color: #dc3545 !important; color: white; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Verify button exists and bind handlers
    const $saveBtn = $('#saveGradesBtn');
    if ($saveBtn.length === 0) {
        console.error('Save All Grades button not found!');
    } else {
        console.log('Save All Grades button found, binding click handler');
    }
    // Auto-calculate final grade and remarks when quarter grades are entered
    $(document).on('input', '.quarter-input', function() {
        const $row = $(this).closest('tr');
        const studentId = $(this).data('student-id');
        
        // Get all quarter grades for this student
        const q1 = parseFloat($row.find('input[data-quarter="1"]').val()) || 0;
        const q2 = parseFloat($row.find('input[data-quarter="2"]').val()) || 0;
        const q3 = parseFloat($row.find('input[data-quarter="3"]').val()) || 0;
        const q4 = parseFloat($row.find('input[data-quarter="4"]').val()) || 0;
        
        // Calculate final grade (average of all quarters with values)
        const quarters = [];
        if ($row.find('input[data-quarter="1"]').val() !== '') quarters.push(q1);
        if ($row.find('input[data-quarter="2"]').val() !== '') quarters.push(q2);
        if ($row.find('input[data-quarter="3"]').val() !== '') quarters.push(q3);
        if ($row.find('input[data-quarter="4"]').val() !== '') quarters.push(q4);
        
        let finalGrade = null;
        if (quarters.length > 0) {
            finalGrade = (quarters.reduce((a, b) => a + b, 0) / quarters.length).toFixed(2);
        }
        
        // Update final grade display
        const $finalGradeDisplay = $row.find('.final-grade-display');
        if (finalGrade !== null) {
            $finalGradeDisplay.text(parseFloat(finalGrade).toFixed(2));
        } else {
            $finalGradeDisplay.text('-');
        }
        
        // Update remarks suggestion (but don't auto-fill - let teacher decide)
        // The auto-fill button will handle this
    });
    
    // Auto-fill remarks button
    $(document).on('click', '.auto-fill-remarks', function() {
        const studentId = $(this).data('student-id');
        const $row = $(this).closest('tr');
        const $remarksInput = $row.find('.remarks-input');
        
        // Get final grade
        const finalGradeText = $row.find('.final-grade-display').text();
        const finalGrade = parseFloat(finalGradeText);
        
        if (isNaN(finalGrade)) {
            toastr.warning('Please enter quarter grades first to calculate final grade.');
            return;
        }
        
        // Determine remarks based on DepEd grading scale
        let remarks = '';
        if (finalGrade >= 90 && finalGrade <= 100) {
            remarks = 'Outstanding';
        } else if (finalGrade >= 85 && finalGrade <= 89) {
            remarks = 'Very Satisfactory';
        } else if (finalGrade >= 80 && finalGrade <= 84) {
            remarks = 'Satisfactory';
        } else if (finalGrade >= 75 && finalGrade <= 79) {
            remarks = 'Fairly Satisfactory';
        } else if (finalGrade < 75) {
            remarks = 'Did Not Meet Expectations';
        }
        
        if (remarks) {
            $remarksInput.val(remarks);
            toastr.success('Remarks auto-filled based on final grade.');
        }
    });
    
    // Save Grades Button - Use both direct binding and event delegation for reliability
    $saveBtn.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Save All Grades button clicked');
        
        const selectedSubjectId = $('#selected_subject').val();
        const selectedAcademicYearId = $('#selected_academic_year').val();
        
        console.log('Selected values:', {
            subject_id: selectedSubjectId,
            academic_year_id: selectedAcademicYearId
        });
        
        if (!selectedSubjectId || !selectedAcademicYearId) {
            toastr.error('Please select Learning Area and Academic Year before saving grades.');
            return false;
        }
        
        const grades = [];
        
        // Collect quarter grades
        $('.quarter-input').each(function() {
            const studentId = $(this).data('student-id');
            const quarter = $(this).data('quarter');
            const value = $(this).val();
            
            // Find or create grade object for this student
            let gradeObj = grades.find(g => g.student_id == studentId);
            if (!gradeObj) {
                gradeObj = { student_id: parseInt(studentId) };
                grades.push(gradeObj);
            }
            
            // Add quarter grade if value exists and is not empty
            if (value !== '' && value !== null && value !== undefined && !isNaN(parseFloat(value))) {
                const numValue = parseFloat(value);
                if (numValue >= 0) {
                    gradeObj['quarter_' + quarter] = numValue;
                }
            }
        });
        
        // Collect remarks
        $('.remarks-input').each(function() {
            const studentId = $(this).data('student-id');
            const remarks = $(this).val().trim();
            
            let gradeObj = grades.find(g => g.student_id == studentId);
            if (!gradeObj) {
                gradeObj = { student_id: parseInt(studentId) };
                grades.push(gradeObj);
            }
            
            if (remarks !== '') {
                gradeObj['remarks'] = remarks;
            }
        });
        
        // Filter out grades with no quarter values
        const validGrades = grades.filter(g => {
            return (g.quarter_1 !== undefined && g.quarter_1 !== null) ||
                   (g.quarter_2 !== undefined && g.quarter_2 !== null) ||
                   (g.quarter_3 !== undefined && g.quarter_3 !== null) ||
                   (g.quarter_4 !== undefined && g.quarter_4 !== null);
        });
        
        console.log('Grades to save:', validGrades);
        
        if (validGrades.length === 0) {
            toastr.warning('Please enter at least one quarter grade before saving.');
            return false;
        }
        
        // Show loading
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);
        
        // Save grades
        $.ajax({
            url: '{{ route("teacher.grading.store-quarterly-grades") }}',
            type: 'POST',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: {
                _token: '{{ csrf_token() }}',
                subject_id: selectedSubjectId,
                academic_year_id: selectedAcademicYearId,
                grades: validGrades
            },
            beforeSend: function() {
                console.log('Sending AJAX request to save quarterly grades...');
            },
            success: function(response) {
                console.log('Success response:', response);
                if (response && response.success) {
                    toastr.success(response.message || `Successfully saved ${validGrades.length} student grade(s)! Students can now view these grades in their portal.`);
                    console.log('Grades saved successfully. Students can now view these grades in their portal.');
                    // Reload page to show updated grades
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    toastr.warning(response.message || 'Grades saved with warnings.');
                    $btn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error saving grades:', {
                    status: status,
                    error: error,
                    response: xhr.responseText,
                    statusCode: xhr.status,
                    responseJSON: xhr.responseJSON
                });
                
                let message = 'Failed to save grades. Please try again.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = [];
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            if (Array.isArray(value)) {
                                errors.push(value[0]);
                            } else {
                                errors.push(value);
                            }
                        });
                        message = 'Validation errors: ' + errors.join(', ');
                    }
                } else if (xhr.status === 0) {
                    message = 'Network error. Please check your connection.';
                } else if (xhr.status === 500) {
                    message = 'Server error. Please contact administrator.';
                }
                
                toastr.error(message);
                $btn.html(originalText).prop('disabled', false);
            },
            complete: function() {
                console.log('AJAX request completed');
            }
        });
        
        return false;
    });
});
</script>
@endpush

@endsection
