<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grade Encoding Access</h3>
                    <p class="dir-subtitle">Choose who may encode grades, and which grade levels are open for encoding.</p>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="dir-card mb-4">
            <div class="p-3 p-md-4">
                <h5 class="mb-1">Open grade levels</h5>
                <p class="text-muted small">Teachers can encode grades only for the levels you leave checked. Nursery and Kindergarten use descriptive letters. Grade 1 through Grade 3 also use letters. Grade 4 through Grade 10 use scores from 0 to 100.</p>
                <form method="POST" action="<?php echo e(route('admin.grade-encoding.levels')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="row g-2">
                        <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $checked = $openLevels === null || in_array($grade, $openLevels, true);
                            ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <label class="d-flex align-items-center gap-2 mb-0">
                                    <input type="checkbox" name="grade_levels[]" value="<?php echo e($grade); ?>" <?php echo e($checked ? 'checked' : ''); ?>>
                                    <span><?php echo e($grade); ?></span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <button type="submit" class="btn btn-primary dir-btn mt-3">Save grade levels</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Account</th>
                        <th>Grade Encoding Access</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $allowed = $user->permissions->contains('name', $permission); ?>
                        <tr>
                            <td><?php echo e($user->name); ?></td>
                            <td><?php echo e($user->role_name); ?></td>
                            <td><?php echo e($user->isActiveAccount() ? 'Active' : 'Inactive'); ?></td>
                            <td><?php echo e($allowed ? 'Allowed' : 'Not Allowed'); ?></td>
                            <td class="text-end">
                                <form method="POST" action="<?php echo e(route('admin.grade-encoding.update', $user)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="allow" value="<?php echo e($allowed ? 0 : 1); ?>">
                                    <button type="submit" class="btn btn-sm <?php echo e($allowed ? 'btn-outline-danger' : 'btn-primary'); ?>">
                                        <?php echo e($allowed ? 'Revoke' : 'Allow'); ?>

                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5">No teachers or registrars are available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914m">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/admin/grade-encoding/index.blade.php ENDPATH**/ ?>