<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            font-size: 10px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .student-name {
            text-align: left;
            font-weight: bold;
            min-width: 120px;
        }
        .present {
            background-color: #d4edda;
            color: #155724;
        }
        .absent {
            background-color: #f8d7da;
            color: #721c24;
        }
        .late {
            background-color: #fff3cd;
            color: #856404;
        }
        .summary {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .summary-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report</h1>
        <p>Generated on: <?php echo e(now()->format('F j, Y g:i A')); ?></p>
        <p>Period: <?php echo e(\Carbon\Carbon::createFromFormat('Y-m', request('month', now()->format('Y-m')))->format('F Y')); ?></p>
    </div>

    <?php if(count($students) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th class="student-name">Student Name</th>
                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th><?php echo e($day); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th>Present</th>
                    <th>Total</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="student-name"><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></td>
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $status = $attendanceMap[$student->id][$day] ?? null;
                                $statusClass = '';
                                $statusText = '-';
                                
                                if ($status === 'present') {
                                    $statusClass = 'present';
                                    $statusText = 'P';
                                } elseif ($status === 'absent') {
                                    $statusClass = 'absent';
                                    $statusText = 'A';
                                } elseif ($status === 'late') {
                                    $statusClass = 'late';
                                    $statusText = 'L';
                                }
                            ?>
                            <td class="<?php echo e($statusClass); ?>"><?php echo e($statusText); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td><?php echo e($summary[$student->id]['present'] ?? 0); ?></td>
                        <td><?php echo e($summary[$student->id]['total'] ?? 0); ?></td>
                        <td><?php echo e($summary[$student->id]['percentage'] ?? 0); ?>%</td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="summary">
            <h3>Summary</h3>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Total Days</th>
                        <th>Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $studentSummary = $summary[$student->id] ?? [];
                            $present = $studentSummary['present'] ?? 0;
                            $total = $studentSummary['total'] ?? 0;
                            $absent = 0;
                            $late = 0;
                            
                            // Calculate absent and late from attendance map
                            foreach($days as $day) {
                                $status = $attendanceMap[$student->id][$day] ?? null;
                                if ($status === 'absent') {
                                    $absent++;
                                } elseif ($status === 'late') {
                                    $late++;
                                }
                            }
                        ?>
                        <tr>
                            <td><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></td>
                            <td><?php echo e($present); ?></td>
                            <td><?php echo e($absent); ?></td>
                            <td><?php echo e($late); ?></td>
                            <td><?php echo e($total); ?></td>
                            <td><?php echo e($studentSummary['percentage'] ?? 0); ?>%</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px;">
            <h3>No Data Available</h3>
            <p>No students found for the selected criteria.</p>
        </div>
    <?php endif; ?>

    <div class="footer">
        <p>This report was generated automatically by Panorama Montessori School LMS System</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html> <?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\attendance\export_pdf.blade.php ENDPATH**/ ?>