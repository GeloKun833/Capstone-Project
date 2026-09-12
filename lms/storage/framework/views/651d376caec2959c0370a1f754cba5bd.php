<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card — <?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?></title>
    <?php echo $__env->make('reports.partials.report-card-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <?php echo $__env->make('reports.partials.report-card-body', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\reports\report-card-print.blade.php ENDPATH**/ ?>