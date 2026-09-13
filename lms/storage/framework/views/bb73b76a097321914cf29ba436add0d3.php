
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Choose Subjects — <?php echo e($curriculum->grade_level); ?></h3>
                    <p class="text-muted mb-0">
                        Only subjects from the <strong><?php echo e($curriculum->grade_level); ?></strong> catalog are shown
                        (same list as Enrollment).
                    </p>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('curriculum.index')); ?>">Curriculum</a></li>
                        <li class="breadcrumb-item active">Assign Subjects</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('class-subject.unified-management')); ?>" class="btn btn-outline-secondary btn-sm">
                        Add subjects in catalog
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if($subjects->isEmpty()): ?>
                    <div class="alert alert-warning mb-0">
                        No subjects in the catalog for <?php echo e($curriculum->grade_level); ?> yet.
                        Go to <a href="<?php echo e(route('class-subject.unified-management')); ?>">Classes &amp; Subjects</a>
                        and add some, then sync.
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('curriculum.assignSubjects', $curriculum)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h6 class="mb-0"><?php echo e($subjects->count()); ?> subject(s) available</h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Deselect All</button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4 col-lg-3">
                                    <label class="ams-check-card w-100">
                                        <input type="checkbox" name="subject_ids[]" value="<?php echo e($subject->id); ?>"
                                            class="form-check-input me-2 subject-cb"
                                            <?php echo e(in_array($subject->id, $assigned, true) ? 'checked' : ''); ?>>
                                        <span><?php echo e($subject->subject_name); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="text-end mt-4">
                            <a href="<?php echo e(route('curriculum.index')); ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Subjects</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.ams-check-card {
    display: flex; align-items: center; gap: .35rem;
    border: 1px solid #e5e7eb; border-radius: 12px; padding: .75rem .9rem;
    background: #fff; cursor: pointer; font-weight: 600; color: #111827;
}
.ams-check-card:hover { border-color: #93c5fd; background: #f8faff; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('selectAll')?.addEventListener('click', function () {
    document.querySelectorAll('.subject-cb').forEach(function (cb) { cb.checked = true; });
});
document.getElementById('deselectAll')?.addEventListener('click', function () {
    document.querySelectorAll('.subject-cb').forEach(function (cb) { cb.checked = false; });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/curriculum/assign_subjects.blade.php ENDPATH**/ ?>