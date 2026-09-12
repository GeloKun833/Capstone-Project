<?php
    use App\Services\ReportCardService;
    $attendanceMonths = $attendanceMonths ?? ReportCardService::ATTENDANCE_MONTHS;
    $attendanceRows = $attendanceRows ?? [
        'school_days' => [],
        'present' => [],
        'absent' => [],
    ];
    $fullName = trim(($student->last_name ?? '').', '.($student->first_name ?? '').' '.($student->middle_name ?? ''));
    $sectionName = optional($student->sections->first())->name;
?>

<div class="report-sheet">
    <div class="school-header">
        <p class="school-name">Panorama Montessori School, Inc.</p>
        <p class="doc-title">Learner&rsquo;s Progress Report</p>
    </div>

    <div class="learner-meta">
        <div><strong>Name:</strong> <?php echo e($fullName); ?></div>
        <div><strong>School Year:</strong> <?php echo e($academicYear->name ?? '—'); ?></div>
        <?php if($sectionName): ?>
            <div><strong>Section:</strong> <?php echo e($sectionName); ?></div>
        <?php endif; ?>
        <?php if(!empty($student->admission_id)): ?>
            <div><strong>Student ID:</strong> <?php echo e($student->admission_id); ?></div>
        <?php endif; ?>
    </div>

    <div class="two-col">
        
        <div class="col">
            <h3>Report on Learner&rsquo;s Observed Values</h3>
            <table class="rc">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:16%">Core Values</th>
                        <th rowspan="2">Behavior Statements</th>
                        <th colspan="4">Quarter</th>
                    </tr>
                    <tr>
                        <th style="width:7%">1</th>
                        <th style="width:7%">2</th>
                        <th style="width:7%">3</th>
                        <th style="width:7%">4</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $observedGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $core => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $rating = $observedRatings->get($indicator->id); ?>
                            <tr>
                                <?php if($i === 0): ?>
                                    <td class="core" rowspan="<?php echo e($items->count()); ?>"><?php echo e($loop->parent->iteration); ?>. <?php echo e($core); ?></td>
                                <?php endif; ?>
                                <td class="left"><?php echo e($indicator->statement); ?></td>
                                <?php $__currentLoopData = ['quarter_1','quarter_2','quarter_3','quarter_4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td class="center"><?php echo e(optional($rating)->{$qf} ?: ''); ?></td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="center">No observed values recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="marking">
                <div><strong>Marking:</strong></div>
                <div><strong>AO</strong> Always Observed</div>
                <div><strong>SO</strong> Sometimes Observed</div>
                <div><strong>RO</strong> Rarely Observed</div>
            </div>

            <div class="att-title">Attendance Record</div>
            <table class="rc">
                <thead>
                    <tr>
                        <th style="width:22%"></th>
                        <?php $__currentLoopData = $attendanceMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th><?php echo e($label); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = [
                        'school_days' => 'No. of school days',
                        'present' => 'No. of days present',
                        'absent' => 'No. of days absent',
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="left"><?php echo e($label); ?></td>
                            <?php $__currentLoopData = array_keys($attendanceMonths); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthNum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $val = (int) ($attendanceRows[$key][$monthNum] ?? 0); ?>
                                <td class="center"><?php echo e($val > 0 ? $val : ''); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $tot = (int) ($attendanceRows[$key]['total'] ?? 0); ?>
                            <td class="center"><strong><?php echo e($tot > 0 ? $tot : ''); ?></strong></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        
        <div class="col">
            <h3>Report on Learning Progress and Achievement</h3>
            <table class="rc">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:34%">Learning Areas</th>
                        <th colspan="4">Quarter</th>
                        <th rowspan="2" style="width:11%">Final Grade</th>
                        <th rowspan="2" style="width:12%">Remarks</th>
                    </tr>
                    <tr>
                        <th style="width:8%">1</th>
                        <th style="width:8%">2</th>
                        <th style="width:8%">3</th>
                        <th style="width:8%">4</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $learningRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $subjName = $row->subject->subject_name ?? 'N/A';
                            $isChild = ReportCardService::isMapehChild($subjName);
                            $isParent = strcasecmp(trim($subjName), 'MAPEH') === 0;
                            $remark = $row->remarks ?: ReportCardService::remarkForScore(
                                $row->final_grade !== null ? (float) $row->final_grade : null
                            );
                        ?>
                        <tr>
                            <td class="left <?php echo e($isChild ? 'indent' : ''); ?>">
                                <?php if($isParent): ?><strong><?php echo e($subjName); ?></strong><?php else: ?><?php echo e($subjName); ?><?php endif; ?>
                            </td>
                            <?php $__currentLoopData = ['quarter_1','quarter_2','quarter_3','quarter_4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="center"><?php echo e($row->{$qf} !== null ? number_format((float)$row->{$qf}, 0) : ''); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="center"><?php echo e($row->final_grade !== null ? number_format((float)$row->final_grade, 0) : ''); ?></td>
                            <td class="center"><?php echo e($row->final_grade !== null ? $remark : ''); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="center">No grades recorded.</td></tr>
                    <?php endif; ?>

                    <?php if($learningRows->isNotEmpty()): ?>
                        <tr>
                            <td class="ga-label">General Average</td>
                            <?php $__currentLoopData = ['q1','q2','q3','q4','final']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="center">
                                    <strong><?php echo e(isset($generalAverages[$k]) && $generalAverages[$k] !== null ? number_format($generalAverages[$k], 0) : ''); ?></strong>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="center">
                                <strong><?php echo e(ReportCardService::remarkForScore($generalAverages['final'] ?? null)); ?></strong>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3 style="margin-top:14px;">Report on Learning Progress and Achievement</h3>
            <table class="rc scale">
                <thead>
                    <tr>
                        <th>Descriptors</th>
                        <th style="width:28%">Grading Scale</th>
                        <th style="width:22%">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Outstanding</td><td class="center">90 – 100</td><td class="center">Passed</td></tr>
                    <tr><td>Very Satisfactory</td><td class="center">85 – 89</td><td class="center">Passed</td></tr>
                    <tr><td>Satisfactory</td><td class="center">80 – 84</td><td class="center">Passed</td></tr>
                    <tr><td>Fairly Satisfactory</td><td class="center">75 – 79</td><td class="center">Passed</td></tr>
                    <tr><td>Did Not Meet Expectations</td><td class="center">Below 75</td><td class="center">Failed</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\reports\partials\report-card-body.blade.php ENDPATH**/ ?>