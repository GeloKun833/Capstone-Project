
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">My Submission</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('student.assignments.index')); ?>">Assignments</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('student.assignments.show', $assignment->id)); ?>"><?php echo e($assignment->title); ?></a></li>
                        <li class="breadcrumb-item active">Submission</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('student.assignments.show', $assignment->id)); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Assignment
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-file-alt"></i> Submission Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Submitted Successfully!</strong>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-box p-3 bg-light rounded mb-3">
                                    <p class="mb-2"><strong><i class="fas fa-calendar-check text-success"></i> Submitted On:</strong></p>
                                    <h6 class="text-primary"><?php echo e($submission->submitted_at->format('M d, Y h:i A')); ?></h6>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-box p-3 bg-light rounded mb-3">
                                    <p class="mb-2"><strong><i class="fas fa-calendar-alt text-info"></i> Due Date:</strong></p>
                                    <h6 class="text-muted"><?php echo e($assignment->dueDateTime->format('M d, Y h:i A')); ?></h6>
                                </div>
                            </div>

                            <?php if($submission->is_late): ?>
                                <div class="col-md-12">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Late Submission</strong>
                                        <p class="mb-0">Submitted <?php echo e($submission->late_minutes); ?> minutes after the deadline.</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-md-12">
                                    <div class="alert alert-success">
                                        <i class="fas fa-check me-2"></i>
                                        <strong>Submitted On Time!</strong>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="col-md-12">
                                <h6 class="mb-2"><i class="fas fa-file"></i> Submitted File:</h6>
                                <div class="d-grid">
                                    <a href="<?php echo e(Storage::url($submission->file_path)); ?>" target="_blank" class="btn btn-primary">
                                        <i class="fas fa-download me-2"></i> Download: <?php echo e($submission->file_name); ?>

                                    </a>
                                </div>
                            </div>

                            <?php if($submission->comments): ?>
                                <div class="col-md-12 mt-3">
                                    <h6 class="mb-2"><i class="fas fa-comment"></i> Your Comments:</h6>
                                    <div class="p-3 bg-light rounded">
                                        <?php echo e($submission->comments); ?>

                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Assignment Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2"><?php echo e($assignment->title); ?></h6>
                        <p class="text-muted mb-3"><?php echo e($assignment->description); ?></p>
                        
                        <div class="row text-muted small">
                            <div class="col-6">
                                <p class="mb-1"><strong>Subject:</strong> <?php echo e($assignment->subject->subject_name ?? 'N/A'); ?></p>
                                <p class="mb-1"><strong>Teacher:</strong> <?php echo e($assignment->teacher->full_name ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>Section:</strong> <?php echo e($assignment->section->name ?? 'N/A'); ?></p>
                                <p class="mb-1"><strong>Max Score:</strong> <?php echo e($assignment->max_score); ?> points</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-chart-line"></i> Grading Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if($submission->score !== null): ?>
                            <div class="text-center mb-3">
                                <div class="score-circle bg-<?php echo e($submission->score >= ($submission->max_score * 0.75) ? 'success' : ($submission->score >= ($submission->max_score * 0.5) ? 'warning' : 'danger')); ?> text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                     style="width: 120px; height: 120px;">
                                    <div>
                                        <h2 class="mb-0"><?php echo e($submission->score); ?></h2>
                                        <small>/ <?php echo e($submission->max_score); ?></small>
                                    </div>
                                </div>
                                <h6 class="mt-3">
                                    <?php echo e(number_format(($submission->score / $submission->max_score) * 100, 1)); ?>%
                                </h6>
                            </div>

                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Graded!</strong>
                                <p class="mb-0 mt-1 small">Graded on: <?php echo e($submission->graded_at->format('M d, Y')); ?></p>
                            </div>

                            <?php if($submission->teacher_feedback): ?>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="mb-2"><i class="fas fa-comment-dots text-info"></i> Teacher Feedback:</h6>
                                        <p class="mb-0"><?php echo e($submission->teacher_feedback); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-hourglass-half fa-3x text-warning mb-3"></i>
                                <h6>Awaiting Grading</h6>
                                <p class="text-muted small">Your teacher will grade this assignment soon. Check back later for your score and feedback.</p>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <div class="info-box">
                            <p class="mb-2 small">
                                <strong><i class="fas fa-info-circle"></i> Submission Status:</strong>
                                <span class="badge bg-<?php echo e($submission->status === 'graded' ? 'success' : ($submission->status === 'late' ? 'warning' : 'info')); ?> ms-2">
                                    <?php echo e(ucfirst($submission->status)); ?>

                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history"></i> Timeline
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="timeline-list">
                            <li class="timeline-item">
                                <i class="fas fa-calendar-plus text-primary"></i>
                                <div class="timeline-content">
                                    <h6>Assignment Created</h6>
                                    <small class="text-muted"><?php echo e($assignment->created_at->format('M d, Y')); ?></small>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <i class="fas fa-upload text-success"></i>
                                <div class="timeline-content">
                                    <h6>You Submitted</h6>
                                    <small class="text-muted"><?php echo e($submission->submitted_at->format('M d, Y h:i A')); ?></small>
                                </div>
                            </li>
                            <?php if($submission->graded_at): ?>
                                <li class="timeline-item">
                                    <i class="fas fa-check-circle text-info"></i>
                                    <div class="timeline-content">
                                        <h6>Graded</h6>
                                        <small class="text-muted"><?php echo e($submission->graded_at->format('M d, Y')); ?></small>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.timeline-list {
    list-style: none;
    padding-left: 0;
    position: relative;
}

.timeline-list::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    padding-left: 40px;
    position: relative;
    margin-bottom: 20px;
}

.timeline-item i {
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    z-index: 1;
}

.timeline-content h6 {
    margin-bottom: 2px;
    font-size: 14px;
}

.score-circle {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
</style>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\student\assignments\submission.blade.php ENDPATH**/ ?>