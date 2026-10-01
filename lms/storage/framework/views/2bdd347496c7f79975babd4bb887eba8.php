<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="card">
            <div class="card-body">
                <h4>Attendance Record</h4>
                <p><strong>Student:</strong> <?php echo e($attendance->student->first_name); ?> <?php echo e($attendance->student->last_name); ?></p>
                <p><strong>Subject:</strong> <?php echo e($attendance->subject->subject_name ?? 'N/A'); ?></p>
                <p><strong>Date:</strong> <?php echo e(\Carbon\Carbon::parse($attendance->date)->format('M d, Y')); ?></p>
                <p><strong>Status:</strong> <span class="badge <?php echo e($attendance->status === 'present' ? 'bg-success' : 'bg-danger'); ?>"><?php echo e(ucfirst($attendance->status)); ?></span></p>
                <p><strong>Remarks:</strong> <?php echo e($attendance->remarks ?? 'None'); ?></p>
                <a href="<?php echo e(route('attendance.edit', $attendance)); ?>" class="btn btn-primary">Edit</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\attendance\show.blade.php ENDPATH**/ ?>