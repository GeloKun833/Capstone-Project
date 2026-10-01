
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Choose Subjects — <?php echo e($curriculum->grade_level); ?></h3>
                    <p class="text-muted mb-0">
                        Choose subjects for <strong><?php echo e($curriculum->grade_level); ?></strong>, then assign them to each created section.
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

                        <hr class="my-4">
                        <div class="mb-3">
                            <h6 class="mb-1">Subjects by section</h6>
                            <p class="text-muted small mb-0">All sections created for this grade appear here. Choose which catalog subjects belong to each section.</p>
                        </div>
                        <?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <section class="section-subject-card mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong><?php echo e($section->name); ?></strong>
                                    <span class="text-muted small"><?php echo e($section->grade_level); ?></span>
                                </div>
                                <div class="row g-2">
                                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-4 col-lg-3">
                                            <label class="ams-check-card w-100">
                                                <input type="checkbox"
                                                    name="section_subjects[<?php echo e($section->id); ?>][]"
                                                    value="<?php echo e($subject->id); ?>"
                                                    class="form-check-input me-2"
                                                    <?php echo e($section->subjects->contains('id', $subject->id) ? 'checked' : ''); ?>>
                                                <span><?php echo e($subject->subject_name); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </section>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="alert alert-info mb-0">No sections have been created for <?php echo e($curriculum->grade_level); ?> yet. Create a section first, then return here to assign its subjects.</div>
                        <?php endif; ?>

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
.section-subject-card { padding: 1rem; border: 1px solid #e5e7eb; border-radius: 10px; background: #f8fafc; }
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

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\curriculum\assign_subjects.blade.php ENDPATH**/ ?>