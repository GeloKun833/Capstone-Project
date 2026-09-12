<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Student Promotion History</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('promotions.index')); ?>">Promotions</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('promotions.history')); ?>">History</a></li>
                        <li class="breadcrumb-item active"><?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?></li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('promotions.history')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to History
                    </a>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Student</p>
                        <h5 class="mb-0"><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></h5>
                        <small class="text-muted"><?php echo e($student->admission_id ?? 'STU-'.$student->id); ?></small>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Current Grade</p>
                        <h5 class="mb-0"><?php echo e($student->year_level ?? '—'); ?></h5>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Section</p>
                        <h5 class="mb-0"><?php echo e($student->section ?? '—'); ?></h5>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Enrollment Status</p>
                        <span class="badge bg-<?php echo e($student->enrollment_status === 'graduated' ? 'primary' : ($student->enrollment_status === 'active' ? 'success' : 'secondary')); ?>">
                            <?php echo e(ucfirst($student->enrollment_status ?? 'unknown')); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Promotion Records (<?php echo e($student->promotions->count()); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if($student->promotions->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Academic Year</th>
                                    <th>GPA</th>
                                    <th>Status</th>
                                    <th>Promoted By</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $student->promotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(optional($promotion->promotion_date)->format('M d, Y') ?? '—'); ?></td>
                                        <td><?php echo e($promotion->from_year_level); ?></td>
                                        <td><?php echo e($promotion->to_year_level); ?></td>
                                        <td>
                                            <?php echo e($promotion->fromAcademicYear->name ?? 'N/A'); ?>

                                            <i class="fas fa-arrow-right"></i>
                                            <?php echo e($promotion->toAcademicYear->name ?? 'N/A'); ?>

                                        </td>
                                        <td>
                                            <?php if($promotion->final_gpa): ?>
                                                <?php echo e(number_format($promotion->final_gpa, 2)); ?>

                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo e($promotion->statusBadge); ?>">
                                                <?php echo e(ucfirst($promotion->promotion_status)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($promotion->promoter->name ?? 'Unknown'); ?></td>
                                        <td><?php echo e($promotion->remarks ?: '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No Promotion Records</h5>
                        <p class="text-muted mb-0">This student has not been promoted yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\promotions\student-history.blade.php ENDPATH**/ ?>