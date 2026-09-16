<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">

        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Parents</h3>
                    <p class="dir-subtitle">Parent user accounts and their linked children.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('list/users')); ?>">User Management</a></li>
                        <li class="breadcrumb-item active">Parents</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dir-card dir-filters">
            <form method="GET" action="<?php echo e(route('list/parents')); ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">User ID</label>
                        <input type="text" name="search_id" class="form-control" placeholder="Search by ID" value="<?php echo e(request('search_id')); ?>">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="search_name" class="form-control" placeholder="Search by name" value="<?php echo e(request('search_name')); ?>">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Email</label>
                        <input type="text" name="search_email" class="form-control" placeholder="Search by email" value="<?php echo e(request('search_email')); ?>">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="search_phone" class="form-control" placeholder="Search by phone" value="<?php echo e(request('search_phone')); ?>">
                    </div>
                    <div class="col-lg-2 col-md-6 pb-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">Search</button>
                            <a href="<?php echo e(route('list/parents')); ?>" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Parent accounts</h5>
                    <span class="dir-count mt-1"><?php echo e($parents->total()); ?> account<?php echo e($parents->total() === 1 ? '' : 's'); ?></span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table dir-table mb-0">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Linked children</th>
                            <th>Status</th>
                            <th>Date joined</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $emailKey = strtolower(trim((string) $parent->email));
                                $children = $emailKey !== '' ? ($childrenByEmail[$emailKey] ?? collect()) : collect();
                                $status = $parent->status ?: '—';
                                $statusClass = match (strtolower((string) $parent->status)) {
                                    'active' => 'dir-badge--active',
                                    'inactive' => 'dir-badge--inactive',
                                    'disable', 'disabled' => 'dir-badge--disabled',
                                    default => 'dir-badge--neutral',
                                };
                                $pPhoto = \App\Support\AvatarUploader::url($parent->avatar);
                            ?>
                            <tr>
                                <td class="text-muted"><?php echo e($parent->user_id ?: $parent->id); ?></td>
                                <td>
                                    <div class="dir-person">
                                        <img src="<?php echo e($pPhoto); ?>" alt="<?php echo e($parent->name); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                                        <span>
                                            <a href="<?php echo e(url('view/user/edit/'.$parent->user_id)); ?>" class="dir-person-name"><?php echo e($parent->name); ?></a>
                                            <span class="dir-person-meta">Parent</span>
                                        </span>
                                    </div>
                                </td>
                                <td><?php echo e($parent->email ?: '—'); ?></td>
                                <td><?php echo e($parent->phone_number ?: '—'); ?></td>
                                <td>
                                    <?php if($children->isEmpty()): ?>
                                        <span class="dir-muted">No linked students</span>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="dir-chip dir-chip--soft" title="<?php echo e($child->year_level ?: $child->class); ?><?php echo e($child->section ? ' · '.$child->section : ''); ?>">
                                                    <?php echo e($child->full_name); ?>

                                                    <?php if($child->year_level || $child->class): ?>
                                                        · <?php echo e($child->year_level ?: $child->class); ?>

                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="dir-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                                <td><?php echo e($parent->join_date ?: '—'); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1 justify-content-end">
                                        <a href="<?php echo e(url('view/user/edit/'.$parent->user_id)); ?>" class="dir-icon-btn" title="Edit parent">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <a class="dir-icon-btn is-danger delete-parent"
                                           data-bs-toggle="modal"
                                           data-bs-target="#deleteParent"
                                           data-user_id="<?php echo e($parent->user_id); ?>"
                                           data-avatar="<?php echo e($parent->avatar); ?>"
                                           title="Delete parent">
                                            <i class="far fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="dir-empty">
                                        <div><i class="fas fa-users"></i></div>
                                        <strong>No parent accounts found</strong>
                                        <div class="small mt-1">Try a different search.</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center py-3">
                <?php echo e($parents->links()); ?>

            </div>
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="deleteParent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Delete parent?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-0">This will remove the parent user account. Linked student records are not deleted.</p>
            </div>
            <div class="modal-footer border-0">
                <form action="<?php echo e(route('user/delete')); ?>" method="POST" class="d-flex gap-2 w-100 justify-content-end">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="user_id" class="e_user_id" value="">
                    <input type="hidden" name="avatar" class="e_avatar" value="">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('script'); ?>
<script>
    $(document).on('click', '.delete-parent', function () {
        $('.e_user_id').val($(this).data('user_id'));
        $('.e_avatar').val($(this).data('avatar'));
    });
</script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914b">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/usermanagement/list_parents.blade.php ENDPATH**/ ?>