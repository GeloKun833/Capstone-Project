<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?></title>
    <style>
        <?php echo $styles; ?>

        .combined-page { page-break-after: always; }
        .combined-page:last-child { page-break-after: auto; }
    </style>
</head>
<body>
    <?php $__empty_1 = true; $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="combined-page"><?php echo $page; ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p>No enrolled students were found for this section.</p>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/reports/combined-pdf.blade.php ENDPATH**/ ?>