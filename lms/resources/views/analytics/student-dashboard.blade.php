@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">My Academic Analytics</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Analytics</li>
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

            <!-- Performance Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $analytics['performance_indicators']['average_score'] }}%</h4>
                                    <p class="mb-0">Average Score</p>
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
                                    <h4 class="mb-0">{{ $analytics['performance_indicators']['total_assignments'] }}</h4>
                                    <p class="mb-0">Total Assignments</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-tasks fa-2x"></i>
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
                                    <h4 class="mb-0">{{ $analytics['performance_indicators']['excellent_grades_count'] }}</h4>
                                    <p class="mb-0">Excellent Grades</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-star fa-2x"></i>
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
                                    <h4 class="mb-0">{{ $analytics['performance_indicators']['performance_level'] }}</h4>
                                    <p class="mb-0">Performance Level</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-trophy fa-2x"></i>
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
                            <h5 class="card-title">Grade Trends</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="gradeTrendsChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Attendance Summary</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="attendanceChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject Performance -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Subject Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="subjectPerformanceChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject Performance Table -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Detailed Subject Performance</h3>
                                    </div>
                                </div>
                            </div>

                            @if(count($analytics['subject_performance']) > 0)
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Subject</th>
                                                <th>Average Score</th>
                                                <th>Assignments</th>
                                                <th>Highest Score</th>
                                                <th>Lowest Score</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analytics['subject_performance'] as $subject)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $subject['subject'] }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $subject['average_score'] }}%</strong>
                                                    </td>
                                                    <td>{{ $subject['assignments_count'] }}</td>
                                                    <td>
                                                        <span class="badge bg-success">{{ $subject['highest_score'] }}%</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-danger">{{ $subject['lowest_score'] }}%</span>
                                                    </td>
                                                    <td>
                                                        @if($subject['average_score'] >= 90)
                                                            <span class="badge bg-success">Excellent</span>
                                                        @elseif($subject['average_score'] >= 80)
                                                            <span class="badge bg-primary">Good</span>
                                                        @elseif($subject['average_score'] >= 70)
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
                                    <h5 class="text-muted">No Performance Data</h5>
                                    <p class="text-muted">No grades have been recorded yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            @if(count($analytics['recent_activities']) > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="card card-table">
                            <div class="card-body">
                                <div class="page-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="page-title">Recent Activities</h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Activity</th>
                                                <th>Subject</th>
                                                <th>Submitted</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analytics['recent_activities'] as $activity)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $activity['activity'] }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>{{ $activity['subject'] }}</td>
                                                    <td>{{ $activity['submitted_at'] }}</td>
                                                    <td>
                                                        @if($activity['status'] === 'submitted')
                                                            <span class="badge bg-warning">Submitted</span>
                                                        @elseif($activity['status'] === 'graded')
                                                            <span class="badge bg-success">Graded</span>
                                                        @else
                                                            <span class="badge bg-secondary">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($activity['score'] !== '-')
                                                            <strong>{{ $activity['score'] }}</strong>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Performance Alerts -->
            @if($analytics['performance_indicators']['improvement_needed'])
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                <div>
                                    <h5 class="alert-heading">Performance Alert</h5>
                                    <p class="mb-0">You have {{ $analytics['performance_indicators']['low_grades_count'] }} assignment(s) with scores below 75%. Consider reviewing these subjects and seeking additional help if needed.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
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
let gradeTrendsChart, attendanceChart, subjectPerformanceChart;

$(document).ready(function() {
    initializeCharts();
});

function initializeCharts() {
    // Grade Trends Chart
    const gradeCtx = document.getElementById('gradeTrendsChart').getContext('2d');
    gradeTrendsChart = new Chart(gradeCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($analytics['grade_trends'])->pluck('period')) !!},
            datasets: [{
                label: 'Grade Score (%)',
                data: {!! json_encode(collect($analytics['grade_trends'])->pluck('score')) !!},
                borderColor: '#3d5ee1',
                backgroundColor: 'rgba(61, 94, 225, 0.1)',
                tension: 0.4,
                fill: true
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

    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    attendanceChart = new Chart(attendanceCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($analytics['attendance_summary'])->pluck('month')) !!},
            datasets: [{
                label: 'Attendance Rate (%)',
                data: {!! json_encode(collect($analytics['attendance_summary'])->pluck('percentage')) !!},
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

    // Subject Performance Chart
    const subjectCtx = document.getElementById('subjectPerformanceChart').getContext('2d');
    subjectPerformanceChart = new Chart(subjectCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($analytics['subject_performance'])->pluck('subject')) !!},
            datasets: [{
                label: 'Average Score (%)',
                data: {!! json_encode(collect($analytics['subject_performance'])->pluck('average_score')) !!},
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
}

function applyFilters() {
    const academicYear = $('#academic_year_filter').val();
    const semester = $('#semester_filter').val();
    
    // Build query string
    const params = new URLSearchParams();
    if (academicYear) params.append('academic_year_id', academicYear);
    if (semester) params.append('semester_id', semester);
    
    // Redirect with parameters
    window.location.href = '{{ route("analytics.student-dashboard") }}?' + params.toString();
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