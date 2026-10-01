<?php $__env->startSection('content'); ?>
<?php
    $tabs = [
        'overview' => ['label' => 'Overview', 'icon' => 'fa-chart-pie'],
        'grades' => ['label' => 'Grades', 'icon' => 'fa-clipboard-list'],
        'attendance' => ['label' => 'Attendance', 'icon' => 'fa-user-check'],
        'activities' => ['label' => 'Activities', 'icon' => 'fa-tasks'],
        'assignments' => ['label' => 'Assignments', 'icon' => 'fa-file-alt'],
        'feedback' => ['label' => 'Teacher Feedback', 'icon' => 'fa-comment-dots'],
    ];

    $attendanceBadge = function (?string $status): string {
        return match ($status) {
            'present' => 'hub-pill hub-pill--success',
            'late' => 'hub-pill hub-pill--warn',
            'excused' => 'hub-pill hub-pill--info',
            default => 'hub-pill hub-pill--danger',
        };
    };

    $statusPill = function (?string $status): string {
        $key = strtolower((string) $status);
        if (in_array($key, ['submitted', 'graded', 'completed', 'passed'], true)) {
            return 'hub-pill hub-pill--success';
        }
        if (in_array($key, ['pending', 'draft', 'to do', 'todo'], true)) {
            return 'hub-pill hub-pill--warn';
        }
        if (in_array($key, ['late', 'overdue', 'missing', 'not submitted'], true)) {
            return 'hub-pill hub-pill--danger';
        }
        return 'hub-pill hub-pill--muted';
    };
?>

