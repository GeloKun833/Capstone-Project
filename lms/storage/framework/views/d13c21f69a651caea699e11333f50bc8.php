
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">My Assignments</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Assignments</li>
                    </ul>
                </div>
            </div>
        </div>

        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('student.assignments.index')); ?>">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <select name="subject_id" class="form-control">
                                    <option value="">All Subjects</option>
                                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($subject->id); ?>" <?php echo e(request('subject_id') == $subject->id ? 'selected' : ''); ?>>
                                            <?php echo e($subject->subject_name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">All Assignments</option>
                                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Not Submitted</option>
                                    <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Submitted</option>
                                    <option value="due_soon" <?php echo e(request('status') == 'due_soon' ? 'selected' : ''); ?>>Due Soon</option>
                                    <option value="overdue" <?php echo e(request('status') == 'overdue' ? 'selected' : ''); ?>>Overdue</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <button type="submit" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <a href="<?php echo e(route('student.assignments.index')); ?>" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-redo"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="row">
            <div class="col-md-12">
                <?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $submission = $assignment->submissions()->where('student_id', auth()->user()->student->id)->first();
                        $isSubmitted = $submission !== null;
                        $isOverdue = !$isSubmitted && now() > $assignment->dueDateTime;
                        $isDueSoon = !$isSubmitted && now()->diffInHours($assignment->dueDateTime) <= 24 && now() < $assignment->dueDateTime;
                    ?>
                    
                    <div class="card mb-3 <?php echo e($isOverdue ? 'border-danger' : ($isDueSoon ? 'border-warning' : '')); ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="card-title mb-0 me-3">
                                            <a href="<?php echo e(route('student.assignments.show', $assignment->id)); ?>">
                                                <?php echo e($assignment->title); ?>

                                            </a>
                                        </h5>
                                        
                                        <?php if($isSubmitted): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Submitted
                                            </span>
                                        <?php elseif($isOverdue): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Overdue
                                            </span>
                                        <?php elseif($isDueSoon): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Due Soon
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-hourglass-half"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="text-muted mb-2"><?php echo e(Str::limit($assignment->description, 150)); ?></p>
                                    
                                    <div class="row text-muted small">
                                        <div class="col-md-6">
                                            <i class="fas fa-book"></i> <strong>Subject:</strong> <?php echo e($assignment->subject->subject_name ?? 'N/A'); ?><br>
                                            <i class="fas fa-user"></i> <strong>Teacher:</strong> <?php echo e($assignment->teacher->full_name ?? 'N/A'); ?>

                                        </div>
                                        <div class="col-md-6">
                                            <i class="fas fa-calendar"></i> <strong>Due:</strong> <?php echo e($assignment->dueDateTime->format('M d, Y h:i A')); ?><br>
                                            <i class="fas fa-star"></i> <strong>Points:</strong> <?php echo e($assignment->max_score); ?>

                                        </div>
                                    </div>
                                    
                                    <?php if($isSubmitted): ?>
                                        <div class="mt-2">
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-calendar-check"></i> Submitted: <?php echo e($submission->submitted_at->format('M d, Y h:i A')); ?>

                                            </span>
                                            <?php if($submission->score !== null): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Score: <?php echo e($submission->score); ?>/<?php echo e($submission->max_score); ?>

                                                </span>
                                            <?php endif; ?>
                                            <?php if($submission->is_late): ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock"></i> Late Submission
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="ms-3">
                                    <a href="<?php echo e(route('student.assignments.show', $assignment->id)); ?>" class="btn btn-primary">
                                        <?php if($isSubmitted): ?>
                                            <i class="fas fa-eye"></i> View
                                        <?php else: ?>
                                            <i class="fas fa-upload"></i> Submit
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h5>No Assignments Found</h5>
                            <p class="text-muted">You don't have any assignments at the moment. Check back later!</p>
                        </div>
                    </div>
                <?php endif; ?>

                
                <?php if($assignments->hasPages()): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($assignments->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\student\assignments\index.blade.php ENDPATH**/ ?>