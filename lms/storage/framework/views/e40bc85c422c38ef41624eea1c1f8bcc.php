
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">All Calendar Events</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Calendar Management</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <a href="<?php echo e(route('calendar.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Event
                        </a>

                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('calendar.events.list')); ?>" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Event Type</label>
                                <select name="event_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="exam" <?php echo e(request('event_type') == 'exam' ? 'selected' : ''); ?>>Exam</option>
                                    <option value="activity" <?php echo e(request('event_type') == 'activity' ? 'selected' : ''); ?>>Activity</option>
                                    <option value="meeting" <?php echo e(request('event_type') == 'meeting' ? 'selected' : ''); ?>>Meeting</option>
                                    <option value="deadline" <?php echo e(request('event_type') == 'deadline' ? 'selected' : ''); ?>>Deadline</option>
                                    <option value="holiday" <?php echo e(request('event_type') == 'holiday' ? 'selected' : ''); ?>>Holiday</option>
                                    <option value="other" <?php echo e(request('event_type') == 'other' ? 'selected' : ''); ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date Range</label>
                                <select name="date_range" class="form-control">
                                    <option value="">All Dates</option>
                                    <option value="today" <?php echo e(request('date_range') == 'today' ? 'selected' : ''); ?>>Today</option>
                                    <option value="week" <?php echo e(request('date_range') == 'week' ? 'selected' : ''); ?>>This Week</option>
                                    <option value="month" <?php echo e(request('date_range') == 'month' ? 'selected' : ''); ?>>This Month</option>
                                    <option value="future" <?php echo e(request('date_range') == 'future' ? 'selected' : ''); ?>>Future Events</option>
                                    <option value="past" <?php echo e(request('date_range') == 'past' ? 'selected' : ''); ?>>Past Events</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search events..." value="<?php echo e(request('search')); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="<?php echo e(route('calendar.events.list')); ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Events Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Events List (<?php echo e($events->total()); ?> total)</h5>
                </div>
                <div class="card-body">
                    <?php if($events->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Type</th>
                                        <th>Date & Time</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="event-info">
                                                    <h6 class="mb-1"><?php echo e($event->title); ?></h6>
                                                    <?php if($event->description): ?>
                                                        <small class="text-muted"><?php echo e(Str::limit($event->description, 50)); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo e($event->event_color); ?>; color: white;">
                                                    <?php echo e(ucfirst($event->event_type)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <div class="datetime-info">
                                                    <div><strong>Start:</strong> <?php echo e($event->start_time->format('M d, Y g:i A')); ?></div>
                                                    <div><strong>End:</strong> <?php echo e($event->end_time->format('M d, Y g:i A')); ?></div>
                                                    <?php if($event->is_all_day): ?>
                                                        <small class="text-info">All Day Event</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($event->subject): ?>
                                                    <span class="badge bg-primary"><?php echo e($event->subject->subject_name); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($event->teacher): ?>
                                                    <span class="badge bg-success"><?php echo e($event->teacher->full_name); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($event->room): ?>
                                                    <span class="badge bg-warning"><?php echo e($event->room->room_name); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($event->start_time->isPast()): ?>
                                                    <span class="badge bg-secondary">Past</span>
                                                <?php elseif($event->start_time->isToday()): ?>
                                                    <span class="badge bg-success">Today</span>
                                                <?php elseif($event->start_time->isTomorrow()): ?>
                                                    <span class="badge bg-info">Tomorrow</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary">Upcoming</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo e(route('calendar.show', $event->id)); ?>">
                                                            <i class="fas fa-eye"></i> View Details
                                                        </a></li>
                                                        <?php if($event->canBeManagedBy(Auth::user())): ?>
                                                            <li><a class="dropdown-item" href="<?php echo e(route('calendar.edit', $event->id)); ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="<?php echo e(route('calendar.destroy', $event->id)); ?>" method="POST"
                                                                      onsubmit="return confirm('Are you sure you want to delete this event?')" style="display: inline;">
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
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            <?php echo e($events->appends(request()->query())->links()); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5>No events found</h5>
                            <p class="text-muted">No events match your current filters.</p>
                            <a href="<?php echo e(route('calendar.index')); ?>" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-calendar"></i> Open Calendar
                            </a>
                            <a href="<?php echo e(route('calendar.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Event
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.event-info h6 {
    color: #333;
    font-weight: 600;
}

.datetime-info {
    font-size: 0.9rem;
}

.datetime-info div {
    margin-bottom: 2px;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}

.table td {
    vertical-align: middle;
}

.dropdown-item {
    padding: 0.5rem 1rem;
}

.dropdown-item i {
    width: 16px;
    margin-right: 8px;
}
</style>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\calendar\events-list.blade.php ENDPATH**/ ?>