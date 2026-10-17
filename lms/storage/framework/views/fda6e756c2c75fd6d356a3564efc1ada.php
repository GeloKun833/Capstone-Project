
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Create activity</h3>
                    <p class="dir-subtitle">Save a draft while you work, then post it to one class.</p>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('lessons.activities.index', $lesson)); ?>" class="btn btn-outline-secondary dir-btn">Back</a>
                </div>
            </div>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <strong>The activity was not posted.</strong>
                <ul class="mb-0 mt-2">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php echo $__env->make('activities.partials.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/activities/create.blade.php ENDPATH**/ ?>