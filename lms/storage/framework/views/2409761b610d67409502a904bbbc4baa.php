
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Announcement Details</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('announcements.index')); ?>">Announcements</a></li>
                            <li class="breadcrumb-item active">View Announcement</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        <?php if(Auth::user()->role_name === 'Admin' || (Auth::user()->role_name === 'Teacher' && $announcement->created_by === Auth::id())): ?>
                            <a href="<?php echo e(route('announcements.edit', $announcement->id)); ?>" class="btn btn-primary me-2">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <!-- Announcement Header -->
                            <div class="announcement-header mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <?php if($announcement->is_pinned): ?>
                                        <i class="fas fa-thumbtack text-warning me-3" style="font-size: 1.5rem;" title="Pinned Announcement"></i>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h2 class="mb-2"><?php echo e($announcement->title); ?></h2>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <span class="badge bg-<?php echo e($announcement->priority_color); ?> me-2">
                                                <i class="<?php echo e($announcement->type_icon); ?> me-1"></i>
                                                <?php echo e(ucfirst($announcement->type)); ?>

                                            </span>
                                            <span class="badge bg-<?php echo e($announcement->priority_color); ?> me-2">
                                                <?php echo e(ucfirst($announcement->priority)); ?> Priority
                                            </span>
                                            <span class="badge bg-info bg-opacity-10 text-info me-2">
                                                <?php echo e(ucfirst($announcement->target_audience)); ?>

                                            </span>
                                            <?php if($announcement->is_scheduled && $announcement->scheduled_at > now()): ?>
                                                <span class="badge bg-warning">Scheduled</span>
                                            <?php elseif($announcement->expires_at && $announcement->expires_at < now()): ?>
                                                <span class="badge bg-secondary">Expired</span>
                                            <?php elseif($announcement->is_active): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Announcement Content -->
                            <div class="announcement-content mb-4">
                                <div class="content-body">
                                    <?php echo nl2br(e($announcement->content)); ?>

                                </div>
                            </div>

                            <?php $files = $announcement->attachments ?? []; ?>
                            <?php if(!empty($files)): ?>
                                <div class="announcement-attachments mb-4">
                                    <h6 class="mb-3"><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                                    <div class="list-group">
                                        <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $path = $file['path'] ?? '';
                                                $name = $file['original_name'] ?? basename($path);
                                                $ext = strtolower($file['ext'] ?? pathinfo($name, PATHINFO_EXTENSION));
                                                $url = $path ? asset('storage/' . $path) : '#';
                                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
                                            ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                    <div>
                                                        <i class="fas fa-file me-2"></i>
                                                        <strong><?php echo e($name); ?></strong>
                                                        <?php if(!empty($file['size'])): ?>
                                                            <small class="text-muted">(<?php echo e(number_format(($file['size'] ?? 0) / 1024, 1)); ?> KB)</small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <a href="<?php echo e($url); ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                                        <?php echo e($isImage ? 'Open Image' : 'Download / Open'); ?>

                                                    </a>
                                                </div>
                                                <?php if($isImage && $path): ?>
                                                    <div class="mt-2">
                                                        <img src="<?php echo e($url); ?>" alt="<?php echo e($name); ?>" class="img-fluid rounded" style="max-height:220px;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Announcement Metadata -->
                            <div class="announcement-meta">
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Announcement Details</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>Created by:</strong> 
                                                <span><?php echo e($announcement->creator->name); ?></span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Created on:</strong> 
                                                <span><?php echo e($announcement->created_at->format('F d, Y \a\t h:i A')); ?></span>
                                            </li>
                                            <?php if($announcement->scheduled_at): ?>
                                                <li class="mb-2">
                                                    <strong>Scheduled for:</strong> 
                                                    <span><?php echo e($announcement->scheduled_at->format('F d, Y \a\t h:i A')); ?></span>
                                                </li>
                                            <?php endif; ?>
                                            <?php if($announcement->expires_at): ?>
                                                <li class="mb-2">
                                                    <strong>Expires on:</strong> 
                                                    <span><?php echo e($announcement->expires_at->format('F d, Y \a\t h:i A')); ?></span>
                                                </li>
                                            <?php endif; ?>
                                            <li class="mb-2">
                                                <strong>Last updated:</strong> 
                                                <span><?php echo e($announcement->updated_at->format('F d, Y \a\t h:i A')); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Targeting Information</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>Target Audience:</strong> 
                                                <span><?php echo e(ucfirst($announcement->target_audience)); ?></span>
                                            </li>
                                            <?php if($announcement->target_roles): ?>
                                                <li class="mb-2">
                                                    <strong>Specific Roles:</strong> 
                                                    <span><?php echo e(implode(', ', array_map('ucfirst', $announcement->target_roles))); ?></span>
                                                </li>
                                            <?php endif; ?>
                                            <?php if($announcement->target_sections): ?>
                                                <li class="mb-2">
                                                    <strong>Specific Sections:</strong> 
                                                    <span><?php echo e(implode(', ', $announcement->target_sections)); ?></span>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if(Auth::user()->role_name === 'Admin' || (Auth::user()->role_name === 'Teacher' && $announcement->created_by === Auth::id())): ?>
                                    <a href="<?php echo e(route('announcements.edit', $announcement->id)); ?>" class="btn btn-primary">
                                        <i class="fas fa-edit me-2"></i>Edit Announcement
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="deleteAnnouncement(<?php echo e($announcement->id); ?>)">
                                        <i class="fas fa-trash me-2"></i>Delete Announcement
                                    </button>
                                <?php endif; ?>
                                <?php if(Auth::user()->role_name === 'Admin'): ?>
                                    <button type="button" class="btn btn-warning" onclick="togglePin(<?php echo e($announcement->id); ?>)">
                                        <i class="fas fa-thumbtack me-2"></i>
                                        <?php echo e($announcement->is_pinned ? 'Unpin' : 'Pin'); ?> Announcement
                                    </button>
                                <?php endif; ?>
                                <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Status Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="status-item mb-3">
                                <strong>Visibility:</strong>
                                <?php if($announcement->isVisibleTo(Auth::user())): ?>
                                    <span class="badge bg-success">Visible to you</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Not visible to you</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="status-item mb-3">
                                <strong>Pinned Status:</strong>
                                <?php if($announcement->is_pinned): ?>
                                    <span class="badge bg-warning">Pinned</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Pinned</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="status-item mb-3">
                                <strong>Active Status:</strong>
                                <?php if($announcement->is_active): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($announcement->is_scheduled): ?>
                                <div class="status-item mb-3">
                                    <strong>Scheduled:</strong>
                                    <?php if($announcement->scheduled_at > now()): ?>
                                        <span class="badge bg-warning">Will be published on <?php echo e($announcement->scheduled_at->format('M d, Y')); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Published</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($announcement->expires_at): ?>
                                <div class="status-item">
                                    <strong>Expiration:</strong>
                                    <?php if($announcement->expires_at < now()): ?>
                                        <span class="badge bg-secondary">Expired on <?php echo e($announcement->expires_at->format('M d, Y')); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Expires on <?php echo e($announcement->expires_at->format('M d, Y')); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this announcement? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function deleteAnnouncement(id) {
        if (confirm('Are you sure you want to delete this announcement?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/announcements/${id}`;
            form.innerHTML = `
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function togglePin(id) {
        fetch(`/announcements/${id}/toggle-pin`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\announcements\show.blade.php ENDPATH**/ ?>