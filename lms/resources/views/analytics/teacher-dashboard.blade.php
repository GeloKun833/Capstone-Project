@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Performance Analytics Dashboard</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Performance Analytics</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <button type="button" class="btn btn-primary" onclick="exportReport()">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="academic_year_filter" name="academic_year_id">
                                <option value="">All Academic Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="semester_filter" name="semester_id">
                                <option value="">All Semesters</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ $semesterId == $semester->id ? 'selected' : '' }}>
                                        {{ $semester->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
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
                                    <h4 class="mb-0">{{ count($analytics['class_averages']) }}</h4>
                                    <p class="mb-0">Subjects Taught</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-book fa-2x"></i>
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
                                    <h4 class="mb-0">{{ count($analytics['top_students']['top_students']) }}</h4>
                                    <p class="mb-0">Top Performers</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-trophy fa-2x"></i>
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
                                    <h4 class="mb-0">{{ count($analytics['attendance_overview']) }}</h4>
                                    <p class="mb-0">Classes Monitored</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
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
                                    <h4 class="mb-0">{{ count($analytics['recent_grades']) }}</h4>
                                    <p class="mb-0">Recent Grades</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-bar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Class Averages by Subject</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="classAveragesChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Assessment Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="assessmentBreakdownChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Overview -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Class Attendance Overview</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="attendanceOverviewChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top and Lowest Performing Students -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Top Performing Students</h3>
                                    </div>
                                </div>
                            </div>

                            @if(count($analytics['top_students']['top_students']) > 0)
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Student</th>
                                                <th>Average Score</th>
                                                <th>Assignments</th>
                                                <th>Subjects</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analytics['top_students']['top_students'] as $student)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $student['student_name'] }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $student['average_score'] }}%</strong>
                                                    </td>
                                                    <td>{{ $student['assignments_count'] }}</td>
                                                    <td>{{ $student['subjects_count'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-muted">No top performing students data available.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Students Needing Support</h3>
                                    </div>
                                </div>
                            </div>

                            @if(count($analytics['top_students']['lowest_students']) > 0)
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Student</th>
                                                <th>Average Score</th>
                                                <th>Assignments</th>
                                                <th>Subjects</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analytics['top_students']['lowest_students'] as $student)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $student['student_name'] }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <strong class="text-danger">{{ $student['average_score'] }}%</strong>
                                                    </td>
                                                    <td>{{ $student['assignments_count'] }}</td>
                                                    <td>{{ $student['subjects_count'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-muted">No students needing support data available.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Grades -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Recent Grades</h3>
                                    </div>
                                </div>
                            </div>

                            @if(count($analytics['recent_grades']) > 0)
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Student</th>
                                                <th>Subject</th>
                                                <th>Score</th>
                                                <th>Date</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analytics['recent_grades'] as $grade)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $grade['student_name'] }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>{{ $grade['subject'] }}</td>
                                                    <td>
                                                        <strong>{{ $grade['score'] }}%</strong>
                                                    </td>
                                                    <td>{{ $grade['date'] }}</td>
                                                    <td>
                                                        @if($grade['score'] >= 90)
                                                            <span class="badge bg-success">Excellent</span>
                                                        @elseif($grade['score'] >= 80)
                                                            <span class="badge bg-primary">Good</span>
                                                        @elseif($grade['score'] >= 70)
                                                            <span class="badge bg-warning">Average</span>
                                                        @else
                                                            <span class="badge bg-danger">Needs Improvement</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Recent Grades</h5>
                                    <p class="text-muted">No grades have been recorded recently.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
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
    
    .badge.bg-success {
        background-color: #7bb13c !important;
    }
    
    .badge.bg-primary {
        background-color: #3d5ee1 !important;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
    }
    
    .table-avatar h2 a {
        color: #333;
        font-weight: 500;
        text-decoration: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let classAveragesChart, assessmentBreakdownChart, attendanceOverviewChart;

$(document).ready(function() {
    initializeCharts();
});

function initializeCharts() {
    // Class Averages Chart
    const classCtx = document.getElementById('classAveragesChart').getContext('2d');
    classAveragesChart = new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($analytics['class_averages'])->pluck('subject')) !!},
            datasets: [{
                label: 'Average Score (%)',
                data: {!! json_encode(collect($analytics['class_averages'])->pluck('average_score')) !!},
                backgroundColor: [
                    '#3d5ee1',
                    '#7bb13c',
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8',
                    '#6c757d'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Assessment Breakdown Chart
    const assessmentCtx = document.getElementById('assessmentBreakdownChart').getContext('2d');
    assessmentBreakdownChart = new Chart(assessmentCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(collect($analytics['assessment_breakdown'])->pluck('assessment_type')) !!},
            datasets: [{
                data: {!! json_encode(collect($analytics['assessment_breakdown'])->pluck('count')) !!},
                backgroundColor: [
                    '#3d5ee1',
                    '#7bb13c',
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8',
                    '#6c757d'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Attendance Overview Chart
    const attendanceCtx = document.getElementById('attendanceOverviewChart').getContext('2d');
    attendanceOverviewChart = new Chart(attendanceCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($analytics['attendance_overview'])->pluck('section')) !!},
            datasets: [{
                label: 'Attendance Rate (%)',
                data: {!! json_encode(collect($analytics['attendance_overview'])->pluck('attendance_rate')) !!},
                backgroundColor: [
                    '#7bb13c',
                    '#3d5ee1',
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8',
                    '#6c757d'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function applyFilters() {
    const academicYear = $('#academic_year_filter').val();
    const semester = $('#semester_filter').val();
    
    // Build query string
    const params = new URLSearchParams();
    if (academicYear) params.append('academic_year_id', academicYear);
    if (semester) params.append('semester_id', semester);
    
    // Redirect with parameters
    window.location.href = '{{ route("analytics.teacher-dashboard") }}?' + params.toString();
}

function exportReport() {
    const academicYear = $('#academic_year_filter').val();
    const semester = $('#semester_filter').val();
    
    // Build query string
    const params = new URLSearchParams();
    if (academicYear) params.append('academic_year_id', academicYear);
    if (semester) params.append('semester_id', semester);
    
    // Download report
    window.location.href = '{{ route("analytics.export-report") }}?' + params.toString();
}
</script>
@endpush 