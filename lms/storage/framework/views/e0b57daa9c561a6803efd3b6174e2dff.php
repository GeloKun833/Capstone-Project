
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Assign Grade Levels to <?php echo e($teacher->full_name); ?></h3>
                </div>
            </div>
        </div>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(route('teacher.assignGradeLevels', $teacher->id)); ?>">
            <?php echo csrf_field(); ?>
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Select Grade Levels <span class="text-danger">*</span></label>
                        <small class="form-text text-muted">Check the grade levels this teacher will handle.</small>
                        <div class="row">
                            <?php $__empty_1 = true; $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input <?php $__errorArgs = ['grade_levels'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="checkbox" name="grade_levels[]" value="<?php echo e($level); ?>" id="grade<?php echo e($loop->index); ?>" <?php echo e(in_array($level, $assigned) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="grade<?php echo e($loop->index); ?>">
                                            <?php echo e($level); ?>

                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="col-12">
                                    <div class="alert alert-warning">No grade levels available to assign.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php $__errorArgs = ['grade_levels'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Grade Levels</button>
                    <a href="<?php echo e(route('teacher/list/page')); ?>" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\teacher\assign_grade_levels.blade.php ENDPATH**/ ?>