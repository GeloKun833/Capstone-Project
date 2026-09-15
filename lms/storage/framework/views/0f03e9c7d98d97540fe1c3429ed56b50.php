<?php $__env->startSection('content'); ?>
    <div class="main-wrapper">
        <div class="error-box">
            <h1>419</h1>
            <h3 class="h2 mb-3"><i class="fas fa-exclamation-triangle"></i> Expired Error</h3>
            <p class="h4 font-weight-normal">The page you requested was not found.</p>
            <a href="<?php echo e(route('home')); ?>" class="btn btn-primary">Back to Home</a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.error', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/errors/419.blade.php ENDPATH**/ ?>