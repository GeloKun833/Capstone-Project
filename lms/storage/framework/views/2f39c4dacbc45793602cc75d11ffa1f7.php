
<div class="row">
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-user text-primary"></i> Student Name:</strong></p>
        <p class="text-muted"><?php echo e($submission->student->first_name); ?> <?php echo e($submission->student->last_name); ?></p>
    </div>
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-envelope text-primary"></i> Email:</strong></p>
        <p class="text-muted"><?php echo e($submission->student->email); ?></p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-calendar text-success"></i> Submitted On:</strong></p>
        <p class="text-muted"><?php echo e($submission->submitted_at->format('M d, Y h:i A')); ?></p>
    </div>
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-clock text-info"></i> Status:</strong></p>
        <p>
            <?php if($submission->isLate()): ?>
                <span class="badge bg-warning">Late</span>
            <?php else: ?>
                <span class="badge bg-success">On Time</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <p class="mb-1"><strong><i class="fas fa-file text-primary"></i> Submitted File:</strong></p>
        <div class="border p-3 rounded bg-light">
            <i class="fas fa-file-<?php echo e($submission->file_type); ?> me-2"></i>
            <a href="<?php echo e($submission->file_url); ?>" target="_blank" class="text-decoration-none">
                <?php echo e($submission->file_name); ?>

            </a>
            <a href="<?php echo e($submission->file_url); ?>" download class="btn btn-sm btn-primary ms-2">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
    </div>
</div>

<?php if($submission->comments): ?>
<div class="row">
    <div class="col-md-12 mb-3">
        <p class="mb-1"><strong><i class="fas fa-comment text-primary"></i> Student Comments:</strong></p>
        <div class="border p-3 rounded bg-light">
            <?php echo e($submission->comments); ?>

        </div>
    </div>
</div>
<?php endif; ?>

<?php if($submission->score): ?>
<div class="row">
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-star text-warning"></i> Score:</strong></p>
        <h5 class="text-success"><?php echo e($submission->score); ?> / <?php echo e($submission->max_score); ?></h5>
    </div>
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-percentage text-info"></i> Percentage:</strong></p>
        <h5 class="text-primary"><?php echo e($submission->percentage); ?>%</h5>
    </div>
</div>

<?php if($submission->teacher_feedback): ?>
<div class="row">
    <div class="col-md-12">
        <p class="mb-1"><strong><i class="fas fa-chalkboard-teacher text-primary"></i> Teacher Feedback:</strong></p>
        <div class="border p-3 rounded bg-light">
            <?php echo e($submission->teacher_feedback); ?>

        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\assignments\partials\submission-view.blade.php ENDPATH**/ ?>