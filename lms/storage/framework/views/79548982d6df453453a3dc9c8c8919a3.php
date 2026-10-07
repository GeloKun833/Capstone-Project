<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grade Encoding Access</h3>
                    <p class="dir-subtitle">Choose who may encode and update grades. This does not change the user's role.</p>
                </div>
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