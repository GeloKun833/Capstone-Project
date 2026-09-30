
<?php $__env->startSection('content'); ?>

<?php
    $canManage = Auth::user()->role_name === 'Admin'
        || (Auth::user()->role_name === 'Teacher' && (int) $announcement->created_by === (int) Auth::id());
    $priorityClass = match ($announcement->priority) {
        'urgent' => 'dir-badge--disabled',
        'high' => 'dir-badge--inactive',
        'normal' => 'dir-badge--active',
        default => 'dir-badge--neutral',
    };
    $statusClass = 'dir-badge--neutral';
    $statusLabel = 'Inactive';
    if ($announcement->is_scheduled && $announcement->scheduled_at && $announcement->scheduled_at->isFuture()) {
        $statusClass = 'dir-badge--inactive';
        $statusLabel = 'Scheduled';
    } elseif ($announcement->expires_at && $announcement->expires_at->isPast()) {
        $statusClass = 'dir-badge--neutral';
        $statusLabel = 'Expired';
    } elseif ($announcement->is_active) {
        $statusClass = 'dir-badge--active';
        $statusLabel = 'Active';
    } else {
        $statusClass = 'dir-badge--disabled';
        $statusLabel = 'Inactive';
    }
?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Announcement Details</h3>
                    <p class="dir-subtitle">Full notice, attachments, and targeting.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('announcements.index')); ?>">Announcements</a></li>
                        <li class="breadcrumb-item active">View Announcement</li>
                    </ul>
                    <?php if($canManage): ?>
                        <a href="<?php echo e(route('announcements.edit', $announcement->id)); ?>" class="btn btn-primary dir-btn me-1">
                            <i class="fas fa-pen me-1"></i> Edit
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-outline-secondary dir-btn">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="dir-card">
                    <div class="dir-toolbar">
                        <div class="d-flex align-items-start gap-3">
                            <span class="dir-title-icon"><i class="<?php echo e($announcement->type_icon); ?>"></i></span>
                            <div>
                                <h5 class="dir-toolbar-title mb-1">
                                    <?php if($announcement->is_pinned): ?>
                                        <i class="fas fa-thumbtack text-warning me-1"></i>
                                    <?php endif; ?>
                                    <?php echo e($announcement->title); ?>

                                </h5>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <span class="dir-chip"><?php echo e(ucfirst($announcement->type)); ?></span>
                                    <span class="dir-badge <?php echo e($priorityClass); ?>"><?php echo e(ucfirst($announcement->priority)); ?> priority</span>
                                    <span class="dir-chip dir-chip--soft"><?php echo e(ucfirst($announcement->target_audience)); ?></span>
                                    <span class="dir-badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="mb-4" style="white-space: pre-wrap; line-height: 1.65;"><?php echo e($announcement->content); ?></div>

                        <?php $files = $announcement->attachments ?? []; ?>
                        <?php if(!empty($files)): ?>
                            <h6 class="dir-toolbar-title mb-3"><i class="fas fa-paperclip me-1"></i> Attachments</h6>
                            <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $path = $file['path'] ?? '';
                                    $name = $file['original_name'] ?? basename($path);
                                    $ext = strtolower($file['ext'] ?? pathinfo($name, PATHINFO_EXTENSION));
                                    $url = $path ? asset('storage/' . $path) : '#';
                                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
                                ?>
                                <div class="dir-file-row flex-column align-items-stretch">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <i class="fas fa-file me-2 text-muted"></i>
                                            <strong><?php echo e($name); ?></strong>
                                            <?php if(!empty($file['size'])): ?>
                                                <small class="text-muted">(<?php echo e(number_format(($file['size'] ?? 0) / 1024, 1)); ?> KB)</small>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?php echo e($url); ?>" class="btn btn-sm btn-outline-secondary dir-btn" target="_blank" rel="noopener">
                                            <?php echo e($isImage ? 'Open image' : 'Download / Open'); ?>

                                        </a>
                                    </div>
                                    <?php if($isImage && $path): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo e($url); ?>" alt="<?php echo e($name); ?>" class="img-fluid rounded" style="max-height:220px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dir-card mb-3">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Details</h5>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <div class="dir-person-meta">Created by</div>
                            <div><?php echo e($announcement->creator->name ?? 'N/A'); ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="dir-person-meta">Created on</div>
                            <div><?php echo e($announcement->created_at->format('M d, Y')); ?> · <?php echo e($announcement->created_at->format('h:i A')); ?></div>
                        </div>
                        <?php if($announcement->scheduled_at): ?>
                            <div class="mb-3">
                                <div class="dir-person-meta">Scheduled for</div>
                                <div><?php echo e($announcement->scheduled_at->format('M d, Y')); ?> · <?php echo e($announcement->scheduled_at->format('h:i A')); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if($announcement->expires_at): ?>
                            <div class="mb-3">
                                <div class="dir-person-meta">Expires on</div>
                                <div><?php echo e($announcement->expires_at->format('M d, Y')); ?> · <?php echo e($announcement->expires_at->format('h:i A')); ?></div>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="dir-person-meta">Last updated</div>
                            <div><?php echo e($announcement->updated_at->format('M d, Y')); ?> · <?php echo e($announcement->updated_at->format('h:i A')); ?></div>
                        </div>
                    </div>
                </div>

                <div class="dir-card mb-3">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Targeting</h5>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <div class="dir-person-meta">Audience</div>
                            <span class="dir-chip dir-chip--soft"><?php echo e(ucfirst($announcement->target_audience)); ?></span>
                        </div>
                        <?php if($announcement->target_roles): ?>
                            <div class="mb-3">
                                <div class="dir-person-meta">Specific roles</div>
                                <div><?php echo e(implode(', ', array_map('ucfirst', $announcement->target_roles))); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if($announcement->target_sections): ?>
                            <div>
                                <div class="dir-person-meta">Specific sections</div>
                                <div><?php echo e(implode(', ', $announcement->target_sections)); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dir-card">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Actions</h5>
                    </div>
                    <div class="p-3 d-grid gap-2">
                        <?php if($canManage): ?>
                            <a href="<?php echo e(route('announcements.edit', $announcement->id)); ?>" class="btn btn-primary dir-btn">
                                <i class="fas fa-pen me-1"></i> Edit
                            </a>
                            <button type="button" class="btn btn-outline-danger dir-btn" onclick="deleteAnnouncement(<?php echo e($announcement->id); ?>)">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        <?php endif; ?>
                        <?php if(Auth::user()->role_name === 'Admin'): ?>
                            <button type="button" class="btn btn-outline-secondary dir-btn" onclick="togglePin(<?php echo e($announcement->id); ?>)">
                                <i class="fas fa-thumbtack me-1"></i>
                                <?php echo e($announcement->is_pinned ? 'Unpin' : 'Pin'); ?>

                            </button>
                        <?php endif; ?>
                        <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-outline-secondary dir-btn">
                            <i class="fas fa-arrow-left me-1"></i> Back to list
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Delete announcement?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-0">This will permanently remove the announcement. This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <form id="deleteForm" method="POST" action="<?php echo e(route('announcements.destroy', $announcement->id)); ?>" class="d-flex gap-2 w-100 justify-content-end">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="button" class="btn btn-light dir-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger dir-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914c">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function deleteAnnouncement() {
        const modalEl = document.getElementById('deleteModal');
        if (window.bootstrap && modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (confirm('Delete this announcement?')) {
            document.getElementById('deleteForm').submit();
        }
    }

    function togglePin(id) {
        fetch('/announcements/' + id + '/toggle-pin', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
        })
        .then(function (response) {
            if (!response.ok) throw new Error('Pin failed');
            return response.json().catch(function () { return { success: true }; });
        })
        .then(function () { location.reload(); })
        .catch(function () {
            alert('Could not update pin status.');
        });
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/announcements/show.blade.php ENDPATH**/ ?>