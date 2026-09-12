
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Assignment Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('assignments.index')); ?>">Assignments</a></li>
                        <li class="breadcrumb-item active"><?php echo e($assignment->title); ?></li>
                    </ul>
                </div>
                <div class="col-auto text-right float-right ml-auto">
                    <div class="btn-group" role="group">
                        <a href="<?php echo e(route('assignments.edit', $assignment->id)); ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?php echo e(route('assignments.submissions', $assignment->id)); ?>" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Grade Submissions
                        </a>
                        <a href="<?php echo e(route('assignments.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Assignment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Title</h6>
                                <p class="fw-bold"><?php echo e($assignment->title); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                <?php switch($assignment->status):
                                    case ('draft'): ?>
                                        <span class="badge bg-warning">Draft</span>
                                        <?php break; ?>
                                    <?php case ('published'): ?>
                                        <span class="badge bg-success">Published</span>
                                        <?php break; ?>
                                    <?php case ('closed'): ?>
                                        <span class="badge bg-danger">Closed</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-secondary"><?php echo e(ucfirst($assignment->status)); ?></span>
                                <?php endswitch; ?>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Subject</h6>
                                <p class="fw-bold"><?php echo e($assignment->subject->name ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Section</h6>
                                <p class="fw-bold"><?php echo e($assignment->section->name ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Academic Year</h6>
                                <p class="fw-bold"><?php echo e($assignment->academicYear->name ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Semester</h6>
                                <p class="fw-bold"><?php echo e($assignment->semester->name ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Due Date</h6>
                                <p class="fw-bold">
                                    <?php if($assignment->due_date): ?>
                                        <?php echo e(\Carbon\Carbon::parse($assignment->due_date)->format('M d, Y')); ?>

                                        <?php if($assignment->due_time): ?>
                                            at <?php echo e($assignment->due_time); ?>

                                        <?php endif; ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Maximum Score</h6>
                                <p class="fw-bold"><?php echo e($assignment->max_score); ?></p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted">Description</h6>
                            <p><?php echo e($assignment->description); ?></p>
                        </div>

                        <?php if($assignment->submission_instructions): ?>
                            <div class="mb-4">
                                <h6 class="text-muted">Submission Instructions</h6>
                                <p><?php echo e($assignment->submission_instructions); ?></p>
                            </div>
                        <?php endif; ?>

                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Late Submissions</h6>
                                <p class="fw-bold">
                                    <?php if($assignment->allows_late_submission): ?>
                                        <span class="text-success">Allowed</span>
                                        <?php if($assignment->late_submission_penalty): ?>
                                            <br><small class="text-muted">Penalty: <?php echo e($assignment->late_submission_penalty); ?>%</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-danger">Not Allowed</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">File Upload</h6>
                                <p class="fw-bold">
                                    <?php if($assignment->requires_file_upload): ?>
                                        <span class="text-success">Required</span>
                                        <br><small class="text-muted">Allowed: <?php echo e($assignment->submissionAllowedLabels()); ?></small>
                                        <?php if($assignment->max_file_size): ?>
                                            <br><small class="text-muted">Max size: <?php echo e($assignment->max_file_size); ?>MB</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Optional / all common types</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Quick Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="mb-3">
                                    <h4 class="text-primary"><?php echo e($assignment->submissions()->count()); ?></h4>
                                    <small class="text-muted">Total Submissions</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <h4 class="text-success"><?php echo e($assignment->submissions()->where('status', 'graded')->count()); ?></h4>
                                    <small class="text-muted">Graded</small>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="mb-3">
                                    <h4 class="text-warning"><?php echo e($assignment->submissions()->where('status', 'submitted')->count()); ?></h4>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <h4 class="text-danger"><?php echo e($assignment->submissions()->where('status', 'late')->count()); ?></h4>
                                    <small class="text-muted">Late</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if($assignment->status === 'draft'): ?>
                                <form action="<?php echo e(route('assignments.publish', $assignment->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-publish me-2"></i>Publish Assignment
                                    </button>
                                </form>
                            <?php elseif($assignment->status === 'published'): ?>
                                <form action="<?php echo e(route('assignments.close', $assignment->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-lock me-2"></i>Close Assignment
                                    </button>
                                </form>
                            <?php endif; ?>

                            <a href="<?php echo e(route('assignments.submissions', $assignment->id)); ?>" class="btn btn-primary">
                                <i class="fas fa-check-circle me-2"></i>Grade Submissions
                            </a>

                            <a href="<?php echo e(route('assignments.edit', $assignment->id)); ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit Assignment
                            </a>

                            <form action="<?php echo e(route('assignments.destroy', $assignment->id)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger w-100" 
                                        onclick="return confirm('Are you sure you want to delete this assignment? This action cannot be undone.')">
                                    <i class="fas fa-trash me-2"></i>Delete Assignment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Export</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo e(route('assignments.export-pdf', $assignment->id)); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                            </a>
                            <a href="<?php echo e(route('assignments.export-excel')); ?>" class="btn btn-outline-success">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\assignments\show.blade.php ENDPATH**/ ?>