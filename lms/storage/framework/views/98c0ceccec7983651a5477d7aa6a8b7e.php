
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Assign Teachers to Subject: <?php echo e($subject->subject_name); ?></h3>
                </div>
            </div>
        </div>
        <form method="POST" action="<?php echo e(route('subjects.assignTeachers', $subject->id)); ?>">
            <?php echo csrf_field(); ?>
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Select Teachers</label>
                        <div class="row">
                            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="teacher_ids[]" value="<?php echo e($teacher->id); ?>" id="teacher<?php echo e($teacher->id); ?>" <?php echo e(in_array($teacher->id, $assigned) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="teacher<?php echo e($teacher->id); ?>">
                                            <?php echo e($teacher->full_name ?: $teacher->user->name ?? 'Unknown Teacher'); ?>

                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Teachers</button>
                    <a href="<?php echo e(route('subject/list/page')); ?>" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\subjects\assign_teachers.blade.php ENDPATH**/ ?>