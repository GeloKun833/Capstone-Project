<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GPA Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #f0f0f0; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>GPA Ranking Report</h2>
    <p>Generated: <?php echo e(now()->format('F d, Y H:i')); ?></p>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student</th>
                <th>GPA</th>
                <th>Academic Year</th>
                <th>Semester</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $gpaRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($record->rank ?? '-'); ?></td>
                    <td><?php echo e($record->student->first_name ?? ''); ?> <?php echo e($record->student->last_name ?? ''); ?></td>
                    <td><?php echo e(number_format($record->gpa, 2)); ?></td>
                    <td><?php echo e($record->academicYear->name ?? 'N/A'); ?></td>
                    <td><?php echo e($record->semester->name ?? 'N/A'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" style="text-align:center;">No GPA records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/exports/gpa-pdf.blade.php ENDPATH**/ ?>