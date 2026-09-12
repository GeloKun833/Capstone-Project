<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="<?php echo e(route('attendance.update', $attendance)); ?>" method="POST">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <p><strong>Student:</strong> <?php echo e($attendance->student->first_name); ?> <?php echo e($attendance->student->last_name); ?></p>
                    <p><strong>Subject:</strong> <?php echo e($attendance->subject->subject_name ?? 'N/A'); ?></p>
                    <p><strong>Date:</strong> <?php echo e(\Carbon\Carbon::parse($attendance->date)->format('M d, Y')); ?></p>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="present" <?php echo e($attendance->status === 'present' ? 'selected' : ''); ?>>Present</option>
                            <option value="absent" <?php echo e($attendance->status === 'absent' ? 'selected' : ''); ?>>Absent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="<?php echo e($attendance->remarks); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\attendance\edit.blade.php ENDPATH**/ ?>