
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Student Performance Analysis</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
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
                                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($student->id); ?>" <?php echo e($studentId == $student->id ? 'selected' : ''); ?>>
                                        <?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="academic_year_filter" name="academic_year_id">
                                <option value="">Select Academic Year</option>
                                <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>" <?php echo e($academicYearId == $year->id ? 'selected' : ''); ?>>
                                        <?php echo e($year->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="semester_filter" name="semester_id">
                                <option value="">Select Semester</option>
                                <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($semester->id); ?>" <?php echo e($semesterId == $semester->id ? 'selected' : ''); ?>>
                                        <?php echo e($semester->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

            <?php if($analysis): ?>
                <!-- Performance Summary Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo e($analysis['overall_average']); ?>%</h4>
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
                                        <h4 class="mb-0"><?php echo e($analysis['total_subjects']); ?></h4>
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
                                        <h4 class="mb-0"><?php echo e($analysis['improvement_needed']); ?>%</h4>
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
                                        <h4 class="mb-0"><?php echo e(count($analysis['weak_areas'])); ?></h4>
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
                                            <?php $__currentLoopData = $analysis['subject_performance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a><?php echo e($subject['subject_name']); ?></a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo e($subject['average_score']); ?>%</strong>
                                                    </td>
                                                    <td><?php echo e($subject['total_assignments']); ?></td>
                                                    <td>
                                                        <?php if(count($subject['weak_topics']) > 0): ?>
                                                            <?php $__currentLoopData = $subject['weak_topics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <span class="badge bg-warning me-1"><?php echo e($topic); ?></span>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php else: ?>
                                                            <span class="text-success">No weak topics</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($subject['average_score'] >= 90): ?>
                                                            <span class="badge bg-success">Excellent</span>
                                                        <?php elseif($subject['average_score'] >= 80): ?>
                                                            <span class="badge bg-primary">Good</span>
                                                        <?php elseif($subject['average_score'] >= 70): ?>
                                                            <span class="badge bg-warning">Average</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Needs Improvement</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="viewSubjectDetails(<?php echo e($subject['subject_id']); ?>)">
                                                            <i class="fas fa-eye"></i> Details
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommended Lessons -->
                <?php if($recommendations && $recommendations->count() > 0): ?>
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
                                        <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title"><?php echo e($lesson->title); ?></h6>
                                                            <span class="badge bg-primary"><?php echo e($lesson->relevance_score); ?></span>
                                                        </div>
                                                        <p class="card-text text-muted"><?php echo e(Str::limit($lesson->description, 100)); ?></p>
                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-book"></i> <?php echo e($lesson->subject->subject_name); ?>

                                                            </small>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i> <?php echo e($lesson->lesson_date->format('M d, Y')); ?>

                                                            </small>
                                                        </div>
                                                        <p class="card-text">
                                                            <small class="text-muted"><?php echo e($lesson->relevance_reason); ?></small>
                                                        </p>
                                                        <a href="<?php echo e(route('lessons.show', $lesson)); ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-eye"></i> View Lesson
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Class Comparison -->
                <?php if($classComparison): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Class Comparison</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4><?php echo e($classComparison['student_average']); ?>%</h4>
                                                <p class="text-muted">Your Average</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4><?php echo e($classComparison['class_average']); ?>%</h4>
                                                <p class="text-muted">Class Average</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="<?php echo e($classComparison['difference'] >= 0 ? 'text-success' : 'text-danger'); ?>">
                                                    <?php echo e($classComparison['difference'] >= 0 ? '+' : ''); ?><?php echo e($classComparison['difference']); ?>%
                                                </h4>
                                                <p class="text-muted">Difference</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4><?php echo e($classComparison['percentile']); ?>%</h4>
                                                <p class="text-muted">Percentile</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
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
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
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
    window.location.href = '<?php echo e(route("lessons.recommendations.student-analysis")); ?>?' + params.toString();
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
    window.location.href = '<?php echo e(route("lessons.recommendations.export")); ?>?' + params.toString();
}

function viewSubjectDetails(subjectId) {
    // Add subject filter and reload
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.set('subject_id', subjectId);
    window.location.href = window.location.pathname + '?' + currentParams.toString();
}

<?php if($analysis): ?>
$(document).ready(function() {
    // Subject Performance Chart
    const subjectCtx = document.getElementById('subjectPerformanceChart').getContext('2d');
    subjectChart = new Chart(subjectCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(collect($analysis['subject_performance'])->pluck('subject_name')); ?>,
            datasets: [{
                label: 'Average Score (%)',
                data: <?php echo json_encode(collect($analysis['subject_performance'])->pluck('average_score')); ?>,
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
            labels: <?php echo json_encode(collect($analysis['trends'])->pluck('month')->map(function($month) {
                return date('F', mktime(0, 0, 0, $month, 1));
            })); ?>,
            datasets: [{
                label: 'Average Score (%)',
                data: <?php echo json_encode(collect($analysis['trends'])->pluck('average_score')); ?>,
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
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\lessons\recommendations\student-analysis.blade.php ENDPATH**/ ?>