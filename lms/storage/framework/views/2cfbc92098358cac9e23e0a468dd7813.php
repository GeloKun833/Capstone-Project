
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Class Schedules</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Class Schedules</li>
                    </ul>
                </div>
                <div class="col-auto text-end">
                    <a href="<?php echo e(route('admin.schedules.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Schedule
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('admin.schedules.index')); ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Section</label>
                                <select name="section_id" class="form-control">
                                    <option value="">All Sections</option>
                                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($section->id); ?>" <?php echo e(request('section_id') == $section->id ? 'selected' : ''); ?>>
                                            <?php echo e($section->name); ?><?php if($section->grade_level): ?> (<?php echo e($section->grade_level); ?>)<?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Teacher</label>
                                <select name="teacher_id" class="form-control">
                                    <option value="">All Teachers</option>
                                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teacher->id); ?>" <?php echo e(request('teacher_id') == $teacher->id ? 'selected' : ''); ?>>
                                            <?php echo e($teacher->full_name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Day of Week</label>
                                <select name="day_of_week" class="form-control">
                                    <option value="">All Days</option>
                                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($day); ?>" <?php echo e(request('day_of_week') == $day ? 'selected' : ''); ?>>
                                            <?php echo e(ucfirst($day)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary form-control">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedules Table -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0 datatable table-striped">
                                <thead class="student-thread">
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
                                                <span class="badge bg-primary"><?php echo e(ucfirst($schedule->day_of_week)); ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo e(Carbon\Carbon::parse($schedule->start_time)->format('h:i A')); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo e(Carbon\Carbon::parse($schedule->end_time)->format('h:i A')); ?></small>
                                            </td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <span style="width: 30px; height: 30px; border-radius: 50%; background: <?php echo e($schedule->color); ?>; display: inline-block;"></span>
                                                    <a><?php echo e($schedule->subject->subject_name ?? 'N/A'); ?></a>
                                                </h2>
                                                <?php if($schedule->subject?->class): ?>
                                                    <small class="text-muted"><?php echo e($schedule->subject->class); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><?php echo e($schedule->section->name ?? 'N/A'); ?></div>
                                                <?php if($schedule->section?->grade_level): ?>
                                                    <small class="text-muted"><?php echo e($schedule->section->grade_level); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($schedule->teacher->full_name ?? 'N/A'); ?></td>
                                            <td><?php echo e($schedule->room->room_name ?? 'TBD'); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo e(ucfirst($schedule->class_type ?? 'lecture')); ?></span>
                                            </td>
                                            <td>
                                                <?php if($schedule->is_active): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="actions">
                                                    <a href="<?php echo e(route('admin.schedules.edit', $schedule)); ?>" class="btn btn-sm bg-success-light me-2">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                    <form action="<?php echo e(route('admin.schedules.destroy', $schedule)); ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-sm bg-danger-light">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="mb-3">
                                                    <i class="fas fa-calendar-alt text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                                </div>
                                                <h5 class="text-muted">No class schedules found</h5>
                                                <p class="text-muted">Create your first class schedule to get started.</p>
                                                <a href="<?php echo e(route('admin.schedules.create')); ?>" class="btn btn-primary mt-3">
                                                    <i class="fas fa-plus"></i> Create Schedule
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if($schedules->hasPages()): ?>
                            <div class="mt-3">
                                <?php echo e($schedules->links()); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\admin\schedules\index.blade.php ENDPATH**/ ?>