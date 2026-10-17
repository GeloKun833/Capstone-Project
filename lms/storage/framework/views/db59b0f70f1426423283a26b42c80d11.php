<?php $__env->startSection('content'); ?>

<?php
    $total = $activities->count();
?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Lesson Activities</h3>
                    <p class="dir-subtitle">
                        <?php echo e($lesson->title); ?>

                        <?php if($lesson->subject): ?>
                            · <?php echo e($lesson->subject->subject_name); ?>

                        <?php endif; ?>
                        <?php if($lesson->section): ?>
                            · <?php echo e($lesson->section->grade_level ? $lesson->section->grade_level.' · ' : ''); ?><?php echo e($lesson->section->name); ?>

                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.index')); ?>">Lesson Planner</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.show', $lesson)); ?>"><?php echo e($lesson->title); ?></a></li>
                        <li class="breadcrumb-item active">Activities</li>
                    </ul>
                    <div class="dir-header-actions">
                        <a href="<?php echo e(route('lessons.show', $lesson)); ?>" class="btn btn-outline-secondary dir-btn">Back</a>
                        <a href="<?php echo e(route('lessons.activities.create', $lesson)); ?>" class="btn btn-primary dir-btn">
                            <i class="fas fa-plus me-1"></i> Add Activity
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Activities</h5>
                    <span class="dir-count mt-1"><?php echo e($total); ?> activit<?php echo e($total === 1 ? 'y' : 'ies'); ?></span>
                </div>
                <form method="GET" class="d-flex flex-wrap gap-2">
                    <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control" placeholder="Search title" style="min-width: 180px;">
                    <select name="status" class="form-control" style="min-width: 140px;">
                        <option value="">Draft and posted</option>
                        <option value="draft" <?php if($status === 'draft'): echo 'selected'; endif; ?>>Draft</option>
                        <option value="published" <?php if($status === 'published'): echo 'selected'; endif; ?>>Posted</option>
                    </select>
                    <button class="btn btn-outline-secondary dir-btn" type="submit">Filter</button>
                </form>
            </div>

            <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $due = $activity->due_date;
                    $dueLabel = $activity->formattedDue();
                    $dueState = 'muted';
                    $dueHint = '';
                    if ($due) {
                        if ($activity->is_overdue) {
                            $dueState = 'danger';
                            $dueHint = 'Overdue';
                        } elseif ($due->isToday()) {
                            $dueState = 'warn';
                            $dueHint = 'Due today';
                        } else {
                            $dueState = 'ok';
                            $dueHint = $due->diffForHumans();
                        }
                    }
                    $submitted = (int) $activity->submission_count;
                    $graded = (int) $activity->graded_count;
                ?>
                <article class="act-row">
                    <div class="act-row-main">
                        <a class="act-title" href="<?php echo e(route('lessons.activities.show', [$lesson, $activity])); ?>"><?php echo e($activity->title); ?></a>
                        <?php if($activity->activity_type): ?>
                            <div class="text-muted small"><?php echo e($activity->typeLabel()); ?></div>
                        <?php endif; ?>
                        <p class="act-copy"><?php echo e(Str::limit(strip_tags((string) $activity->instructions), 140)); ?></p>
                        <div class="act-meta">
                            <span class="act-chip act-chip-<?php echo e($dueState); ?>"><i class="far fa-calendar"></i> <?php echo e($dueLabel); ?><?php if($dueHint): ?> · <?php echo e($dueHint); ?><?php endif; ?></span>
                            <?php if($activity->isQuiz()): ?>
                                <span class="act-chip">Quiz · <?php echo e($activity->points ?: 0); ?> points</span>
                            <?php elseif($activity->allows_submission): ?>
                                <span class="act-chip"><?php echo e($submitted); ?> submitted · <?php echo e($graded); ?> graded</span>
                                <span class="act-chip act-chip-ok">Submissions on</span>
                            <?php else: ?>
                                <span class="act-chip">No file upload</span>
                            <?php endif; ?>
                            <?php if($activity->isPublished()): ?>
                                <span class="act-chip act-chip-ok">Posted</span>
                            <?php else: ?>
                                <span class="act-chip act-chip-warn">Draft</span>
                            <?php endif; ?>
                            <?php if($activity->points): ?>
                                <span class="act-chip"><?php echo e($activity->points); ?> points</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="act-row-actions">
                        <?php if($activity->isQuiz() && $activity->isPublished()): ?>
                            <a href="<?php echo e(route('lessons.activities.quiz-results', [$lesson, $activity])); ?>" class="dir-icon-btn" title="Quiz results"><i class="fas fa-check-square"></i></a>
                        <?php elseif($activity->allows_submission && $activity->isPublished()): ?>
                            <a href="<?php echo e(route('lessons.activities.submissions', [$lesson, $activity])); ?>" class="dir-icon-btn" title="Submissions"><i class="fas fa-upload"></i></a>
                        <?php endif; ?>
                        <?php if($activity->isPublished()): ?>
                            <form action="<?php echo e(route('lessons.activities.unpublish', [$lesson, $activity])); ?>" method="POST" <?php if($activity->isQuiz()): ?> onsubmit="return confirm('Unpublish this quiz? Students cannot start a new attempt. Existing results stay.')" <?php endif; ?>>
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="dir-icon-btn" title="Move to draft">Draft</button>
                            </form>
                        <?php else: ?>
                            <form action="<?php echo e(route('lessons.activities.publish', [$lesson, $activity])); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="dir-icon-btn" title="Post">Post</button>
                            </form>
                        <?php endif; ?>
                        <form action="<?php echo e(route('lessons.activities.duplicate', [$lesson, $activity])); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dir-icon-btn" title="Duplicate"><i class="far fa-copy"></i></button>
                        </form>
                        <a href="<?php echo e(route('lessons.activities.show', [$lesson, $activity])); ?>" class="dir-icon-btn" title="View"><i class="far fa-eye"></i></a>
                        <a href="<?php echo e(route('lessons.activities.edit', [$lesson, $activity])); ?>" class="dir-icon-btn" title="Edit"><i class="far fa-edit"></i></a>
                        <button type="button" class="dir-icon-btn is-danger delete-activity-btn" title="Delete"
                                data-title="<?php echo e($activity->title); ?>"
                                data-action="<?php echo e(route('lessons.activities.destroy', [$lesson, $activity])); ?>">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="dir-empty">
                    <i class="fas fa-tasks d-block"></i>
                    <h5 class="mt-2 mb-1">No activities yet</h5>
                    <p class="mb-3">Add a task, worksheet, or practice activity for this lesson.</p>
                    <a href="<?php echo e(route('lessons.activities.create', $lesson)); ?>" class="btn btn-primary dir-btn">
                        <i class="fas fa-plus me-1"></i> Create Activity
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="deleteActivityModal" tabindex="-1" aria-labelledby="deleteActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteActivityModalLabel">Delete activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Delete “<strong id="deleteActivityTitle"></strong>”?</p>
                <p class="dir-muted mb-0">The activity and its student submissions will be removed. This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteActivityForm" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger dir-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260915b">
<style>
.act-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.15rem;
    border-top: 1px solid #eef2f7;
}
.act-row:first-of-type { border-top: 0; }
.act-title {
    display: block;
    font-weight: 750;
    color: #111827;
    text-decoration: none;
    margin-bottom: 0.2rem;
}
.act-title:hover { color: #3d5ee1; }
.act-copy { margin: 0 0 0.55rem; color: #6b7280; font-size: 0.9rem; }
.act-meta { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.act-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.72rem;
    font-weight: 650;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e8eaed;
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
}
.act-chip-ok { background: #ecfdf5; border-color: #d1fae5; color: #047857; }
.act-chip-warn { background: #fff7ed; border-color: #ffedd5; color: #c2410c; }
.act-chip-danger { background: #fef2f2; border-color: #fee2e2; color: #b91c1c; }
.act-row-actions { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 0.25rem; }
.act-row-actions form { margin: 0; }
@media (max-width: 700px) {
    .act-row { flex-direction: column; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.delete-activity-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        document.getElementById('deleteActivityTitle').textContent = button.dataset.title || 'this activity';
        document.getElementById('deleteActivityForm').action = button.dataset.action;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteActivityModal')).show();
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/activities/index.blade.php ENDPATH**/ ?>