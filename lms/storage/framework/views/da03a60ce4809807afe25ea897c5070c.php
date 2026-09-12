<?php $__env->startSection('content'); ?>

<?php
    $enrollment = null;
    if (auth()->user()?->student && $lesson->subject_id) {
        $enrollment = auth()->user()->student->enrollments()
            ->where('subject_id', $lesson->subject_id)
            ->where('status', 'active')
            ->first();
    }
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo e($activity->title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <?php if($enrollment): ?>
                            <li class="breadcrumb-item">
                                <a href="<?php echo e(route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'lessons'])); ?>">
                                    <?php echo e($lesson->subject->subject_name ?? 'Class'); ?>

                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo e(route('student.lessons.show', [$enrollment->id, $lesson->id])); ?>">
                                    <?php echo e($lesson->title); ?>

                                </a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><?php echo e($lesson->title); ?></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active"><?php echo e($activity->title); ?></li>
                    </ul>
                    <p class="text-muted mb-0">
                        Lesson: <?php echo e($lesson->title); ?>

                        | Due: <?php echo e($activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('M d, Y') : 'N/A'); ?>

                    </p>
                </div>
                <div class="col-auto">
                    <?php if($enrollment): ?>
                        <a href="<?php echo e(route('student.lessons.show', [$enrollment->id, $lesson->id])); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Lesson
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5>Instructions</h5>
                <p><?php echo nl2br(e($activity->instructions)); ?></p>
            </div>
        </div>

        <?php if($activity->allows_submission): ?>
            <?php if($existingSubmission): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="alert alert-success mb-3">
                            You submitted this activity on
                            <?php echo e($existingSubmission->submitted_at?->format('M d, Y H:i') ?? $existingSubmission->created_at->format('M d, Y H:i')); ?>.
                        </div>

                        <?php if($existingSubmission->status === 'graded'): ?>
                            <h5 class="mb-3">Your Grade</h5>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Score</small>
                                        <strong class="fs-4">
                                            <?php echo e($existingSubmission->total_score); ?><?php echo e($existingSubmission->max_possible_score ? ' / ' . $existingSubmission->max_possible_score : ''); ?>

                                        </strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Percentage</small>
                                        <strong class="fs-4"><?php echo e(number_format((float) ($existingSubmission->percentage ?? 0), 1)); ?>%</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Letter Grade</small>
                                        <span class="badge bg-<?php echo e($existingSubmission->letter_grade_color); ?> fs-6">
                                            <?php echo e($existingSubmission->letter_grade ?? '-'); ?>

                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Graded On</small>
                                        <strong><?php echo e(optional($existingSubmission->graded_at)->format('M d, Y') ?? 'N/A'); ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light border rounded p-3">
                                <h6 class="mb-2"><i class="fas fa-comment me-1"></i> Teacher Comments</h6>
                                <?php if($existingSubmission->feedback): ?>
                                    <p class="mb-0" style="white-space: pre-line;"><?php echo e($existingSubmission->feedback); ?></p>
                                <?php else: ?>
                            <p class="text-muted mb-0">No teacher comments provided.</p>
                        <?php endif; ?>
                    </div>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Submitted — waiting for teacher grade</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo e(route('student.activities.submit', [$lesson, $activity])); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label">Upload File (PDF, DOC, PPT, JPG, PNG — max 10MB)</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comments (optional)</label>
                                <textarea name="comments" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Submit Activity</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">This activity does not require an online submission.</div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\activities\student-show.blade.php ENDPATH**/ ?>