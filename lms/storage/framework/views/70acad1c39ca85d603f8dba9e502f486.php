<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="col">
                <h3 class="page-title">Edit Activity</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="<?php echo e(route('lessons.activities.update', [$lesson, $activity])); ?>" method="POST">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="<?php echo e(old('title', $activity->title)); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instructions</label>
                        <textarea name="instructions" class="form-control" rows="5" required><?php echo e(old('instructions', $activity->instructions)); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?php echo e(old('due_date', $activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('Y-m-d') : '')); ?>" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="allows_submission" class="form-check-input" id="allows_submission" <?php echo e($activity->allows_submission ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="allows_submission">Allow student submissions</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Activity</button>
                    <a href="<?php echo e(route('lessons.activities.show', [$lesson, $activity])); ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\activities\edit.blade.php ENDPATH**/ ?>