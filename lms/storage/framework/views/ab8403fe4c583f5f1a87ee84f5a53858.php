<?php $__env->startSection('content'); ?>

<?php
    $canCreate = auth()->user()->role_name === 'Teacher';
    $assignmentTotal = $stats['total'] ?? (method_exists($assignments, 'total') ? $assignments->total() : $assignments->count());
?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page asg-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">All Assignments</h3>
                    <p class="dir-subtitle">Create, publish, and grade work for your classes.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end <?php echo e($canCreate ? 'mb-2' : 'mb-0'); ?>">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">All Assignments</li>
                    </ul>
                    <?php if($canCreate): ?>
                        <a href="<?php echo e(route('assignments.create')); ?>" class="btn btn-primary dir-btn">
                            <i class="fas fa-plus me-1"></i> Create Assignment
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="asg-stats">
            <div class="asg-stat">
                <span>Total</span>
                <strong><?php echo e($stats['total'] ?? $assignmentTotal); ?></strong>
            </div>
            <div class="asg-stat">
                <span>Published</span>
                <strong><?php echo e($stats['published'] ?? 0); ?></strong>
            </div>
            <div class="asg-stat">
                <span>Draft</span>
                <strong><?php echo e($stats['draft'] ?? 0); ?></strong>
            </div>
            <div class="asg-stat">
                <span>Due this week</span>
                <strong><?php echo e($stats['due_soon'] ?? 0); ?></strong>
            </div>
        </div>

        <div class="asg-card asg-filters">
            <form method="GET" action="<?php echo e(route('assignments.index')); ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Title or description">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label" for="subject_id">Subject</label>
                        <select class="form-control" id="subject_id" name="subject_id">
                            <option value="">All Subjects</option>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($subject->id); ?>" <?php echo e((string) request('subject_id') === (string) $subject->id ? 'selected' : ''); ?>>
                                    <?php echo e($subject->subject_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label" for="section_id">Section</label>
                        <select class="form-control" id="section_id" name="section_id">
                            <option value="">All Sections</option>
                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>" <?php echo e((string) request('section_id') === (string) $section->id ? 'selected' : ''); ?>>
                                    <?php echo e($section->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                            <option value="published" <?php echo e(request('status') == 'published' ? 'selected' : ''); ?>>Published</option>
                            <option value="closed" <?php echo e(request('status') == 'closed' ? 'selected' : ''); ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 pb-1">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="<?php echo e(route('assignments.index')); ?>" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="asg-card">
            <div class="asg-toolbar">
                <div>
                    <h5>Assignment list</h5>
                    <span><?php echo e($assignmentTotal); ?> assignment<?php echo e($assignmentTotal === 1 ? '' : 's'); ?></span>
                </div>
            </div>

            <?php if($assignments->count() > 0): ?>
                <div class="asg-list">
                    <?php $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $due = $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date) : null;
                            $isOverdue = $due && $due->lt(now()->startOfDay()) && $assignment->status === 'published';
                            $isDueSoon = $due && !$isOverdue && $due->lte(now()->addDays(3)->endOfDay()) && $assignment->status === 'published';
                            $statusTone = match ($assignment->status) {
                                'published' => 'is-live',
                                'closed' => 'is-closed',
                                default => 'is-draft',
                            };
                            $subCount = $assignment->submissions_count ?? $assignment->submissions()->count();
                        ?>
                        <article class="asg-item <?php echo e($statusTone); ?>">
                            <div class="asg-item-main">
                                <a href="<?php echo e(route('assignments.show', $assignment->id)); ?>" class="asg-title"><?php echo e($assignment->title); ?></a>
                                <?php if($assignment->description): ?>
                                    <p class="asg-desc"><?php echo e(Str::limit(strip_tags($assignment->description), 110)); ?></p>
                                <?php endif; ?>
                                <div class="asg-meta">
                                    <span class="asg-pill"><?php echo e($assignment->subject->subject_name ?? 'No subject'); ?></span>
                                    <span class="asg-pill asg-pill-soft"><?php echo e($assignment->section->name ?? 'No section'); ?></span>
                                    <span class="asg-status <?php echo e($statusTone); ?>"><?php echo e(ucfirst($assignment->status)); ?></span>
                                </div>
                            </div>
                            <div class="asg-item-side">
                                <div class="asg-due <?php echo e($isOverdue ? 'is-overdue' : ($isDueSoon ? 'is-soon' : '')); ?>">
                                    <?php if($due): ?>
                                        <strong><?php echo e($due->format('M j, Y')); ?></strong>
                                        <small><?php echo e($isOverdue ? 'Overdue' : $due->diffForHumans()); ?></small>
                                    <?php else: ?>
                                        <strong>No due date</strong>
                                        <small>Open</small>
                                    <?php endif; ?>
                                </div>
                                <div class="asg-subs">
                                    <strong><?php echo e($subCount); ?></strong>
                                    <small>submission<?php echo e($subCount === 1 ? '' : 's'); ?></small>
                                </div>
                            </div>
                            <div class="asg-item-actions">
                                <a href="<?php echo e(route('assignments.show', $assignment->id)); ?>" class="dir-icon-btn" title="View"><i class="far fa-eye"></i></a>
                                <a href="<?php echo e(route('assignments.edit', $assignment->id)); ?>" class="dir-icon-btn" title="Edit"><i class="far fa-edit"></i></a>
                                <a href="<?php echo e(route('assignments.submissions', $assignment->id)); ?>" class="dir-icon-btn" title="Grade submissions"><i class="fas fa-check-circle"></i></a>
                                <?php if($assignment->status === 'draft'): ?>
                                    <form action="<?php echo e(route('assignments.publish', $assignment->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="dir-icon-btn is-success" title="Publish"><i class="fas fa-paper-plane"></i></button>
                                    </form>
                                <?php elseif($assignment->status === 'published'): ?>
                                    <form action="<?php echo e(route('assignments.close', $assignment->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="dir-icon-btn" title="Close"><i class="fas fa-lock"></i></button>
                                    </form>
                                <?php endif; ?>
                                <form action="<?php echo e(route('assignments.destroy', $assignment->id)); ?>" method="POST" onsubmit="return confirm('Delete this assignment?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="dir-icon-btn is-danger" title="Delete"><i class="far fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($assignments->hasPages()): ?>
                    <div class="p-3 d-flex justify-content-center">
                        <?php echo e($assignments->links()); ?>

                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="dir-empty">
                    <i class="fas fa-tasks d-block"></i>
                    <h5 class="mt-2 mb-1">No assignments found</h5>
                    <p class="mb-3">Create an assignment for one of your classes to get started.</p>
                    <?php if($canCreate): ?>
                        <a href="<?php echo e(route('assignments.create')); ?>" class="btn btn-primary dir-btn">
                            <i class="fas fa-plus me-1"></i> Create Assignment
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914i">
<style>
.asg-page .page-header { margin-bottom: 0.85rem; }
.asg-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
    margin-bottom: 0.9rem;
}
.asg-stat,
.asg-card {
    background: #fff;
    border: 1px solid #e8eef7;
    border-radius: 18px;
    box-shadow: 0 14px 32px rgba(79, 114, 205, 0.05);
}
.asg-stat { padding: 0.95rem 1.05rem; }
.asg-stat span {
    display: block;
    font-size: 0.72rem;
    font-weight: 750;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 0.2rem;
}
.asg-stat strong {
    font-size: 1.45rem;
    font-weight: 750;
    color: #1e293b;
}
.asg-filters { padding: 1rem 1.1rem 0.65rem; }
.asg-filters .form-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #94a3b8;
}
.asg-filters .form-control {
    border-radius: 10px;
    border-color: #e5e7eb;
    min-height: 42px;
}
.asg-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.95rem 1.15rem;
    border-bottom: 1px solid #eef2f7;
}
.asg-toolbar h5 {
    margin: 0;
    font-size: 1.02rem;
    font-weight: 750;
    color: #1e293b;
}
.asg-toolbar span {
    display: inline-flex;
    margin-top: 0.2rem;
    padding: 0 0.5rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 0.74rem;
    font-weight: 700;
}
.asg-list { padding: 0.55rem 0.7rem 0.8rem; }
.asg-item {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 180px auto;
    gap: 1rem;
    align-items: center;
    padding: 0.95rem 1rem 0.95rem 1.05rem;
    margin-bottom: 0.55rem;
    border: 1px solid #edf1f7;
    border-radius: 16px;
    background: #fff;
    box-shadow: inset 4px 0 0 #c7d2fe;
}
.asg-item.is-live { box-shadow: inset 4px 0 0 #34d399; }
.asg-item.is-draft { box-shadow: inset 4px 0 0 #fbbf24; }
.asg-item.is-closed { box-shadow: inset 4px 0 0 #cbd5e1; }
.asg-title {
    display: block;
    font-size: 1.02rem;
    font-weight: 750;
    color: #1e293b;
    text-decoration: none;
    line-height: 1.3;
}
.asg-title:hover { color: #4f46e5; }
.asg-desc {
    margin: 0.28rem 0 0.5rem;
    font-size: 0.82rem;
    color: #94a3b8;
    line-height: 1.4;
}
.asg-meta { display: flex; flex-wrap: wrap; gap: 0.35rem; }
.asg-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 0.74rem;
    font-weight: 650;
}
.asg-pill-soft { background: #f1f5f9; color: #475569; }
.asg-status {
    display: inline-flex;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 750;
}
.asg-status.is-live { background: #dcfce7; color: #166534; }
.asg-status.is-draft { background: #fef3c7; color: #92400e; }
.asg-status.is-closed { background: #e2e8f0; color: #475569; }
.asg-item-side {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}
.asg-due, .asg-subs { text-align: right; }
.asg-due strong, .asg-subs strong {
    display: block;
    font-size: 0.9rem;
    color: #1e293b;
}
.asg-due small, .asg-subs small {
    color: #94a3b8;
    font-size: 0.72rem;
}
.asg-due.is-soon strong { color: #b45309; }
.asg-due.is-overdue strong, .asg-due.is-overdue small { color: #b91c1c; }
.asg-item-actions {
    display: flex;
    gap: 0.3rem;
    justify-content: flex-end;
}
.asg-item-actions form { display: inline; }
@media (max-width: 991px) {
    .asg-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .asg-item { grid-template-columns: 1fr; }
    .asg-item-side { justify-content: flex-start; }
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/assignments/index.blade.php ENDPATH**/ ?>