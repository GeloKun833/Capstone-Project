<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <h3 class="page-title">Submission Details</h3>
        </div>
        <div class="card">
            <div class="card-body">
                <p><strong>Student:</strong> <?php echo e($submission->student->first_name); ?> <?php echo e($submission->student->last_name); ?></p>
                <p><strong>Submitted:</strong> <?php echo e($submission->submitted_at ? $submission->submitted_at->format('M d, Y H:i') : $submission->created_at->format('M d, Y H:i')); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-info"><?php echo e(ucfirst($submission->status)); ?></span></p>
                <?php if($submission->comments): ?><p><strong>Comments:</strong> <?php echo e($submission->comments); ?></p><?php endif; ?>
                <?php if($submission->file_path): ?>
                    <a href="<?php echo e(asset('storage/' . $submission->file_path)); ?>" class="btn btn-primary" target="_blank"><i class="fas fa-download"></i> Download File</a>
                <?php endif; ?>
                <?php if(Auth::user()->role_name === 'Teacher'): ?>
                    <a href="<?php echo e(route('lessons.activities.grade-submission', [$lesson, $activity, $submission])); ?>" class="btn btn-success">Grade Submission</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\activities\view-submission.blade.php ENDPATH**/ ?>