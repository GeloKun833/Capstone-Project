<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><i class="fas fa-home me-2"></i>Parent Portal</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Children</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if($children->isEmpty()): ?>
            <div class="card card-table comman-shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                    <h4>No children linked yet</h4>
                    <p class="text-muted mb-0">Your account email must match the parent email on your child's student record. Contact the registrar if you need help.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card card-table comman-shadow h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:56px;height:56px;">
                                        <?php echo e(strtoupper(substr($child->first_name, 0, 1))); ?>

                                    </div>
                                    <div>
                                        <h5 class="mb-0"><?php echo e($child->full_name); ?></h5>
                                        <small class="text-muted"><?php echo e($child->year_level ?? 'Student'); ?></small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-envelope me-1"></i> <?php echo e($child->email ?? 'No email'); ?>

                                </p>
                                <a href="<?php echo e(route('parent.child.hub', ['childId' => $child->id, 'tab' => 'overview'])); ?>" class="btn btn-primary w-100">
                                    Open Child Portal
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\parent\index.blade.php ENDPATH**/ ?>