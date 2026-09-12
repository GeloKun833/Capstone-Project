
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title"><?php echo e($child->full_name); ?> - Profile</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active"><?php echo e($child->full_name); ?> - Profile</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Profile Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <img src="<?php echo e($child->upload ? asset('storage/' . $child->upload) : URL::to('assets/img/profiles/avatar-01.jpg')); ?>" 
                                         alt="Student Photo" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                    <h4 class="mt-3 mb-1"><?php echo e($child->full_name); ?></h4>
                                    <p class="text-muted">Student ID: <?php echo e($child->admission_id); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2">Personal Information</h6>
                                            <p class="mb-1"><strong>Email:</strong> <?php echo e($child->email ?? 'N/A'); ?></p>
                                            <p class="mb-1"><strong>Phone:</strong> <?php echo e($child->phone ?? 'N/A'); ?></p>
                                            <p class="mb-1"><strong>Date of Birth:</strong> <?php echo e($child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->format('M d, Y') : 'N/A'); ?></p>
                                            <p class="mb-1"><strong>Gender:</strong> <?php echo e($child->gender ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2">Academic Information</h6>
                                            <p class="mb-1"><strong>Section:</strong> <?php echo e($child->sections->first()->name ?? 'Not Assigned'); ?></p>
                                            <p class="mb-1"><strong>Academic Year:</strong> <?php echo e($academicYear->year ?? 'Not Set'); ?></p>
                                            <p class="mb-1"><strong>Semester:</strong> <?php echo e($semester->name ?? 'Not Set'); ?></p>
                                            <p class="mb-1"><strong>Status:</strong> 
                                                <span class="badge bg-success">Active</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-primary mb-2">Current GPA</h5>
                                        <h3 class="mb-0"><?php echo e($child->getCurrentGpa($academicYear->id ?? null, $semester->id ?? null)->gpa ?? 'N/A'); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-graduation-cap text-primary" style="font-size: 24px;"></i>
                            </div>
                            <h4 class="mb-1"><?php echo e($totalGrades); ?></h4>
                            <p class="text-muted mb-0">Total Grades</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-chart-line text-success" style="font-size: 24px;"></i>
                            </div>
                            <h4 class="mb-1"><?php echo e(number_format($averageGrade, 1)); ?>%</h4>
                            <p class="text-muted mb-0">Average Grade</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-calendar-check text-info" style="font-size: 24px;"></i>
                            </div>
                            <h4 class="mb-1"><?php echo e($totalAttendance); ?></h4>
                            <p class="text-muted mb-0">Total Attendance</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-percentage text-warning" style="font-size: 24px;"></i>
                            </div>
                            <h4 class="mb-1"><?php echo e($attendancePercentage); ?>%</h4>
                            <p class="text-muted mb-0">Attendance Rate</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrolled Classes -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Enrolled Classes</h5>
                        </div>
                        <div class="card-body">
                            <?php if($enrollments->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Subject Code</th>
                                                <th>Subject Name</th>
                                                <th>Academic Year</th>
                                                <th>Semester</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <i class="fas fa-book text-primary"></i>
                                                            </a>
                                                            <a href="#"><?php echo e($enrollment->subject->subject_code ?? 'N/A'); ?></a>
                                                        </h2>
                                                    </td>
                                                    <td><?php echo e($enrollment->subject->subject_name ?? 'N/A'); ?></td>
                                                    <td><?php echo e($enrollment->academicYear->year ?? 'N/A'); ?></td>
                                                    <td><?php echo e($enrollment->semester->name ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo e(ucfirst($enrollment->status)); ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo e(route('parent.child.grades', $child->id)); ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-chart-line"></i> Grades
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-book-open text-muted" style="font-size: 48px;"></i>
                                    <h5 class="mt-3 text-muted">No Classes Enrolled</h5>
                                    <p class="text-muted">This student is not currently enrolled in any classes.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <!-- Recent Grades -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Recent Grades</h5>
                            <a href="<?php echo e(route('parent.child.grades', $child->id)); ?>" class="float-end">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if($recentGrades->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Score</th>
                                                <th>Percentage</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $recentGrades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e($grade->subject->subject_name ?? 'N/A'); ?></td>
                                                    <td><?php echo e($grade->score); ?>/<?php echo e($grade->max_score); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo e($grade->percentage >= 90 ? 'success' : ($grade->percentage >= 75 ? 'info' : 'warning')); ?>">
                                                            <?php echo e(number_format($grade->percentage, 1)); ?>%
                                                        </span>
                                                    </td>
                                                    <td><?php echo e(\Carbon\Carbon::parse($grade->created_at)->format('M d, Y')); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-line text-muted" style="font-size: 48px;"></i>
                                    <h5 class="mt-3 text-muted">No Recent Grades</h5>
                                    <p class="text-muted">No grades have been recorded yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Attendance -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Recent Attendance</h5>
                            <a href="<?php echo e(route('parent.child.attendance', $child->id)); ?>" class="float-end">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if($recentAttendance->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $recentAttendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e(\Carbon\Carbon::parse($attendance->date)->format('M d, Y')); ?></td>
                                                    <td><?php echo e($attendance->subject->subject_name ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo e($attendance->status === 'present' ? 'success' : 'danger'); ?>">
                                                            <?php echo e(ucfirst($attendance->status)); ?>

                                                        </span>
                                                    </td>
                                                    <td><?php echo e(\Carbon\Carbon::parse($attendance->created_at)->format('h:i A')); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-check text-muted" style="font-size: 48px;"></i>
                                    <h5 class="mt-3 text-muted">No Recent Attendance</h5>
                                    <p class="text-muted">No attendance records found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\parent\child_profile.blade.php ENDPATH**/ ?>