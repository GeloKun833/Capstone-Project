<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grades Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Grades Report</h2>
    <p>Generated: <?php echo e(now()->format('F d, Y H:i')); ?></p>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Component</th>
                <th>Score</th>
                <th>%</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($grade->student->first_name ?? ''); ?> <?php echo e($grade->student->last_name ?? ''); ?></td>
                    <td><?php echo e($grade->subject->subject_name ?? 'N/A'); ?></td>
                    <td><?php echo e($grade->component->name ?? 'N/A'); ?></td>
                    <td><?php echo e($grade->score); ?>/<?php echo e($grade->max_score); ?></td>
                    <td><?php echo e(number_format($grade->percentage, 1)); ?>%</td>
                    <td><?php echo e($grade->remarks ?? '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="text-align:center;">No grades found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\exports\grades-pdf.blade.php ENDPATH**/ ?>