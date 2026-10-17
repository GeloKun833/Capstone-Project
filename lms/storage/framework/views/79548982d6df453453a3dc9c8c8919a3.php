<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grade Encoding Access</h3>
                    <p class="dir-subtitle">Set the grade entry dates first, then allow teachers to encode grades.</p>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
        <?php if(isset($errors) && $errors->any()): ?>
            <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>

        <div class="dir-card mb-4">
            <div class="p-3 p-md-4">
                <h5 class="mb-1">Grade entry dates</h5>
                <p class="text-muted small mb-2">Set the opening date and the due date first. Teachers can encode grades only from the opening date through the due date.</p>
                <p class="mb-3"><strong><?php echo e($windowNote); ?></strong></p>
                <form method="POST" action="<?php echo e(route('admin.grade-encoding.window')); ?>" id="encodingWindowForm">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="encodingStartsOn">Opening date</label>
                            <input type="date" class="form-control" id="encodingStartsOn" name="encoding_starts_on" required
                                   min="<?php echo e($startMin); ?>"
                                   value="<?php echo e(old('encoding_starts_on', $startsOn)); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="encodingDueOn">Due date</label>
                            <input type="date" class="form-control" id="encodingDueOn" name="encoding_due_on" required
                                   min="<?php echo e(old('encoding_starts_on', $startsOn) ?: $schoolToday); ?>"
                                   value="<?php echo e(old('encoding_due_on', $dueOn)); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary dir-btn mt-3">Save dates</button>
                </form>
            </div>
        </div>

        <div class="dir-card mb-4">
            <div class="p-3 p-md-4">
                <h5 class="mb-1">Open grade levels</h5>
                <p class="text-muted small">Teachers can encode grades only for the levels you leave checked. Nursery and Kindergarten use descriptive letters. Grade 1 through Grade 3 also use letters. Grade 4 through Grade 10 use scores from 0 to 100.</p>
                <form method="POST" action="<?php echo e(route('admin.grade-encoding.levels')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="row g-2">
                        <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $checked = $openLevels === null || in_array($grade, $openLevels, true);
                            ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <label class="d-flex align-items-center gap-2 mb-0">
                                    <input type="checkbox" name="grade_levels[]" value="<?php echo e($grade); ?>" <?php echo e($checked ? 'checked' : ''); ?>>
                                    <span><?php echo e($grade); ?></span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <button type="submit" class="btn btn-primary dir-btn mt-3">Save grade levels</button>
                </form>
            </div>
        </div>

        <div class="dir-card">
            <div class="p-3 p-md-4 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h5 class="mb-1">Teachers</h5>
                        <p class="text-muted small mb-0">This list is teachers only. Students, parents, and registrars are not included.</p>
                    </div>
                    <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#allowAllTeachersModal" <?php if(! $hasWindow): echo 'disabled'; endif; ?>>
                        Allow all teachers
                    </button>
                </div>
                <?php if (! ($hasWindow)): ?>
                    <p class="text-muted small">Set the opening date and the due date before allowing teachers.</p>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Account</th>
                            <th>Grade Encoding Access</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $allowed = $user->permissions->contains('name', $permission); ?>
                            <tr>
                                <td><?php echo e($user->name); ?></td>
                                <td><?php echo e($user->isActiveAccount() ? 'Active' : 'Inactive'); ?></td>
                                <td><?php echo e($allowed ? 'Allowed' : 'Not Allowed'); ?></td>
                                <td class="text-end">
                                    <?php if($allowed): ?>
                                        <form method="POST" action="<?php echo e(route('admin.grade-encoding.update', $user)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="allow" value="0">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Revoke</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?php echo e(route('admin.grade-encoding.update', $user)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="allow" value="1">
                                            <button type="submit" class="btn btn-sm btn-primary" <?php if(! $hasWindow): echo 'disabled'; endif; ?>>Allow</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4">No teachers are available.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="allowAllTeachersModal" tabindex="-1" aria-labelledby="allowAllTeachersTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allowAllTeachersTitle">Allow all teachers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Allow every teacher to encode grades?</p>
                <p class="dir-muted mb-0"><?php echo e($users->count()); ?> <?php echo e($users->count() === 1 ? 'teacher' : 'teachers'); ?> will be allowed. <?php echo e($windowNote); ?> Students, parents, and registrars stay off this list.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?php echo e(route('admin.grade-encoding.allow-all')); ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-primary dir-btn">Allow all teachers</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20261018b">
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    var start = document.getElementById('encodingStartsOn');
    var due = document.getElementById('encodingDueOn');
    if (!start || !due) return;
    function syncDue() {
        if (!start.value) return;
        due.min = start.value;
        if (due.value && due.value < start.value) due.value = start.value;
    }
    start.addEventListener('change', syncDue);
    syncDue();
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/admin/grade-encoding/index.blade.php ENDPATH**/ ?>