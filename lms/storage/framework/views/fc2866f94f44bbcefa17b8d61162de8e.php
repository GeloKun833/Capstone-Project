
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Announcements</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Announcements</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        <?php if(Auth::user()->role_name === 'Admin' || Auth::user()->role_name === 'Teacher'): ?>
                            <a href="<?php echo e(route('announcements.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> New Announcement
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="type_filter">
                                <option value="">All Types</option>
                                <option value="general">General</option>
                                <option value="academic">Academic</option>
                                <option value="event">Event</option>
                                <option value="reminder">Reminder</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="priority_filter">
                                <option value="">All Priorities</option>
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="status_filter">
                                <option value="">All Status</option>
                                <option value="pinned">Pinned Only</option>
                                <option value="active">Active Only</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" id="applyFilters">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">All Announcements</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="#" class="btn btn-outline-primary me-2" id="exportAnnouncements">
                                            <i class="fas fa-download"></i> Export
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <?php if($announcements->count() == 0): ?>
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-bullhorn text-primary" style="font-size: 4rem; opacity: 0.6;"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">No Announcements Found</h4>
                                    <p class="text-muted mb-4">There are no announcements available for your role at this time.</p>
                                    
                                    <?php if(Auth::user()->role_name === 'Admin' || Auth::user()->role_name === 'Teacher'): ?>
                                        <a href="<?php echo e(route('announcements.create')); ?>" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Create First Announcement
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <!-- Announcements List -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>Priority</th>
                                                <th>Target Audience</th>
                                                <th>Created By</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="<?php echo e($announcement->is_pinned ? 'table-warning' : ''); ?>">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if($announcement->is_pinned): ?>
                                                                <i class="fas fa-thumbtack text-warning me-2" title="Pinned"></i>
                                                            <?php endif; ?>
                                                            <div>
                                                                <h2 class="table-avatar">
                                                                    <a href="<?php echo e(route('announcements.show', $announcement->id)); ?>" class="avatar avatar-sm me-2">
                                                                        <i class="<?php echo e($announcement->type_icon); ?> text-<?php echo e($announcement->priority_color); ?>"></i>
                                                                    </a>
                                                                    <a href="<?php echo e(route('announcements.show', $announcement->id)); ?>" class="text-dark fw-bold">
                                                                        <?php echo e($announcement->title); ?>

                                                                    </a>
                                                                </h2>
                                                                <small class="text-muted">
                                                                    <?php echo e(Str::limit($announcement->content, 100)); ?>

                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo e($announcement->priority_color); ?> bg-opacity-10 text-<?php echo e($announcement->priority_color); ?>">
                                                            <i class="<?php echo e($announcement->type_icon); ?> me-1"></i>
                                                            <?php echo e(ucfirst($announcement->type)); ?>

                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo e($announcement->priority_color); ?>">
                                                            <?php echo e(ucfirst($announcement->priority)); ?>

                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info bg-opacity-10 text-info">
                                                            <?php echo e(ucfirst($announcement->target_audience)); ?>

                                                        </span>
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <img class="avatar-img rounded-circle" src="<?php echo e($announcement->creator->avatar ?? URL::to('assets/img/profiles/avatar-01.jpg')); ?>" alt="User Image">
                                                            </a>
                                                            <a href="#"><?php echo e($announcement->creator->name); ?></a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="text-dark fw-medium"><?php echo e($announcement->created_at->format('M d, Y')); ?></div>
                                                            <small class="text-muted"><?php echo e($announcement->created_at->format('h:i A')); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if($announcement->is_scheduled && $announcement->scheduled_at > now()): ?>
                                                            <span class="badge bg-warning">Scheduled</span>
                                                        <?php elseif($announcement->expires_at && $announcement->expires_at < now()): ?>
                                                            <span class="badge bg-secondary">Expired</span>
                                                        <?php elseif($announcement->is_active): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="actions">
                                                            <a href="<?php echo e(route('announcements.show', $announcement->id)); ?>" class="btn btn-sm bg-primary-light me-2">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if(Auth::user()->role_name === 'Admin' || (Auth::user()->role_name === 'Teacher' && $announcement->created_by === Auth::id())): ?>
                                                                <a href="<?php echo e(route('announcements.edit', $announcement->id)); ?>" class="btn btn-sm bg-success-light me-2">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="javascript:void(0);" class="btn btn-sm bg-danger-light" onclick="deleteAnnouncement(<?php echo e($announcement->id); ?>)">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if(Auth::user()->role_name === 'Admin'): ?>
                                                                <a href="javascript:void(0);" class="btn btn-sm bg-warning-light ms-2" onclick="togglePin(<?php echo e($announcement->id); ?>)" title="<?php echo e($announcement->is_pinned ? 'Unpin' : 'Pin'); ?>">
                                                                    <i class="fas fa-thumbtack"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    <?php echo e($announcements->links()); ?>

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
        const form = document.getElementById('deleteForm');
        if (!form) return;
        form.action = '/announcements/' + id;
        const modalEl = document.getElementById('deleteModal');
        if (window.bootstrap && modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (confirm('Are you sure you want to delete this announcement?')) {
            form.submit();
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
        .catch(function (error) {
            console.error('Error:', error);
            alert('Could not update pin status.');
        });
    }

    document.getElementById('applyFilters')?.addEventListener('click', function () {
        const typeFilter = document.getElementById('type_filter').value;
        const priorityFilter = document.getElementById('priority_filter').value;
        const statusFilter = document.getElementById('status_filter').value;

        let url = new URL(window.location);
        url.searchParams.delete('type');
        url.searchParams.delete('priority');
        url.searchParams.delete('status');
        if (typeFilter) url.searchParams.set('type', typeFilter);
        if (priorityFilter) url.searchParams.set('priority', priorityFilter);
        if (statusFilter) url.searchParams.set('status', statusFilter);

        window.location.href = url.toString();
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/announcements/index.blade.php ENDPATH**/ ?>