<div class="page-wrapper">
    <div class="content container-fluid hub-page">
        <div class="hub-hero">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <p class="hub-kicker mb-1">My Children</p>
                    <h3 class="hub-title mb-1"><?php echo e($child->full_name); ?></h3>
                    <p class="hub-subtitle mb-0">
                        <?php echo e($child->year_level ?: $child->class ?: 'Grade'); ?>

                        <?php if($child->sectionLabel()): ?>
                            · <?php echo e($child->sectionLabel()); ?>

                        <?php endif; ?>
                    </p>
                </div>
                <?php if($children->count() > 1): ?>
                    <div class="hub-switch">
                        <label class="hub-label mb-1">Switch child</label>
                        <select class="form-select hub-select js-hub-switch-child">
                            <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linkedChild): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e(route('parent.child.hub', ['childId' => $linkedChild->id, 'tab' => $tab])); ?>"
                                    <?php if($linkedChild->id === $child->id): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($linkedChild->full_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="hub-tabs" role="tablist">
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="hub-tab <?php echo e($tab === $key ? 'is-active' : ''); ?>"
                   href="<?php echo e(route('parent.child.hub', ['childId' => $child->id, 'tab' => $key])); ?>">
                    <i class="fas <?php echo e($meta['icon']); ?>"></i>
                    <span><?php echo e($meta['label']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if(in_array($tab, ['overview', 'grades'], true)): ?>
            <form method="GET" action="<?php echo e(route('parent.child.hub', $child->id)); ?>" class="hub-toolbar">
                <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                <div>
                    <label class="hub-label">Academic year</label>
                    <select name="academic_year_id" class="form-select hub-select" onchange="this.form.submit()">
                        <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($year->id); ?>" <?php if($academicYear && $academicYear->id == $year->id): echo 'selected'; endif; ?>>
                                <?php echo e($year->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php if($tab === 'grades' && $academicYear): ?>
                    <a class="btn hub-btn-primary ms-auto"
                       target="_blank"
                       href="<?php echo e(route('parent.child.report-card', ['childId' => $child->id, 'academic_year_id' => $academicYear->id])); ?>">
                        <i class="fas fa-print me-2"></i>Print report card
                    </a>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <?php if($tab === 'overview'): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="hub-stat">
                        <div class="hub-stat-icon" style="background:linear-gradient(135deg,#4facfe,#00f2fe)"><i class="fas fa-percentage"></i></div>
                        <div>
                            <div class="hub-stat-value"><?php echo e(number_format($overview['averageGrade'], 1)); ?>%</div>
                            <div class="hub-stat-label">Average grade</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="hub-stat">
                        <div class="hub-stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2)"><i class="fas fa-user-check"></i></div>
                        <div>
                            <div class="hub-stat-value"><?php echo e($overview['attendancePercentage']); ?>%</div>
                            <div class="hub-stat-label">Attendance rate</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="hub-stat">
                        <div class="hub-stat-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c)"><i class="fas fa-book-open"></i></div>
                        <div>
                            <div class="hub-stat-value"><?php echo e($overview['enrollments']->count()); ?></div>
                            <div class="hub-stat-label">Active enrollments</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="hub-stat">
                        <div class="hub-stat-icon" style="background:linear-gradient(135deg,#fa709a,#fee140)"><i class="fas fa-award"></i></div>
                        <div>
                            <div class="hub-stat-value"><?php echo e($overview['currentGpa']->gpa ?? '—'); ?></div>
                            <div class="hub-stat-label">Latest GPA</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="hub-card h-100">
                        <div class="hub-card-head">
                            <h5>Recent grades</h5>
                            <a href="<?php echo e(route('parent.child.hub', ['childId' => $child->id, 'tab' => 'grades'])); ?>">View all</a>
                        </div>
                        <div class="hub-card-body">
                            <?php $__empty_1 = true; $__currentLoopData = $overview['recentGrades']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="hub-row">
                                    <div>
                                        <div class="hub-row-title"><?php echo e($grade->subject->subject_name ?? 'Subject'); ?></div>
                                        <div class="hub-row-meta">Component score</div>
                                    </div>
                                    <strong class="hub-score"><?php echo e(number_format($grade->percentage ?? 0, 1)); ?>%</strong>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="hub-empty">No grades posted yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hub-card h-100">
                        <div class="hub-card-head">
                            <h5>Recent attendance</h5>
                            <a href="<?php echo e(route('parent.child.hub', ['childId' => $child->id, 'tab' => 'attendance'])); ?>">View all</a>
                        </div>
                        <div class="hub-card-body">
                            <?php $__empty_1 = true; $__currentLoopData = $overview['recentAttendance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="hub-row">
                                    <div>
                                        <div class="hub-row-title"><?php echo e($record->subject->subject_name ?? 'Subject'); ?></div>
                                        <div class="hub-row-meta"><?php echo e($record->date ? \Carbon\Carbon::parse($record->date)->format('M d, Y') : '—'); ?></div>
                                    </div>
                                    <span class="<?php echo e($attendanceBadge($record->status)); ?>"><?php echo e(ucfirst($record->status)); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="hub-empty">No attendance records yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'grades'): ?>
            <div class="hub-card mb-4">
                <div class="hub-card-head">
                    <h5>Observed values</h5>
                    <span class="hub-hint">AO · SO · RO</span>
                </div>
                <div class="hub-card-body table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Core values</th>
                                <th>Behavior statements</th>
                                <th class="text-center">Q1</th>
                                <th class="text-center">Q2</th>
                                <th class="text-center">Q3</th>
                                <th class="text-center">Q4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $observedIndicators ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $core => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $rating = ($observedRatings ?? collect())->get($indicator->id); ?>
                                    <tr>
                                        <?php if($i === 0): ?>
                                            <td rowspan="<?php echo e($items->count()); ?>" class="hub-core"><?php echo e($core); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo e($indicator->statement); ?></td>
                                        <?php $__currentLoopData = ['quarter_1','quarter_2','quarter_3','quarter_4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center"><?php echo e(optional($rating)->{$qf} ?: '—'); ?></td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="hub-empty">No observed values yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="hub-card">
                <div class="hub-card-head">
                    <h5>Learning progress</h5>
                </div>
                <div class="hub-card-body table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Learning area</th>
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
                                    <td class="fw-semibold"><?php echo e($qg->subject->subject_name ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo e($qg->quarter_1 !== null ? number_format($qg->quarter_1, 0) : '—'); ?></td>
                                    <td class="text-center"><?php echo e($qg->quarter_2 !== null ? number_format($qg->quarter_2, 0) : '—'); ?></td>
                                    <td class="text-center"><?php echo e($qg->quarter_3 !== null ? number_format($qg->quarter_3, 0) : '—'); ?></td>
                                    <td class="text-center"><?php echo e($qg->quarter_4 !== null ? number_format($qg->quarter_4, 0) : '—'); ?></td>
                                    <td class="text-center"><strong><?php echo e($qg->final_grade !== null ? number_format($qg->final_grade, 0) : '—'); ?></strong></td>
                                    <td class="text-center">
                                        <span class="hub-pill hub-pill--muted">
                                            <?php echo e($qg->remarks ?? \App\Services\ReportCardService::remarkForScore($qg->final_grade !== null ? (float) $qg->final_grade : null)); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="7" class="hub-empty">No quarterly grades posted yet.</td></tr>
                            <?php endif; ?>
                            <?php if($quarterlyGrades->isNotEmpty()): ?>
                                <tr class="hub-total">
                                    <td class="text-end">General average</td>
                                    <?php $__currentLoopData = ['q1','q2','q3','q4','final']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="text-center">
                                            <?php echo e(isset($generalAverages[$k]) && $generalAverages[$k] !== null ? number_format($generalAverages[$k], 2) : '—'); ?>

                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <td class="text-center">
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
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3"><div class="hub-stat"><div class="hub-stat-icon" style="background:#16a34a"><i class="fas fa-check"></i></div><div><div class="hub-stat-value"><?php echo e($attendanceSummary['present']); ?></div><div class="hub-stat-label">Present</div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="hub-stat"><div class="hub-stat-icon" style="background:#dc2626"><i class="fas fa-times"></i></div><div><div class="hub-stat-value"><?php echo e($attendanceSummary['absent']); ?></div><div class="hub-stat-label">Absent</div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="hub-stat"><div class="hub-stat-icon" style="background:#6366f1"><i class="fas fa-list"></i></div><div><div class="hub-stat-value"><?php echo e($attendanceSummary['total']); ?></div><div class="hub-stat-label">Total records</div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="hub-stat"><div class="hub-stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2)"><i class="fas fa-chart-line"></i></div><div><div class="hub-stat-value"><?php echo e($attendanceSummary['percentage']); ?>%</div><div class="hub-stat-label">Attendance rate</div></div></div></div>
            </div>
            <div class="hub-card">
                <div class="hub-card-head"><h5>Attendance log</h5></div>
                <div class="hub-card-body table-responsive">
                    <table class="hub-table">
                        <thead><tr><th>Date</th><th>Subject</th><th>Status</th><th>Remarks</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $attendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($record->date ? \Carbon\Carbon::parse($record->date)->format('M d, Y') : '—'); ?></td>
                                    <td><?php echo e($record->subject->subject_name ?? 'N/A'); ?></td>
                                    <td><span class="<?php echo e($attendanceBadge($record->status)); ?>"><?php echo e(ucfirst($record->status)); ?></span></td>
                                    <td><?php echo e($record->remarks ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="4" class="hub-empty">No attendance records for this period.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'activities'): ?>
            <div class="hub-card">
                <div class="hub-card-head"><h5>Assigned activities</h5></div>
                <div class="hub-card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $submission = $submissions->firstWhere('activity_id', $activity->id); ?>
                        <div class="hub-row">
                            <div>
                                <div class="hub-row-title"><?php echo e($activity->title); ?></div>
                                <div class="hub-row-meta">
                                    <?php echo e($activity->lesson->subject->subject_name ?? 'Subject'); ?>

                                    · Due <?php echo e($activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('M d, Y') : '—'); ?>

                                </div>
                            </div>
                            <span class="<?php echo e($statusPill($submission ? $submission->status : 'Not submitted')); ?>">
                                <?php echo e($submission ? ucfirst($submission->status) : 'Not submitted'); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="hub-empty">No activities found.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'assignments'): ?>
            <div class="hub-card">
                <div class="hub-card-head"><h5>Assignment submissions</h5></div>
                <div class="hub-card-body table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Subject</th>
                                <th>Submitted</th>
                                <th>Score</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $assignmentSubmissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo e($submission->assignment->title ?? 'Assignment'); ?></td>
                                    <td><?php echo e($submission->assignment->subject->subject_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($submission->submitted_at?->format('M d, Y h:i A') ?? '—'); ?></td>
                                    <td><?php echo e($submission->score !== null ? number_format($submission->score, 2) . ' / ' . number_format($submission->max_score ?? 100, 0) : '—'); ?></td>
                                    <td><span class="<?php echo e($statusPill($submission->status ?? 'pending')); ?>"><?php echo e(ucfirst($submission->status ?? 'pending')); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="hub-empty">No assignment submissions yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if($tab === 'feedback'): ?>
            <div class="hub-card">
                <div class="hub-card-head"><h5>Teacher feedback</h5></div>
                <div class="hub-card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $feedbackItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="hub-feedback">
                            <div class="d-flex justify-content-between gap-3 flex-wrap">
                                <div>
                                    <span class="hub-pill hub-pill--muted"><?php echo e($item->type); ?></span>
                                    <h6 class="hub-feedback-title"><?php echo e($item->title); ?></h6>
                                    <div class="hub-row-meta"><?php echo e($item->subject); ?></div>
                                </div>
                                <small class="text-muted"><?php echo e($item->date ? \Carbon\Carbon::parse($item->date)->format('M d, Y') : ''); ?></small>
                            </div>
                            <?php if($item->score !== null): ?>
                                <p class="hub-feedback-score mb-2">Score: <?php echo e($item->score); ?><?php if($item->max_score): ?> / <?php echo e($item->max_score); ?><?php endif; ?></p>
                            <?php endif; ?>
                            <p class="mb-0"><?php echo e($item->feedback ?: 'No written feedback provided.'); ?></p>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="hub-empty">No teacher feedback available yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.hub-page { --hub: #667eea; --hub-2: #764ba2; color: #0f172a; }
.hub-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-radius: 20px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 18px 40px rgba(102, 126, 234, 0.28);
}
.hub-kicker { font-size: .75rem; letter-spacing: .08em; text-transform: uppercase; opacity: .85; font-weight: 600; }
.hub-title { font-weight: 700; letter-spacing: -.02em; }
.hub-subtitle { opacity: .9; }
.hub-switch { min-width: 220px; }
.hub-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: inherit; opacity: .8; }
.hub-hero .hub-label { color: #fff; }
.hub-select { border-radius: 12px; border: 1px solid #e2e8f0; }
.hub-hero .hub-select { background: rgba(255,255,255,.95); }
.hub-tabs {
    display: flex; flex-wrap: wrap; gap: .5rem;
    background: #fff; border-radius: 16px; padding: .55rem;
    box-shadow: 0 8px 24px rgba(15,23,42,.06); margin-bottom: 1.25rem;
}
.hub-tab {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .55rem 1rem; border-radius: 999px; color: #475569;
    font-weight: 600; text-decoration: none; font-size: .9rem;
}
.hub-tab:hover { background: #f1f5f9; color: #334155; }
.hub-tab.is-active { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; }
.hub-toolbar {
    display: flex; flex-wrap: wrap; align-items: end; gap: 1rem;
    background: #fff; border-radius: 16px; padding: 1rem 1.15rem;
    box-shadow: 0 8px 24px rgba(15,23,42,.06); margin-bottom: 1.25rem;
}
.hub-btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2); border: 0; color: #fff;
    border-radius: 12px; padding: .6rem 1rem; font-weight: 600;
}
.hub-stat {
    background: #fff; border-radius: 16px; padding: 1.1rem 1.2rem;
    display: flex; gap: 1rem; align-items: center;
    box-shadow: 0 8px 24px rgba(15,23,42,.06); height: 100%;
}
.hub-stat-icon {
    width: 52px; height: 52px; border-radius: 14px; color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
}
.hub-stat-value { font-size: 1.45rem; font-weight: 800; line-height: 1.1; }
.hub-stat-label { color: #64748b; font-size: .82rem; }
.hub-card {
    background: #fff; border-radius: 18px; overflow: hidden;
    box-shadow: 0 8px 24px rgba(15,23,42,.06);
}
.hub-card-head {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.2rem; border-bottom: 1px solid #f1f5f9;
}
.hub-card-head h5 { margin: 0; font-weight: 700; }
.hub-card-head a { font-size: .85rem; font-weight: 600; color: #667eea; text-decoration: none; }
.hub-card-body { padding: 1rem 1.2rem 1.15rem; }
.hub-row {
    display: flex; justify-content: space-between; align-items: center; gap: 1rem;
    padding: .85rem 0; border-bottom: 1px solid #f1f5f9;
}
.hub-row:last-child { border-bottom: 0; }
.hub-row-title { font-weight: 600; }
.hub-row-meta { color: #64748b; font-size: .8rem; }
.hub-score { color: #4f46e5; }
.hub-empty { text-align: center; color: #94a3b8; padding: 1.5rem .5rem; }
.hub-table { width: 100%; margin: 0; }
.hub-table th {
    font-size: .75rem; text-transform: uppercase; letter-spacing: .04em;
    color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0;
    padding: .65rem .5rem; background: #f8fafc;
}
.hub-table td { padding: .75rem .5rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.hub-core { font-weight: 700; background: #f8fafc; }
.hub-total td { background: #eef2ff; font-weight: 700; }
.hub-pill {
    display: inline-flex; align-items: center; border-radius: 999px;
    padding: .2rem .65rem; font-size: .75rem; font-weight: 700;
}
.hub-pill--success { background: #dcfce7; color: #166534; }
.hub-pill--danger { background: #fee2e2; color: #991b1b; }
.hub-pill--warn { background: #fef3c7; color: #92400e; }
.hub-pill--info { background: #e0f2fe; color: #075985; }
.hub-pill--muted { background: #f1f5f9; color: #475569; }
.hub-hint { font-size: .75rem; color: #64748b; }
.hub-feedback {
    border: 1px solid #eef2ff; border-left: 4px solid #667eea;
    border-radius: 14px; padding: 1rem; margin-bottom: .85rem; background: #fafafe;
}
.hub-feedback:last-child { margin-bottom: 0; }
.hub-feedback-title { margin: .45rem 0 .15rem; font-weight: 700; }
.hub-feedback-score { font-weight: 600; color: #4338ca; }
@media (max-width: 767px) {
    .hub-title { font-size: 1.35rem; }
    .hub-tabs { overflow-x: auto; flex-wrap: nowrap; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.querySelectorAll('.js-hub-switch-child').forEach(function (select) {
        select.addEventListener('change', function () {
            window.location.href = this.value;
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\parent\child_hub.blade.php ENDPATH**/ ?>