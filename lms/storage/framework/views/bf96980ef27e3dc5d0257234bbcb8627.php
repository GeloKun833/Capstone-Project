
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Class Posts</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Class Posts</li>
                    </ul>
                </div>
                <div class="col-auto text-end float-end ms-auto download-grp">
                    <a href="<?php echo e(route('class-posts.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Post
                    </a>
                </div>
            </div>
        </div>

        
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('class-posts.index')); ?>">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by title..." value="<?php echo e(request('search')); ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="subject_id" class="form-control">
                                    <option value="">All Subjects</option>
                                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($subject->id); ?>" <?php echo e(request('subject_id') == $subject->id ? 'selected' : ''); ?>>
                                            <?php echo e($subject->subject_name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="section_id" class="form-control">
                                    <option value="">All Sections</option>
                                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($section->id); ?>" <?php echo e(request('section_id') == $section->id ? 'selected' : ''); ?>>
                                            <?php echo e($section->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="announcement" <?php echo e(request('type') == 'announcement' ? 'selected' : ''); ?>>Announcement</option>
                                    <option value="resource" <?php echo e(request('type') == 'resource' ? 'selected' : ''); ?>>Resource</option>
                                    <option value="discussion" <?php echo e(request('type') == 'discussion' ? 'selected' : ''); ?>>Discussion</option>
                                    <option value="reminder" <?php echo e(request('type') == 'reminder' ? 'selected' : ''); ?>>Reminder</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <button type="submit" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <a href="<?php echo e(route('class-posts.index')); ?>" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-redo"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="row">
            <div class="col-md-12">
                <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="card mb-3 <?php echo e($post->is_pinned ? 'border-primary' : ''); ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <?php if($post->is_pinned): ?>
                                            <span class="badge bg-primary me-2">
                                                <i class="fas fa-thumbtack"></i> Pinned
                                            </span>
                                        <?php endif; ?>
                                        <span class="badge bg-<?php echo e($post->type == 'announcement' ? 'info' : ($post->type == 'resource' ? 'success' : ($post->type == 'reminder' ? 'warning' : 'secondary'))); ?>">
                                            <?php echo e(ucfirst($post->type)); ?>

                                        </span>
                                        <span class="badge bg-<?php echo e($post->priority == 'urgent' ? 'danger' : ($post->priority == 'high' ? 'warning' : 'secondary')); ?> ms-2">
                                            <?php echo e(ucfirst($post->priority)); ?>

                                        </span>
                                    </div>
                                    <h5 class="card-title">
                                        <a href="<?php echo e(route('class-posts.show', $post->id)); ?>"><?php echo e($post->title); ?></a>
                                    </h5>
                                    <p class="card-text text-muted mb-2">
                                        <?php echo e(Str::limit(strip_tags($post->content), 200)); ?>

                                    </p>
                                    <div class="text-muted small">
                                        <i class="fas fa-user"></i> <?php echo e($post->teacher->name ?? 'Unknown'); ?>

                                        <i class="fas fa-book ms-3"></i> <?php echo e($post->subject->subject_name ?? 'N/A'); ?>

                                        <i class="fas fa-users ms-3"></i> <?php echo e($post->section->name ?? 'N/A'); ?>

                                        <i class="fas fa-clock ms-3"></i> <?php echo e($post->created_at->diffForHumans()); ?>

                                        <?php if($post->file_path): ?>
                                            <i class="fas fa-paperclip ms-3"></i> Attachment
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?php echo e(route('class-posts.show', $post->id)); ?>">
                                            <i class="fas fa-eye"></i> View
                                        </a></li>
                                        <?php if(auth()->user()->role_name == 'Teacher' && $post->teacher_id == auth()->user()->teacher->id): ?>
                                            <li><a class="dropdown-item" href="<?php echo e(route('class-posts.edit', $post->id)); ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="<?php echo e(route('class-posts.destroy', $post->id)); ?>" method="POST" onsubmit="return confirm('Are you sure?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5>No Class Posts Found</h5>
                            <p class="text-muted">Start by creating your first class post</p>
                            <a href="<?php echo e(route('class-posts.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Post
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                
                <?php if($posts->hasPages()): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($posts->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\class-posts\index.blade.php ENDPATH**/ ?>