@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Weight Settings</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Weight Settings</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="subject_filter" name="subject_id">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="academic_year_filter" name="academic_year_id">
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="semester_filter" name="semester_id">
                                <option value="">Select Semester</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" id="loadWeights">Load Weights</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Grading Weight Configuration</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <button type="button" class="btn btn-outline-primary me-2" id="resetWeights">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                        <button type="button" class="btn btn-primary" id="saveWeights">
                                            <i class="fas fa-save"></i> Save Weights
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Weight Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="totalWeight">0%</h4>
                                                    <p class="mb-0">Total Weight</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-percentage fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="activeComponents">0</h4>
                                                    <p class="mb-0">Active Components</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-check-circle fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="highestWeight">0%</h4>
                                                    <p class="mb-0">Highest Weight</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-arrow-up fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="lowestWeight">0%</h4>
                                                    <p class="mb-0">Lowest Weight</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-arrow-down fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Weight Configuration Table -->
                            <div class="table-responsive">
                                <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                    <thead class="student-thread">
                                        <tr>
                                            <th>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="something" id="selectAllWeights">
                                                </div>
                                            </th>
                                            <th>Component Name</th>
                                            <th>Description</th>
                                            <th>Weight (%)</th>
                                            <th>Status</th>
                                            <th>Last Updated</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="weightSettingsBody">
                                        <!-- Sample data will be loaded here -->
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="1">
                                                </div>
                                            </td>
                                            <td>
                                                <h2>
                                                    <a>Quiz</a>
                                                </h2>
                                            </td>
                                            <td>Short assessments and quizzes</td>
                                            <td>
                                                <input type="number" class="form-control weight-input" 
                                                       value="20" min="0" max="100" style="width: 80px;">
                                            </td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>2024-01-15</td>
                                            <td class="text-end">
                                                <div class="actions">
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="2">
                                                </div>
                                            </td>
                                            <td>
                                                <h2>
                                                    <a>Midterm Exam</a>
                                                </h2>
                                            </td>
                                            <td>Mid-semester examination</td>
                                            <td>
                                                <input type="number" class="form-control weight-input" 
                                                       value="30" min="0" max="100" style="width: 80px;">
                                            </td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>2024-01-15</td>
                                            <td class="text-end">
                                                <div class="actions">
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="3">
                                                </div>
                                            </td>
                                            <td>
                                                <h2>
                                                    <a>Final Exam</a>
                                                </h2>
                                            </td>
                                            <td>End-of-semester examination</td>
                                            <td>
                                                <input type="number" class="form-control weight-input" 
                                                       value="40" min="0" max="100" style="width: 80px;">
                                            </td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>2024-01-15</td>
                                            <td class="text-end">
                                                <div class="actions">
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="4">
                                                </div>
                                            </td>
                                            <td>
                                                <h2>
                                                    <a>Participation</a>
                                                </h2>
                                            </td>
                                            <td>Class participation and engagement</td>
                                            <td>
                                                <input type="number" class="form-control weight-input" 
                                                       value="10" min="0" max="100" style="width: 80px;">
                                            </td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>2024-01-15</td>
                                            <td class="text-end">
                                                <div class="actions">
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Weight Validation -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info" id="weightValidation">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Weight Validation:</strong> Total weight should equal 100%. Current total: <span id="currentTotal">100%</span>
                                    </div>
                                </div>
                            </div>
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

.search-student-btn .btn {
    height: 45px;
    padding: 10px 20px;
    font-weight: 600;
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

/* Summary cards */
.card.bg-primary {
    background-color: #3d5ee1 !important;
}

.card.bg-success {
    background-color: #7bb13c !important;
}

.card.bg-warning {
    background-color: #ffc107 !important;
}

.card.bg-info {
    background-color: #17a2b8 !important;
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

.btn-outline-primary {
    color: #3d5ee1;
    border-color: #3d5ee1;
}

.btn-outline-primary:hover {
    background-color: #18aefa;
    border-color: #18aefa;
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

/* Checkbox styling */
.form-check-input {
    width: 18px;
    height: 18px;
    margin-top: 0;
}

.form-check-input:checked {
    background-color: #3d5ee1;
    border-color: #3d5ee1;
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
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
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
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Load weights
    $('#loadWeights').on('click', function() {
        const subject = $('#subject_filter').val();
        const academicYear = $('#academic_year_filter').val();
        const semester = $('#semester_filter').val();
        
        if (!subject || !academicYear || !semester) {
            alert('Please select all criteria before loading weights.');
            return;
        }
        
        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...').prop('disabled', true);
        
        // Simulate loading (replace with actual AJAX call)
        setTimeout(function() {
            $('#loadWeights').html('Load Weights').prop('disabled', false);
            
            // Update summary cards
            $('#totalWeight').text('100%');
            $('#activeComponents').text('4');
            $('#highestWeight').text('40%');
            $('#lowestWeight').text('10%');
            
            // Show success message
            alert('Weight settings loaded successfully!');
        }, 1000);
    });
    
    // Select all functionality
    $('#selectAllWeights').on('change', function() {
        $('.form-check-input').prop('checked', $(this).is(':checked'));
    });
    
    // Weight input validation
    $(document).on('input', '.weight-input', function() {
        calculateTotalWeight();
    });
    
    // Calculate total weight
    function calculateTotalWeight() {
        let total = 0;
        $('.weight-input').each(function() {
            total += parseInt($(this).val()) || 0;
        });
        
        $('#currentTotal').text(total + '%');
        
        // Update validation message
        if (total === 100) {
            $('#weightValidation').removeClass('alert-warning alert-danger').addClass('alert-success')
                .html('<i class="fas fa-check-circle me-2"></i><strong>Weight Validation:</strong> Total weight is valid (100%). Current total: <span id="currentTotal">' + total + '%</span>');
        } else if (total > 100) {
            $('#weightValidation').removeClass('alert-success alert-danger').addClass('alert-warning')
                .html('<i class="fas fa-exclamation-triangle me-2"></i><strong>Weight Validation:</strong> Total weight exceeds 100%. Current total: <span id="currentTotal">' + total + '%</span>');
        } else {
            $('#weightValidation').removeClass('alert-success alert-warning').addClass('alert-info')
                .html('<i class="fas fa-info-circle me-2"></i><strong>Weight Validation:</strong> Total weight should equal 100%. Current total: <span id="currentTotal">' + total + '%</span>');
        }
    }
    
    // Save weights
    $('#saveWeights').on('click', function() {
        const total = parseInt($('#currentTotal').text());
        
        if (total !== 100) {
            alert('Total weight must equal 100% before saving.');
            return;
        }
        
        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);
        
        // Simulate saving (replace with actual AJAX call)
        setTimeout(function() {
            $('#saveWeights').html('<i class="fas fa-save"></i> Save Weights').prop('disabled', false);
            alert('Weight settings saved successfully!');
        }, 1000);
    });
    
    // Reset weights
    $('#resetWeights').on('click', function() {
        if (confirm('Are you sure you want to reset all weights to default values?')) {
            $('.weight-input').each(function() {
                const defaultValue = $(this).data('default') || 25;
                $(this).val(defaultValue);
            });
            calculateTotalWeight();
            alert('Weights reset to default values.');
        }
    });
    
    // Initialize total weight calculation
    calculateTotalWeight();
});
</script>
@endpush

@endsection 