<?php $__env->startSection('content'); ?>

<?php
    $selectedGrades = old('access_allowed_grades', $settings->access_allowed_grades ?? $grades);
    if (!is_array($selectedGrades)) {
        $selectedGrades = $grades;
    }
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Access Control</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('setting/page')); ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Access Control</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="settings-menu-links mb-4">
            <ul class="nav nav-tabs menu-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('setting/page')); ?>">
                        <i class="fas fa-cog me-2"></i>General Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo e(route('setting.access-limits')); ?>">
                        <i class="fas fa-user-lock me-2"></i>Access Control
                    </a>
                </li>
            </ul>
        </div>

        <div class="alert <?php echo e($settings->access_limits_enabled ? 'alert-warning' : 'alert-info'); ?>">
            <div class="d-flex align-items-start gap-2">
                <i class="fas <?php echo e($settings->access_limits_enabled ? 'fa-exclamation-triangle' : 'fa-info-circle'); ?> mt-1"></i>
                <div>
                    <strong><?php echo e($settings->access_limits_enabled ? 'Limited Access is ON' : 'Limited Access is OFF'); ?></strong>
                    <div class="small mb-0">
                        Use this during system overload or maintenance. Admin and Registrar always keep full access.
                        For other roles, only the first N accounts (by account ID) within your quotas can log in.
                    </div>
                </div>
            </div>
        </div>

        <form action="<?php echo e(route('setting.access-limits.update')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="row">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sliders-h me-2 text-primary"></i>Load / Maintenance Quotas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="access_limits_enabled" id="access_limits_enabled" value="1"
                                       <?php echo e(old('access_limits_enabled', $settings->access_limits_enabled) ? 'checked' : ''); ?>>
                                <label class="form-check-label fw-semibold" for="access_limits_enabled">
                                    Enable limited access mode
                                </label>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Teachers</label>
                                    <input type="number" min="0" max="5000" name="max_teachers" class="form-control"
                                           value="<?php echo e(old('max_teachers', $settings->max_teachers)); ?>"
                                           placeholder="e.g. 5">
                                    <small class="text-muted">Blank = unlimited. 0 = none.</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Students / Grade</label>
                                    <input type="number" min="0" max="5000" name="max_students_per_grade" class="form-control"
                                           value="<?php echo e(old('max_students_per_grade', $settings->max_students_per_grade)); ?>"
                                           placeholder="e.g. 10">
                                    <small class="text-muted">Blank = unlimited. 0 = none.</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Parents / Grade</label>
                                    <input type="number" min="0" max="5000" name="max_parents_per_grade" class="form-control"
                                           value="<?php echo e(old('max_parents_per_grade', $settings->max_parents_per_grade)); ?>"
                                           placeholder="e.g. 10">
                                    <small class="text-muted">Blank = unlimited. 0 = none.</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Grades included in limits</label>
                                <div class="border rounded p-3" style="max-height: 220px; overflow-y: auto;">
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="select-all-grades">Select all</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-grades">Clear</button>
                                    </div>
                                    <div class="row">
                                        <?php $__currentLoopData = $grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input grade-checkbox" type="checkbox"
                                                           name="access_allowed_grades[]" value="<?php echo e($grade); ?>"
                                                           id="grade_<?php echo e(\Illuminate\Support\Str::slug($grade)); ?>"
                                                           <?php echo e(in_array($grade, $selectedGrades, true) ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="grade_<?php echo e(\Illuminate\Support\Str::slug($grade)); ?>">
                                                        <?php echo e($grade); ?>

                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <small class="text-muted">If none selected, all grades are included.</small>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Message shown to blocked users</label>
                                <textarea name="access_limits_message" class="form-control" rows="3"
                                          placeholder="System is under limited access due to high load. Please try again later."><?php echo e(old('access_limits_message', $settings->access_limits_message)); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie me-2 text-success"></i>Current Preview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 p-3 rounded border">
                                <div class="d-flex justify-content-between">
                                    <span>Teachers</span>
                                    <strong><?php echo e($preview['teachers_allowed']); ?> / <?php echo e($preview['teachers_total']); ?> allowed</strong>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Grade</th>
                                            <th>Students</th>
                                            <th>Parents</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $preview['grades']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td><?php echo e($row['grade']); ?></td>
                                                <td><?php echo e($row['students_allowed']); ?> / <?php echo e($row['students_total']); ?></td>
                                                <td><?php echo e($row['parents_allowed']); ?> / <?php echo e($row['parents_total']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="3" class="text-muted text-center">No grade data</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted d-block mt-3">
                                Preview uses your saved settings. Save first to refresh quotas after edits.
                            </small>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-2">How selection works</h6>
                            <ul class="small text-muted mb-0 ps-3">
                                <li>Admin &amp; Registrar are never blocked.</li>
                                <li>Example: Teachers = 5 → first 5 active teacher accounts can log in.</li>
                                <li>Example: Students/Parents = 10 per grade → first 10 per selected grade.</li>
                                <li>Already-logged-in users outside the quota are signed out on the next page load.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('setting/page')); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Access Control
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.getElementById('select-all-grades')?.addEventListener('click', function () {
        document.querySelectorAll('.grade-checkbox').forEach(function (el) { el.checked = true; });
    });
    document.getElementById('clear-grades')?.addEventListener('click', function () {
        document.querySelectorAll('.grade-checkbox').forEach(function (el) { el.checked = false; });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/setting/access-limits.blade.php ENDPATH**/ ?>