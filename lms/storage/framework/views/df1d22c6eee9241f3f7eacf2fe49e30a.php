<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Quiz results</h3>
                    <p class="text-muted mb-0"><?php echo e($activity->title); ?> · Scores stay on the quiz until you encode the term grade.</p>
                </div>
                <div class="col-auto d-flex gap-2">
                    <a href="<?php echo e(route('lessons.activities.quiz-preview', [$lesson, $activity])); ?>" class="btn btn-outline-secondary">Preview</a>
                    <a href="<?php echo e(route('lessons.activities.edit', [$lesson, $activity])); ?>" class="btn btn-outline-secondary">Edit quiz</a>
                </div>
            </div>
        </div>
        <div class="dir-card">
            <div class="table-responsive">
                <table class="table dir-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student number</th>
                            <th>Attempt</th>
                            <th>Submitted</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(trim(($attempt->student->last_name ?? '').', '.($attempt->student->first_name ?? ''))); ?></td>
                                <td><?php echo e($attempt->student->admission_id ?: ($attempt->student->roll ?: '—')); ?></td>
                                <td><?php echo e($attempt->attempt_number); ?></td>
                                <td><?php echo e($attempt->submitted_at?->timezone('Asia/Manila')->format('M j, Y g:i A') ?: 'In progress'); ?></td>
                                <td>
                                    <?php if($attempt->status === 'graded'): ?>
                                        <?php echo e($attempt->score); ?> / <?php echo e($attempt->max_score); ?> (<?php echo e(number_format((float) $attempt->percentage, 1)); ?>%)
                                    <?php elseif($attempt->status === 'submitted'): ?>
                                        Needs grading
                                    <?php else: ?>
                                        In progress
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($attempt->status !== 'graded'): ?>
                                        —
                                    <?php elseif($attempt->passed === null): ?>
                                        Recorded
                                    <?php elseif($attempt->passed): ?>
                                        Passed
                                    <?php else: ?>
                                        Below passing score
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($attempt->status !== 'in_progress'): ?>
                                        <a href="<?php echo e(route('lessons.activities.quiz-grade', [$lesson, $activity, $attempt])); ?>">Review</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7">No attempts yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/quizzes/results.blade.php ENDPATH**/ ?>