@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">GPA Ranking</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">GPA Ranking</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="student-group-form">
                <div class="row">
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
                        <div class="form-group">
                            <select class="form-control" id="section_filter" name="section_id">
                                <option value="">Select Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" id="filterGpa">Filter Results</button>
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
                                        <h3 class="page-title">Student GPA Rankings</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('teacher.grading.export-gpa') }}" class="btn btn-outline-primary me-2">
                                            <i class="fas fa-download"></i> Export GPA
                                        </a>
                                        <button type="button" class="btn btn-primary" id="refreshRankings">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="totalStudents">0</h4>
                                                    <p class="mb-0">Total Students</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-users fa-2x"></i>
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
                                                    <h4 class="mb-0" id="avgGpa">0.00</h4>
                                                    <p class="mb-0">Average GPA</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-chart-line fa-2x"></i>
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
                                                    <h4 class="mb-0" id="topGpa">0.00</h4>
                                                    <p class="mb-0">Highest GPA</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-trophy fa-2x"></i>
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
                                                    <h4 class="mb-0" id="honorStudents">0</h4>
                                                    <p class="mb-0">Honor Students</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-medal fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                    <thead class="student-thread">
                                        <tr>
                                            <th>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="something" id="selectAllGpa">
                                                </div>
                                            </th>
                                            <th>Rank</th>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Section</th>
                                            <th>GPA</th>
                                            <th>Letter Grade</th>
                                            <th>Total Units</th>
                                            <th>Remarks</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="gpaTableBody">
                                        <!-- Sample data will be loaded here -->
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="1">
                                                </div>
                                            </td>
                                            <td><span class="badge bg-primary">1</span></td>
                                            <td>STU001</td>
                                            <td>
                                                <h2>
                                                    <a>John Doe</a>
                                                </h2>
                                            </td>
                                            <td>Grade 10-A</td>
                                            <td><strong>3.95</strong></td>
                                            <td><span class="badge bg-success">A</span></td>
                                            <td>24</td>
                                            <td><span class="badge bg-success">Excellent</span></td>
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
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="2">
                                                </div>
                                            </td>
                                            <td><span class="badge bg-primary">2</span></td>
                                            <td>STU002</td>
                                            <td>
                                                <h2>
                                                    <a>Jane Smith</a>
                                                </h2>
                                            </td>
                                            <td>Grade 10-A</td>
                                            <td><strong>3.87</strong></td>
                                            <td><span class="badge bg-success">A</span></td>
                                            <td>24</td>
                                            <td><span class="badge bg-success">Excellent</span></td>
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
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="3">
                                                </div>
                                            </td>
                                            <td><span class="badge bg-primary">3</span></td>
                                            <td>STU003</td>
                                            <td>
                                                <h2>
                                                    <a>Mike Johnson</a>
                                                </h2>
                                            </td>
                                            <td>Grade 10-A</td>
                                            <td><strong>3.75</strong></td>
                                            <td><span class="badge bg-success">A</span></td>
                                            <td>24</td>
                                            <td><span class="badge bg-success">Excellent</span></td>
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
                                    </tbody>
                                </table>
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
    // Filter GPA rankings
    $('#filterGpa').on('click', function() {
        const academicYear = $('#academic_year_filter').val();
        const semester = $('#semester_filter').val();
        const section = $('#section_filter').val();
        
        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Filtering...').prop('disabled', true);
        
        // Simulate filtering (replace with actual AJAX call)
        setTimeout(function() {
            $('#filterGpa').html('Filter Results').prop('disabled', false);
            
            // Update summary cards
            $('#totalStudents').text('25');
            $('#avgGpa').text('3.45');
            $('#topGpa').text('3.95');
            $('#honorStudents').text('8');
            
            // Show success message
            alert('GPA rankings filtered successfully!');
        }, 1000);
    });
    
    // Select all functionality
    $('#selectAllGpa').on('change', function() {
        $('.form-check-input').prop('checked', $(this).is(':checked'));
    });
    
    // Refresh rankings
    $('#refreshRankings').on('click', function() {
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...').prop('disabled', true);
        
        setTimeout(function() {
            $('#refreshRankings').html('<i class="fas fa-sync-alt"></i> Refresh').prop('disabled', false);
            alert('Rankings refreshed successfully!');
        }, 1000);
    });
    
    // Export GPA
    $('#exportGpa').on('click', function() {
        alert('GPA export functionality will be implemented here.');
    });
});
</script>
@endpush

@endsection 