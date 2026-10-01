<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Learner&rsquo;s Observed Values</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Observed Values</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Select Section, Academic Year &amp; Student</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('teacher.grading.observed-values')); ?>" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Section *</label>
                        <select name="section_id" class="form-control form-select" required onchange="this.form.submit()">
                            <option value="">-- Select Section --</option>
                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>" <?php echo e((string) $sectionId === (string) $section->id ? 'selected' : ''); ?>>
                                    <?php echo e($section->name); ?> (<?php echo e($section->grade_level ?? 'N/A'); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Academic Year *</label>
                        <select name="academic_year_id" class="form-control form-select" required onchange="this.form.submit()">
                            <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($year->id); ?>" <?php echo e((string) $academicYearId === (string) $year->id ? 'selected' : ''); ?>>
                                    <?php echo e($year->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Student *</label>
                        <select name="student_id" class="form-control form-select" <?php if($students->isEmpty()): ?> disabled <?php endif; ?> onchange="this.form.submit()">
                            <option value="">-- Select Student --</option>
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($student->id); ?>" <?php echo e((string) $studentId === (string) $student->id ? 'selected' : ''); ?>>
                                    <?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if($studentId && $indicators->isNotEmpty()): ?>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-uppercase">Report on Learner&rsquo;s Observed Values</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Marking: <strong>AO</strong> = Always Observed &nbsp;|&nbsp;
                        <strong>SO</strong> = Sometimes Observed &nbsp;|&nbsp;
                        <strong>RO</strong> = Rarely Observed
                    </p>

                    <form method="POST" action="<?php echo e(route('teacher.grading.observed-values.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="section_id" value="<?php echo e($sectionId); ?>">
                        <input type="hidden" name="academic_year_id" value="<?php echo e($academicYearId); ?>">
                        <input type="hidden" name="student_id" value="<?php echo e($studentId); ?>">

                        <div class="table-responsive">
                            <table class="table table-bordered observed-values-table">
                                <thead>
                                    <tr>
                                        <th style="width: 18%;">Core Values</th>
                                        <th>Behavior Statements</th>
                                        <th class="text-center" style="width: 10%;">1</th>
                                        <th class="text-center" style="width: 10%;">2</th>
                                        <th class="text-center" style="width: 10%;">3</th>
                                        <th class="text-center" style="width: 10%;">4</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $rowIndex = 0; ?>
                                    <?php $__currentLoopData = $indicators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coreValue => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $rating = $ratings->get($indicator->id);
                                                $rowspan = $items->count();
                                            ?>
                                            <tr>
                                                <?php if($i === 0): ?>
                                                    <td rowspan="<?php echo e($rowspan); ?>" class="fw-bold align-middle"><?php echo e($coreValue); ?></td>
                                                <?php endif; ?>
                                                <td>
                                                    <?php echo e($indicator->statement); ?>

                                                    <input type="hidden" name="ratings[<?php echo e($rowIndex); ?>][indicator_id]" value="<?php echo e($indicator->id); ?>">
                                                </td>
                                                <?php $__currentLoopData = [1, 2, 3, 4]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php $field = 'quarter_' . $q; ?>
                                                    <td>
                                                        <select class="form-control form-select form-select-sm" name="ratings[<?php echo e($rowIndex); ?>][<?php echo e($field); ?>]">
                                                            <option value="">—</option>
                                                            <?php $__currentLoopData = ['AO', 'SO', 'RO']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mark): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($mark); ?>" <?php echo e(optional($rating)->{$field} === $mark ? 'selected' : ''); ?>><?php echo e($mark); ?></option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    </td>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tr>
                                            <?php $rowIndex++; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Save Observed Values
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif($sectionId && $students->isEmpty()): ?>
            <div class="alert alert-warning">No students found in this section.</div>
        <?php elseif($sectionId && ! $studentId): ?>
            <div class="alert alert-info">Select a student to enter observed values.</div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.observed-values-table th,
.observed-values-table td {
    border: 1px solid #212529;
    vertical-align: middle;
}
.observed-values-table thead th {
    background: #f8f9fa;
    text-align: center;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\grading\observed-values.blade.php ENDPATH**/ ?>