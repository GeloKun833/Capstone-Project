<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">My Subjects</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
                                <li class="breadcrumb-item active">My Subjects</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Total Subjects</h6>
                                    <h3><?php echo e($totalSubjects); ?></h3>
                                </div>
                                <div class="db-icon">
                                    <img src="<?php echo e(URL::to('assets/img/icons/teacher-icon-01.svg')); ?>" alt="Dashboard Icon">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Total Students</h6>
                                    <h3><?php echo e($totalStudents); ?></h3>
                                </div>
                                <div class="db-icon">
                                    <img src="<?php echo e(URL::to('assets/img/icons/dash-icon-01.svg')); ?>" alt="Dashboard Icon">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Total Sections</h6>
                                    <h3><?php echo e($teacherSections); ?></h3>
                                </div>
                                <div class="db-icon">
                                    <img src="<?php echo e(URL::to('assets/img/icons/teacher-icon-02.svg')); ?>" alt="Dashboard Icon">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Active Status</h6>
                                    <h3><?php echo e($totalSubjects > 0 ? 'Active' : 'Inactive'); ?></h3>
                                </div>
                                <div class="db-icon">
                                    <img src="<?php echo e(URL::to('assets/img/icons/teacher-icon-03.svg')); ?>" alt="Dashboard Icon">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subjects Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card flex-fill comman-shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <h5 class="card-title">My Assigned Subjects</h5>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Last Updated: <?php echo e(now()->format('M d, Y g:i A')); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if($teacherSubjects && $teacherSubjects->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Subject ID</th>
                                                <th>Subject Name</th>
                                                <th>Class</th>
                                                <th>Students</th>
                                                <th>Academic Year</th>
                                                <th>Semester</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $teacherSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo e($subject->id); ?></span>
                                                </td>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="#" class="avatar avatar-sm me-2">
                                                            <img class="avatar-img rounded-circle" src="<?php echo e(URL::to('assets/img/profiles/avatar-01.jpg')); ?>" alt="Subject">
                                                        </a>
                                                        <a href="#" class="fw-bold"><?php echo e($subject->subject_name); ?></a>
                                                    </h2>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo e($subject->class ?? 'N/A'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success"><?php echo e($subject->enrollments ? $subject->enrollments->count() : 0); ?></span>
                                                </td>
                                                <td>
                                                    <?php if($subject->enrollments && $subject->enrollments->count() > 0): ?>
                                                        <?php
                                                            $academicYears = $subject->enrollments->pluck('academicYear.name')->unique()->filter();
                                                        ?>
                                                        <?php if($academicYears->count() > 0): ?>
                                                            <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <span class="badge bg-secondary"><?php echo e($year); ?></span>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($subject->enrollments && $subject->enrollments->count() > 0): ?>
                                                        <?php
                                                            $semesters = $subject->enrollments->pluck('semester.name')->unique()->filter();
                                                        ?>
                                                        <?php if($semesters->count() > 0): ?>
                                                            <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <span class="badge bg-info"><?php echo e($semester); ?></span>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="actions">
                                                        <a href="#" class="btn btn-sm bg-success-light me-2" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?php echo e(route('assignments.create')); ?>" class="btn btn-sm bg-primary-light me-2" title="Create Assignment">
                                                            <i class="fas fa-tasks"></i>
                                                        </a>
                                                        <a href="<?php echo e(route('class-posts.create')); ?>" class="btn btn-sm bg-warning-light" title="Create Post">
                                                            <i class="fas fa-bullhorn"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-book fa-4x text-muted mb-3"></i>
                                        <h4>No Subjects Assigned</h4>
                                        <p class="text-muted">You haven't been assigned to any subjects yet.</p>
                                        <div class="mt-3">
                                            <p class="text-muted small">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Contact your administrator to get assigned to subjects.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <?php if($teacherSubjects && $teacherSubjects->count() > 0): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="<?php echo e(route('assignments.create')); ?>" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                        <i class="fas fa-tasks fa-2x mb-2"></i>
                                        <span class="fw-bold">Create Assignment</span>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?php echo e(route('class-posts.create')); ?>" class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                        <i class="fas fa-bullhorn fa-2x mb-2"></i>
                                        <span class="fw-bold">Create Class Post</span>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?php echo e(route('lessons.create')); ?>" class="btn btn-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                        <i class="fas fa-book-open fa-2x mb-2"></i>
                                        <span class="fw-bold">Create Lesson</span>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?php echo e(route('attendance.index')); ?>" class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                        <span class="fw-bold">Take Attendance</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

<style>
.empty-state {
    padding: 2rem;
}

.empty-state i {
    color: #6c757d;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.table-avatar {
    margin: 0;
    display: flex;
    align-items: center;
}

.table-avatar .avatar {
    margin-right: 0.5rem;
}

.actions .btn {
    margin-right: 0.25rem;
}

.actions .btn:last-child {
    margin-right: 0;
}

.bg-success-light {
    background-color: #d1e7dd;
    color: #0f5132;
}

.bg-primary-light {
    background-color: #cfe2ff;
    color: #084298;
}

.bg-warning-light {
    background-color: #fff3cd;
    color: #664d03;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\teacher\subjects.blade.php ENDPATH**/ ?>