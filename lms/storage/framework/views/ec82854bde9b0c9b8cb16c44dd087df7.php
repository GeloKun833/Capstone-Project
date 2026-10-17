<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo e($activity->title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.activities.index', $lesson)); ?>">Activities</a></li>
                        <li class="breadcrumb-item active"><?php echo e($activity->title); ?></li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('lessons.activities.edit', [$lesson, $activity])); ?>" class="btn btn-primary me-2"><i class="fas fa-edit"></i> Edit</a>
                    <a href="<?php echo e(route('lessons.activities.submissions', [$lesson, $activity])); ?>" class="btn btn-info"><i class="fas fa-inbox"></i> Submissions</a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p><strong>Status:</strong> <?php echo e($activity->isPublished() ? 'Posted' : 'Draft'); ?></p>
                <p><strong>Due:</strong> <?php echo e($activity->formattedDue()); ?></p>
                <p><strong>Type:</strong> <?php echo e($activity->typeLabel()); ?></p>
                <p><strong>Points:</strong> <?php echo e($activity->points ?: 'Not scored'); ?></p>
                <p><strong>Student response:</strong>
                    <?php if($activity->isQuiz()): ?>
                        Quiz
                        · <?php echo e($activity->quizQuestionItems()->count()); ?> questions
                        · <?php echo e($activity->attempts_allowed ?: 1); ?> attempt<?php echo e(($activity->attempts_allowed ?: 1) === 1 ? '' : 's'); ?>

                        · Passing score <?php echo e($activity->passing_score ?: '—'); ?>%
                    <?php elseif($activity->usesFileSubmission()): ?>
                        File submission
                    <?php else: ?>
                        <?php echo e($activity->allows_submission ? 'File turn-in' : 'No file turn-in'); ?>

                    <?php endif; ?>
                </p>
                <?php if($activity->isQuiz()): ?>
                    <p>
                        <a href="<?php echo e(route('lessons.activities.quiz-preview', [$lesson, $activity])); ?>">Preview</a>
                        · <a href="<?php echo e(route('lessons.activities.quiz-results', [$lesson, $activity])); ?>">Results and grading</a>
                    </p>
                    <p class="text-muted">Answer keys stay on the grading page. This score is not entered into the term grade until you encode it.</p>
                <?php endif; ?>
                <?php $__currentLoopData = $activity->attachments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p><a href="<?php echo e(asset('storage/'.$file['path'])); ?>" target="_blank" rel="noopener"><?php echo e($file['name'] ?? 'Material'); ?></a></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <hr>
                <h5>Instructions</h5>
                <p><?php echo nl2br(e($activity->instructions)); ?></p>
                <p><strong>Submissions:</strong> <?php echo e($activity->submissions->count()); ?></p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/activities/show.blade.php ENDPATH**/ ?>