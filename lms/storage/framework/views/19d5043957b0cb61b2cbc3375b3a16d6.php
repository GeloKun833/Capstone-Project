<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($assignment->title); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1><?php echo e($assignment->title); ?></h1>
    <p><strong>Subject:</strong> <?php echo e($assignment->subject->subject_name ?? 'N/A'); ?></p>
    <p><strong>Section:</strong> <?php echo e($assignment->section->name ?? 'N/A'); ?></p>
    <p><strong>Due:</strong> <?php echo e($assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') : 'N/A'); ?></p>
    <p><?php echo e($assignment->description); ?></p>
    <h3>Submissions (<?php echo e($assignment->submissions->count()); ?>)</h3>
    <table>
        <thead><tr><th>Student</th><th>Status</th><th>Score</th><th>Submitted</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $assignment->submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($sub->student->first_name ?? ''); ?> <?php echo e($sub->student->last_name ?? ''); ?></td>
                    <td><?php echo e(ucfirst($sub->status ?? 'pending')); ?></td>
                    <td><?php echo e($sub->score ?? '-'); ?></td>
                    <td><?php echo e($sub->submitted_at ? $sub->submitted_at->format('M d, Y') : '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\assignments\pdf.blade.php ENDPATH**/ ?>