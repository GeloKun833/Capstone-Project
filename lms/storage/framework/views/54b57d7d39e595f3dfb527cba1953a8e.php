<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #111; margin: 0; padding: 18px; }
        h1 { margin: 0; font-size: 16px; text-align: center; text-transform: uppercase; }
        .school { text-align: center; font-size: 12px; margin-bottom: 2px; }
        .meta { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .meta td { border: none; padding: 2px 8px 2px 0; vertical-align: top; }
        .label { font-weight: bold; width: 110px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #222; padding: 4px 5px; text-align: left; }
        table.data th { background: #333; color: #fff; }
        table.data tr.alt td { background: #f4f4f4; }
        .note { margin: 8px 0 12px; }
        .footer { margin-top: 16px; border-top: 1px solid #222; padding-top: 6px; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <p class="school">Panorama Montessori School, Inc.</p>
    <h1><?php echo e($title); ?></h1>
    <table class="meta">
        <tr>
            <td class="label">Academic year</td>
            <td><?php echo e($year->name ?? 'No current academic year'); ?></td>
            <td class="label">Section</td>
            <td><?php echo e($sectionLabel); ?></td>
        </tr>
        <tr>
            <td class="label">Search</td>
            <td><?php echo e($search !== '' ? $search : '—'); ?></td>
            <td class="label">Rows</td>
            <td><?php echo e(number_format($rows->count())); ?></td>
        </tr>
    </table>
    <?php if($note): ?>
        <p class="note"><?php echo e($note); ?></p>
    <?php endif; ?>
    <?php if($summary): ?>
        <p class="note">
            <?php $__currentLoopData = $summary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <strong><?php echo e($label); ?>:</strong> <?php echo e(is_numeric($value) ? number_format($value) : $value); ?><?php if(! $loop->last): ?> &nbsp;&nbsp; <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </p>
    <?php endif; ?>
    <table class="data">
        <thead>
            <tr>
                <?php $__empty_1 = true; $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <th><?php echo e($column); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <th>Report</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo e($loop->even ? 'alt' : ''); ?>">
                    <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td><?php echo e($cell); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e(max(1, count($columns))); ?>">No records for this report.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <p class="footer">Generated <?php echo e($generatedAt); ?></p>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/reports/school-pdf.blade.php ENDPATH**/ ?>