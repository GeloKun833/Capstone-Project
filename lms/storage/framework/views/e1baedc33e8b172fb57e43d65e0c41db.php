
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Enrollment Management</h3>
                    <p class="dir-subtitle">One row per student, with subjects grouped together.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Enrollments</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dir-card dir-filters">
            <form method="GET" action="<?php echo e(route('enrollments.index')); ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-8 col-md-8">
                        <label class="form-label">Search students</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, email, or subject" value="<?php echo e($search ?? ''); ?>">
                    </div>
                    <div class="col-lg-4 col-md-4 pb-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">Search</button>
                            <a href="<?php echo e(route('enrollments.index')); ?>" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Enrolled students</h5>
                    <span class="dir-count mt-1"><?php echo e($paginatedEnrollments->total()); ?> student<?php echo e($paginatedEnrollments->total() === 1 ? '' : 's'); ?></span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table dir-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Grade / Section</th>
                            <th>Subjects</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Enrolled</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $paginatedEnrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $photo = $row['student_upload']
                                    ? \Illuminate\Support\Facades\Storage::url('student-photos/'.$row['student_upload'])
                                    : asset('images/photo_defaults.jpg');
                                $status = strtolower((string) $row['status']);
                                $statusClass = match ($status) {
                                    'active' => 'dir-badge--active',
                                    'pending' => 'dir-badge--inactive',
                                    'completed' => 'dir-badge--neutral',
                                    'dropped', 'inactive' => 'dir-badge--disabled',
                                    default => 'dir-badge--neutral',
                                };
                                $subjects = $row['subjects'] ?? collect();
                                $visibleSubjects = $subjects->take(4);
                                $extraSubjects = max(0, $subjects->count() - 4);
                            ?>
                            <tr>
                                <td>
                                    <div class="dir-person">
                                        <img src="<?php echo e($photo); ?>" alt="<?php echo e($row['student_name']); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                                        <span>
                                            <?php if(!empty($row['student_user_id'])): ?>
                                                <a href="<?php echo e(route('student.sis', $row['student_user_id'])); ?>" class="dir-person-name"><?php echo e($row['student_name']); ?></a>
                                            <?php else: ?>
                                                <span class="dir-person-name"><?php echo e($row['student_name']); ?></span>
                                            <?php endif; ?>
                                            <span class="dir-person-meta"><?php echo e($row['student_email']); ?></span>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php if($row['grade_level'] || ($row['sections'] ?? collect())->isNotEmpty()): ?>
                                        <div class="dir-chip-wrap">
                                            <?php if($row['grade_level']): ?>
                                                <span class="dir-chip"><?php echo e($row['grade_level']); ?></span>
                                            <?php endif; ?>
                                            <?php $__currentLoopData = ($row['sections'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="dir-chip dir-chip--soft"><?php echo e($section); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="dir-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dir-chip-wrap">
                                        <?php $__empty_2 = true; $__currentLoopData = $visibleSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                            <span class="dir-chip" title="<?php echo e($subject); ?>"><?php echo e($subject); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                            <?php if($row['has_portal']): ?>
                                                <span class="dir-chip dir-chip--soft">Portal application</span>
                                            <?php else: ?>
                                                <span class="dir-muted">No subjects</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if($extraSubjects > 0): ?>
                                            <span class="dir-chip dir-chip--soft" title="<?php echo e($subjects->implode(', ')); ?>">+<?php echo e($extraSubjects); ?> more</span>
                                        <?php endif; ?>
                                        <?php if($row['has_portal'] && $subjects->isNotEmpty()): ?>
                                            <span class="dir-chip dir-chip--soft">Portal</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo e($row['academic_year']); ?></td>
                                <td><?php echo e($row['semester']); ?></td>
                                <td>
                                    <span class="dir-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span>
                                </td>
                                <td>
                                    <?php echo e($row['enrollment_date'] ? \Carbon\Carbon::parse($row['enrollment_date'])->format('M j, Y') : '—'); ?>

                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1 justify-content-end">
                                        <?php if(!empty($row['student_user_id'])): ?>
                                            <a href="<?php echo e(route('student.sis', $row['student_user_id'])); ?>" class="dir-icon-btn is-success" title="Student record">
                                                <i class="fas fa-id-card"></i>
                                            </a>
                                        <?php elseif(!empty($row['primary_enrollment_id'])): ?>
                                            <a href="<?php echo e(route('enrollments.show', $row['primary_enrollment_id'])); ?>" class="dir-icon-btn" title="View enrollment">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if($row['has_portal'] && !empty($row['portal_id'])): ?>
                                            <a href="<?php echo e(route('enrollment.registrar.show', $row['portal_id'])); ?>" class="dir-icon-btn" title="Portal application">
                                                <i class="fas fa-globe"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if(!empty($row['primary_enrollment_id'])): ?>
                                            <a href="<?php echo e(route('enrollments.edit', $row['primary_enrollment_id'])); ?>" class="dir-icon-btn" title="Edit enrollment">
                                                <i class="far fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="dir-empty">
                                        <div><i class="fas fa-user-graduate"></i></div>
                                        <strong>No enrollments found</strong>
                                        <div class="small mt-1">Create a user or enroll a student to see them here.</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($paginatedEnrollments->hasPages()): ?>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-3">
                    <p class="text-muted mb-0 small">
                        Showing <?php echo e($paginatedEnrollments->firstItem()); ?>–<?php echo e($paginatedEnrollments->lastItem()); ?> of <?php echo e($paginatedEnrollments->total()); ?> students
                    </p>
                    <?php echo e($paginatedEnrollments->links('pagination::bootstrap-4')); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914b">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/enrollments/index.blade.php ENDPATH**/ ?>