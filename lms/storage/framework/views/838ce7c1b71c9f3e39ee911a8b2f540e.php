<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-sub-header">
                        <h3 class="page-title">Parents</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('list/users')); ?>">User Management</a></li>
                            <li class="breadcrumb-item active">Parents</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="student-group-form">
            <form method="GET" action="<?php echo e(route('list/parents')); ?>">
                <div class="row">
                    <div class="col-lg-2 col-md-6">
                        <div class="form-group">
                            <input type="text" name="search_id" class="form-control" placeholder="Search by ID ..." value="<?php echo e(request('search_id')); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <input type="text" name="search_name" class="form-control" placeholder="Search by Name ..." value="<?php echo e(request('search_name')); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <input type="text" name="search_email" class="form-control" placeholder="Search by Email ..." value="<?php echo e(request('search_email')); ?>">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="form-group">
                            <input type="text" name="search_phone" class="form-control" placeholder="Search by Phone ..." value="<?php echo e(request('search_phone')); ?>">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="search-student-btn">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="<?php echo e(route('list/parents')); ?>" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table comman-shadow">
                    <div class="card-body">
                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="page-title mb-0">Parent User Accounts</h3>
                                    <small class="text-muted"><?php echo e($parents->total()); ?> parent account(s)</small>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0 table-striped">
                                <thead class="student-thread">
                                    <tr>
                                        <th>User ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Linked Children</th>
                                        <th>Status</th>
                                        <th>Date Joined</th>
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
                                                'active' => 'bg-success',
                                                'inactive' => 'bg-warning text-dark',
                                                'disable', 'disabled' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                            $avatar = $parent->avatar ?: 'photo_defaults.jpg';
                                        ?>
                                        <tr>
                                            <td><?php echo e($parent->user_id ?: $parent->id); ?></td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a class="avatar avatar-sm me-2">
                                                        <img class="avatar-img rounded-circle" src="<?php echo e(asset('images/'.$avatar)); ?>" alt="<?php echo e($parent->name); ?>">
                                                    </a>
                                                    <a href="<?php echo e(url('view/user/edit/'.$parent->user_id)); ?>"><?php echo e($parent->name); ?></a>
                                                </h2>
                                            </td>
                                            <td><?php echo e($parent->email ?: '—'); ?></td>
                                            <td><?php echo e($parent->phone_number ?: '—'); ?></td>
                                            <td>
                                                <?php if($children->isEmpty()): ?>
                                                    <span class="text-muted">No linked students</span>
                                                <?php else: ?>
                                                    <ul class="mb-0 ps-3">
                                                        <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <li>
                                                                <?php echo e($child->full_name); ?>

                                                                <?php if($child->year_level || $child->class): ?>
                                                                    <small class="text-muted">(<?php echo e($child->year_level ?: $child->class); ?><?php echo e($child->section ? ' · '.$child->section : ''); ?>)</small>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                                            <td><?php echo e($parent->join_date ?: '—'); ?></td>
                                            <td class="text-end">
                                                <div class="actions">
                                                    <a href="<?php echo e(url('view/user/edit/'.$parent->user_id)); ?>" class="btn btn-sm bg-danger-light" title="Edit Parent">
                                                        <i class="far fa-edit me-1"></i>
                                                    </a>
                                                    <a class="btn btn-sm bg-danger-light delete-parent"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#deleteParent"
                                                       data-user_id="<?php echo e($parent->user_id); ?>"
                                                       data-avatar="<?php echo e($avatar); ?>"
                                                       title="Delete Parent">
                                                        <i class="fe fe-trash-2"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                No parent user accounts found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            <?php echo e($parents->links()); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal custom-modal fade" id="deleteParent" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-header">
                    <h3>Delete Parent</h3>
                    <p>Are you sure you want to delete this parent account?</p>
                </div>
                <div class="modal-btn delete-action">
                    <form action="<?php echo e(route('user/delete')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="user_id" class="e_user_id" value="">
                        <input type="hidden" name="avatar" class="e_avatar" value="">
                        <div class="row">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary paid-continue-btn" style="width: 100%;">Delete</button>
                            </div>
                            <div class="col-6">
                                <a data-bs-dismiss="modal" class="btn btn-primary paid-cancel-btn">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
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

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\usermanagement\list_parents.blade.php ENDPATH**/ ?>