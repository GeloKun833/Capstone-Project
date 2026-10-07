<?php $__env->startSection('content'); ?>

<?php
    $quarterLabels = collect(\App\Support\SchoolQuarter::periods($currentAcademicYear ?? null))
        ->mapWithKeys(fn ($period) => [$period['number'] => $period['label']])
        ->all();
    $hasFilters = $selectedSectionId && in_array((int) $selectedQuarter, [1, 2, 3, 4], true) && $currentAcademicYear;
    $sectionName = $sections->where('id', $selectedSectionId)->first()->name ?? 'N/A';
    $stepQuery = fn ($step) => [
        'section_id' => $selectedSectionId,
        'quarter' => $selectedQuarter,
        'academic_year_id' => $currentAcademicYear->id ?? null,
        'step' => $step,
    ];
?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page ge-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Grade Entry</h3>
                    <p class="dir-subtitle">Enter quarterly grades, observed values, then print the summary.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Grade Entry</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="ge-card ge-filters">
            <div class="ge-card-head">
                <h5>Load grade sheet</h5>
                <p>Choose the section. The academic year and term follow the current school year.</p>
            </div>
            <?php if($sections->isEmpty()): ?>
                <div class="alert alert-warning mx-3 mt-0">
                    No teaching assignments are available for the current Academic Year.
                </div>
            <?php endif; ?>
            <?php if($currentAcademicYear && ! $currentPeriod): ?>
                <div class="alert alert-warning mx-3 mt-0">
                    Today is outside the quarters for <?php echo e($currentAcademicYear->name); ?>.
                </div>
            <?php endif; ?>
            <form method="GET" action="<?php echo e(route('teacher.grading.grade-entry')); ?>" id="filterForm" class="px-3 pb-3">
                <input type="hidden" name="step" value="grades">
                <input type="hidden" id="selected_quarter" value="<?php echo e($selectedQuarter); ?>">
                <input type="hidden" id="selected_academic_year" value="<?php echo e($currentAcademicYear->id ?? ''); ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="academic_year_label">Academic Year</label>
                        <input type="text" class="form-control ge-locked" id="academic_year_label" value="<?php echo e($currentAcademicYear->name ?? 'No current academic year'); ?>" readonly tabindex="-1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="quarter_label">Term</label>
                        <input type="text" class="form-control ge-locked" id="quarter_label" value="<?php echo e($currentPeriod ? $currentPeriod['label'].' · '.$currentPeriod['start']->format('M j, Y').' – '.$currentPeriod['end']->format('M j, Y') : 'Outside the school year'); ?>" readonly tabindex="-1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="selected_section">Section</label>
                        <select class="form-control form-select" name="section_id" id="selected_section" required <?php if($sections->isEmpty() || ! $currentPeriod): ?> disabled <?php endif; ?>>
                            <option value="">Select section</option>
                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>" <?php echo e((string) ($selectedSectionId ?? '') === (string) $section->id ? 'selected' : ''); ?>>
                                    <?php echo e($section->grade_level ? $section->grade_level.' · ' : ''); ?><?php echo e($section->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary dir-btn w-100" <?php if($sections->isEmpty() || $subjects->isEmpty() || ! $currentPeriod): ?> disabled <?php endif; ?>>
                            <i class="fas fa-search me-1"></i> Load
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if($hasFilters && $sectionSubjects->isNotEmpty() && $students->count() > 0): ?>
            <div class="ge-steps">
                <span class="ge-step <?php echo e($step === 'grades' ? 'is-active' : ''); ?>">1. Grade Entry</span>
                <span class="ge-step-line"></span>
                <span class="ge-step <?php echo e($step === 'observed' ? 'is-active' : ''); ?>">2. Observed Values</span>
                <span class="ge-step-line"></span>
                <span class="ge-step <?php echo e($step === 'summary' ? 'is-active' : ''); ?>">3. Summary &amp; Print</span>
            </div>

            <?php if($step === 'grades'): ?>
            <div class="ge-card" id="gradesStepCard">
                <div class="ge-card-head ge-card-head-row">
                    <div>
                        <h5><?php echo e($descriptive ? 'Descriptive Method' : 'Numerical Method'); ?> — <?php echo e($quarterLabels[(int) $selectedQuarter] ?? 'Current term'); ?></h5>
                        <p>
                            <?php echo e($sectionName); ?> uses the <?php echo e($descriptive ? 'Descriptive' : 'Numerical'); ?> Method
                            · <?php echo e($currentAcademicYear->name); ?>

                            · <?php echo e($gradedCount); ?> / <?php echo e($expectedCount); ?> <?php echo e($descriptive ? 'assessed' : 'graded'); ?>

                            <?php if($expectedCount > $gradedCount): ?>
                                · <?php echo e($expectedCount - $gradedCount); ?> missing
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if($hasQuarterGrades): ?>
                        <a class="btn btn-outline-primary dir-btn"
                           href="<?php echo e(route('teacher.grading.grade-entry', $stepQuery('observed'))); ?>">
                            Continue to Observed Values
                        </a>
                    <?php endif; ?>
                </div>
                <?php if($descriptive): ?>
                    <div class="ge-note">
                        This class uses the Descriptive Method. Choose A, B, C, D, or E. Numerical scores are not accepted.
                        A Advancing (Namumukod-tangi) · B Benchmarking (Napamamalas) · C Connecting (Natutungo) · D Developing (Napauunlad) · E Emerging (Nagsisimula).
                    </div>
                    <div class="ge-levels">
                        <?php $__currentLoopData = $descriptiveScale; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $letter => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span title="<?php echo e($meta['description']); ?>"><?php echo e($letter); ?> — <?php echo e($meta['english']); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="ge-note">This class uses the Numerical Method. Enter a score from 0 to 100. Descriptive letters are not accepted.</div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table dir-table mb-0" id="gradesTable" data-method="<?php echo e($descriptive ? 'descriptive' : 'numerical'); ?>">
                        <thead>
                            <tr>
                                <th style="width:48px">#</th>
                                <th>Student</th>
                                <?php $__currentLoopData = $sectionSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="text-center"><?php echo e($subject->subject_name); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="text-center dir-muted"><?php echo e($index + 1); ?></td>
                                    <td>
                                        <span class="dir-person-name"><?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?></span>
                                    </td>
                                    <?php $__currentLoopData = $sectionSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $key = $student->id . '_' . $subject->id; ?>
                                        <td>
                                            <?php if($descriptive): ?>
                                                <select class="form-select form-select-sm quarter-subject-input"
                                                        data-student-id="<?php echo e($student->id); ?>"
                                                        data-subject-id="<?php echo e($subject->id); ?>">
                                                    <option value="">—</option>
                                                    <?php $__currentLoopData = $descriptiveScale; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $letter => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($letter); ?>" <?php if(($gradeMap[$key] ?? '') === $letter): echo 'selected'; endif; ?> title="<?php echo e($meta['description']); ?>">
                                                            <?php echo e($letter); ?> — <?php echo e($meta['english']); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="number" class="form-control quarter-subject-input"
                                                       data-student-id="<?php echo e($student->id); ?>"
                                                       data-subject-id="<?php echo e($subject->id); ?>"
                                                       value="<?php echo e($gradeMap[$key] ?? ''); ?>"
                                                       min="0" max="100" step="0.01" placeholder="—">
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="ge-footer">
                    <button type="button" class="btn btn-primary dir-btn" id="saveGradesBtn">
                        <i class="fas fa-save me-1"></i> Save Grades &amp; Continue
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if($step === 'observed' && $hasQuarterGrades): ?>
            <div class="ge-card">
                <div class="ge-card-head">
                    <h5>Step 2 — Observed values (<?php echo e($quarterLabels[(int) $selectedQuarter]); ?>)</h5>
                    <p>AO Always Observed · SO Sometimes Observed · RO Rarely Observed</p>
                </div>
                <form method="POST" action="<?php echo e(route('teacher.grading.observed-values.store')); ?>" id="observedForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="section_id" value="<?php echo e($selectedSectionId); ?>">
                    <input type="hidden" name="academic_year_id" value="<?php echo e($currentAcademicYear->id); ?>">
                    <input type="hidden" name="quarter" value="<?php echo e($selectedQuarter); ?>">

                    <div class="p-3">
                        <?php $rIdx = 0; ?>
                        <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="ge-student-block">
                                <h6><?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?></h6>
                                <div class="table-responsive">
                                    <table class="table dir-table observed-inline-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:18%">Core values</th>
                                                <th>Behavior statements</th>
                                                <th class="text-center" style="width:120px">Q<?php echo e($selectedQuarter); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $observedIndicators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $core => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $key = $student->id . '_' . $indicator->id;
                                                        $mark = $observedMap[$key] ?? '';
                                                    ?>
                                                    <tr>
                                                        <?php if($i === 0): ?>
                                                            <td rowspan="<?php echo e($items->count()); ?>" class="align-middle fw-bold"><?php echo e($core); ?></td>
                                                        <?php endif; ?>
                                                        <td>
                                                            <?php echo e($indicator->statement); ?>

                                                            <input type="hidden" name="ratings[<?php echo e($rIdx); ?>][student_id]" value="<?php echo e($student->id); ?>">
                                                            <input type="hidden" name="ratings[<?php echo e($rIdx); ?>][indicator_id]" value="<?php echo e($indicator->id); ?>">
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm" name="ratings[<?php echo e($rIdx); ?>][mark]">
                                                                <option value="">—</option>
                                                                <?php $__currentLoopData = ['AO','SO','RO']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($opt); ?>" <?php echo e($mark === $opt ? 'selected' : ''); ?>><?php echo e($opt); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <?php $rIdx++; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="ge-footer ge-footer-split">
                        <a class="btn btn-outline-secondary dir-btn" href="<?php echo e(route('teacher.grading.grade-entry', $stepQuery('grades'))); ?>">Back to grades</a>
                        <button type="submit" class="btn btn-primary dir-btn">
                            <i class="fas fa-save me-1"></i> Save Observed Values
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <?php if($step === 'summary' && $hasQuarterGrades): ?>
            <div class="ge-card">
                <div class="ge-card-head ge-card-head-row">
                    <div>
                        <h5>Step 3 — <?php echo e($quarterLabels[(int) $selectedQuarter]); ?> <?php echo e($descriptive ? 'assessments' : 'average & remarks'); ?></h5>
                        <p><?php echo e($sectionName); ?> · <?php echo e($currentAcademicYear->name); ?></p>
                    </div>
                    <a class="btn btn-primary dir-btn"
                       href="<?php echo e(route('teacher.grading.quarter-report', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id])); ?>"
                       target="_blank">
                        <i class="fas fa-print me-1"></i> Printable form
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table dir-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:48px">#</th>
                                <th>Student</th>
                                <th class="text-center"><?php echo e($descriptive ? 'Assessed' : 'Quarter average'); ?></th>
                                <th class="text-center"><?php echo e($descriptive ? 'Missing' : 'Remarks'); ?></th>
                                <th class="text-end">Print</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $sum = $studentQuarterSummaries[$student->id] ?? ['average'=>null,'remark'=>'—']; ?>
                                <tr>
                                    <td class="text-center dir-muted"><?php echo e($index + 1); ?></td>
                                    <td><span class="dir-person-name"><?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?></span></td>
                                    <td class="text-center">
                                        <?php if($descriptive): ?>
                                            <?php echo e($sum['filled'] ?? 0); ?> / <?php echo e($sectionSubjects->count()); ?>

                                        <?php else: ?>
                                            <?php echo e($sum['average'] !== null ? number_format($sum['average'], 2) : '—'); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($descriptive): ?>
                                            <?php echo e($sum['missing'] ?? 0); ?>

                                        <?php elseif(($sum['remark'] ?? '—') === 'Passed'): ?>
                                            <span class="dir-badge dir-badge--active">Passed</span>
                                        <?php elseif(($sum['remark'] ?? '—') === 'Failed'): ?>
                                            <span class="dir-badge dir-badge--disabled">Failed</span>
                                        <?php else: ?>
                                            <span class="dir-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a class="dir-icon-btn"
                                           target="_blank"
                                           title="Print"
                                           href="<?php echo e(route('teacher.grading.student-report', ['student'=>$student->id,'academic_year_id'=>$currentAcademicYear->id,'quarter'=>$selectedQuarter])); ?>">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="ge-footer ge-footer-split">
                    <a class="btn btn-outline-secondary dir-btn" href="<?php echo e(route('teacher.grading.grade-entry', $stepQuery('observed'))); ?>">Back to observed values</a>
                    <a class="btn btn-primary dir-btn" href="<?php echo e(route('teacher.grading.grade-entry', $stepQuery('grades'))); ?>">Edit grades</a>
                </div>
            </div>
            <?php endif; ?>

        <?php elseif($hasFilters && $sectionSubjects->isEmpty()): ?>
            <div class="ge-card">
                <div class="dir-empty">
                    <i class="fas fa-book d-block"></i>
                    <h5 class="mt-2 mb-1">No subjects for this section</h5>
                    <p class="mb-0">Ask Admin to assign subjects under Classes &amp; Subjects.</p>
                </div>
            </div>
        <?php elseif($hasFilters && $students->isEmpty()): ?>
            <div class="ge-card">
                <div class="dir-empty">
                    <i class="fas fa-user-graduate d-block"></i>
                    <h5 class="mt-2 mb-1">No students in this section</h5>
                    <p class="mb-0">There are no enrolled students to grade yet.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="ge-card">
                <div class="dir-empty">
                    <i class="fas fa-edit d-block"></i>
                    <h5 class="mt-2 mb-1">Ready to enter grades</h5>
                    <p class="mb-0">Select section, quarter, and academic year, then load the grade sheet.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914j">
<style>
.ge-page .page-header { margin-bottom: 0.85rem; }
.ge-card {
    background: #fff;
    border: 1px solid #e8eef7;
    border-radius: 18px;
    box-shadow: 0 14px 32px rgba(79, 114, 205, 0.05);
    overflow: hidden;
}
.ge-card + .ge-card, .ge-steps + .ge-card { margin-top: 0.9rem; }
.ge-filters { margin-bottom: 0.9rem; }
.ge-card-head { padding: 1rem 1.15rem 0.35rem; }
.ge-card-head h5 {
    margin: 0;
    font-size: 1.02rem;
    font-weight: 750;
    color: #1e293b;
}
.ge-card-head p {
    margin: 0.2rem 0 0;
    font-size: 0.8rem;
    color: #94a3b8;
}
.ge-card-head-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    flex-wrap: wrap;
    padding-bottom: 0.75rem;
}
.ge-filters .form-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #94a3b8;
}
.ge-filters .form-control,
.ge-filters .form-select {
    border-radius: 10px;
    min-height: 42px;
    border-color: #e5e7eb;
}
.ge-locked { background: #fafaf9; color: #1c1917; pointer-events: none; }
.quarter-subject-input {
    text-align: center;
    font-weight: 650;
    min-width: 72px;
    border-radius: 10px;
}
.ge-levels {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    padding: 0 1rem 0.75rem;
    color: #57534e;
    font-size: 0.78rem;
}
.ge-levels span {
    background: #fafaf9;
    border: 1px solid #e7e5e4;
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
}
.ge-steps {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
    margin-bottom: 0.9rem;
}
.ge-step {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    padding: 0 0.8rem;
    border-radius: 999px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
}
.ge-step.is-active {
    background: #eef2ff;
    color: #4338ca;
}
.ge-step-line {
    width: 18px;
    height: 1px;
    background: #e2e8f0;
}
.ge-note {
    margin: 0 1.15rem 0.85rem;
    padding: 0.7rem 0.85rem;
    border-radius: 12px;
    background: #f8faff;
    color: #475569;
    font-size: 0.85rem;
}
.ge-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 0.9rem 1.15rem;
    border-top: 1px solid #eef2f7;
}
.ge-footer-split { justify-content: space-between; }
.ge-student-block {
    border: 1px solid #edf1f7;
    border-radius: 14px;
    padding: 0.85rem;
    margin-bottom: 0.75rem;
}
.ge-student-block h6 {
    font-weight: 750;
    margin-bottom: 0.65rem;
    color: #1e293b;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    $('#saveGradesBtn').on('click', function(e) {
        e.preventDefault();
        const sectionId = $('#selected_section').val();
        const quarter = $('#selected_quarter').val();
        const academicYearId = $('#selected_academic_year').val();
        if (!sectionId || !quarter || !academicYearId) {
            toastr.error('Select a section. The academic year and quarter are set from the current school calendar.');
            return;
        }

        const grades = [];
        const descriptive = $('#gradesTable').data('method') === 'descriptive';
        $('.quarter-subject-input').each(function() {
            const value = $(this).val();
            if (value === '' || value === null) return;
            const row = {
                student_id: parseInt($(this).data('student-id'), 10),
                subject_id: parseInt($(this).data('subject-id'), 10)
            };
            if (descriptive) {
                row.level = String(value).toUpperCase();
            } else {
                const num = parseFloat(value);
                if (isNaN(num) || num < 0 || num > 100) return;
                row.score = num;
            }
            grades.push(row);
        });

        if (!grades.length) {
            toastr.warning('Enter at least one grade before saving.');
            return;
        }

        const $btn = $(this);
        const original = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);

        $.ajax({
            url: '<?php echo e(route("teacher.grading.store-quarterly-grades")); ?>',
            type: 'POST',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            data: {
                _token: '<?php echo e(csrf_token()); ?>',
                section_id: sectionId,
                quarter: quarter,
                academic_year_id: academicYearId,
                grades: grades
            },
            success: function(response) {
                if (response && response.success) {
                    toastr.success(response.message || 'Grades saved.');
                    const url = new URL('<?php echo e(route("teacher.grading.grade-entry")); ?>', window.location.origin);
                    url.searchParams.set('section_id', sectionId);
                    url.searchParams.set('step', 'observed');
                    setTimeout(function() { window.location.href = url.toString(); }, 800);
                } else {
                    toastr.warning(response.message || 'Saved with warnings.');
                    $btn.html(original).prop('disabled', false);
                }
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to save grades.');
                $btn.html(original).prop('disabled', false);
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/grading/grade-entry.blade.php ENDPATH**/ ?>