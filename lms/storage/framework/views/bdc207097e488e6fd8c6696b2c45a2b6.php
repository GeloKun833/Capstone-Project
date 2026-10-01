<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Grade Details</h3></div>
        <div class="card">
            <div class="card-body">
                <p><strong>Student:</strong> <?php echo e($submission->student->first_name); ?> <?php echo e($submission->student->last_name); ?></p>
                <p><strong>Score:</strong> <?php echo e($submission->total_score ?? '-'); ?> / <?php echo e($submission->max_possible_score ?? '-'); ?></p>
                <p><strong>Percentage:</strong> <?php echo e($submission->percentage ? $submission->percentage . '%' : '-'); ?></p>
                <p><strong>Letter Grade:</strong> <?php echo e($submission->letter_grade ?? '-'); ?></p>
                <?php if($submission->feedback): ?><p><strong>Feedback:</strong> <?php echo e($submission->feedback); ?></p><?php endif; ?>
                <?php if($submission->grades->count()): ?>
                    <h5 class="mt-3">Rubric Scores</h5>
                    <table class="table table-bordered">
                        <thead><tr><th>Rubric</th><th>Score</th></tr></thead>
                        <tbody>
                            <?php $__currentLoopData = $submission->grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr><td><?php echo e($grade->rubric->name ?? 'Rubric'); ?></td><td><?php echo e($grade->score); ?></td></tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\activities\view-grade.blade.php ENDPATH**/ ?>