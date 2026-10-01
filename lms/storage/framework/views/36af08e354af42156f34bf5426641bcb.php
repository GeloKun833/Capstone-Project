<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Schedule Details</h3>
                    <p class="dir-subtitle">Weekly class time and assignment.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.schedules.index')); ?>">Schedules</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ul>
                    <a href="<?php echo e(route('admin.schedules.edit', $schedule)); ?>" class="btn btn-primary dir-btn">
                        <i class="fas fa-pen me-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Class information</h5>
                    <span class="dir-count mt-1"><?php echo e(ucfirst($schedule->day_of_week)); ?></span>
                </div>
            </div>
            <div class="p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="dir-person-meta">Teacher</div>
                        <div class="dir-person-name"><?php echo e($schedule->teacher->full_name ?? 'N/A'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="dir-person-meta">Section</div>
                        <div class="dir-person-name"><?php echo e($schedule->section->name ?? 'N/A'); ?></div>
                        <?php if($schedule->section): ?>
                            <span class="dir-person-meta"><?php echo e($schedule->section->grade_level); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <div class="dir-person-meta">Subject</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="dir-dot" style="background: <?php echo e($schedule->color ?: '#3d5ee1'); ?>;"></span>
                            <span class="dir-person-name"><?php echo e($schedule->subject->subject_name ?? 'N/A'); ?></span>
                        </div>
                        <?php if($schedule->subject?->class): ?>
                            <span class="dir-person-meta"><?php echo e($schedule->subject->class); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <div class="dir-person-meta">Room</div>
                        <div><?php echo e($schedule->room->room_name ?? '—'); ?></div>
                    </div>
                </div>

                <h5 class="dir-toolbar-title mb-3">Schedule details</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="dir-person-meta">Day</div>
                        <span class="dir-day"><?php echo e(ucfirst($schedule->day_of_week)); ?></span>
                    </div>
                    <div class="col-md-3">
                        <div class="dir-person-meta">Time</div>
                        <div class="dir-time">
                            <?php echo e(\Carbon\Carbon::parse($schedule->start_time)->format('h:i A')); ?>

                            –
                            <?php echo e(\Carbon\Carbon::parse($schedule->end_time)->format('h:i A')); ?>

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dir-person-meta">Type</div>
                        <span class="dir-chip"><?php echo e(ucfirst($schedule->class_type)); ?></span>
                    </div>
                    <div class="col-md-3">
                        <div class="dir-person-meta">Status</div>
                        <?php if($schedule->is_active): ?>
                            <span class="dir-badge dir-badge--active">Active</span>
                        <?php else: ?>
                            <span class="dir-badge dir-badge--disabled">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <div class="dir-person-meta">Notes</div>
                        <div><?php echo e($schedule->notes ?: '—'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914c">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\admin\schedules\show.blade.php ENDPATH**/ ?>