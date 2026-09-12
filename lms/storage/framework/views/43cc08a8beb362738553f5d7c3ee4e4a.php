
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm-12">
                    <div class="page-sub-header">
                        <h3 class="page-title">Activity Log</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">User Management</a></li>
                            <li class="breadcrumb-item active">Activity Log</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <?php if($isAdmin): ?>
            <div class="row mb-3">
                <div class="col-12">
                    <ul class="nav nav-pills activity-log-tabs">
                        <li class="nav-item">
                            <a
                                class="nav-link <?php echo e($scope === 'all' ? 'active' : ''); ?>"
                                href="<?php echo e(route('activity.log', array_merge(request()->except('page', 'scope'), ['scope' => 'all']))); ?>"
                            >
                                <i class="fas fa-clipboard-list me-1"></i> All System Activity
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link <?php echo e($scope === 'mine' ? 'active' : ''); ?>"
                                href="<?php echo e(route('activity.log', array_merge(request()->except('page', 'scope', 'user_id'), ['scope' => 'mine']))); ?>"
                            >
                                <i class="fas fa-user me-1"></i> My Activity
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="student-group-form mb-4">
            <form method="GET" action="<?php echo e(route('activity.log')); ?>" class="row g-3 align-items-end">
                <input type="hidden" name="scope" value="<?php echo e($scope); ?>">

                <div class="col-lg-3 col-md-6">
                    <label for="search" class="form-label mb-1">Search</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="<?php echo e(request('search')); ?>"
                        placeholder="Search action or description..."
                    >
                </div>

                <?php if($isAdmin && $scope === 'all'): ?>
                    <div class="col-lg-3 col-md-6">
                        <label for="user_id" class="form-label mb-1">User</label>
                        <select class="form-control select" id="user_id" name="user_id">
                            <option value="">All users</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filterUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($filterUser->id); ?>" <?php if(request('user_id') == $filterUser->id): echo 'selected'; endif; ?>>
                                    <?php echo e($filterUser->name); ?> (<?php echo e($filterUser->role_name); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="col-lg-2 col-md-4">
                    <label for="date_from" class="form-label mb-1">From</label>
                    <input
                        type="date"
                        class="form-control"
                        id="date_from"
                        name="date_from"
                        value="<?php echo e(request('date_from')); ?>"
                    >
                </div>

                <div class="col-lg-2 col-md-4">
                    <label for="date_to" class="form-label mb-1">To</label>
                    <input
                        type="date"
                        class="form-control"
                        id="date_to"
                        name="date_to"
                        value="<?php echo e(request('date_to')); ?>"
                    >
                </div>

                <div class="col-lg-2 col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="<?php echo e(route('activity.log', ['scope' => $scope])); ?>" class="btn btn-secondary">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table comman-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <?php if($isAdmin && $scope === 'all'): ?>
                                    System-wide activity
                                <?php else: ?>
                                    Your recent activity
                                <?php endif; ?>
                            </h5>
                            <span class="text-muted small"><?php echo e($activities->total()); ?> record(s)</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <?php if($isAdmin && $scope === 'all'): ?>
                                            <th>User</th>
                                            <th>Role</th>
                                        <?php endif; ?>
                                        <th>Event</th>
                                        <th>Description</th>
                                        <th>Subject</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($activity->created_at->format('M d, Y h:i A')); ?></td>
                                            <?php if($isAdmin && $scope === 'all'): ?>
                                                <td><?php echo e($activity->causer->name ?? 'System'); ?></td>
                                                <td><?php echo e($activity->causer->role_name ?? '-'); ?></td>
                                            <?php endif; ?>
                                            <td>
                                                <?php if($activity->event): ?>
                                                    <span class="badge bg-light text-dark"><?php echo e($activity->event); ?></span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($activity->description); ?></td>
                                            <td>
                                                <?php if($activity->subject_type): ?>
                                                    <?php echo e(class_basename($activity->subject_type)); ?> #<?php echo e($activity->subject_id); ?>

                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($activity->properties && count($activity->properties)): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#activity-details-<?php echo e($activity->id); ?>"
                                                    >
                                                        View
                                                    </button>

                                                    <div class="modal fade" id="activity-details-<?php echo e($activity->id); ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Activity Details</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <pre class="activity-log-json mb-0"><?php echo e(json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="<?php echo e(($isAdmin && $scope === 'all') ? 7 : 5); ?>" class="text-center py-4 text-muted">
                                                No activity found for the selected filters.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if($activities->hasPages() || $activities->total() > 0): ?>
                            <div class="pagination-wrapper mt-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div class="pagination-info">
                                        <p class="text-muted mb-0">
                                            Showing <?php echo e($activities->firstItem() ?? 0); ?> to <?php echo e($activities->lastItem() ?? 0); ?> of <?php echo e($activities->total()); ?> results
                                        </p>
                                    </div>
                                    <?php if($activities->hasPages()): ?>
                                        <div class="pagination-controls">
                                            <?php echo e($activities->links('pagination.bootstrap-limited')); ?>

                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .activity-log-tabs .nav-link {
        border-radius: 999px;
        padding: 0.55rem 1.1rem;
        color: #475569;
        font-weight: 500;
    }

    .activity-log-tabs .nav-link.active {
        background: #2563eb;
        color: #fff;
    }

    .activity-log-json {
        white-space: pre-wrap;
        word-break: break-word;
        background: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
        font-size: 0.85rem;
    }

    .pagination-wrapper {
        padding-top: 1.25rem;
        border-top: 1px solid #e9ecef;
    }

    .pagination-limited {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
    }

    .pagination-limited .page-item {
        list-style: none;
    }

    .pagination-limited .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0.4rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #fff;
        color: #495057;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .pagination-limited .page-link:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        color: #495057;
    }

    .pagination-limited .page-item.active .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .pagination-limited .page-item.disabled .page-link {
        background: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .pagination-limited .page-item:first-child .page-link,
    .pagination-limited .page-item:last-child .page-link {
        min-width: auto;
        font-weight: 600;
    }

    .pagination-limited .page-item:not(.disabled):not(.active):first-child .page-link,
    .pagination-limited .page-item:not(.disabled):not(.active):last-child .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .pagination-limited .page-item:not(.disabled):not(.active):first-child .page-link:hover,
    .pagination-limited .page-item:not(.disabled):not(.active):last-child .page-link:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }

    .pagination-info {
        font-size: 0.9rem;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\dashboard\activity_log.blade.php ENDPATH**/ ?>