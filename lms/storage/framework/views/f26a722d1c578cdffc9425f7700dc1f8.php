
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">My Classes &amp; Subjects</h3>
                    <p class="dir-subtitle">Teaching load assigned from Classes &amp; Subjects and Class Schedules.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Classes &amp; Subjects</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Teaching classes</span>
                    <div class="dir-stat-value"><?php echo e($stats['classes']); ?></div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Assigned subjects</span>
                    <div class="dir-stat-value"><?php echo e($stats['subjects']); ?></div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Assigned sections</span>
                    <div class="dir-stat-value"><?php echo e($stats['sections']); ?></div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">With schedule</span>
                    <div class="dir-stat-value"><?php echo e($stats['scheduled']); ?></div>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Assigned teaching load</h5>
                    <span class="dir-count mt-1"><?php echo e($assignments->count()); ?> assignment<?php echo e($assignments->count() === 1 ? '' : 's'); ?></span>
                </div>
            </div>
            <?php if($assignments->isNotEmpty()): ?>
                <div class="table-responsive">
                    <table class="table dir-table mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Section / Class</th>
                                <th>Grade</th>
                                <th>Students</th>
                                <th>Schedule</th>
                                <th>Source</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $subject = $row['subject'];
                                    $section = $row['section'];
                                    $sourceLabel = match ($row['source']) {
                                        'schedule' => 'Class Schedule',
                                        'section_subject' => 'Section Subject',
                                        'assignment' => 'Teacher Assignment',
                                        default => 'Subject Only',
                                    };
                                ?>
                                <tr>
                                    <td>
                                        <span class="dir-person-name"><?php echo e($subject->subject_name); ?></span>
                                        <span class="dir-person-meta"><?php echo e($subject->subject_id ?? ('ID ' . $subject->id)); ?></span>
                                    </td>
                                    <td>
                                        <?php if($section): ?>
                                            <div><?php echo e($section->name); ?></div>
                                            <?php if($row['is_adviser']): ?>
                                                <span class="dir-badge dir-badge--inactive">Adviser</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="dir-muted">No section linked yet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="dir-chip dir-chip--soft"><?php echo e($section->grade_level ?? $subject->class ?? 'N/A'); ?></span></td>
                                    <td><?php echo e($row['students_count']); ?></td>
                                    <td>
                                        <div><?php echo e($row['schedule_summary']); ?></div>
                                        <?php if($row['schedules']->isNotEmpty()): ?>
                                            <span class="dir-person-meta">
                                                <?php $__currentLoopData = $row['schedules']->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php echo e(ucfirst($sch->day_of_week)); ?>

                                                    <?php echo e(\Carbon\Carbon::parse($sch->start_time)->format('g:i A')); ?>–
                                                    <?php echo e(\Carbon\Carbon::parse($sch->end_time)->format('g:i A')); ?><?php if(!$loop->last): ?>; <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($row['schedules']->count() > 2): ?>
                                                    +<?php echo e($row['schedules']->count() - 2); ?> more
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="dir-chip"><?php echo e($sourceLabel); ?></span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <?php if(Route::has('attendance.index')): ?>
                                                <a href="<?php echo e(route('attendance.index')); ?>" class="dir-icon-btn" title="Attendance"><i class="fas fa-calendar-check"></i></a>
                                            <?php endif; ?>
                                            <?php if(Route::has('assignments.create')): ?>
                                                <a href="<?php echo e(route('assignments.create')); ?>" class="dir-icon-btn" title="Create Assignment"><i class="fas fa-tasks"></i></a>
                                            <?php endif; ?>
                                            <?php if(Route::has('lessons.create')): ?>
                                                <a href="<?php echo e(route('lessons.create')); ?>" class="dir-icon-btn" title="Create Lesson"><i class="fas fa-book-open"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="dir-empty">
                    <i class="fas fa-chalkboard-teacher d-block"></i>
                    <h5 class="mt-2 mb-1">No teaching assignments yet</h5>
                    <p class="mb-0">Classes appear here after Admin assigns you under Classes &amp; Subjects or Class Schedules.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-lg-6">
                <div class="dir-card">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Assigned subjects</h5>
                    </div>
                    <div class="p-3">
                        <?php $__empty_1 = true; $__currentLoopData = $assignedSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="d-flex justify-content-between align-items-center py-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                                <div>
                                    <div class="dir-person-name"><?php echo e($subject->subject_name); ?></div>
                                    <span class="dir-person-meta"><?php echo e($subject->subject_id); ?> · <?php echo e($subject->class ?? 'N/A'); ?></span>
                                </div>
                                <span class="dir-chip">Subject</span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="dir-muted mb-0">No subjects assigned yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="dir-card">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Assigned sections</h5>
                    </div>
                    <div class="p-3">
                        <?php $__empty_1 = true; $__currentLoopData = $assignedSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="d-flex justify-content-between align-items-center py-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                                <div>
                                    <div class="dir-person-name"><?php echo e($section->name); ?></div>
                                    <span class="dir-person-meta"><?php echo e($section->grade_level ?? 'N/A'); ?></span>
                                </div>
                                <span class="dir-chip dir-chip--soft">Section</span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="dir-muted mb-0">No sections assigned yet.</p>
                        <?php endif; ?>

                        <?php if($adviserSections->isNotEmpty()): ?>
                            <div class="dir-person-meta text-uppercase mt-3 mb-1">Homeroom (Adviser)</div>
                            <?php $__currentLoopData = $adviserSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <div class="dir-person-name"><?php echo e($section->name); ?></div>
                                        <span class="dir-person-meta"><?php echo e($section->grade_level ?? 'N/A'); ?> · <?php echo e($section->students_count); ?> students</span>
                                    </div>
                                    <span class="dir-badge dir-badge--inactive">Adviser</span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914e">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/teacher/classes.blade.php ENDPATH**/ ?>