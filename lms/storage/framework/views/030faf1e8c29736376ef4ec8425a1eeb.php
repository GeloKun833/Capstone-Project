<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Study Recommendations</h3></div>
        <?php if($analysis): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Performance Summary</h5>
                    <p><strong>Overall Average:</strong> <?php echo e($analysis['overall_average'] ?? 'N/A'); ?>%</p>
                    <p><strong>Subjects Needing Improvement:</strong> <?php echo e($analysis['improvement_needed'] ?? 0); ?>%</p>
                </div>
            </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header"><h5>Recommended Lessons</h5></div>
            <div class="card-body">
                <?php $__empty_1 = true; $__currentLoopData = $recommendations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border-bottom pb-3 mb-3">
                        <h6><?php echo e($lesson->title); ?></h6>
                        <p class="text-muted mb-1"><?php echo e($lesson->subject->subject_name ?? 'Subject'); ?> — Relevance: <?php echo e($lesson->relevance_score ?? 0); ?></p>
                        <small><?php echo e($lesson->relevance_reason ?? 'Recommended based on your performance.'); ?></small>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted">No recommendations available yet. Keep studying!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\lessons\recommendations\my-recommendations.blade.php ENDPATH**/ ?>