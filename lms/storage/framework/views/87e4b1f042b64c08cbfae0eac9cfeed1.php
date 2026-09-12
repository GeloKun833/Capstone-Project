<?php $__env->startSection('content'); ?>
<?php
    $hubRoute = $routePrefix === 'admin' ? 'admin.grading.performance-hub' : 'teacher.grading.performance-hub';
    $resolveRoute = $routePrefix === 'admin' ? 'admin.grading.resolve-alert' : 'teacher.grading.resolve-alert';
    $exportRoute = $routePrefix === 'admin' ? 'admin.grading.export-gpa' : 'teacher.grading.export-gpa';
    $filterQs = [
        'academic_year_id' => $selectedAcademicYearId,
        'semester_id' => $selectedSemesterId,
        'section_id' => $selectedSectionId,
    ];
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Performance Hub</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(url('/')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">GPA · Analytics · Alerts</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        
        <div class="card ph-card mb-3">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route($hubRoute)); ?>" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                    <div class="col-md-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                            <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($year->id); ?>" <?php if((int)$selectedAcademicYearId === (int)$year->id): echo 'selected'; endif; ?>><?php echo e($year->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Semester / Period</label>
                        <select name="semester_id" class="form-select" onchange="this.form.submit()">
                            <?php $__empty_1 = true; $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <option value="<?php echo e($sem->id); ?>" <?php if((int)$selectedSemesterId === (int)$sem->id): echo 'selected'; endif; ?>><?php echo e($sem->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <option value="">No semesters</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Sections</option>
                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>" <?php if((int)$selectedSectionId === (int)$section->id): echo 'selected'; endif; ?>>
                                    <?php echo e($section->grade_level ? $section->grade_level.' — ' : ''); ?><?php echo e($section->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn ph-btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        
        <?php if($sectionSummary): ?>
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <div class="ph-stat">
                        <div class="ph-stat-label">Students Ranked</div>
                        <div class="ph-stat-value"><?php echo e($sectionSummary['students']); ?></div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="ph-stat">
                        <div class="ph-stat-label">Class Average</div>
                        <div class="ph-stat-value"><?php echo e($sectionSummary['class_average'] !== null ? number_format($sectionSummary['class_average'], 2) : '—'); ?></div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="ph-stat">
                        <div class="ph-stat-label">Class GPA</div>
                        <div class="ph-stat-value"><?php echo e($sectionSummary['class_gpa'] !== null ? number_format($sectionSummary['class_gpa'], 2) : '—'); ?></div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="ph-stat">
                        <div class="ph-stat-label">Open Alerts</div>
                        <div class="ph-stat-value"><?php echo e($sectionSummary['open_alerts']); ?></div>
                        <div class="small text-muted mt-1">Passed <?php echo e($sectionSummary['passed']); ?> · Failed <?php echo e($sectionSummary['failed']); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <ul class="nav nav-pills ph-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?php echo e($tab === 'ranking' ? 'active' : ''); ?>"
                   href="<?php echo e(route($hubRoute, array_merge($filterQs, ['tab' => 'ranking']))); ?>">
                    <i class="fas fa-trophy me-1"></i> GPA Ranking
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e($tab === 'analytics' ? 'active' : ''); ?>"
                   href="<?php echo e(route($hubRoute, array_merge($filterQs, ['tab' => 'analytics', 'student_id' => $selectedStudentId]))); ?>">
                    <i class="fas fa-chart-line me-1"></i> Performance Analytics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e($tab === 'alerts' ? 'active' : ''); ?>"
                   href="<?php echo e(route($hubRoute, array_merge($filterQs, ['tab' => 'alerts']))); ?>">
                    <i class="fas fa-exclamation-triangle me-1"></i> Grade Alerts
                </a>
            </li>
        </ul>

        
        <?php if($tab === 'ranking'): ?>
            <div class="card ph-card">
                <div class="card-header ph-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 text-white">GPA Ranking</h5>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-light"
                           href="<?php echo e(route($exportRoute, array_merge($filterQs, ['format' => 'excel']))); ?>">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a class="btn btn-sm btn-light"
                           href="<?php echo e(route($exportRoute, array_merge($filterQs, ['format' => 'pdf']))); ?>">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Rankings use subject averages from quarterly grades (General Average → 4.0 GPA scale).
                        Tied GPAs share the same rank.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="ph-thead">
                                <tr>
                                    <th class="text-center" style="width:70px">Rank</th>
                                    <th>Student</th>
                                    <th class="text-center">Subjects</th>
                                    <th class="text-center">General Average</th>
                                    <th class="text-center">GPA</th>
                                    <th class="text-center">Letter</th>
                                    <th class="text-center">Remarks</th>
                                    <th class="text-center">Analytics</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $rankingRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="text-center fw-bold">
                                            <?php if($row->rank <= 3): ?>
                                                <span class="ph-medal ph-medal-<?php echo e($row->rank); ?>"><?php echo e($row->rank); ?></span>
                                            <?php else: ?>
                                                <?php echo e($row->rank); ?>

                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($row->student->last_name ?? ''); ?>, <?php echo e($row->student->first_name ?? ''); ?></strong>
                                        </td>
                                        <td class="text-center"><?php echo e($row->subjects_count); ?></td>
                                        <td class="text-center fw-bold">
                                            <?php echo e($row->general_average !== null ? number_format($row->general_average, 2) : '—'); ?>

                                        </td>
                                        <td class="text-center fw-bold"><?php echo e(number_format($row->gpa, 2)); ?></td>
                                        <td class="text-center"><?php echo e($row->letter_grade); ?></td>
                                        <td class="text-center">
                                            <?php if($row->pass_fail === 'Passed'): ?>
                                                <span class="badge bg-success">Passed</span>
                                            <?php elseif($row->pass_fail === 'Failed'): ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-sm btn-outline-success"
                                               href="<?php echo e(route($hubRoute, array_merge($filterQs, ['tab' => 'analytics', 'student_id' => $row->student->id]))); ?>">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No GPA records yet. Save grades in Grade Entry first.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($tab === 'analytics'): ?>
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <div class="card ph-card h-100">
                        <div class="card-header ph-header"><h5 class="mb-0 text-white">Select Student</h5></div>
                        <div class="card-body">
                            <form method="GET" action="<?php echo e(route($hubRoute)); ?>">
                                <input type="hidden" name="tab" value="analytics">
                                <input type="hidden" name="academic_year_id" value="<?php echo e($selectedAcademicYearId); ?>">
                                <input type="hidden" name="semester_id" value="<?php echo e($selectedSemesterId); ?>">
                                <input type="hidden" name="section_id" value="<?php echo e($selectedSectionId); ?>">
                                <label class="form-label">Student</label>
                                <select name="student_id" class="form-select mb-3" onchange="this.form.submit()">
                                    <option value="">— Choose student —</option>
                                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($student->id); ?>" <?php if((int)$selectedStudentId === (int)$student->id): echo 'selected'; endif; ?>>
                                            <?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </form>

                            <?php if($sectionSummary): ?>
                                <h6 class="mt-2">Top Performers</h6>
                                <ul class="list-group list-group-flush mb-3">
                                    <?php $__empty_1 = true; $__currentLoopData = $sectionSummary['top_students']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $top): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <li class="list-group-item d-flex justify-content-between px-0">
                                            <span>#<?php echo e($top->rank); ?> <?php echo e($top->student->last_name); ?>, <?php echo e($top->student->first_name); ?></span>
                                            <strong><?php echo e($top->general_average !== null ? number_format($top->general_average, 2) : number_format($top->gpa, 2)); ?></strong>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <li class="list-group-item px-0 text-muted">No data</li>
                                    <?php endif; ?>
                                </ul>
                                <h6>Needs Attention</h6>
                                <ul class="list-group list-group-flush">
                                    <?php $__empty_1 = true; $__currentLoopData = $sectionSummary['needs_attention']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $low): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <li class="list-group-item d-flex justify-content-between px-0">
                                            <span><?php echo e($low->student->last_name); ?>, <?php echo e($low->student->first_name); ?></span>
                                            <span class="text-danger fw-bold"><?php echo e(number_format($low->general_average, 2)); ?></span>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <li class="list-group-item px-0 text-muted">None below passing</li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 mb-3">
                    <?php if($analytics): ?>
                        <div class="card ph-card mb-3">
                            <div class="card-header ph-header">
                                <h5 class="mb-0 text-white">
                                    <?php echo e($analytics['student']->last_name); ?>, <?php echo e($analytics['student']->first_name); ?>

                                    — Learning Progress
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="ph-stat">
                                            <div class="ph-stat-label">General Average</div>
                                            <div class="ph-stat-value"><?php echo e($analytics['general_average'] !== null ? number_format($analytics['general_average'], 2) : '—'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="ph-stat">
                                            <div class="ph-stat-label">GPA</div>
                                            <div class="ph-stat-value"><?php echo e($analytics['gpa'] ? number_format($analytics['gpa']->gpa, 2) : '—'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="ph-stat">
                                            <div class="ph-stat-label">Open Alerts</div>
                                            <div class="ph-stat-value"><?php echo e($analytics['alerts']->count()); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <canvas id="performanceTrendChart" height="120"></canvas>

                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="ph-thead">
                                            <tr>
                                                <th>Learning Area</th>
                                                <th class="text-center">Q1</th>
                                                <th class="text-center">Q2</th>
                                                <th class="text-center">Q3</th>
                                                <th class="text-center">Q4</th>
                                                <th class="text-center">Final</th>
                                                <th class="text-center">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $analytics['subjects']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e($subj->subject_name); ?></td>
                                                    <td class="text-center"><?php echo e($subj->q1 !== null ? number_format($subj->q1, 0) : '—'); ?></td>
                                                    <td class="text-center"><?php echo e($subj->q2 !== null ? number_format($subj->q2, 0) : '—'); ?></td>
                                                    <td class="text-center"><?php echo e($subj->q3 !== null ? number_format($subj->q3, 0) : '—'); ?></td>
                                                    <td class="text-center"><?php echo e($subj->q4 !== null ? number_format($subj->q4, 0) : '—'); ?></td>
                                                    <td class="text-center fw-bold"><?php echo e($subj->final !== null ? number_format($subj->final, 0) : '—'); ?></td>
                                                    <td class="text-center"><?php echo e($subj->remarks ?: '—'); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td class="fw-bold">Quarter / General Average</td>
                                                <?php $__currentLoopData = ['q1','q2','q3','q4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <td class="text-center fw-bold">
                                                        <?php echo e($analytics['quarter_averages'][$qk] !== null ? number_format($analytics['quarter_averages'][$qk], 2) : '—'); ?>

                                                    </td>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <td class="text-center fw-bold">
                                                    <?php echo e($analytics['general_average'] !== null ? number_format($analytics['general_average'], 2) : '—'); ?>

                                                </td>
                                                <td class="text-center fw-bold">
                                                    <?php echo e(\App\Services\StudentPerformanceService::remarkForAverage($analytics['general_average'] ?? null)); ?>

                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if($analytics['alerts']->isNotEmpty()): ?>
                                    <div class="mt-3">
                                        <h6>Active Alerts</h6>
                                        <?php $__currentLoopData = $analytics['alerts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="alert alert-warning py-2 mb-2">
                                                <i class="fas fa-<?php echo e($alert->alert_icon); ?> me-1"></i>
                                                <?php echo e($alert->message); ?>

                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card ph-card">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="fas fa-user-graduate fa-3x mb-3"></i>
                                <h5>Select a student to view performance analytics</h5>
                                <p class="mb-0">Quarter trends, subject grades, GPA, and alerts will appear here.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($tab === 'alerts'): ?>
            <div class="card ph-card">
                <div class="card-header ph-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 text-white">Grade Alerts</h5>
                    <form method="GET" action="<?php echo e(route($hubRoute)); ?>" class="d-flex gap-2 flex-wrap">
                        <input type="hidden" name="tab" value="alerts">
                        <input type="hidden" name="academic_year_id" value="<?php echo e($selectedAcademicYearId); ?>">
                        <input type="hidden" name="semester_id" value="<?php echo e($selectedSemesterId); ?>">
                        <input type="hidden" name="section_id" value="<?php echo e($selectedSectionId); ?>">
                        <select name="alert_status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="open" <?php if($alertFilter === 'open'): echo 'selected'; endif; ?>>Open</option>
                            <option value="resolved" <?php if($alertFilter === 'resolved'): echo 'selected'; endif; ?>>Resolved</option>
                            <option value="all" <?php if($alertFilter === 'all'): echo 'selected'; endif; ?>>All</option>
                        </select>
                        <select name="alert_type" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="low_grade" <?php if($alertType === 'low_grade'): echo 'selected'; endif; ?>>Low Grade</option>
                            <option value="performance_drop" <?php if($alertType === 'performance_drop'): echo 'selected'; endif; ?>>Performance Drop</option>
                            <option value="at_risk" <?php if($alertType === 'at_risk'): echo 'selected'; endif; ?>>At Risk</option>
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="ph-thead">
                                <tr>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th class="text-center">Value</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $alerts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($alert->student->last_name ?? ''); ?>, <?php echo e($alert->student->first_name ?? ''); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo e($alert->alert_type === 'at_risk' ? 'bg-danger' : ($alert->alert_type === 'performance_drop' ? 'bg-warning text-dark' : 'bg-secondary')); ?>">
                                                <?php echo e(str_replace('_', ' ', ucfirst($alert->alert_type))); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($alert->subject->subject_name ?? '—'); ?></td>
                                        <td><?php echo e($alert->message); ?></td>
                                        <td class="text-center"><?php echo e($alert->current_value !== null ? number_format((float)$alert->current_value, 2) : '—'); ?></td>
                                        <td>
                                            <?php if($alert->is_resolved): ?>
                                                <span class="badge bg-success">Resolved</span>
                                                <div class="small text-muted"><?php echo e(optional($alert->resolved_at)->format('M d, Y')); ?></div>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Open</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if(! $alert->is_resolved): ?>
                                                <form method="POST" action="<?php echo e(route($resolveRoute, $alert)); ?>" class="d-inline"
                                                      onsubmit="return confirm('Mark this alert as resolved?');">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-sm btn-success">Resolve</button>
                                                </form>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                            <a class="btn btn-sm btn-outline-success ms-1"
                                               href="<?php echo e(route($hubRoute, array_merge($filterQs, ['tab' => 'analytics', 'student_id' => $alert->student_id]))); ?>">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No alerts for this filter.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if($alerts): ?>
                        <div class="mt-3"><?php echo e($alerts->links()); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
:root {
    --ph-accent: #2f6f4e;
    --ph-accent-mid: #3d8b63;
    --ph-soft: #e8f3ec;
    --ph-border: #c5d5cb;
}
.ph-card { border: 1px solid var(--ph-border); box-shadow: none; }
.ph-header {
    background: linear-gradient(135deg, var(--ph-accent) 0%, var(--ph-accent-mid) 100%);
    border-bottom: none;
}
.ph-thead th {
    background: var(--ph-soft) !important;
    color: #1f4d35 !important;
    border-color: var(--ph-border) !important;
    font-weight: 700;
}
.ph-btn-primary {
    background: var(--ph-accent);
    border-color: var(--ph-accent);
    color: #fff;
}
.ph-btn-primary:hover { background: #1f4d35; border-color: #1f4d35; color: #fff; }
.ph-tabs .nav-link {
    color: var(--ph-accent);
    border: 1px solid var(--ph-border);
    margin-right: .35rem;
    background: #fff;
}
.ph-tabs .nav-link.active {
    background: var(--ph-accent) !important;
    border-color: var(--ph-accent) !important;
    color: #fff !important;
}
.ph-stat {
    background: var(--ph-soft);
    border: 1px solid var(--ph-border);
    border-radius: .5rem;
    padding: 1rem;
    height: 100%;
}
.ph-stat-label { font-size: .8rem; color: #4a6354; text-transform: uppercase; letter-spacing: .03em; }
.ph-stat-value { font-size: 1.6rem; font-weight: 700; color: #1f4d35; }
.ph-medal {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 50%; color: #fff; font-size: .85rem;
}
.ph-medal-1 { background: #c9a227; }
.ph-medal-2 { background: #8a8f98; }
.ph-medal-3 { background: #a86b3c; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php if($tab === 'analytics' && $analytics): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('performanceTrendChart');
    if (!ctx) return;
    const labels = <?php echo json_encode($analytics['trend_labels'], 15, 512) ?>;
    const values = <?php echo json_encode($analytics['trend_values'], 15, 512) ?>;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Quarter Average',
                data: values,
                borderColor: '#2f6f4e',
                backgroundColor: 'rgba(47, 111, 78, 0.15)',
                tension: 0.25,
                fill: true,
                spanGaps: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: {
                y: { suggestedMin: 60, suggestedMax: 100 }
            }
        }
    });
})();
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\grading\performance-hub.blade.php ENDPATH**/ ?>