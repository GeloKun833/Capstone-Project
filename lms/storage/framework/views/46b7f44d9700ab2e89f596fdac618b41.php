
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Promote Students: <?php echo e($fromGradeLevel); ?> → <?php echo e($toGradeLevel); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('promotions.index')); ?>">Promotions</a></li>
                        <li class="breadcrumb-item active">Process Promotion</li>
                    </ul>
                </div>
            </div>
        </div>

        <form action="<?php echo e(route('promotions.store')); ?>" method="POST" id="promotionForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="from_grade" value="<?php echo e($fromGradeLevel); ?>">
            <input type="hidden" name="to_grade" value="<?php echo e($toGradeLevel); ?>">

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 text-white">Academic Year &amp; Section</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">From Academic Year <span class="text-danger">*</span></label>
                                <select name="from_academic_year_id" class="form-control" required>
                                    <option value="">Select Academic Year</option>
                                    <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($year->id); ?>"
                                            <?php echo e((string) old('from_academic_year_id', $fromAcademicYear->id ?? '') === (string) $year->id ? 'selected' : ''); ?>>
                                            <?php echo e($year->name); ?>

                                            <?php if($year->isCurrent()): ?> (Current) <?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">To Academic Year <span class="text-danger">*</span></label>
                                <select name="to_academic_year_id" class="form-control" required>
                                    <option value="">Select Academic Year</option>
                                    <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($year->id); ?>"
                                            <?php echo e((string) old('to_academic_year_id', $toAcademicYear->id ?? '') === (string) $year->id ? 'selected' : ''); ?>>
                                            <?php echo e($year->name); ?>

                                            <?php if($year->statusLabel() === 'upcoming'): ?> (Upcoming) <?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Promotion Date <span class="text-danger">*</span></label>
                                <input type="date" name="promotion_date" class="form-control"
                                       value="<?php echo e(old('promotion_date', date('Y-m-d'))); ?>" required>
                            </div>
                        </div>
                        <?php if($toGradeLevel !== 'Graduated'): ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Default Destination Section</label>
                                    <select name="to_section_id" class="form-control">
                                        <option value="">Auto-assign (by capacity)</option>
                                        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($section->id); ?>" <?php echo e((string) old('to_section_id') === (string) $section->id ? 'selected' : ''); ?>>
                                                <?php echo e($section->name); ?> (<?php echo e($section->grade_level); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <small class="text-muted">Used for promoted students unless overridden per row.</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if($toGradeLevel !== 'Graduated' && $sections->isEmpty()): ?>
                        <div class="alert alert-warning mb-0 mt-2">
                            No sections found for <strong><?php echo e($toGradeLevel); ?></strong>. Students will still be promoted; assign sections later if needed.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">Select Students (<?php echo e($students->count()); ?> total)</h5>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                <i class="fas fa-check-square"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                <i class="fas fa-square"></i> Deselect All
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($students->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="checkAll" class="form-check-input" checked>
                                        </th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Current Grade</th>
                                        <th>GPA</th>
                                        <th>Status</th>
                                        <?php if($toGradeLevel !== 'Graduated'): ?>
                                            <th>Section Override</th>
                                        <?php endif; ?>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $latestGpa = $student->gpaRecords->first(); ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="students[]" value="<?php echo e($student->id); ?>"
                                                       class="form-check-input student-checkbox" checked>
                                            </td>
                                            <td><?php echo e($student->admission_id ?? 'STU-'.$student->id); ?></td>
                                            <td>
                                                <strong><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo e($student->email); ?></small>
                                            </td>
                                            <td><?php echo e($student->year_level); ?></td>
                                            <td>
                                                <?php if($latestGpa): ?>
                                                    <span class="badge bg-<?php echo e($latestGpa->gpa >= 3.0 ? 'success' : ($latestGpa->gpa >= 2.0 ? 'warning' : 'danger')); ?>">
                                                        <?php echo e(number_format($latestGpa->gpa, 2)); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <select name="promotion_status[<?php echo e($student->id); ?>]"
                                                        class="form-control form-control-sm status-select"
                                                        data-student="<?php echo e($student->id); ?>">
                                                    <?php if($toGradeLevel === 'Graduated'): ?>
                                                        <option value="graduated" selected>Graduate</option>
                                                        <option value="retained">Retain (same grade)</option>
                                                    <?php else: ?>
                                                        <option value="promoted" selected>Promote</option>
                                                        <option value="retained">Retain</option>
                                                        <?php if($fromGradeLevel === 'Grade 10'): ?>
                                                            <option value="graduated">Graduate</option>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                            <?php if($toGradeLevel !== 'Graduated'): ?>
                                                <td>
                                                    <select name="student_sections[<?php echo e($student->id); ?>]"
                                                            class="form-control form-control-sm section-select"
                                                            data-student="<?php echo e($student->id); ?>">
                                                        <option value="">Use default / auto</option>
                                                        <optgroup label="Promote → <?php echo e($toGradeLevel); ?>">
                                                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($section->id); ?>"><?php echo e($section->name); ?></option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </optgroup>
                                                        <optgroup label="Retain → <?php echo e($fromGradeLevel); ?>">
                                                            <?php $__currentLoopData = $retainSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($section->id); ?>"><?php echo e($section->name); ?></option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </optgroup>
                                                    </select>
                                                </td>
                                            <?php endif; ?>
                                            <td>
                                                <input type="text" name="remarks[<?php echo e($student->id); ?>]"
                                                       class="form-control form-control-sm"
                                                       placeholder="Optional remarks">
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-graduation-cap"></i> Process Promotion
                            </button>
                            <a href="<?php echo e(route('promotions.index')); ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5>No Active Students Found</h5>
                            <p class="text-muted">There are no students in <?php echo e($fromGradeLevel); ?> to promote.</p>
                            <a href="<?php echo e(route('promotions.index')); ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Back to Promotions
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>

    </div>
</div>

<?php $__env->startSection('script'); ?>
<script>
$(document).ready(function() {
    $('#checkAll').on('change', function() {
        $('.student-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('.student-checkbox').on('change', function() {
        if (!$(this).is(':checked')) {
            $('#checkAll').prop('checked', false);
        }
    });

    $('#selectAll').on('click', function() {
        $('.student-checkbox').prop('checked', true);
        $('#checkAll').prop('checked', true);
    });

    $('#deselectAll').on('click', function() {
        $('.student-checkbox').prop('checked', false);
        $('#checkAll').prop('checked', false);
    });

    $('#promotionForm').on('submit', function(e) {
        var checkedStudents = $('.student-checkbox:checked').length;
        if (checkedStudents === 0) {
            e.preventDefault();
            alert('Please select at least one student to promote.');
            return false;
        }

        var fromAy = $('select[name="from_academic_year_id"]').val();
        var toAy = $('select[name="to_academic_year_id"]').val();
        if (fromAy && toAy && fromAy === toAy) {
            e.preventDefault();
            alert('From and To academic years should be different.');
            return false;
        }

        if (!confirm('Process promotion for ' + checkedStudents + ' student(s)? This updates grade levels, sections, and subject enrollments.')) {
            e.preventDefault();
            return false;
        }
    });

    if ($('#studentsTable tbody tr').length > 10) {
        $('#studentsTable').DataTable({
            pageLength: 25,
            order: [[2, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 5, 6, 7] }
            ]
        });
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\promotions\create.blade.php ENDPATH**/ ?>