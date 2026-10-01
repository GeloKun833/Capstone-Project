<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Enrollment Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('enrollments.index')); ?>">Enrollments</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('enrollments.edit', $enrollment)); ?>" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
                    <a href="<?php echo e(route('enrollments.index')); ?>" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Student</label>
                        <p class="fw-semibold mb-0">
                            <?php echo e(trim(($enrollment->student->first_name ?? '').' '.($enrollment->student->last_name ?? '')) ?: 'N/A'); ?>

                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Subject</label>
                        <p class="fw-semibold mb-0"><?php echo e($enrollment->subject->subject_name ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Academic Year</label>
                        <p class="fw-semibold mb-0"><?php echo e($enrollment->academicYear->name ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Semester</label>
                        <p class="fw-semibold mb-0"><?php echo e($enrollment->semester->name ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Status</label>
                        <p class="fw-semibold mb-0"><span class="badge bg-info"><?php echo e($enrollment->status ?? 'active'); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Created</label>
                        <p class="fw-semibold mb-0"><?php echo e(optional($enrollment->created_at)->format('M d, Y h:i A')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\enrollments\show.blade.php ENDPATH**/ ?>