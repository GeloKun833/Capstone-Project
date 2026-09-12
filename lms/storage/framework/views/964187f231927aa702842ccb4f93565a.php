
<div class="alert alert-info mb-3">
    <h6 class="mb-1"><i class="fas fa-user me-2"></i><?php echo e($submission->student->first_name); ?> <?php echo e($submission->student->last_name); ?></h6>
    <p class="mb-0 small">
        <i class="fas fa-calendar me-1"></i>Submitted: <?php echo e($submission->submitted_at->format('M d, Y h:i A')); ?>

        <?php if($submission->isLate()): ?>
            <span class="badge bg-warning ms-2">Late</span>
        <?php else: ?>
            <span class="badge bg-success ms-2">On Time</span>
        <?php endif; ?>
    </p>
</div>

<div class="mb-3">
    <label class="form-label"><i class="fas fa-file me-2"></i>Submitted File:</label>
    <div class="border p-2 rounded bg-light">
        <i class="fas fa-file-<?php echo e($submission->file_type); ?> me-2"></i>
        <a href="<?php echo e($submission->file_url); ?>" target="_blank" class="text-decoration-none">
            <?php echo e($submission->file_name); ?>

        </a>
        <a href="<?php echo e($submission->file_url); ?>" download class="btn btn-sm btn-primary ms-2">
            <i class="fas fa-download"></i> Download
        </a>
    </div>
</div>

<?php if($submission->comments): ?>
<div class="mb-3">
    <label class="form-label"><i class="fas fa-comment me-2"></i>Student Comments:</label>
    <div class="border p-2 rounded bg-light">
        <?php echo e($submission->comments); ?>

    </div>
</div>
<?php endif; ?>

<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\assignments\partials\submission-details.blade.php ENDPATH**/ ?>