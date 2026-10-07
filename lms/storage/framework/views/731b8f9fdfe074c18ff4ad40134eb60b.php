<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page att-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Attendance Report</h3>
                    <p class="dir-subtitle">
                        <?php echo e($academicYear->name); ?> · <?php echo e($section->grade_level ? $section->grade_level.' · ' : ''); ?><?php echo e($section->name); ?> · <?php echo e($subject->subject_name); ?>

                    </p>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('attendance.index', ['section_id' => $sectionId, 'subject_id' => $subjectId])); ?>" class="btn btn-outline-secondary dir-btn">Back to attendance</a>
                </div>
            </div>
        </div>

        <div class="att-report-switch">
            <a href="<?php echo e(route('attendance.report', ['section_id' => $sectionId, 'subject_id' => $subjectId, 'period' => 'weekly'])); ?>" class="att-report-tab <?php echo e($period === 'weekly' ? 'is-active' : ''); ?>">Weekly</a>
            <a href="<?php echo e(route('attendance.report', ['section_id' => $sectionId, 'subject_id' => $subjectId, 'period' => 'monthly'])); ?>" class="att-report-tab <?php echo e($period === 'monthly' ? 'is-active' : ''); ?>">Monthly</a>
            <span class="att-report-range">
                <?php if($period === 'weekly'): ?>
                    Week of <?php echo e($rangeStart->format('M j')); ?> – <?php echo e($rangeEnd->format('M j, Y')); ?>

                <?php else: ?>
                    <?php echo e($rangeStart->format('F Y')); ?>

                <?php endif; ?>
            </span>
        </div>

        <?php if(count($rows) === 0): ?>
            <div class="att-empty">
                <h5>No students in this section</h5>
                <p>No students are enrolled in this class for <?php echo e($academicYear->name); ?>.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive att-table-wrap">
                <table class="table att-table att-report-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student Number</th>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="text-center"><?php echo e($period === 'weekly' ? $day->format('D j') : $day->format('j')); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <th class="text-center">Present</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><strong><?php echo e($row['student']->last_name); ?>, <?php echo e($row['student']->first_name); ?></strong></td>
                                <td><?php echo e($row['student']->admission_id ?: '—'); ?></td>
                                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $record = $row['cells'][$day->toDateString()] ?? null; ?>
                                    <td class="text-center">
                                        <?php if($record?->status === 'present'): ?>
                                            <span class="att-mark att-mark-present"><?php echo e($record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('g:i A') : 'Present'); ?></span>
                                        <?php elseif($record?->status === 'absent'): ?>
                                            <span class="att-mark att-mark-absent">Absent</span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <td class="text-center"><?php echo e($row['present']); ?></td>
                                <td class="text-center"><?php echo e($row['absent']); ?></td>
                                <td class="text-center"><?php echo e(number_format($row['rate'], 1)); ?>%</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914m">
<style>
.att-page .page-header { margin-bottom: 0.75rem; }
.att-report-switch { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.85rem; }
.att-report-tab {
    display: inline-block;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    border: 1px solid #e7e5e4;
    background: #fff;
    color: #44403c;
    font-weight: 700;
    font-size: 0.85rem;
}
.att-report-tab.is-active { background: #3d5ee1; border-color: #3d5ee1; color: #fff; }
.att-report-range { color: #78716c; font-size: 0.88rem; margin-left: 0.25rem; }
.att-table-wrap { max-height: 72vh; border: 1px solid #e7e5e4; border-radius: 12px; background: #fff; }
.att-table { margin: 0; }
.att-table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fafaf9;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #57534e;
    border-bottom: 1px solid #e7e5e4;
    white-space: nowrap;
}
.att-report-table td:first-child,
.att-report-table th:first-child { position: sticky; left: 0; background: #fff; z-index: 1; }
.att-report-table thead th:first-child { z-index: 2; background: #fafaf9; }
.att-mark { font-size: 0.75rem; font-weight: 700; white-space: nowrap; }
.att-mark-present { color: #166534; }
.att-mark-absent { color: #b91c1c; }
.att-empty {
    background: #fff;
    border: 1px dashed #d6d3d1;
    border-radius: 12px;
    padding: 1.5rem 1rem;
    text-align: center;
}
.att-empty h5 { margin: 0 0 0.25rem; font-size: 1rem; }
.att-empty p { margin: 0; color: #78716c; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/attendance/report.blade.php ENDPATH**/ ?>