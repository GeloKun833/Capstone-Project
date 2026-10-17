<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Student preview</h3>
                    <p class="text-muted mb-0"><?php echo e($activity->title); ?> · Answer keys stay hidden in this preview.</p>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('lessons.activities.edit', [$lesson, $activity])); ?>" class="btn btn-outline-secondary">Back to quiz</a>
                </div>
            </div>
        </div>
        <div class="dir-card mb-3">
            <div class="p-3 p-md-4">
                <h5><?php echo e($activity->title); ?></h5>
                <p style="white-space: pre-line;"><?php echo e($activity->instructions); ?></p>
                <p class="mb-0 text-muted"><?php echo e($activity->points ?: 0); ?> points
                    <?php if($activity->time_limit_minutes): ?> · <?php echo e($activity->time_limit_minutes); ?> minutes <?php endif; ?>
                    · <?php echo e($activity->attempts_allowed ?: 1); ?> attempt<?php echo e(($activity->attempts_allowed ?: 1) === 1 ? '' : 's'); ?>

                    · Passing score <?php echo e($activity->passing_score ?: '—'); ?>%
                </p>
            </div>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="dir-card mb-3">
                <div class="p-3 p-md-4">
                    <h5>Question <?php echo e($index + 1); ?> of <?php echo e($questions->count()); ?></h5>
                    <p style="white-space: pre-line;"><?php echo e($question['prompt']); ?></p>
                    <p class="text-muted small"><?php echo e($question['type_label']); ?> · <?php echo e($question['points']); ?> points</p>
                    <?php if($question['image']): ?>
                        <img src="<?php echo e($question['image']); ?>" alt="" class="mb-3 rounded" style="max-width: 100%; max-height: 240px;">
                    <?php endif; ?>
                    <?php if(in_array($question['type'], ['short_answer', 'essay'], true)): ?>
                        <div class="form-control bg-light"><?php echo e($question['type'] === 'essay' ? 'Students write a longer response here.' : 'Students write a short response here.'); ?></div>
                    <?php else: ?>
                        <?php $__currentLoopData = $question['choices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $choice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="d-flex gap-2 mb-2"><input type="<?php echo e($question['type'] === 'multiple_select' ? 'checkbox' : 'radio'); ?>" disabled> <span><?php echo e($choice['text']); ?></span></label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="alert alert-warning">Add questions before previewing the quiz.</div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/quizzes/preview.blade.php ENDPATH**/ ?>