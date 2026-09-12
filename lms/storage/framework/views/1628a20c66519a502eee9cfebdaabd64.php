<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">Database Backup & Recovery</h3></div>
                <div class="col-auto">
                    <form action="<?php echo e(route('admin.backup.create')); ?>" method="POST"><?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-database"></i> Create Backup Now</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-muted">Backups are stored securely on the server. Download and store copies off-site for disaster recovery.</p>
                <table class="table table-hover">
                    <thead><tr><th>Filename</th><th>Size</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($backup['name']); ?></td>
                                <td><?php echo e($backup['size']); ?></td>
                                <td><?php echo e($backup['date']); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.backup.download', $backup['name'])); ?>" class="btn btn-sm btn-info">Download</a>
                                    <form action="<?php echo e(route('admin.backup.destroy', $backup['name'])); ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this backup?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-center">No backups yet. Click "Create Backup Now" to generate one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\admin\backup\index.blade.php ENDPATH**/ ?>