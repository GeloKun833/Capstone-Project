
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-12 col-sm-6">
                    <h3 class="page-title">Teacher Information System (TIS)</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('teacher/list/page')); ?>">Teachers</a></li>
                        <li class="breadcrumb-item active"><?php echo e($teacher->full_name); ?></li>
                    </ul>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="float-end d-flex flex-wrap gap-2">
                        <a href="<?php echo e(url('teacher/edit/'.$user->user_id)); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Edit Teacher
                        </a>
                        <a href="<?php echo e(url('view/user/edit/'.$user->user_id)); ?>" class="btn btn-outline-info">
                            <i class="fas fa-user-edit me-1"></i>Edit User
                        </a>
                        <a href="<?php echo e(route('teacher/list/page')); ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <?php if(!empty($teacher->avatar)): ?>
                        <img src="<?php echo e(URL::to('images/'.$teacher->avatar)); ?>" alt="<?php echo e($teacher->full_name); ?>" class="rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                    <?php else: ?>
                        <img src="<?php echo e(URL::to('images/photo_defaults.jpg')); ?>" alt="<?php echo e($teacher->full_name); ?>" class="rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <h4 class="mb-1"><?php echo e($teacher->full_name); ?></h4>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-primary"><?php echo e($user->role_name); ?></span>
                            <span class="badge bg-<?php echo e(strtolower($user->status) === 'active' ? 'success' : 'danger'); ?>">
                                <?php echo e(ucfirst($user->status)); ?>

                            </span>
                            <small class="text-muted">User ID: <?php echo e($user->user_id); ?></small>
                        </div>
                    </div>
                    <div class="d-flex gap-3 text-center">
                        <div>
                            <div class="fw-bold text-info"><?php echo e($assignedSubjects->count()); ?></div>
                            <small class="text-muted">Subjects</small>
                        </div>
                        <div>
                            <div class="fw-bold text-success"><?php echo e($assignedSections->count()); ?></div>
                            <small class="text-muted">Sections</small>
                        </div>
                        <div>
                            <div class="fw-bold text-dark"><?php echo e($teachingSchedule->count()); ?></div>
                            <small class="text-muted">Schedules</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm mb-3">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Full Name:</td>
                                <td><?php echo e($teacher->full_name); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email:</td>
                                <td><a href="mailto:<?php echo e($user->email); ?>"><?php echo e($user->email); ?></a></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phone:</td>
                                <td><?php echo e($teacher->phone_number ?: 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Gender:</td>
                                <td><?php echo e($teacher->gender ?: 'Not specified'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Date of Birth:</td>
                                <td><?php echo e($teacher->date_of_birth ?: 'Not specified'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Join Date:</td>
                                <td><?php echo e($user->join_date ?: 'N/A'); ?></td>
                            </tr>
                        </table>
                        <h6 class="fw-bold mb-2"><i class="fas fa-map-marker-alt me-1"></i>Address</h6>
                        <p class="mb-1"><?php echo e($teacher->address ?: 'N/A'); ?></p>
                        <p class="mb-0 text-muted">
                            <?php echo e(trim(collect([$teacher->city, $teacher->state, $teacher->zip_code])->filter()->implode(', '))); ?>

                            <?php if($teacher->country): ?><br><?php echo e($teacher->country); ?><?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Professional Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Qualification:</td>
                                <td><?php echo e($teacher->qualification ?: 'Not specified'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Experience:</td>
                                <td><?php echo e($teacher->experience ?: 'Not specified'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Department:</td>
                                <td><?php echo e($user->department ?: 'Not assigned'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Position:</td>
                                <td><?php echo e($user->position ?: 'Teacher'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Assigned Subjects (<?php echo e($assignedSubjects->count()); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if($assignedSubjects->isNotEmpty()): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Subject Name</th>
                                            <th>Class/Grade</th>
                                            <th>Sections</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $assignedSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><strong><?php echo e($subject->subject_name); ?></strong></td>
                                            <td><span class="badge bg-primary"><?php echo e($subject->class); ?></span></td>
                                            <td>
                                                <?php if($subject->sections && $subject->sections->isNotEmpty()): ?>
                                                    <?php $__currentLoopData = $subject->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="badge bg-secondary"><?php echo e($section->name); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No sections</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No subjects assigned yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Assigned Sections (<?php echo e($assignedSections->count()); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if($assignedSections->isNotEmpty()): ?>
                            <div class="row">
                                <?php $__currentLoopData = $assignedSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <h5 class="mb-1"><?php echo e($section->name); ?></h5>
                                            <p class="mb-0 text-muted"><?php echo e($section->grade_level); ?></p>
                                            <small class="text-muted">Room: <?php echo e($section->room_number ?: 'N/A'); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No sections assigned yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>More Details</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Additional records are hidden to keep this page clear. Open any item below to view.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tisGradeLevelsModal">
                                <i class="fas fa-layer-group me-1"></i> Teaching Grade Levels
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tisScheduleModal">
                                <i class="fas fa-calendar-alt me-1"></i> Teaching Schedule
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tisPostsModal">
                                <i class="fas fa-bullhorn me-1"></i> Recent Class Posts
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="tisGradeLevelsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Teaching Grade Levels</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if($gradeLevels->isNotEmpty()): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gradeLevel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge bg-warning text-dark px-3 py-2"><?php echo e($gradeLevel->grade_level); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No teaching grade levels assigned.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="tisScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i>Teaching Schedule (<?php echo e($teachingSchedule->count()); ?>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if($teachingSchedule->isNotEmpty()): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Section</th>
                                    <th>Room</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $teachingSchedule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e(ucfirst($schedule->day_of_week)); ?></td>
                                    <td><?php echo e($schedule->time_range); ?></td>
                                    <td><strong><?php echo e($schedule->subject->subject_name ?? 'N/A'); ?></strong></td>
                                    <td><?php echo e($schedule->section->name ?? 'N/A'); ?></td>
                                    <td><?php echo e($schedule->room->name ?? ($schedule->room->room_name ?? 'N/A')); ?></td>
                                    <td><?php echo e($schedule->class_type_display ?? ucfirst($schedule->class_type ?? 'lecture')); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No active class schedules assigned yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="tisPostsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bullhorn me-2"></i>Recent Class Posts (<?php echo e($classPosts->count()); ?>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if($classPosts->isNotEmpty()): ?>
                    <?php $__currentLoopData = $classPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><?php echo e($post->title); ?></h6>
                                <small class="text-muted"><?php echo e($post->created_at?->format('M d, Y')); ?></small>
                            </div>
                            <p class="text-muted small mb-2">
                                <span class="badge bg-secondary me-1"><?php echo e(ucfirst($post->type ?? 'announcement')); ?></span>
                                <?php if($post->subject): ?>
                                    <?php echo e($post->subject->subject_name); ?>

                                <?php endif; ?>
                            </p>
                            <p class="mb-0"><?php echo e(\Illuminate\Support\Str::limit(strip_tags($post->content), 200)); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No class posts published yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\teacher\tis.blade.php ENDPATH**/ ?>