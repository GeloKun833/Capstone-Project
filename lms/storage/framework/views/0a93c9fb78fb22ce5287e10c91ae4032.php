<?php $__env->startSection('content'); ?>
<?php $responses = $attempt->responses->keyBy('quiz_question_id'); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grade attempt</h3>
                    <p class="text-muted mb-0">
                        <?php echo e(trim(($attempt->student->last_name ?? '').', '.($attempt->student->first_name ?? ''))); ?>

                        · <?php echo e($attempt->student->admission_id ?: ($attempt->student->roll ?: 'No student number')); ?>

                        · Attempt <?php echo e($attempt->attempt_number); ?>

                        · <?php echo e($attempt->submitted_at?->timezone('Asia/Manila')->format('M j, Y g:i A')); ?>

                    </p>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('lessons.activities.quiz-results', [$lesson, $activity])); ?>" class="btn btn-outline-secondary">All results</a>
                </div>
            </div>
        </div>
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>
        <form action="<?php echo e(route('lessons.activities.quiz-grade.save', [$lesson, $activity, $attempt])); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <?php $__currentLoopData = $activity->quizQuestionItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $response = $responses->get($question->id); ?>
                <div class="dir-card mb-3">
                    <div class="p-3 p-md-4">
                        <h5><?php echo e($index + 1); ?>. <?php echo e($question->prompt); ?></h5>
                        <p class="text-muted small"><?php echo e($question->typeLabel()); ?> · <?php echo e($question->points); ?> points</p>
                        <?php if($question->attachment_path): ?>
                            <img src="<?php echo e(asset('storage/'.$question->attachment_path)); ?>" alt="" class="mb-3 rounded" style="max-width: 100%; max-height: 220px;">
                        <?php endif; ?>
                        <p class="mb-2"><strong>Student answer.</strong>
                            <?php if(in_array($question->question_type, ['short_answer', 'essay'], true)): ?>
                                <span style="white-space: pre-line;"><?php echo e(data_get($response, 'answer.text', 'No answer')); ?></span>
                            <?php else: ?>
                                <?php $selected = data_get($response, 'answer.selected', []); ?>
                                <?php echo e(collect($question->choices ?? [])->filter(fn ($choice, $choiceIndex) => in_array($choiceIndex, $selected, false))->implode(', ') ?: 'No answer'); ?>

                            <?php endif; ?>
                        </p>
                        <?php if($question->isAutoGraded()): ?>
                            <p class="mb-2"><strong>Answer key.</strong>
                                <?php echo e(collect($question->choices ?? [])->filter(fn ($choice, $choiceIndex) => in_array($choiceIndex, $question->answer_key ?? [], false))->implode(', ')); ?>

                            </p>
                            <p class="mb-0">Points earned: <?php echo e(data_get($response, 'points_earned', 0)); ?> / <?php echo e($question->points); ?></p>
                        <?php else: ?>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label" for="score<?php echo e($question->id); ?>">Points earned</label>
                                    <input class="form-control" id="score<?php echo e($question->id); ?>" type="number" min="0" max="<?php echo e($question->points); ?>" step="0.5"
                                           name="scores[<?php echo e($question->id); ?>]" value="<?php echo e($response->points_earned ?? ''); ?>">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label" for="feedback<?php echo e($question->id); ?>">Feedback for this question</label>
                                    <input class="form-control" id="feedback<?php echo e($question->id); ?>" name="question_feedback[<?php echo e($question->id); ?>]" value="<?php echo e($response->feedback ?? ''); ?>">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <div class="dir-card mb-3">
                <div class="p-3 p-md-4">
                    <label class="form-label" for="feedback">Feedback for the attempt</label>
                    <textarea class="form-control" id="feedback" name="feedback" rows="3"><?php echo e($attempt->feedback); ?></textarea>
                    <?php if($attempt->status === 'graded'): ?>
                        <p class="mt-3 mb-0">Total <?php echo e($attempt->score); ?> / <?php echo e($attempt->max_score); ?> · <?php echo e(number_format((float) $attempt->percentage, 1)); ?>%
                            · <?php echo e($attempt->passed ? 'Passed' : 'Below the passing score'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save grading</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/quizzes/grade.blade.php ENDPATH**/ ?>