<?php $__env->startSection('content'); ?>
<?php $quarters = collect($periods ?? [])->mapWithKeys(fn ($period) => [$period['number'] => $period['label']])->all(); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grading Progress</h3>
                    <p class="dir-subtitle"><?php echo e($academicYear->name ?? 'No current academic year'); ?></p>
                </div>
            </div>
        </div>
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Academic Year</label>
                    <input type="text" class="form-control" value="<?php echo e($academicYear->name ?? 'No current academic year'); ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="quarter">Term</label>
                    <select class="form-select" name="quarter" id="quarter" onchange="this.form.submit()">
                        <?php $__currentLoopData = $quarters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $number => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($number); ?>" <?php if($quarter === $number): echo 'selected'; endif; ?>><?php echo e($label); ?><?php echo e($number === ($currentQuarter ?? 0) ? ' · Current' : ''); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Period</th>
                        <th>Method</th>
                        <th>Report status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($row['grade']); ?></td>
                            <td><?php echo e($row['section']); ?></td>
                            <td><?php echo e($row['subject']); ?></td>
                            <td><?php echo e($row['teacher']); ?></td>
                            <td><?php echo e($quarters[$quarter]); ?></td>
                            <td><?php echo e($row['method']); ?></td>
                            <td><?php echo e($row['status']); ?> · <?php echo e($row['done']); ?>/<?php echo e($row['total']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7">No class schedules are available for the current academic year.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914m">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/grading/progress.blade.php ENDPATH**/ ?>