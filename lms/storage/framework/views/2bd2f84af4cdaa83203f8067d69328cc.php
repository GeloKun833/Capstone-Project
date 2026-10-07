<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Deactivate Teacher</h3>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <p class="mb-1"><strong>Teacher:</strong> <?php echo e($user->name); ?></p>
                    <p class="mb-3"><strong>Academic Year:</strong> <?php echo e($year->displayName()); ?></p>
                    <p class="fw-semibold">Assignments to be transferred:</p>
                    <ul>
                        <?php $__currentLoopData = $summary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($item['label']); ?> → <?php echo e($item['teacher']); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <div class="alert alert-info">
                        After confirmation, these assignments will be transferred to the selected teachers and <?php echo e($user->name); ?> will be marked Inactive. Historical Academic Year records will not be changed.
                    </div>
                    <form method="POST" action="<?php echo e(route('teacher.deactivate.confirm', $user->user_id)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php $__currentLoopData = $replacements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $teacherId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <input type="hidden" name="replacements[<?php echo e($key); ?>]" value="<?php echo e($teacherId); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('teacher.deactivate.transfer', $user->user_id)); ?>" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Confirm Transfer &amp; Deactivate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/usermanagement/teacher_transfer_confirm.blade.php ENDPATH**/ ?>