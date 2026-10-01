<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo e($activity->title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.activities.index', $lesson)); ?>">Activities</a></li>
                        <li class="breadcrumb-item active"><?php echo e($activity->title); ?></li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('lessons.activities.edit', [$lesson, $activity])); ?>" class="btn btn-primary me-2"><i class="fas fa-edit"></i> Edit</a>
                    <a href="<?php echo e(route('lessons.activities.submissions', [$lesson, $activity])); ?>" class="btn btn-info"><i class="fas fa-inbox"></i> Submissions</a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p><strong>Due Date:</strong> <?php echo e($activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('M d, Y') : 'N/A'); ?></p>
                <p><strong>Allows Submission:</strong> <?php echo e($activity->allows_submission ? 'Yes' : 'No'); ?></p>
                <hr>
                <h5>Instructions</h5>
                <p><?php echo nl2br(e($activity->instructions)); ?></p>
                <p><strong>Submissions:</strong> <?php echo e($activity->submissions->count()); ?></p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\activities\show.blade.php ENDPATH**/ ?>