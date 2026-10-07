<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
        <div class="content container-fluid dir-page">
            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <h3 class="page-title mb-1">Teachers</h3>
                        <p class="dir-subtitle">Browse teacher profiles in a card layout.</p>
                    </div>
                    <div class="col-auto text-end">
                        <ul class="breadcrumb justify-content-end mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">All Teachers</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="dir-card">
                <div class="dir-toolbar">
                    <div>
                        <h5 class="dir-toolbar-title">Teacher grid</h5>
                        <span class="dir-count mt-1"><?php echo e(method_exists($teacherGrid, 'total') ? $teacherGrid->total() : $teacherGrid->count()); ?> records</span>
                    </div>
                    <div class="dir-actions">
                        <div class="dir-toggle" role="group" aria-label="View">
                            <a href="<?php echo e(route('teacher/list/page')); ?>" title="List view"><i class="fa fa-list"></i></a>
                            <a href="<?php echo e(route('teacher/grid/page')); ?>" class="is-active" title="Grid view"><i class="fa fa-th"></i></a>
                        </div>
                    </div>
                </div>

                <div class="dir-grid">
                    <div class="row g-3">
                        <?php $__empty_1 = true; $__currentLoopData = $teacherGrid; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $tName = $list->full_name ?: (optional($list->user)->name ?? 'Teacher');
                                $tPhoto = \App\Support\AvatarUploader::url(optional($list->user)->avatar ?? $list->avatar ?? null);
                            ?>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="dir-grid-card">
                                    <a href="<?php echo e(url('teacher/sis/'.$list->user_id)); ?>">
                                        <img src="<?php echo e($tPhoto); ?>" alt="<?php echo e($tName); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                                    </a>
                                    <h5><a href="<?php echo e(url('teacher/sis/'.$list->user_id)); ?>"><?php echo e($tName); ?></a></h5>
                                    <div class="dir-person-meta">Teacher</div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12">
                                <div class="dir-empty">
                                    <div><i class="fas fa-chalkboard-teacher"></i></div>
                                    <strong>No teachers found</strong>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if(method_exists($teacherGrid, 'links')): ?>
                        <div class="d-flex justify-content-center mt-3"><?php echo e($teacherGrid->links()); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914b">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/teacher/teachers-grid.blade.php ENDPATH**/ ?>