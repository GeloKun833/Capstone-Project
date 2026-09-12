<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Schedule Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.schedules.index')); ?>">Schedules</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('admin.schedules.edit', $schedule)); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Class Information</h5>
                <div class="row mb-4">
                    <div class="col-md-4 mb-2"><strong>Teacher:</strong> <?php echo e($schedule->teacher->full_name ?? 'N/A'); ?></div>
                    <div class="col-md-4 mb-2"><strong>Section:</strong> <?php echo e($schedule->section->name ?? 'N/A'); ?>

                        <?php if($schedule->section): ?>
                            <span class="text-muted">(<?php echo e($schedule->section->grade_level); ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-2"><strong>Subject:</strong> <?php echo e($schedule->subject->subject_name ?? 'N/A'); ?>

                        <?php if($schedule->subject?->class): ?>
                            <span class="text-muted">(<?php echo e($schedule->subject->class); ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-2"><strong>Room:</strong> <?php echo e($schedule->room->room_name ?? '—'); ?></div>
                </div>

                <h5 class="mb-3">Schedule Details</h5>
                <div class="row">
                    <div class="col-md-3 mb-2"><strong>Day:</strong> <?php echo e(ucfirst($schedule->day_of_week)); ?></div>
                    <div class="col-md-3 mb-2"><strong>Time:</strong>
                        <?php echo e(\Carbon\Carbon::parse($schedule->start_time)->format('h:i A')); ?>

                        –
                        <?php echo e(\Carbon\Carbon::parse($schedule->end_time)->format('h:i A')); ?>

                    </div>
                    <div class="col-md-3 mb-2"><strong>Type:</strong> <?php echo e(ucfirst($schedule->class_type)); ?></div>
                    <div class="col-md-3 mb-2"><strong>Status:</strong>
                        <?php if($schedule->is_active): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-12 mt-2">
                        <strong>Notes:</strong>
                        <div class="text-muted"><?php echo e($schedule->notes ?: '—'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\admin\schedules\show.blade.php ENDPATH**/ ?>