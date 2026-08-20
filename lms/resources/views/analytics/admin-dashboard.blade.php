@extends('layouts.master')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">School Analytics Dashboard</h3>
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

            <!-- Charts Row 1 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">GPA Comparison by Grade Level</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="gpaComparisonChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Pass/Fail Rates by Subject</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="passFailRatesChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Subject Performance Overview</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="subjectPerformanceChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">School Attendance Summary</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="attendanceSummaryChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Comparison -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Section Performance Comparison</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="sectionComparisonChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Subject Performance Details</h3>
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
                                                <th>Students</th>
                                                <th>Assignments</th>
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
                                                    <td>{{ $subject['students_count'] }}</td>
                                                    <td>{{ $subject['assignments_count'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-muted">No subject performance data available.</p>
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
                                        <h3 class="page-title">Pass/Fail Analysis</h3>
                                    </div>
                                </div>
                            </div>

                            @if(count($analytics['pass_fail_rates']) > 0)
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Subject</th>
                                                <th>Pass Rate</th>
                                                <th>Fail Rate</th>
                                                <th>Total Students</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analytics['pass_fail_rates'] as $rate)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $rate['subject'] }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">{{ $rate['pass_rate'] }}%</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-danger">{{ $rate['fail_rate'] }}%</span>
                                                    </td>
                                                    <td>{{ $rate['total_students'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-muted">No pass/fail data available.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- GPA Comparison Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">GPA Analysis by Grade Level</h3>
                                    </div>
                                </div>
                            </div>

                            @if(count($analytics['gpa_comparison']) > 0)
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Grade Level</th>
                                                <th>Average GPA</th>
                                                <th>Students</th>
                                                <th>Highest GPA</th>
                                                <th>Lowest GPA</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analytics['gpa_comparison'] as $gpa)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $gpa['grade_level'] }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $gpa['average_gpa'] }}</strong>
                                                    </td>
                                                    <td>{{ $gpa['students_count'] }}</td>
                                                    <td>
                                                        <span class="badge bg-success">{{ $gpa['highest_gpa'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-danger">{{ $gpa['lowest_gpa'] }}</span>
                                                    </td>
                                                    <td>
                                                        @if($gpa['average_gpa'] >= 3.5)
                                                            <span class="badge bg-success">Excellent</span>
                                                        @elseif($gpa['average_gpa'] >= 3.0)
                                                            <span class="badge bg-primary">Good</span>
                                                        @elseif($gpa['average_gpa'] >= 2.5)
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
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No GPA Data</h5>
                                    <p class="text-muted">No GPA data has been calculated yet.</p>
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
let gpaComparisonChart, passFailRatesChart, subjectPerformanceChart, attendanceSummaryChart, sectionComparisonChart;

// Debug: Log analytics data
console.log('📊 Analytics Data:', {
    gpa_comparison: {!! json_encode($analytics['gpa_comparison']) !!},
    pass_fail_rates: {!! json_encode($analytics['pass_fail_rates']) !!},
    subject_performance: {!! json_encode($analytics['subject_performance']) !!},
    attendance_summary: {!! json_encode($analytics['attendance_summary']) !!},
    section_comparison: {!! json_encode($analytics['section_comparison']) !!}
});

$(document).ready(function() {
    initializeCharts();
});

function initializeCharts() {
    // GPA Comparison Chart
    const gpaLabels = {!! json_encode(collect($analytics['gpa_comparison'])->pluck('grade_level')) !!};
    const gpaData = {!! json_encode(collect($analytics['gpa_comparison'])->pluck('average_gpa')) !!};
    
    console.log('📈 GPA Chart Data:', { labels: gpaLabels, data: gpaData });
    
    const gpaCtx = document.getElementById('gpaComparisonChart');
    if (gpaCtx) {
        gpaComparisonChart = new Chart(gpaCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: gpaLabels.length > 0 ? gpaLabels : ['No Data'],
                datasets: [{
                    label: 'Average GPA',
                    data: gpaData.length > 0 ? gpaData : [0],
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
                        max: 4.0
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: gpaData.length === 0,
                        text: 'No GPA data available - Add student grades and calculate GPA first'
                    }
                }
            }
        });
        console.log('✅ GPA Chart Created');
    }

    // Pass/Fail Rates Chart
    const passFailLabels = {!! json_encode(collect($analytics['pass_fail_rates'])->pluck('subject')) !!};
    const passRateData = {!! json_encode(collect($analytics['pass_fail_rates'])->pluck('pass_rate')) !!};
    const failRateData = {!! json_encode(collect($analytics['pass_fail_rates'])->pluck('fail_rate')) !!};
    
    console.log('📊 Pass/Fail Chart Data:', { labels: passFailLabels, passRate: passRateData, failRate: failRateData });
    
    const passFailCtx = document.getElementById('passFailRatesChart');
    if (passFailCtx) {
        passFailRatesChart = new Chart(passFailCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: passFailLabels.length > 0 ? passFailLabels : ['No Data'],
                datasets: [{
                    label: 'Pass Rate (%)',
                    data: passRateData.length > 0 ? passRateData : [0],
                    backgroundColor: '#7bb13c',
                    borderWidth: 1
                }, {
                    label: 'Fail Rate (%)',
                    data: failRateData.length > 0 ? failRateData : [0],
                    backgroundColor: '#dc3545',
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
                        position: 'top'
                    },
                    title: {
                        display: passRateData.length === 0,
                        text: 'No grade data available - Add student grades first'
                    }
                }
            }
        });
        console.log('✅ Pass/Fail Chart Created');
    }

    // Subject Performance Chart
    const subjectLabels = {!! json_encode(collect($analytics['subject_performance'])->pluck('subject')) !!};
    const subjectData = {!! json_encode(collect($analytics['subject_performance'])->pluck('average_score')) !!};
    
    console.log('📚 Subject Performance Chart Data:', { labels: subjectLabels, data: subjectData });
    
    const subjectCtx = document.getElementById('subjectPerformanceChart');
    if (subjectCtx) {
        subjectPerformanceChart = new Chart(subjectCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: subjectLabels.length > 0 ? subjectLabels : ['No Data'],
                datasets: [{
                    label: 'Average Score (%)',
                    data: subjectData.length > 0 ? subjectData : [0],
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
                    },
                    title: {
                        display: subjectData.length === 0,
                        text: 'No subject performance data - Add grades for subjects first'
                    }
                }
            }
        });
        console.log('✅ Subject Performance Chart Created');
    }

    // Attendance Summary Chart
    const attendanceLabels = {!! json_encode(collect($analytics['attendance_summary'])->pluck('month')) !!};
    const attendanceData = {!! json_encode(collect($analytics['attendance_summary'])->pluck('attendance_rate')) !!};
    
    console.log('📅 Attendance Chart Data:', { labels: attendanceLabels, data: attendanceData });
    
    const attendanceCtx = document.getElementById('attendanceSummaryChart');
    if (attendanceCtx) {
        attendanceSummaryChart = new Chart(attendanceCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: attendanceLabels.length > 0 ? attendanceLabels : ['No Data'],
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: attendanceData.length > 0 ? attendanceData : [0],
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
                    },
                    title: {
                        display: attendanceData.length === 0,
                        text: 'No attendance data - Record student attendance first'
                    }
                }
            }
        });
        console.log('✅ Attendance Chart Created');
    }

    // Section Comparison Chart
    const sectionLabels = {!! json_encode(collect($analytics['section_comparison'])->pluck('section')) !!};
    const sectionData = {!! json_encode(collect($analytics['section_comparison'])->pluck('average_score')) !!};
    
    console.log('🏫 Section Comparison Chart Data:', { labels: sectionLabels, data: sectionData });
    
    const sectionCtx = document.getElementById('sectionComparisonChart');
    if (sectionCtx) {
        sectionComparisonChart = new Chart(sectionCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: sectionLabels.length > 0 ? sectionLabels : ['No Data'],
                datasets: [{
                    label: 'Average Score (%)',
                    data: sectionData.length > 0 ? sectionData : [0],
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
                    },
                    title: {
                        display: sectionData.length === 0,
                        text: 'No section data - Create sections and assign students first'
                    }
                }
            }
        });
        console.log('✅ Section Comparison Chart Created');
    }
    
    console.log('🎉 All charts initialized successfully!');
}

function applyFilters() {
    const academicYear = $('#academic_year_filter').val();
    const semester = $('#semester_filter').val();
    
    // Build query string
    const params = new URLSearchParams();
    if (academicYear) params.append('academic_year_id', academicYear);
    if (semester) params.append('semester_id', semester);
    
    // Redirect with parameters
    window.location.href = '{{ route("analytics.admin-dashboard") }}?' + params.toString();
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