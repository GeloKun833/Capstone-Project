
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Class Schedules</h3>
                    <p class="dir-subtitle">Assign class times by teacher, section, and subject.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Class Schedules</li>
                    </ul>
                    <a href="<?php echo e(route('admin.schedules.create')); ?>" class="btn btn-primary dir-btn">
                        <i class="fas fa-plus me-1"></i> Create Schedule
                    </a>
                </div>
            </div>
        </div>

        <div class="dir-card dir-filters">
            <form method="GET" action="<?php echo e(route('admin.schedules.index')); ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-control">
                            <option value="">All Sections</option>
                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>" <?php echo e(request('section_id') == $section->id ? 'selected' : ''); ?>>
                                    <?php echo e($section->name); ?><?php if($section->grade_level): ?> (<?php echo e($section->grade_level); ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-control">
                            <option value="">All Teachers</option>
                            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teacher->id); ?>" <?php echo e(request('teacher_id') == $teacher->id ? 'selected' : ''); ?>>
                                    <?php echo e($teacher->full_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Day of Week</label>
                        <select name="day_of_week" class="form-control">
                            <option value="">All Days</option>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($day); ?>" <?php echo e(request('day_of_week') == $day ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($day)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 pb-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="<?php echo e(route('admin.schedules.index')); ?>" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Weekly class times</h5>
                    <span class="dir-count mt-1"><?php echo e($schedules->total()); ?> schedule<?php echo e($schedules->total() === 1 ? '' : 's'); ?></span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table dir-table mb-0">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Section</th>
                            <th>Teacher</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <span class="dir-day"><?php echo e(ucfirst($schedule->day_of_week)); ?></span>
                                </td>
                                <td>
                                    <div class="dir-time"><?php echo e(\Carbon\Carbon::parse($schedule->start_time)->format('h:i A')); ?></div>
                                    <span class="dir-person-meta"><?php echo e(\Carbon\Carbon::parse($schedule->end_time)->format('h:i A')); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="dir-dot" style="background: <?php echo e($schedule->color ?: '#3d5ee1'); ?>;"></span>
                                        <div>
                                            <span class="dir-person-name"><?php echo e($schedule->subject->subject_name ?? 'N/A'); ?></span>
                                            <?php if($schedule->subject?->class): ?>
                                                <span class="dir-person-meta"><?php echo e($schedule->subject->class); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo e($schedule->section->name ?? 'N/A'); ?></div>
                                    <?php if($schedule->section?->grade_level): ?>
                                        <span class="dir-person-meta"><?php echo e($schedule->section->grade_level); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($schedule->teacher->full_name ?? 'N/A'); ?></td>
                                <td><?php echo e($schedule->room->room_name ?? 'TBD'); ?></td>
                                <td>
                                    <span class="dir-chip"><?php echo e(ucfirst($schedule->class_type ?? 'lecture')); ?></span>
                                </td>
                                <td>
                                    <?php if($schedule->is_active): ?>
                                        <span class="dir-badge dir-badge--active">Active</span>
                                    <?php else: ?>
                                        <span class="dir-badge dir-badge--disabled">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?php echo e(route('admin.schedules.edit', $schedule)); ?>" class="dir-icon-btn" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form action="<?php echo e(route('admin.schedules.destroy', $schedule)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this class schedule?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="dir-icon-btn is-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="dir-empty">
                                        <i class="fas fa-clock d-block"></i>
                                        <h5 class="mt-2 mb-1">No class schedules found</h5>
                                        <p class="mb-3">Create a class schedule to show it on teacher, student, and parent calendars.</p>
                                        <a href="<?php echo e(route('admin.schedules.create')); ?>" class="btn btn-primary dir-btn">
                                            <i class="fas fa-plus me-1"></i> Create Schedule
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($schedules->hasPages()): ?>
                <div class="px-3 py-3">
                    <?php echo e($schedules->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914c">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/admin/schedules/index.blade.php ENDPATH**/ ?>