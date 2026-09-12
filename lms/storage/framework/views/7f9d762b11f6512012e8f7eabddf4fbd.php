
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo e($classPost->title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('class-posts.index')); ?>">Class Posts</a></li>
                        <li class="breadcrumb-item active"><?php echo e($classPost->title); ?></li>
                    </ul>
                </div>
                <div class="col-auto text-end">
                    <?php if(auth()->user()->role_name === 'Teacher' && auth()->user()->teacher && auth()->user()->teacher->id === $classPost->teacher_id): ?>
                        <a href="<?php echo e(route('class-posts.edit', $classPost)); ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Post
                        </a>
                        <form action="<?php echo e(route('class-posts.destroy', $classPost)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        
                        <div class="post-header mb-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h2 class="mb-2"><?php echo e($classPost->title); ?></h2>
                                    <div class="post-meta">
                                        <span class="badge bg-<?php echo e($classPost->type_color); ?> me-2">
                                            <i class="<?php echo e($classPost->type_icon); ?>"></i> <?php echo e(ucfirst($classPost->type)); ?>

                                        </span>
                                        <span class="badge bg-<?php echo e($classPost->priority_color); ?> me-2">
                                            <i class="fas fa-flag"></i> <?php echo e(ucfirst($classPost->priority)); ?> Priority
                                        </span>
                                        <?php if($classPost->is_pinned): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-thumbtack"></i> Pinned
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="post-info bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><i class="fas fa-user text-primary"></i> <strong>Posted by:</strong> <?php echo e($classPost->teacher->full_name ?? 'Teacher'); ?></p>
                                        <p class="mb-2"><i class="fas fa-book text-success"></i> <strong>Subject:</strong> <?php echo e($classPost->subject->subject_name); ?></p>
                                        <p class="mb-0"><i class="fas fa-users text-info"></i> <strong>Section:</strong> <?php echo e($classPost->section->name); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><i class="fas fa-calendar-alt text-warning"></i> <strong>Posted:</strong> <?php echo e($classPost->created_at->format('M d, Y g:i A')); ?></p>
                                        <p class="mb-2"><i class="fas fa-calendar text-secondary"></i> <strong>Academic Year:</strong> <?php echo e($classPost->academicYear->name ?? 'N/A'); ?></p>
                                        <p class="mb-0"><i class="fas fa-calendar-week text-secondary"></i> <strong>Semester:</strong> <?php echo e($classPost->semester->name ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                                <?php if($classPost->expires_at): ?>
                                    <div class="mt-2">
                                        <p class="mb-0"><i class="fas fa-clock text-danger"></i> <strong>Expires:</strong> <?php echo e($classPost->expires_at->format('M d, Y')); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="post-content mb-4">
                            <h5 class="mb-3">Content</h5>
                            <div class="content-box p-4 bg-white border rounded">
                                <?php echo $classPost->formatted_content; ?>

                            </div>
                        </div>

                        
                        <?php if($classPost->file_path): ?>
                            <div class="post-attachment mb-4">
                                <h5 class="mb-3">Attachment</h5>
                                <div class="attachment-box p-3 bg-light border rounded">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-<?php echo e($classPost->file_type); ?> fa-2x text-primary me-3"></i>
                                            <div>
                                                <p class="mb-0 fw-bold"><?php echo e($classPost->file_name); ?></p>
                                                <small class="text-muted"><?php echo e(strtoupper($classPost->file_type)); ?> File</small>
                                            </div>
                                        </div>
                                        <a href="<?php echo e($classPost->file_url); ?>" class="btn btn-primary btn-sm" target="_blank">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <?php if($classPost->allows_comments): ?>
                            <div class="comments-section">
                                <h5 class="mb-3">
                                    <i class="fas fa-comments"></i> Comments 
                                    <span class="badge bg-secondary"><?php echo e($classPost->comment_count); ?></span>
                                </h5>
                                
                                
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <form action="<?php echo e(route('class-posts.store-comment', $classPost)); ?>" method="POST" enctype="multipart/form-data">
                                            <?php echo csrf_field(); ?>
                                            <div class="form-group mb-3">
                                                <label class="form-label">Add a comment</label>
                                                <textarea name="content" rows="3" class="form-control <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                          placeholder="Write your comment..." required><?php echo e(old('content')); ?></textarea>
                                                <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="form-label">Attachment (Optional)</label>
                                                <input type="file" name="comment_file" class="form-control" 
                                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png">
                                                <small class="form-text text-muted">Max size: 5MB</small>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-paper-plane"></i> Post Comment
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                
                                <?php if($classPost->comments->count() > 0): ?>
                                    <div class="comments-list">
                                        <?php $__currentLoopData = $classPost->approvedComments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="d-flex align-items-start">
                                                            <div class="avatar me-3">
                                                                <i class="fas fa-user-circle fa-2x text-primary"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1"><?php echo e($comment->user->name ?? 'User'); ?></h6>
                                                                <small class="text-muted"><?php echo e($comment->created_at->diffForHumans()); ?></small>
                                                                <p class="mt-2 mb-0"><?php echo e($comment->content); ?></p>
                                                                <?php if($comment->file_path): ?>
                                                                    <div class="mt-2">
                                                                        <a href="<?php echo e(asset('storage/' . $comment->file_path)); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                            <i class="fas fa-paperclip"></i> <?php echo e($comment->file_name); ?>

                                                                        </a>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <?php if(auth()->id() === $comment->user_id || auth()->user()->role_name === 'Teacher'): ?>
                                                            <form action="<?php echo e(route('class-posts.delete-comment', $comment)); ?>" method="POST" onsubmit="return confirm('Delete this comment?');">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo method_field('DELETE'); ?>
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No comments yet. Be the first to comment!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Post Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge bg-<?php echo e($classPost->is_active ? 'success' : 'secondary'); ?> fs-6">
                                <?php echo e($classPost->is_active ? 'Published' : 'Draft'); ?>

                            </span>
                        </div>
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Post ID</small>
                            <strong>#<?php echo e($classPost->id); ?></strong>
                        </div>
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Comments</small>
                            <strong><?php echo e($classPost->allows_comments ? 'Enabled' : 'Disabled'); ?></strong>
                        </div>
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Confirmation Required</small>
                            <strong><?php echo e($classPost->requires_confirmation ? 'Yes' : 'No'); ?></strong>
                        </div>
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Published</small>
                            <strong><?php echo e($classPost->published_at ? $classPost->published_at->format('M d, Y g:i A') : 'Not published'); ?></strong>
                        </div>
                        <?php if($classPost->expires_at): ?>
                            <div class="info-item mb-3">
                                <small class="text-muted d-block">Expires</small>
                                <strong class="text-danger"><?php echo e($classPost->expires_at->format('M d, Y')); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <?php if(auth()->user()->role_name === 'Teacher' && auth()->user()->teacher && auth()->user()->teacher->id === $classPost->teacher_id): ?>
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo e(route('class-posts.toggle-pin', $classPost)); ?>" method="POST" class="mb-2">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-<?php echo e($classPost->is_pinned ? 'warning' : 'outline-warning'); ?> btn-sm w-100">
                                    <i class="fas fa-thumbtack"></i> <?php echo e($classPost->is_pinned ? 'Unpin Post' : 'Pin Post'); ?>

                                </button>
                            </form>
                            <?php if($classPost->is_active): ?>
                                <form action="<?php echo e(route('class-posts.unpublish', $classPost)); ?>" method="POST" class="mb-2">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fas fa-eye-slash"></i> Unpublish
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="<?php echo e(route('class-posts.publish', $classPost)); ?>" method="POST" class="mb-2">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                        <i class="fas fa-eye"></i> Publish
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.post-content {
    font-size: 1rem;
    line-height: 1.8;
}

.content-box {
    min-height: 200px;
}

.post-meta .badge {
    font-size: 0.85rem;
    padding: 6px 12px;
}

.attachment-box {
    transition: all 0.3s ease;
}

.attachment-box:hover {
    background: #e9ecef !important;
}

.info-item {
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.comments-section {
    border-top: 2px solid #e9ecef;
    padding-top: 30px;
    margin-top: 30px;
}

.avatar {
    width: 40px;
    height: 40px;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\class-posts\show.blade.php ENDPATH**/ ?>