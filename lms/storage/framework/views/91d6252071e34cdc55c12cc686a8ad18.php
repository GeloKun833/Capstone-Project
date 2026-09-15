
<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h2>My Attendance Record</h2>
    
    <form method="GET" action="<?php echo e(route('attendance.student')); ?>" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="subject_id" class="form-label">Subject</label>
            <select name="subject_id" id="subject_id" class="form-select">
                <option value="">All Subjects</option>
                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($subject->id); ?>" <?php echo e(request('subject_id') == $subject->id ? 'selected' : ''); ?>>
                        <?php echo e($subject->subject_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="month" class="form-label">Month</label>
            <input type="month" name="month" id="month" class="form-control" 
                value="<?php echo e(request('month', now()->format('Y-m'))); ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Marked By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(\Carbon\Carbon::parse($attendance->date)->format('M d, Y')); ?></td>
                                <td><?php echo e($attendance->subject->subject_name); ?></td>
                                <td>
                                    <?php if($attendance->status === 'present'): ?>
                                        <span class="badge bg-success">Present</span>
                                    <?php elseif($attendance->status === 'late'): ?>
                                        <span class="badge bg-warning text-dark">Late</span>
                                    <?php elseif($attendance->status === 'excused'): ?>
                                        <span class="badge bg-info">Excused</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Absent</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($attendance->remarks ?: '-'); ?></td>
                                <td><?php echo e($attendance->teacher->full_name); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No attendance records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($attendances->isNotEmpty()): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Summary</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1">Total Classes: <?php echo e($summary['total']); ?></p>
                                <p class="mb-1">Present: <?php echo e($summary['present']); ?></p>
                                <p class="mb-1">Late: <?php echo e($summary['late'] ?? 0); ?></p>
                                <p class="mb-1">Absent: <?php echo e($summary['absent']); ?></p>
                                <p class="mb-1">Excused: <?php echo e($summary['excused'] ?? 0); ?></p>
                                <p class="mb-0">Attendance Rate: <?php echo e($summary['percentage']); ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/attendance/student_view.blade.php ENDPATH**/ ?>