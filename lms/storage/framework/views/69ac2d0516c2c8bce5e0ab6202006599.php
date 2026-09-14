
<?php $__env->startSection('content'); ?>

<?php
    $canCreate = auth()->user()->role_name === 'Teacher';
    $postTotal = method_exists($posts, 'total') ? $posts->total() : $posts->count();
?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page asg-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Class Posts</h3>
                    <p class="dir-subtitle">Share announcements, resources, and reminders with your classes.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end <?php echo e($canCreate ? 'mb-2' : 'mb-0'); ?>">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Class Posts</li>
                    </ul>
                    <?php if($canCreate): ?>
                        <a href="<?php echo e(route('class-posts.create')); ?>" class="btn btn-primary dir-btn">
                            <i class="fas fa-plus me-1"></i> Create Post
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="asg-card asg-filters">
            <form method="GET" action="<?php echo e(route('class-posts.index')); ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Title or content" value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-control">
                            <option value="">All Subjects</option>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($subject->id); ?>" <?php echo e((string) request('subject_id') === (string) $subject->id ? 'selected' : ''); ?>>
                                    <?php echo e($subject->subject_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-control">
                            <option value="">All Sections</option>
                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>" <?php echo e((string) request('section_id') === (string) $section->id ? 'selected' : ''); ?>>
                                    <?php echo e($section->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <?php $__currentLoopData = ['announcement','resource','discussion','reminder']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>" <?php echo e(request('type') == $type ? 'selected' : ''); ?>><?php echo e(ucfirst($type)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 pb-1">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="<?php echo e(route('class-posts.index')); ?>" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="asg-card">
            <div class="asg-toolbar">
                <div>
                    <h5>All posts</h5>
                    <span><?php echo e($postTotal); ?> post<?php echo e($postTotal === 1 ? '' : 's'); ?></span>
                </div>
            </div>

            <?php if($posts->count() > 0): ?>
                <div class="asg-list">
                    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $teacherName = $post->teacher->full_name ?? $post->teacher->name ?? 'Unknown';
                        ?>
                        <article class="asg-item <?php echo e($post->is_pinned ? 'is-live' : ''); ?>">
                            <div class="asg-item-main">
                                <a href="<?php echo e(route('class-posts.show', $post->id)); ?>" class="asg-title"><?php echo e($post->title); ?></a>
                                <p class="asg-desc"><?php echo e(Str::limit(strip_tags($post->content), 140)); ?></p>
                                <div class="asg-meta">
                                    <?php if($post->is_pinned): ?>
                                        <span class="asg-status is-live"><i class="fas fa-thumbtack me-1"></i>Pinned</span>
                                    <?php endif; ?>
                                    <span class="asg-pill"><?php echo e(ucfirst($post->type)); ?></span>
                                    <span class="asg-pill asg-pill-soft"><?php echo e(ucfirst($post->priority)); ?></span>
                                    <span class="asg-pill asg-pill-soft"><?php echo e($post->subject->subject_name ?? 'N/A'); ?></span>
                                    <span class="asg-pill asg-pill-soft"><?php echo e($post->section->name ?? 'N/A'); ?></span>
                                    <?php if($post->file_path): ?>
                                        <span class="asg-pill asg-pill-soft"><i class="fas fa-paperclip me-1"></i>Attachment</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="asg-item-side">
                                <div class="asg-due">
                                    <strong><?php echo e($teacherName); ?></strong>
                                    <small><?php echo e($post->created_at->diffForHumans()); ?></small>
                                </div>
                            </div>
                            <div class="asg-item-actions">
                                <a href="<?php echo e(route('class-posts.show', $post->id)); ?>" class="dir-icon-btn" title="View"><i class="far fa-eye"></i></a>
                                <?php if($canCreate && auth()->user()->teacher && $post->teacher_id == auth()->user()->teacher->id): ?>
                                    <a href="<?php echo e(route('class-posts.edit', $post->id)); ?>" class="dir-icon-btn" title="Edit"><i class="far fa-edit"></i></a>
                                    <form action="<?php echo e(route('class-posts.destroy', $post->id)); ?>" method="POST" onsubmit="return confirm('Delete this post?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="dir-icon-btn is-danger" title="Delete"><i class="far fa-trash-alt"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($posts->hasPages()): ?>
                    <div class="p-3 d-flex justify-content-center"><?php echo e($posts->links()); ?></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="dir-empty">
                    <i class="fas fa-inbox d-block"></i>
                    <h5 class="mt-2 mb-1">No class posts yet</h5>
                    <p class="mb-3">Share a note, resource, or reminder with one of your classes.</p>
                    <?php if($canCreate): ?>
                        <a href="<?php echo e(route('class-posts.create')); ?>" class="btn btn-primary dir-btn">
                            <i class="fas fa-plus me-1"></i> Create Post
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914k">
<style>
.asg-page .page-header { margin-bottom: 0.85rem; }
.asg-card {
    background: #fff;
    border: 1px solid #e8eef7;
    border-radius: 18px;
    box-shadow: 0 14px 32px rgba(79, 114, 205, 0.05);
}
.asg-filters { padding: 1rem 1.1rem 0.65rem; margin-bottom: 0.9rem; }
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
    padding: 0.95rem 1.15rem;
    border-bottom: 1px solid #eef2f7;
}
.asg-toolbar h5 { margin: 0; font-size: 1.02rem; font-weight: 750; color: #1e293b; }
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
    grid-template-columns: minmax(0, 1fr) 160px auto;
    gap: 1rem;
    align-items: center;
    padding: 0.95rem 1rem;
    margin-bottom: 0.55rem;
    border: 1px solid #edf1f7;
    border-radius: 16px;
    background: #fff;
    box-shadow: inset 4px 0 0 #c7d2fe;
}
.asg-item.is-live { box-shadow: inset 4px 0 0 #6366f1; }
.asg-title {
    display: block;
    font-size: 1.02rem;
    font-weight: 750;
    color: #1e293b;
    text-decoration: none;
}
.asg-title:hover { color: #4f46e5; }
.asg-desc { margin: 0.28rem 0 0.5rem; font-size: 0.82rem; color: #94a3b8; }
.asg-meta { display: flex; flex-wrap: wrap; gap: 0.35rem; }
.asg-pill, .asg-status {
    display: inline-flex;
    align-items: center;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 650;
}
.asg-pill { background: #eef2ff; color: #4338ca; }
.asg-pill-soft { background: #f1f5f9; color: #475569; }
.asg-status.is-live { background: #eef2ff; color: #4338ca; font-weight: 750; }
.asg-due { text-align: right; }
.asg-due strong { display: block; font-size: 0.86rem; color: #1e293b; }
.asg-due small { color: #94a3b8; font-size: 0.72rem; }
.asg-item-actions { display: flex; gap: 0.3rem; }
.asg-item-actions form { display: inline; }
@media (max-width: 991px) {
    .asg-item { grid-template-columns: 1fr; }
    .asg-due { text-align: left; }
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/class-posts/index.blade.php ENDPATH**/ ?>