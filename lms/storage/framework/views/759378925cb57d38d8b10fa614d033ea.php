<?php echo $__env->make('enrollment.partials.file-card-assets', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php
    $canPreview = $document
        && ($document->submission_method ?? null) !== 'in_person'
        && $document->file_path;
    $previewUrl = $canPreview ? route('enrollment.portal.preview-document', $document->id) : null;
    $ext = strtoupper(pathinfo((string) ($document->file_name ?? ''), PATHINFO_EXTENSION));
    if ($ext === 'JPEG') {
        $ext = 'JPG';
    }
    $kind = $document && $document->isPdf() ? 'pdf' : (($document && $document->isImage()) ? 'image' : 'file');
    if ($kind === 'pdf') {
        $ext = 'PDF';
    }
?>
<?php if(! $previewUrl): ?>
    <span class="text-muted">—</span>
<?php else: ?>
    <a class="enr-file-card" href="<?php echo e($previewUrl); ?>" target="_blank" rel="noopener"
       data-enr-file data-kind="<?php echo e($kind); ?>" data-src="<?php echo e($previewUrl); ?>" title="<?php echo e($document->file_name); ?>">
        <span class="enr-file-card__meta">
            <span class="enr-file-card__name"><?php echo e($document->file_name); ?></span>
            <span class="enr-file-card__type"><?php echo e($ext ?: 'FILE'); ?></span>
        </span>
        <span class="enr-file-card__preview"></span>
    </a>
<?php endif; ?>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/enrollment/partials/document-thumb.blade.php ENDPATH**/ ?>