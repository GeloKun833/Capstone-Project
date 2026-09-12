<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Promotion History</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('promotions.index')); ?>">Promotions</a></li>
                        <li class="breadcrumb-item active">History</li>
                    </ul>
                </div>
                <div class="col-auto text-end float-end ms-auto download-grp">
                    <a href="<?php echo e(route('promotions.index')); ?>" class="btn btn-primary">
                        <i class="fas fa-graduation-cap"></i> New Promotion
                    </a>
                </div>
            </div>
        </div>

        
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Promotions</h6>
                                <h3 class="mb-0"><?php echo e(number_format($stats['total'])); ?></h3>
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Promoted</h6>
                                <h3 class="mb-0 text-success"><?php echo e(number_format($stats['promoted'])); ?></h3>
                            </div>
                            <div>
                                <i class="fas fa-arrow-up fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Retained</h6>
                                <h3 class="mb-0 text-warning"><?php echo e(number_format($stats['retained'])); ?></h3>
                            </div>
                            <div>
                                <i class="fas fa-redo fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Graduated</h6>
                                <h3 class="mb-0 text-info"><?php echo e(number_format($stats['graduated'])); ?></h3>
                            </div>
                            <div>
                                <i class="fas fa-user-graduate fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('promotions.history')); ?>">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Search student name..." value="<?php echo e(request('search')); ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="academic_year_id" class="form-control">
                                    <option value="">All Academic Years</option>
                                    <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($year->id); ?>" <?php echo e(request('academic_year_id') == $year->id ? 'selected' : ''); ?>>
                                            <?php echo e($year->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="from_grade" class="form-control">
                                    <option value="">All Grade Levels</option>
                                    <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($grade); ?>" <?php echo e(request('from_grade') == $grade ? 'selected' : ''); ?>>
                                            <?php echo e($grade); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="promotion_status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="promoted" <?php echo e(request('promotion_status') == 'promoted' ? 'selected' : ''); ?>>Promoted</option>
                                    <option value="retained" <?php echo e(request('promotion_status') == 'retained' ? 'selected' : ''); ?>>Retained</option>
                                    <option value="graduated" <?php echo e(request('promotion_status') == 'graduated' ? 'selected' : ''); ?>>Graduated</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <button type="submit" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <a href="<?php echo e(route('promotions.history')); ?>" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-redo"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="card">
            <div class="card-body">
                <?php if($promotions->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>From Grade</th>
                                    <th>To Grade</th>
                                    <th>Academic Year</th>
                                    <th>GPA</th>
                                    <th>Status</th>
                                    <th>Promoted By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $promotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($promotion->promotion_date->format('M d, Y')); ?></td>
                                        <td>
                                            <strong><?php echo e($promotion->student->first_name); ?> <?php echo e($promotion->student->last_name); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo e($promotion->student->admission_id ?? 'STU-' . $promotion->student->id); ?></small>
                                        </td>
                                        <td><?php echo e($promotion->from_year_level); ?></td>
                                        <td><?php echo e($promotion->to_year_level); ?></td>
                                        <td>
                                            <?php echo e($promotion->fromAcademicYear->name ?? 'N/A'); ?> 
                                            <i class="fas fa-arrow-right"></i> 
                                            <?php echo e($promotion->toAcademicYear->name ?? 'N/A'); ?>

                                        </td>
                                        <td>
                                            <?php if($promotion->final_gpa): ?>
                                                <span class="badge bg-<?php echo e($promotion->final_gpa >= 3.0 ? 'success' : ($promotion->final_gpa >= 2.0 ? 'warning' : 'danger')); ?>">
                                                    <?php echo e(number_format($promotion->final_gpa, 2)); ?>

                                                </span>
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
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php if($promotion->remarks): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#remarksModal<?php echo e($promotion->id); ?>">
                                                                <i class="fas fa-comment"></i> View Remarks
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <form action="<?php echo e(route('promotions.destroy', $promotion->id)); ?>" method="POST" 
                                                              onsubmit="return confirm('Are you sure? This will rollback the student to their previous grade level.')">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-undo"></i> Rollback
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>

                                            
                                            <?php if($promotion->remarks): ?>
                                                <div class="modal fade" id="remarksModal<?php echo e($promotion->id); ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Promotion Remarks</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Student:</strong> <?php echo e($promotion->student->first_name); ?> <?php echo e($promotion->student->last_name); ?></p>
                                                                <p><strong>Promotion:</strong> <?php echo e($promotion->from_year_level); ?> → <?php echo e($promotion->to_year_level); ?></p>
                                                                <hr>
                                                                <p><?php echo e($promotion->remarks); ?></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <?php if($promotions->hasPages()): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <?php echo e($promotions->links()); ?>

                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No Promotion Records Found</h5>
                        <p class="text-muted">No promotion history available with the current filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\promotions\history.blade.php ENDPATH**/ ?>