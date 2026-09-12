
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title"><?php echo e($child->full_name); ?> - Activities</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active"><?php echo e($child->full_name); ?> - Activities</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <img src="<?php echo e($child->upload ? asset('storage/' . $child->upload) : URL::to('assets/img/profiles/avatar-01.jpg')); ?>" 
                                         alt="Student Photo" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="col-md-6">
                                    <h4 class="mb-2"><?php echo e($child->full_name); ?></h4>
                                    <p class="text-muted mb-1">
                                        <strong>Student ID:</strong> <?php echo e($child->admission_id); ?>

                                    </p>
                                    <p class="text-muted mb-1">
                                        <strong>Section:</strong> <?php echo e($child->sections->first()->name ?? 'Not Assigned'); ?>

                                    </p>
                                    <p class="text-muted mb-0">
                                        <strong>Academic Period:</strong> <?php echo e($academicYear->year ?? 'Not Set'); ?> - <?php echo e($semester->name ?? 'Not Set'); ?>

                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-primary mb-2">Activities Summary</h5>
                                        <h3 class="mb-0"><?php echo e($activities->count()); ?> Total</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Activities Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="bg-success text-white rounded p-4">
                                        <h3><?php echo e($activities->where('due_date', '>', now())->count()); ?></h3>
                                        <p class="mb-0">Pending</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-danger text-white rounded p-4">
                                        <h3><?php echo e($activities->where('due_date', '<', now())->whereDoesntHave('submissions', function($q) use ($child) { $q->where('student_id', $child->id); })->count()); ?></h3>
                                        <p class="mb-0">Overdue</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-info text-white rounded p-4">
                                        <h3><?php echo e($submissions->count()); ?></h3>
                                        <p class="mb-0">Submitted</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-warning text-white rounded p-4">
                                        <h3><?php echo e($submissions->where('status', 'graded')->count()); ?></h3>
                                        <p class="mb-0">Graded</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">All Activities</h5>
                        </div>
                        <div class="card-body">
                            <?php if($activities->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Activity</th>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $submission = $submissions->where('activity_id', $activity->id)->first();
                                                    $isOverdue = $activity->due_date < now() && !$submission;
                                                ?>
                                                <tr class="<?php echo e($isOverdue ? 'table-danger' : ''); ?>">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if($isOverdue): ?>
                                                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                                            <?php endif; ?>
                                                            <div>
                                                                <strong><?php echo e($activity->title); ?></strong>
                                                                <br><small class="text-muted"><?php echo e(Str::limit($activity->instructions, 50)); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <i class="fas fa-book text-primary"></i>
                                                            </a>
                                                            <a href="#"><?php echo e($activity->lesson->subject->subject_name ?? 'N/A'); ?></a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <img class="avatar-img rounded-circle" src="<?php echo e($activity->lesson->teacher->avatar ?? URL::to('assets/img/profiles/avatar-01.jpg')); ?>" alt="User Image">
                                                            </a>
                                                            <a href="#"><?php echo e($activity->lesson->teacher->full_name ?? 'N/A'); ?></a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <span class="<?php echo e($isOverdue ? 'text-danger' : ''); ?>">
                                                            <?php echo e($activity->due_date->format('M d, Y')); ?>

                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if($submission): ?>
                                                            <?php if($submission->status == 'graded'): ?>
                                                                <span class="badge bg-success">Graded</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-info">Submitted</span>
                                                            <?php endif; ?>
                                                        <?php elseif($isOverdue): ?>
                                                            <span class="badge bg-danger">Overdue</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($submission && $submission->status == 'graded'): ?>
                                                            <strong><?php echo e($submission->total_score); ?>/<?php echo e($submission->max_score); ?></strong>
                                                            <br><small class="text-muted"><?php echo e(number_format(($submission->total_score / $submission->max_score) * 100, 1)); ?>%</small>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    <?php echo e($activities->links()); ?>

                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-tasks text-muted" style="font-size: 4rem; opacity: 0.6;"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">No Activities Available</h4>
                                    <p class="text-muted mb-4">No activities have been assigned to this student yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Submissions -->
            <?php if($submissions->count() > 0): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Recent Submissions</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Activity</th>
                                                <th>Subject</th>
                                                <th>Submitted</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                                <th>Feedback</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $submissions->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo e($submission->activity->title); ?></strong>
                                                    </td>
                                                    <td><?php echo e($submission->activity->lesson->subject->subject_name ?? 'N/A'); ?></td>
                                                    <td><?php echo e($submission->submitted_at->format('M d, Y h:i A')); ?></td>
                                                    <td>
                                                        <?php if($submission->status == 'graded'): ?>
                                                            <span class="badge bg-success">Graded</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-info">Submitted</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($submission->status == 'graded'): ?>
                                                            <strong><?php echo e($submission->total_score); ?>/<?php echo e($submission->max_score); ?></strong>
                                                            <br><small class="text-muted"><?php echo e(number_format(($submission->total_score / $submission->max_score) * 100, 1)); ?>%</small>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($submission->comments): ?>
                                                            <span class="text-muted"><?php echo e(Str::limit($submission->comments, 30)); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\parent\child_activities.blade.php ENDPATH**/ ?>