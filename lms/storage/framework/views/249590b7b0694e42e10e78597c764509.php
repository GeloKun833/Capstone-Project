<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo e($lesson->title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item">
                            <a href="<?php echo e(route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'lessons'])); ?>">
                                <?php echo e($enrollment->subject->subject_name ?? 'Class'); ?>

                            </a>
                        </li>
                        <li class="breadcrumb-item active">Lesson</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'lessons'])); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Class
                    </a>
                </div>
            </div>
        </div>

        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge <?php echo e($lesson->status_badge); ?>"><?php echo e(ucfirst($lesson->status)); ?></span>
                                <span class="badge bg-light text-dark ms-1">Lesson #<?php echo e($lesson->id); ?></span>
                            </div>
                            <div class="text-muted">
                                <?php echo e(optional($lesson->lesson_date)->format('M d, Y')); ?>

                            </div>
                        </div>

                        <h4 class="mb-3"><?php echo e($lesson->title); ?></h4>
                        <p class="mb-4" style="white-space: pre-line;"><?php echo e($lesson->description); ?></p>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Subject</small>
                                    <strong><?php echo e($lesson->subject->subject_name ?? 'N/A'); ?></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Section</small>
                                    <strong><?php echo e($lesson->section->name ?? 'N/A'); ?></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Teacher</small>
                                    <strong><?php echo e(optional($lesson->teacher)->full_name ?? optional($lesson->teacher)->name ?? 'N/A'); ?></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Academic Period</small>
                                    <strong>
                                        <?php echo e(optional($lesson->academicYear)->name ?? 'N/A'); ?>

                                        —
                                        <?php echo e(optional($lesson->semester)->name ?? 'N/A'); ?>

                                    </strong>
                                </div>
                            </div>
                        </div>

                        <?php if($lesson->file_url): ?>
                            <div class="alert alert-light border d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-paperclip me-2"></i>
                                    <strong><?php echo e($lesson->file_name ?? 'Lesson Materials'); ?></strong>
                                </div>
                                <a href="<?php echo e($lesson->file_url); ?>" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $lesson->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $submission = ($mySubmissions ?? collect())->get($activity->id);
                                $isGraded = $submission && $submission->status === 'graded';
                            ?>
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?php echo e($activity->title ?? ('Activity #' . $activity->id)); ?></div>
                                        <small class="text-muted d-block mb-2">
                                            Due: <?php echo e(optional($activity->due_date)->format('M d, Y') ?? 'N/A'); ?>

                                        </small>

                                        <?php if($isGraded): ?>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <span class="badge" style="background:#198754;color:#fff;">Graded</span>
                                                <span class="badge" style="background:#0d6efd;color:#fff;">
                                                    Score: <?php echo e($submission->total_score); ?><?php echo e($submission->max_possible_score ? ' / ' . $submission->max_possible_score : ''); ?>

                                                </span>
                                                <?php if($submission->percentage !== null): ?>
                                                    <span class="badge" style="background:#0dcaf0;color:#062830;">
                                                        <?php echo e(number_format((float) $submission->percentage, 1)); ?>%
                                                    </span>
                                                <?php endif; ?>
                                                <?php if($submission->letter_grade): ?>
                                                    <span class="badge" style="background:#198754;color:#fff;"><?php echo e($submission->letter_grade); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if($submission->feedback): ?>
                                                <div class="bg-light border rounded p-2 mt-1">
                                                    <small class="text-muted d-block mb-1"><i class="fas fa-comment me-1"></i>Teacher Comments</small>
                                                    <div style="white-space: pre-line;"><?php echo e($submission->feedback); ?></div>
                                                </div>
                                            <?php else: ?>
                                                <small class="text-muted">No teacher comments yet.</small>
                                            <?php endif; ?>
                                        <?php elseif($submission): ?>
                                            <span class="badge bg-warning text-dark">Submitted — waiting for grade</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not submitted</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-nowrap">
                                        <?php if(Route::has('student.activities.show')): ?>
                                            <a href="<?php echo e(route('student.activities.show', [$lesson->id, $activity->id])); ?>" class="btn btn-sm btn-outline-primary">
                                                Open
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-muted mb-0">No activities added to this lesson yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\student\lesson-show.blade.php ENDPATH**/ ?>