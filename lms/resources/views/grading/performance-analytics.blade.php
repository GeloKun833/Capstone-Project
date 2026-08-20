@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Performance Analytics</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Performance Analytics</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="student_filter" name="student_id">
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
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
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" id="analyzePerformance">Analyze</button>
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
                                        <h3 class="page-title">Student Performance Analysis</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="#" class="btn btn-outline-primary me-2" id="exportAnalytics">
                                            <i class="fas fa-download"></i> Export Report
                                        </a>
                                        <button type="button" class="btn btn-primary" id="refreshAnalytics">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Performance Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="overallGpa">0.00</h4>
                                                    <p class="mb-0">Overall GPA</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-chart-line fa-2x"></i>
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
                                                    <h4 class="mb-0" id="totalSubjects">0</h4>
                                                    <p class="mb-0">Total Subjects</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-book fa-2x"></i>
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
                                                    <h4 class="mb-0" id="improvement">0%</h4>
                                                    <p class="mb-0">Improvement</p>
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
                                                    <h4 class="mb-0" id="attendanceRate">0%</h4>
                                                    <p class="mb-0">Attendance Rate</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-calendar-check fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Performance Trends Chart -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Performance Trends</h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="performanceChart" height="100"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject Performance Table -->
                            <div class="table-responsive">
                                <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                    <thead class="student-thread">
                                        <tr>
                                            <th>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="something" id="selectAllSubjects">
                                                </div>
                                            </th>
                                            <th>Subject</th>
                                            <th>Component</th>
                                            <th>Score</th>
                                            <th>Percentage</th>
                                            <th>Letter Grade</th>
                                            <th>Weight</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="subjectPerformanceBody">
                                        <!-- Sample data will be loaded here -->
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="1">
                                                </div>
                                            </td>
                                            <td>
                                                <h2>
                                                    <a>Mathematics</a>
                                                </h2>
                                            </td>
                                            <td>Midterm Exam</td>
                                            <td><strong>85</strong></td>
                                            <td>85.0%</td>
                                            <td><span class="badge bg-success">B</span></td>
                                            <td>30%</td>
                                            <td><span class="badge bg-success">Good</span></td>
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
                                            <td>
                                                <h2>
                                                    <a>Science</a>
                                                </h2>
                                            </td>
                                            <td>Final Exam</td>
                                            <td><strong>92</strong></td>
                                            <td>92.0%</td>
                                            <td><span class="badge bg-success">A</span></td>
                                            <td>40%</td>
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
                                            <td>
                                                <h2>
                                                    <a>English</a>
                                                </h2>
                                            </td>
                                            <td>Quiz</td>
                                            <td><strong>78</strong></td>
                                            <td>78.0%</td>
                                            <td><span class="badge bg-warning">C+</span></td>
                                            <td>20%</td>
                                            <td><span class="badge bg-warning">Fair</span></td>
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

/* Chart container */
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

.card-title {
    margin-bottom: 0;
    font-weight: 600;
    color: #2c323f;
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize performance chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [{
                label: 'Mathematics',
                data: [75, 78, 82, 85, 88, 90],
                borderColor: '#3d5ee1',
                backgroundColor: 'rgba(61, 94, 225, 0.1)',
                tension: 0.4
            }, {
                label: 'Science',
                data: [80, 85, 87, 89, 91, 92],
                borderColor: '#7bb13c',
                backgroundColor: 'rgba(123, 177, 60, 0.1)',
                tension: 0.4
            }, {
                label: 'English',
                data: [70, 72, 75, 78, 80, 82],
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Analyze performance
    $('#analyzePerformance').on('click', function() {
        const student = $('#student_filter').val();
        const subject = $('#subject_filter').val();
        const academicYear = $('#academic_year_filter').val();
        
        if (!student || !subject || !academicYear) {
            alert('Please select all criteria before analyzing performance.');
            return;
        }
        
        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Analyzing...').prop('disabled', true);
        
        // Simulate analysis (replace with actual AJAX call)
        setTimeout(function() {
            $('#analyzePerformance').html('Analyze').prop('disabled', false);
            
            // Update summary cards
            $('#overallGpa').text('3.45');
            $('#totalSubjects').text('6');
            $('#improvement').text('+12%');
            $('#attendanceRate').text('95%');
            
            // Show success message
            alert('Performance analysis completed successfully!');
        }, 1500);
    });
    
    // Select all functionality
    $('#selectAllSubjects').on('change', function() {
        $('.form-check-input').prop('checked', $(this).is(':checked'));
    });
    
    // Refresh analytics
    $('#refreshAnalytics').on('click', function() {
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...').prop('disabled', true);
        
        setTimeout(function() {
            $('#refreshAnalytics').html('<i class="fas fa-sync-alt"></i> Refresh').prop('disabled', false);
            alert('Analytics refreshed successfully!');
        }, 1000);
    });
    
    // Export analytics
    $('#exportAnalytics').on('click', function() {
        alert('Analytics export functionality will be implemented here.');
    });
});
</script>
@endpush

@endsection 