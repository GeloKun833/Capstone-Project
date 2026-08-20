@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Student Performance Analysis</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Performance Analysis</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <button type="button" class="btn btn-primary" onclick="exportAnalysis()">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="student_filter" name="student_id">
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="academic_year_filter" name="academic_year_id">
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="semester_filter" name="semester_id">
                                <option value="">Select Semester</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ $semesterId == $semester->id ? 'selected' : '' }}>
                                        {{ $semester->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" onclick="analyzePerformance()">
                                <i class="fas fa-chart-line"></i> Analyze
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if($analysis)
                <!-- Performance Summary Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">{{ $analysis['overall_average'] }}%</h4>
                                        <p class="mb-0">Overall Average</p>
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
                                        <h4 class="mb-0">{{ $analysis['total_subjects'] }}</h4>
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
                                        <h4 class="mb-0">{{ $analysis['improvement_needed'] }}%</h4>
                                        <p class="mb-0">Improvement Needed</p>
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
                                        <h4 class="mb-0">{{ count($analysis['weak_areas']) }}</h4>
                                        <p class="mb-0">Weak Areas</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Charts -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Subject Performance</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="subjectPerformanceChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Performance Trends</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="performanceTrendsChart" height="200"></canvas>
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
                                            <h3 class="page-title">Subject Performance Details</h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Subject</th>
                                                <th>Average Score</th>
                                                <th>Total Assignments</th>
                                                <th>Weak Topics</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analysis['subject_performance'] as $subject)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $subject['subject_name'] }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $subject['average_score'] }}%</strong>
                                                    </td>
                                                    <td>{{ $subject['total_assignments'] }}</td>
                                                    <td>
                                                        @if(count($subject['weak_topics']) > 0)
                                                            @foreach($subject['weak_topics'] as $topic)
                                                                <span class="badge bg-warning me-1">{{ $topic }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-success">No weak topics</span>
                                                        @endif
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
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="viewSubjectDetails({{ $subject['subject_id'] }})">
                                                            <i class="fas fa-eye"></i> Details
                                                        </button>
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

                <!-- Recommended Lessons -->
                @if($recommendations && $recommendations->count() > 0)
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-table">
                                <div class="card-body">
                                    <div class="page-header">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <h3 class="page-title">Recommended Lessons</h3>
                                                <p class="text-muted">Based on your performance analysis, here are lessons that could help improve your weak areas:</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        @foreach($recommendations as $lesson)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title">{{ $lesson->title }}</h6>
                                                            <span class="badge bg-primary">{{ $lesson->relevance_score }}</span>
                                                        </div>
                                                        <p class="card-text text-muted">{{ Str::limit($lesson->description, 100) }}</p>
                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-book"></i> {{ $lesson->subject->subject_name }}
                                                            </small>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i> {{ $lesson->lesson_date->format('M d, Y') }}
                                                            </small>
                                                        </div>
                                                        <p class="card-text">
                                                            <small class="text-muted">{{ $lesson->relevance_reason }}</small>
                                                        </p>
                                                        <a href="{{ route('lessons.show', $lesson) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-eye"></i> View Lesson
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Class Comparison -->
                @if($classComparison)
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Class Comparison</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4>{{ $classComparison['student_average'] }}%</h4>
                                                <p class="text-muted">Your Average</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4>{{ $classComparison['class_average'] }}%</h4>
                                                <p class="text-muted">Class Average</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="{{ $classComparison['difference'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $classComparison['difference'] >= 0 ? '+' : '' }}{{ $classComparison['difference'] }}%
                                                </h4>
                                                <p class="text-muted">Difference</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4>{{ $classComparison['percentile'] }}%</h4>
                                                <p class="text-muted">Percentile</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Analysis Available</h5>
                                <p class="text-muted">Select a student and click "Analyze" to view performance analysis.</p>
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let subjectChart, trendsChart;

function analyzePerformance() {
    const student = $('#student_filter').val();
    const academicYear = $('#academic_year_filter').val();
    const semester = $('#semester_filter').val();
    
    if (!student) {
        alert('Please select a student first.');
        return;
    }
    
    // Build query string
    const params = new URLSearchParams();
    if (student) params.append('student_id', student);
    if (academicYear) params.append('academic_year_id', academicYear);
    if (semester) params.append('semester_id', semester);
    
    // Redirect with parameters
    window.location.href = '{{ route("lessons.recommendations.student-analysis") }}?' + params.toString();
}

function exportAnalysis() {
    const student = $('#student_filter').val();
    const academicYear = $('#academic_year_filter').val();
    const semester = $('#semester_filter').val();
    
    if (!student) {
        alert('Please select a student first.');
        return;
    }
    
    // Build query string
    const params = new URLSearchParams();
    if (student) params.append('student_id', student);
    if (academicYear) params.append('academic_year_id', academicYear);
    if (semester) params.append('semester_id', semester);
    
    // Download report
    window.location.href = '{{ route("lessons.recommendations.export") }}?' + params.toString();
}

function viewSubjectDetails(subjectId) {
    // Add subject filter and reload
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.set('subject_id', subjectId);
    window.location.href = window.location.pathname + '?' + currentParams.toString();
}

@if($analysis)
$(document).ready(function() {
    // Subject Performance Chart
    const subjectCtx = document.getElementById('subjectPerformanceChart').getContext('2d');
    subjectChart = new Chart(subjectCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($analysis['subject_performance'])->pluck('subject_name')) !!},
            datasets: [{
                label: 'Average Score (%)',
                data: {!! json_encode(collect($analysis['subject_performance'])->pluck('average_score')) !!},
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

    // Performance Trends Chart
    const trendsCtx = document.getElementById('performanceTrendsChart').getContext('2d');
    trendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($analysis['trends'])->pluck('month')->map(function($month) {
                return date('F', mktime(0, 0, 0, $month, 1));
            })) !!},
            datasets: [{
                label: 'Average Score (%)',
                data: {!! json_encode(collect($analysis['trends'])->pluck('average_score')) !!},
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
});
@endif
</script>
@endpush 