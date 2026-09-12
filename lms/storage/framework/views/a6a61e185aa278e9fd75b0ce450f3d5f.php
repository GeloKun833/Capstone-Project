<?php $__env->startSection('content'); ?>

<?php
    $quarterLabels = [1 => '1st Quarter', 2 => '2nd Quarter', 3 => '3rd Quarter', 4 => '4th Quarter'];
    $hasFilters = $selectedSectionId && in_array((int) $selectedQuarter, [1, 2, 3, 4], true) && $currentAcademicYear;
    $sectionName = $sections->where('id', $selectedSectionId)->first()->name ?? 'N/A';
?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Quarterly Grade Entry</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Grade Entry</li>
                        </ul>
                    </div>
                </div>
            </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

            <div class="card">
                <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Select Section, Quarter & Academic Year</h5>
                </div>
                <div class="card-body">
                <?php if($subjects->isEmpty() || $sections->isEmpty()): ?>
                    <div class="alert alert-warning mb-3">
                        You have no subject/section assignment yet. Ask Admin to assign you under
                        <strong>Classes &amp; Subjects</strong>.
                    </div>
                <?php endif; ?>
                    <form method="GET" action="<?php echo e(route('teacher.grading.grade-entry')); ?>" id="filterForm">
                    <input type="hidden" name="step" value="grades">
                        <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Section *</label>
                            <select class="form-control form-select" name="section_id" id="selected_section" required <?php if($sections->isEmpty()): ?> disabled <?php endif; ?>>
                                <option value="">-- Select Section --</option>
                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($section->id); ?>" <?php echo e((string) ($selectedSectionId ?? '') === (string) $section->id ? 'selected' : ''); ?>>
                                        <?php echo e($section->name); ?> (<?php echo e($section->grade_level ?? 'N/A'); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Quarter *</label>
                            <select class="form-control form-select" name="quarter" id="selected_quarter" required>
                                <option value="">-- Select Quarter --</option>
                                <?php $__currentLoopData = $quarterLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($num); ?>" <?php echo e((int) ($selectedQuarter ?? 0) === $num ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Academic Year *</label>
                                    <select class="form-control form-select" name="academic_year_id" id="selected_academic_year" required>
                                        <option value="">-- Select Academic Year --</option>
                                        <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($year->id); ?>" <?php echo e($currentAcademicYear && $currentAcademicYear->id == $year->id ? 'selected' : ''); ?>>
                                                <?php echo e($year->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-lg" <?php if($sections->isEmpty() || $subjects->isEmpty()): ?> disabled <?php endif; ?>>
                            <i class="fas fa-search me-2"></i>Load Grade Sheet
                                </button>
                            </div>
                </form>
            </div>
        </div>

        <?php if($hasFilters && $sectionSubjects->isNotEmpty() && $students->count() > 0): ?>
            <div class="card mt-3">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge <?php echo e($step === 'grades' ? 'ge-badge-active' : 'ge-badge-idle'); ?>">1. Grade Entry</span>
                        <i class="fas fa-chevron-right text-muted"></i>
                        <span class="badge <?php echo e($step === 'observed' ? 'ge-badge-active' : 'ge-badge-idle'); ?>">2. Observed Values</span>
                        <i class="fas fa-chevron-right text-muted"></i>
                        <span class="badge <?php echo e($step === 'summary' ? 'ge-badge-active' : 'ge-badge-idle'); ?>">3. Quarter Summary &amp; Print</span>
                        </div>
                </div>
            </div>

            
            <?php if($step === 'grades'): ?>
            <div class="card mt-3" id="gradesStepCard">
                <div class="card-header ge-header text-white">
                    <h5 class="mb-0">
                        Step 1 — <?php echo e($quarterLabels[(int) $selectedQuarter]); ?> Grades —
                        <?php echo e($sectionName); ?> (<?php echo e($currentAcademicYear->name); ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert ge-alert d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>Enter grades for all learning areas, then <strong>Save Grades</strong>. Observed Values will open next.</div>
                        <?php if($hasQuarterGrades): ?>
                            <a class="btn btn-outline-success btn-sm"
                               href="<?php echo e(route('teacher.grading.grade-entry', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id,'step'=>'observed'])); ?>">
                                Continue to Observed Values →
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="gradesTable">
                            <thead class="ge-thead">
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    <?php $__currentLoopData = $sectionSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="text-center"><?php echo e($subject->subject_name); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="text-center"><?php echo e($index + 1); ?></td>
                                        <td><strong><?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?></strong></td>
                                        <?php $__currentLoopData = $sectionSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $key = $student->id . '_' . $subject->id; ?>
                                            <td>
                                                <input type="number" class="form-control quarter-subject-input"
                                                       data-student-id="<?php echo e($student->id); ?>"
                                                       data-subject-id="<?php echo e($subject->id); ?>"
                                                       value="<?php echo e($gradeMap[$key] ?? ''); ?>"
                                                       min="0" max="100" step="0.01" placeholder="—">
                                        </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-success btn-lg" id="saveGradesBtn">
                            <i class="fas fa-save me-2"></i>Save Grades &amp; Continue
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if($step === 'observed' && $hasQuarterGrades): ?>
            <div class="card mt-3">
                <div class="card-header ge-header text-white">
                    <h5 class="mb-0 text-uppercase">
                        Step 2 — Report on Learner&rsquo;s Observed Values
                        (<?php echo e($quarterLabels[(int) $selectedQuarter]); ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Marking: <strong>AO</strong> Always Observed |
                        <strong>SO</strong> Sometimes Observed |
                        <strong>RO</strong> Rarely Observed
                        — for <strong><?php echo e($quarterLabels[(int) $selectedQuarter]); ?></strong> only.
                    </p>

                    <form method="POST" action="<?php echo e(route('teacher.grading.observed-values.store')); ?>" id="observedForm">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="section_id" value="<?php echo e($selectedSectionId); ?>">
                        <input type="hidden" name="academic_year_id" value="<?php echo e($currentAcademicYear->id); ?>">
                        <input type="hidden" name="quarter" value="<?php echo e($selectedQuarter); ?>">

                        <?php $rIdx = 0; ?>
                        <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border rounded mb-3 p-3">
                                <h6 class="fw-bold mb-2"><?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?></h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0 observed-inline-table">
                                        <thead>
                                            <tr>
                                                <th style="width:18%">Core Values</th>
                                                <th>Behavior Statements</th>
                                                <th class="text-center" style="width:14%">Q<?php echo e($selectedQuarter); ?></th>
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
                                                            <td rowspan="<?php echo e($items->count()); ?>" class="fw-bold align-middle"><?php echo e($core); ?></td>
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

                        <div class="d-flex justify-content-between">
                            <a class="btn btn-secondary"
                               href="<?php echo e(route('teacher.grading.grade-entry', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id,'step'=>'grades'])); ?>">
                                Back to Grades
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Save Observed Values &amp; Show Summary
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if($step === 'summary' && $hasQuarterGrades): ?>
            <div class="card mt-3">
                <div class="card-header ge-header text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        Step 3 — <?php echo e($quarterLabels[(int) $selectedQuarter]); ?> Average &amp; Remarks
                        (<?php echo e($sectionName); ?>)
                    </h5>
                    <a class="btn btn-light btn-sm ge-print-btn"
                       href="<?php echo e(route('teacher.grading.quarter-report', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id])); ?>"
                       target="_blank">
                        <i class="fas fa-print me-1"></i> Open Printable Form
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="ge-thead">
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th class="text-center">Quarter Average</th>
                                    <th class="text-center">Auto Remarks</th>
                                    <th class="text-center">Print</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $sum = $studentQuarterSummaries[$student->id] ?? ['average'=>null,'remark'=>'—']; ?>
                                    <tr>
                                        <td><?php echo e($index + 1); ?></td>
                                        <td><?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?></td>
                                        <td class="text-center fw-bold">
                                            <?php echo e($sum['average'] !== null ? number_format($sum['average'], 2) : '—'); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php if(($sum['remark'] ?? '—') === 'Passed'): ?>
                                                <span class="badge bg-success">Passed</span>
                                            <?php elseif(($sum['remark'] ?? '—') === 'Failed'): ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-sm btn-outline-success"
                                               target="_blank"
                                               href="<?php echo e(route('teacher.grading.student-report', ['student'=>$student->id,'academic_year_id'=>$currentAcademicYear->id,'quarter'=>$selectedQuarter])); ?>">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-secondary"
                           href="<?php echo e(route('teacher.grading.grade-entry', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id,'step'=>'observed'])); ?>">
                            Back to Observed Values
                        </a>
                        <a class="btn btn-success"
                           href="<?php echo e(route('teacher.grading.grade-entry', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id,'step'=>'grades'])); ?>">
                            Edit Grades
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php elseif($hasFilters && $sectionSubjects->isEmpty()): ?>
            <div class="alert alert-warning mt-3">No subjects for this section.</div>
        <?php elseif($hasFilters && $students->isEmpty()): ?>
            <div class="alert alert-warning mt-3">No students in this section.</div>
            <?php else: ?>
            <div class="card mt-4">
                <div class="card-body text-center py-5">
                    <h4>Ready to Enter Grades</h4>
                    <p class="text-muted mb-0">Select Section, Quarter, and Academic Year to begin.</p>
                </div>
            </div>
            <?php endif; ?>
    </div>
    </div>

<?php $__env->startPush('styles'); ?>
<style>
:root {
    --ge-accent: #2f6f4e;
    --ge-accent-soft: #e8f3ec;
    --ge-accent-mid: #3d8b63;
}
.quarter-subject-input { text-align: center; font-weight: 600; }
.observed-inline-table th, .observed-inline-table td { border: 1px solid #c5d5cb; vertical-align: middle; }
.ge-header {
    background: linear-gradient(135deg, #2f6f4e 0%, #3d8b63 100%) !important;
    border-bottom: none;
}
.ge-thead th {
    background: #e8f3ec !important;
    color: #1f4d35 !important;
    border-color: #c5d5cb !important;
    font-weight: 700;
}
.ge-badge-active {
    background: var(--ge-accent) !important;
    color: #fff !important;
}
.ge-badge-idle {
    background: #e9ecef !important;
    color: #495057 !important;
}
.ge-alert {
    background: var(--ge-accent-soft);
    border: 1px solid #c5d5cb;
    color: #1f4d35;
    border-radius: .375rem;
    padding: .75rem 1rem;
}
.ge-print-btn {
    color: var(--ge-accent) !important;
    border: 1px solid #fff;
    font-weight: 600;
}
.ge-print-btn:hover {
    background: #fff !important;
    color: #1f4d35 !important;
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
            toastr.error('Please select Section, Quarter, and Academic Year.');
            return;
        }
        
        const grades = [];
        $('.quarter-subject-input').each(function() {
            const value = $(this).val();
            if (value === '' || value === null) return;
            const num = parseFloat(value);
            if (isNaN(num) || num < 0 || num > 100) return;
            grades.push({
                student_id: parseInt($(this).data('student-id'), 10),
                subject_id: parseInt($(this).data('subject-id'), 10),
                score: num
            });
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
                    url.searchParams.set('quarter', quarter);
                    url.searchParams.set('academic_year_id', academicYearId);
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

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\grading\grade-entry.blade.php ENDPATH**/ ?>