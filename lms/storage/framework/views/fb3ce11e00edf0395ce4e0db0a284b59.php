<?php $__env->startSection('content'); ?>
<?php
    $tabs = [
        'overview' => ['label' => 'Overview', 'icon' => 'fa-home'],
        'grades' => ['label' => 'Grades', 'icon' => 'fa-clipboard-list'],
        'attendance' => ['label' => 'Attendance', 'icon' => 'fa-user-check'],
        'activities' => ['label' => 'Activities', 'icon' => 'fa-tasks'],
        'assignments' => ['label' => 'Assignments', 'icon' => 'fa-file-alt'],
        'feedback' => ['label' => 'Teacher Feedback', 'icon' => 'fa-comment-dots'],
    ];
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><i class="fas fa-user me-2"></i><?php echo e($child->full_name); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('parent.index')); ?>">Parent Portal</a></li>
                        <li class="breadcrumb-item active"><?php echo e($child->first_name); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if($children->count() > 1): ?>
            <div class="card card-table comman-shadow mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="<?php echo e(route('parent.child.hub', $child->id)); ?>" class="d-flex align-items-center gap-3 flex-wrap">
                        <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                        <label class="form-label mb-0 fw-semibold">Switch child:</label>
                        <select name="childId" class="form-control form-select" style="max-width:280px;" onchange="window.location.href='<?php echo e(url('/parent/child')); ?>/' + this.value + '?tab=<?php echo e($tab); ?>'">
                            <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linkedChild): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($linkedChild->id); ?>" <?php if($linkedChild->id === $child->id): echo 'selected'; endif; ?>>
                                    <?php echo e($linkedChild->full_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <ul class="nav nav-pills parent-hub-tabs mb-4 flex-wrap gap-2">
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === $key ? 'active' : ''); ?>"
                       href="<?php echo e(route('parent.child.hub', ['childId' => $child->id, 'tab' => $key])); ?>">
                        <i class="fas <?php echo e($meta['icon']); ?> me-1"></i><?php echo e($meta['label']); ?>

                    </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <?php if(in_array($tab, ['overview', 'grades'], true)): ?>
            <div class="card card-table comman-shadow mb-4">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('parent.child.hub', $child->id)); ?>" class="row g-3 align-items-end">
                        <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                        <div class="col-md-4">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-control form-select" onchange="this.form.submit()">
                                <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>" <?php if($academicYear && $academicYear->id == $year->id): echo 'selected'; endif; ?>>
                                        <?php echo e($year->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'overview'): ?>
            <div class="row">
                <div class="col-md-3 mb-3"><div class="card text-center p-3"><h4 class="text-primary mb-0"><?php echo e(number_format($overview['averageGrade'], 1)); ?>%</h4><small class="text-muted">Average Grade</small></div></div>
                <div class="col-md-3 mb-3"><div class="card text-center p-3"><h4 class="text-success mb-0"><?php echo e($overview['attendancePercentage']); ?>%</h4><small class="text-muted">Attendance Rate</small></div></div>
                <div class="col-md-3 mb-3"><div class="card text-center p-3"><h4 class="text-info mb-0"><?php echo e($overview['enrollments']->count()); ?></h4><small class="text-muted">Active Enrollments</small></div></div>
                <div class="col-md-3 mb-3"><div class="card text-center p-3"><h4 class="text-warning mb-0"><?php echo e($overview['currentGpa']->gpa ?? '—'); ?></h4><small class="text-muted">Latest GPA</small></div></div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card card-table comman-shadow h-100">
                        <div class="card-header"><h5 class="mb-0">Recent Grades</h5></div>
                        <div class="card-body">
                            <?php $__empty_1 = true; $__currentLoopData = $overview['recentGrades']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span><?php echo e($grade->subject->subject_name ?? 'Subject'); ?></span>
                                    <strong><?php echo e(number_format($grade->percentage ?? 0, 1)); ?>%</strong>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-muted mb-0">No grades yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card card-table comman-shadow h-100">
                        <div class="card-header"><h5 class="mb-0">Recent Attendance</h5></div>
                        <div class="card-body">
                            <?php $__empty_1 = true; $__currentLoopData = $overview['recentAttendance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span><?php echo e($record->date?->format('M d, Y')); ?> — <?php echo e($record->subject->subject_name ?? 'Subject'); ?></span>
                                    <span class="badge bg-<?php echo e($record->status === 'present' ? 'success' : 'danger'); ?>"><?php echo e(ucfirst($record->status)); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-muted mb-0">No attendance records yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'grades'): ?>
            <div class="mb-3 text-end">
                <?php if($academicYear): ?>
                    <a class="btn btn-primary" target="_blank"
                       href="<?php echo e(route('parent.child.report-card', ['childId' => $child->id, 'academic_year_id' => $academicYear->id])); ?>">
                        <i class="fas fa-print me-1"></i> Printable Report Card
                    </a>
                <?php endif; ?>
            </div>

            <div class="card card-table comman-shadow mb-4">
                <div class="card-header bg-success text-white"><h5 class="mb-0 text-uppercase">Report on Learner&rsquo;s Observed Values</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Core Values</th>
                                <th>Behavior Statements</th>
                                <th class="text-center">1</th>
                                <th class="text-center">2</th>
                                <th class="text-center">3</th>
                                <th class="text-center">4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $observedIndicators ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $core => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $rating = ($observedRatings ?? collect())->get($indicator->id); ?>
                                    <tr>
                                        <?php if($i === 0): ?>
                                            <td rowspan="<?php echo e($items->count()); ?>" class="fw-bold"><?php echo e($core); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo e($indicator->statement); ?></td>
                                        <?php $__currentLoopData = ['quarter_1','quarter_2','quarter_3','quarter_4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center"><?php echo e(optional($rating)->{$qf} ?: '—'); ?></td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No observed values yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <p class="small text-muted mt-2 mb-0">AO = Always Observed | SO = Sometimes Observed | RO = Rarely Observed</p>
                </div>
            </div>

            <div class="card card-table comman-shadow mb-4">
                <div class="card-header bg-primary text-white"><h5 class="mb-0 text-uppercase">Report on Learning Progress and Achievement</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Learning Areas</th>
                                <th class="text-center">Q1</th>
                                <th class="text-center">Q2</th>
                                <th class="text-center">Q3</th>
                                <th class="text-center">Q4</th>
                                <th class="text-center">Final</th>
                                <th class="text-center">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $quarterlyGrades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($qg->subject->subject_name ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo e($qg->quarter_1 !== null ? number_format($qg->quarter_1, 0) : '—'); ?></td>
                                    <td class="text-center"><?php echo e($qg->quarter_2 !== null ? number_format($qg->quarter_2, 0) : '—'); ?></td>
                                    <td class="text-center"><?php echo e($qg->quarter_3 !== null ? number_format($qg->quarter_3, 0) : '—'); ?></td>
                                    <td class="text-center"><?php echo e($qg->quarter_4 !== null ? number_format($qg->quarter_4, 0) : '—'); ?></td>
                                    <td class="text-center"><strong><?php echo e($qg->final_grade !== null ? number_format($qg->final_grade, 0) : '—'); ?></strong></td>
                                    <td class="text-center">
                                        <?php echo e($qg->remarks ?? \App\Services\ReportCardService::remarkForScore($qg->final_grade !== null ? (float)$qg->final_grade : null)); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No quarterly grades posted yet.</td></tr>
                            <?php endif; ?>
                            <?php if($quarterlyGrades->isNotEmpty()): ?>
                                <tr class="table-secondary">
                                    <td class="fw-bold text-end">General Average</td>
                                    <?php $__currentLoopData = ['q1','q2','q3','q4','final']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="text-center fw-bold">
                                            <?php echo e(isset($generalAverages[$k]) && $generalAverages[$k] !== null ? number_format($generalAverages[$k], 2) : '—'); ?>

                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <td class="text-center fw-bold">
                                        <?php echo e(\App\Services\ReportCardService::remarkForScore($generalAverages['final'] ?? null)); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'attendance'): ?>
            <div class="row mb-3">
                <div class="col-md-3"><div class="card p-3 text-center"><strong><?php echo e($attendanceSummary['present']); ?></strong><div class="text-muted small">Present</div></div></div>
                <div class="col-md-3"><div class="card p-3 text-center"><strong><?php echo e($attendanceSummary['absent']); ?></strong><div class="text-muted small">Absent</div></div></div>
                <div class="col-md-3"><div class="card p-3 text-center"><strong><?php echo e($attendanceSummary['total']); ?></strong><div class="text-muted small">Total Records</div></div></div>
                <div class="col-md-3"><div class="card p-3 text-center"><strong><?php echo e($attendanceSummary['percentage']); ?>%</strong><div class="text-muted small">Rate</div></div></div>
            </div>
            <div class="card card-table comman-shadow">
                <div class="card-body table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Date</th><th>Subject</th><th>Status</th><th>Remarks</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $attendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($record->date?->format('M d, Y')); ?></td>
                                    <td><?php echo e($record->subject->subject_name ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-<?php echo e($record->status === 'present' ? 'success' : 'danger'); ?>"><?php echo e(ucfirst($record->status)); ?></span></td>
                                    <td><?php echo e($record->remarks ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No attendance records for this period.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'activities'): ?>
            <div class="card card-table comman-shadow mb-4">
                <div class="card-header"><h5 class="mb-0">Assigned Activities</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Activity</th><th>Subject</th><th>Due Date</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php $submission = $submissions->firstWhere('activity_id', $activity->id); ?>
                                <tr>
                                    <td><?php echo e($activity->title); ?></td>
                                    <td><?php echo e($activity->lesson->subject->subject_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('M d, Y') : '—'); ?></td>
                                    <td><?php echo e($submission ? ucfirst($submission->status) : 'Not submitted'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No activities found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'assignments'): ?>
            <div class="card card-table comman-shadow">
                <div class="card-header"><h5 class="mb-0">Assignment Submissions</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Assignment</th><th>Subject</th><th>Submitted</th><th>Score</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $assignmentSubmissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($submission->assignment->title ?? 'Assignment'); ?></td>
                                    <td><?php echo e($submission->assignment->subject->subject_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($submission->submitted_at?->format('M d, Y h:i A') ?? '—'); ?></td>
                                    <td><?php echo e($submission->score !== null ? number_format($submission->score, 2) . ' / ' . number_format($submission->max_score ?? 100, 0) : '—'); ?></td>
                                    <td><?php echo e(ucfirst($submission->status ?? 'pending')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No assignment submissions yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'feedback'): ?>
            <div class="card card-table comman-shadow">
                <div class="card-header bg-success text-white"><h5 class="mb-0">Teacher Feedback & Graded Work</h5></div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $feedbackItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-secondary me-2"><?php echo e($item->type); ?></span>
                                    <strong><?php echo e($item->title); ?></strong>
                                    <div class="text-muted small"><?php echo e($item->subject); ?></div>
                                </div>
                                <small class="text-muted"><?php echo e($item->date ? \Carbon\Carbon::parse($item->date)->format('M d, Y') : ''); ?></small>
                            </div>
                            <?php if($item->score !== null): ?>
                                <p class="mb-2"><strong>Score:</strong> <?php echo e($item->score); ?><?php if($item->max_score): ?> / <?php echo e($item->max_score); ?><?php endif; ?></p>
                            <?php endif; ?>
                            <p class="mb-0"><?php echo e($item->feedback ?: 'No written feedback provided.'); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted text-center py-4 mb-0">No teacher feedback available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.parent-hub-tabs .nav-link {
    border-radius: 999px;
    padding: 0.55rem 1rem;
    color: #475569;
    font-weight: 500;
}
.parent-hub-tabs .nav-link.active {
    background: #2563eb;
    color: #fff;
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\parent\child_hub.blade.php ENDPATH**/ ?>