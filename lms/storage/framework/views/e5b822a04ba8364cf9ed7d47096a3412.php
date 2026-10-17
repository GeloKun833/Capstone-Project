
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="gc-wrap">
            <div class="page-header">
                <h3 class="page-title mb-1">Create announcement</h3>
                <ul class="breadcrumb mb-3">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('announcements.index')); ?>">Announcements</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ul>
            </div>
            <?php echo $__env->make('announcements.partials.composer', [
                'formAction' => route('announcements.store'),
                'sections' => $sections,
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('announcements.partials.classroom-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/announcements/create.blade.php ENDPATH**/ ?>