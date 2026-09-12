<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Add Subject</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('class-subject.unified-management')); ?>">Class & Subject Management</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('subject/list/page')); ?>">Subjects</a></li>
                            <li class="breadcrumb-item active">Add Subject</li>
                        </ul>
                    </div>
                    <div class="col-auto">
                        <a href="<?php echo e(route('class-subject.unified-management')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Management
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo e(route('subject/save')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title"><span>Subject Information</span></h5>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Subject Name <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control" name="subject_name" value="<?php echo e(old('subject_name')); ?>" placeholder="e.g. Math" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Grade Level <span class="login-danger">*</span></label>
                                            <select class="form-control" name="class" required>
                                                <option value="">Select Grade</option>
                                                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($grade); ?>" <?php echo e(old('class') === $grade ? 'selected' : ''); ?>><?php echo e($grade); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <small class="text-muted">This subject will appear on the enrollment form for the selected grade.</small>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="student-submit">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                            <a href="<?php echo e(route('class-subject.unified-management')); ?>" class="btn btn-secondary ms-2">
                                                <i class="fas fa-arrow-left"></i> Back to Management
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\subjects\subject_add.blade.php ENDPATH**/ ?>