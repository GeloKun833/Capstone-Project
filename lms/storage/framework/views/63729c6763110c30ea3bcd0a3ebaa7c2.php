<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grade Slip - <?php echo e($student->full_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        .student-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-info td {
            padding: 5px 10px;
            border: none;
        }
        .student-info td.label {
            font-weight: bold;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .subject-row {
            background-color: #fff;
        }
        .component-row {
            background-color: #f9f9f9;
            font-size: 10px;
        }
        .component-row td {
            padding-left: 30px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border: 2px solid #000;
        }
        .summary table {
            border: none;
        }
        .summary td {
            border: none;
            padding: 5px;
        }
        .summary td.label {
            font-weight: bold;
            width: 200px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .period-info {
            background-color: #333;
            color: white;
            padding: 10px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Grade Slip</h1>
        <h2>Official Student Grade Report</h2>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td class="label">Student Name:</td>
                <td><strong><?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?> <?php echo e($student->middle_name ?? ''); ?></strong></td>
                <td class="label">Student ID:</td>
                <td><strong><?php echo e($student->id); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Year Level:</td>
                <td><?php echo e($student->year_level ?? 'N/A'); ?></td>
                <td class="label">Section:</td>
                <td><?php echo e($student->sections->first() ? $student->sections->first()->name : 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td><?php echo e($student->email ?? 'N/A'); ?></td>
                <td class="label">Generated Date:</td>
                <td><?php echo e(\Carbon\Carbon::now()->format('F d, Y')); ?></td>
            </tr>
        </table>
    </div>

    <?php if($academicYear && $semester): ?>
        <div class="period-info">
            Academic Period: <?php echo e($academicYear->name); ?> - <?php echo e($semester->name); ?>

        </div>
    <?php endif; ?>

    <?php if(count($subjectGrades) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 28%;">Subject</th>
                    <th style="width: 9%;">Q1</th>
                    <th style="width: 9%;">Q2</th>
                    <th style="width: 9%;">Q3</th>
                    <th style="width: 9%;">Q4</th>
                    <th style="width: 11%;">Final</th>
                    <th style="width: 20%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $subjectGrades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $subjectData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $q = $subjectData['quarterly']; ?>
                    <tr>
                        <td class="text-center"><?php echo e($index + 1); ?></td>
                        <td><strong><?php echo e($subjectData['subject']->subject_name); ?></strong></td>
                        <td class="text-center"><?php echo e($q->quarter_1 !== null ? number_format($q->quarter_1, 2) : '—'); ?></td>
                        <td class="text-center"><?php echo e($q->quarter_2 !== null ? number_format($q->quarter_2, 2) : '—'); ?></td>
                        <td class="text-center"><?php echo e($q->quarter_3 !== null ? number_format($q->quarter_3, 2) : '—'); ?></td>
                        <td class="text-center"><?php echo e($q->quarter_4 !== null ? number_format($q->quarter_4, 2) : '—'); ?></td>
                        <td class="text-center"><strong><?php echo e(number_format($subjectData['average'], 2)); ?></strong></td>
                        <td class="text-center"><?php echo e($subjectData['remarks'] ?: ($subjectData['average'] >= 75 ? 'PASSED' : 'FAILED')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999;">
            <p>No grades found for the selected period.</p>
        </div>
    <?php endif; ?>

    <div class="summary">
        <h3 style="margin-top: 0; text-align: center;">Academic Summary</h3>
        <table>
            <?php if($gpa): ?>
                <tr>
                    <td class="label">GPA:</td>
                    <td><strong><?php echo e(number_format($gpa->gpa, 2)); ?></strong></td>
                    <td class="label">Rank:</td>
                    <td><strong><?php echo e($gpa->rank ?? 'N/A'); ?></strong></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Total Attendance:</td>
                <td><strong><?php echo e($attendanceSummary['total']); ?></strong></td>
                <td class="label">Present:</td>
                <td><strong><?php echo e($attendanceSummary['present']); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Absent:</td>
                <td><strong><?php echo e($attendanceSummary['absent']); ?></strong></td>
                <td class="label">Late:</td>
                <td><strong><?php echo e($attendanceSummary['late'] ?? 0); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Excused:</td>
                <td><strong><?php echo e($attendanceSummary['excused'] ?? 0); ?></strong></td>
                <td class="label">Attendance Rate:</td>
                <td><strong><?php echo e(number_format($attendanceSummary['percentage'], 2)); ?>%</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This grade slip was generated on <?php echo e(\Carbon\Carbon::now()->format('F d, Y \a\t g:i A')); ?></p>
        <p>For verification, please contact the school registrar's office.</p>
    </div>
</body>
</html>


<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/reports/grade_slip.blade.php ENDPATH**/ ?>