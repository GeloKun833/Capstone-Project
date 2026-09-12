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
                    <h5 class="card-title mb-0 text-white">Academic Year Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">From Academic Year <span class="text-danger">*</span></label>
                                <select name="from_academic_year_id" class="form-control" required>
                                    <option value="">Select Academic Year</option>
                                    <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">To Academic Year <span class="text-danger">*</span></label>
                                <select name="to_academic_year_id" class="form-control" required>
                                    <option value="">Select Academic Year</option>
                                    <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Promotion Date <span class="text-danger">*</span></label>
                                <input type="date" name="promotion_date" class="form-control" value="<?php echo e(date('Y-m-d')); ?>" required>
                            </div>
                        </div>
                    </div>
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
                                            <input type="checkbox" id="checkAll" class="form-check-input">
                                        </th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Current Grade</th>
                                        <th>GPA</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="students[]" value="<?php echo e($student->id); ?>" 
                                                       class="form-check-input student-checkbox" checked>
                                            </td>
                                            <td><?php echo e($student->admission_id ?? 'STU-' . $student->id); ?></td>
                                            <td>
                                                <strong><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo e($student->email); ?></small>
                                            </td>
                                            <td><?php echo e($student->year_level); ?></td>
                                            <td>
                                                <?php
                                                    $latestGpa = $student->gpaRecords->first();
                                                ?>
                                                <?php if($latestGpa): ?>
                                                    <span class="badge bg-<?php echo e($latestGpa->gpa >= 3.0 ? 'success' : ($latestGpa->gpa >= 2.0 ? 'warning' : 'danger')); ?>">
                                                        <?php echo e(number_format($latestGpa->gpa, 2)); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <select name="promotion_status[<?php echo e($student->id); ?>]" class="form-control form-control-sm">
                                                    <option value="promoted" selected>Promote</option>
                                                    <option value="retained">Retain</option>
                                                    <?php if($toGradeLevel === 'Graduated'): ?>
                                                        <option value="graduated">Graduate</option>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
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
    // Select/Deselect All
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

    // Form validation
    $('#promotionForm').on('submit', function(e) {
        var checkedStudents = $('.student-checkbox:checked').length;
        
        if (checkedStudents === 0) {
            e.preventDefault();
            alert('Please select at least one student to promote.');
            return false;
        }

        if (!confirm(`Are you sure you want to promote ${checkedStudents} student(s)?`)) {
            e.preventDefault();
            return false;
        }
    });

    // DataTable
    if ($('#studentsTable tbody tr').length > 10) {
        $('#studentsTable').DataTable({
            pageLength: 25,
            order: [[2, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 5, 6] }
            ]
        });
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\promotions\create.blade.php ENDPATH**/ ?>