<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Report Cards</title>
    <?php echo $__env->make('reports.partials.report-card-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Print All / Save as PDF</button>
    </div>

    <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $student = $card['student'];
            $academicYear = $card['academicYear'];
            $observedGrouped = $card['observedGrouped'];
            $observedRatings = $card['observedRatings'];
            $learningRows = $card['learningRows'];
            $generalAverages = $card['generalAverages'];
            $attendanceMonths = $card['attendanceMonths'] ?? \App\Services\ReportCardService::ATTENDANCE_MONTHS;
            $attendanceRows = $card['attendanceRows'] ?? null;
            $focusQuarter = $card['focusQuarter'] ?? ($quarter ?? null);
        ?>
        <div class="page-break">
            <?php echo $__env->make('reports.partials.report-card-body', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/reports/report-card-section-print.blade.php ENDPATH**/ ?>