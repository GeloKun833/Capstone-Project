<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
        <div class="content container-fluid dir-page">
            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <h3 class="page-title mb-1">Students</h3>
                        <p class="dir-subtitle">Browse student profiles in a card layout.</p>
                    </div>
                    <div class="col-auto text-end">
                        <ul class="breadcrumb justify-content-end mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">All Students</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="dir-card">
                <div class="dir-toolbar">
                    <div>
                        <h5 class="dir-toolbar-title"><?php echo e(!empty($showingArchived) ? 'Archived students' : 'Active students'); ?></h5>
                        <span class="dir-count mt-1"><?php echo e(method_exists($studentList, 'total') ? $studentList->total() : $studentList->count()); ?> records</span>
                    </div>
                    <div class="dir-actions">
                        <div class="dir-toggle" role="group" aria-label="View">
                            <a href="<?php echo e(route('student/list')); ?><?php echo e(!empty($showingArchived) ? '?archived=1' : ''); ?>" title="List view"><i class="fa fa-list"></i></a>
                            <a href="<?php echo e(route('student/grid')); ?><?php echo e(!empty($showingArchived) ? '?archived=1' : ''); ?>" class="is-active" title="Grid view"><i class="fa fa-th"></i></a>
                        </div>
                    </div>
                </div>

                <div class="dir-grid">
                    <div class="row g-3">
                        <?php $__empty_1 = true; $__currentLoopData = $studentList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $sName = trim(($list->first_name ?? '').' '.($list->last_name ?? ''));
                                $sPhoto = $list->photoUrl();
                                $klass = trim(($list->year_level ?: $list->class).' '.($list->section ?? ''));
                            ?>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="dir-grid-card">
                                    <?php if(!empty($list->user_id)): ?>
                                        <a href="<?php echo e(route('student.sis', $list->user_id)); ?>">
                                            <img src="<?php echo e($sPhoto); ?>" alt="<?php echo e($sName); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                                        </a>
                                        <h5><a href="<?php echo e(route('student.sis', $list->user_id)); ?>"><?php echo e($sName ?: 'Student'); ?></a></h5>
                                    <?php else: ?>
                                        <img src="<?php echo e($sPhoto); ?>" alt="<?php echo e($sName); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                                        <h5><?php echo e($sName ?: 'Student'); ?></h5>
                                    <?php endif; ?>
                                    <div class="dir-person-meta"><?php echo e($klass ?: 'Student'); ?></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12">
                                <div class="dir-empty">
                                    <div><i class="fas fa-user-graduate"></i></div>
                                    <strong>No students found</strong>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if(method_exists($studentList, 'links')): ?>
                        <div class="d-flex justify-content-center mt-3"><?php echo e($studentList->links()); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914b">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\student\student-grid.blade.php ENDPATH**/ ?>