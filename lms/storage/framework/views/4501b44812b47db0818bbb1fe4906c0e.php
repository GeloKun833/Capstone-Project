
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Assign Students to Section: <?php echo e($section->name); ?> (<?php echo e($section->grade_level); ?>)</h3>
                </div>
            </div>
        </div>
        <form method="POST" action="<?php echo e(route('sections.assignStudents', $section->id)); ?>">
            <?php echo csrf_field(); ?>
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Select Students</label>
                        <div class="row">
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="student_ids[]" value="<?php echo e($student->id); ?>" id="student<?php echo e($student->id); ?>" <?php echo e(in_array($student->id, $assigned) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="student<?php echo e($student->id); ?>">
                                            <?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?>

                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Students</button>
                    <a href="<?php echo e(route('sections.index')); ?>" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\sections\assign_students.blade.php ENDPATH**/ ?>