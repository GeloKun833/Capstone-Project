
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Add Block Section</h3>
                    <p class="text-muted mb-0">Sections created here appear on the enrollment form for the selected grade.</p>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('class-subject.unified-management')); ?>">Classes &amp; Subjects</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('sections.index')); ?>">Sections</a></li>
                        <li class="breadcrumb-item active">Add Section</li>
                    </ul>
                </div>
            </div>
        </div>
        <form method="POST" action="<?php echo e(route('sections.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Section / Block Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required
                                    value="<?php echo e(old('name')); ?>" placeholder="e.g. Pasteur, Apple, Franklin">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grade_level">Grade Level <span class="text-danger">*</span></label>
                                <select name="grade_level" id="grade_level" class="form-control" required>
                                    <option value="">Select Grade</option>
                                    <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($grade); ?>" <?php echo e(old('grade_level') === $grade ? 'selected' : ''); ?>><?php echo e($grade); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <small class="text-muted">Must match enrollment grade options so Block Section loads correctly.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="adviser_id">Adviser</label>
                                <select name="adviser_id" id="adviser_id" class="form-control">
                                    <option value="">-- Select Adviser --</option>
                                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teacher->id); ?>" <?php echo e(old('adviser_id') == $teacher->id ? 'selected' : ''); ?>>
                                            <?php echo e($teacher->full_name ?: ($teacher->user->name ?? 'Unknown Teacher')); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capacity">Capacity</label>
                                <input type="number" name="capacity" id="capacity" class="form-control" min="1"
                                    value="<?php echo e(old('capacity', 25)); ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"><?php echo e(old('description')); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Section</button>
                    <a href="<?php echo e(route('sections.index')); ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\sections\create.blade.php ENDPATH**/ ?>