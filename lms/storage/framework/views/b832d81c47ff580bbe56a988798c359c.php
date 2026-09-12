<?php if(!isset($student['hasStudent']) || !$student['hasStudent']): ?>
    <div class="sd-dashboard">
        <div class="sd-empty-state">
            <div class="sd-empty-icon"><i class="far fa-user"></i></div>
            <h2>Student profile not found</h2>
            <p>Your student profile is being set up. Contact the registrar if this continues.</p>
            <a href="<?php echo e(route('dashboard')); ?>" class="sd-btn-primary">Return to Dashboard</a>
        </div>
    </div>
<?php else: ?>
<?php
    $s = $student;
    $stu = $s['student'];
    $firstName = $stu->first_name ?? 'Student';
    $fullName = trim(($stu->first_name ?? '').' '.($stu->middle_name ? substr($stu->middle_name, 0, 1).'. ' : '').($stu->last_name ?? ''));
    $greeting = $s['greeting'] ?? 'Hello';
    $stats = $s['stats'] ?? [];
    $section = $s['section'] ?? $stu->sections->first();
    $ay = $s['currentAcademicYear'] ?? null;
    $sem = $s['currentSemester'] ?? null;
    $enrollments = $s['enrollments'] ?? collect();
    $courseCards = $s['courseCards'] ?? collect();
    $upcomingAssignments = $s['upcomingAssignments'] ?? collect();
    $upcomingEvents = $s['upcomingEvents'] ?? collect();
    $announcements = $s['announcements'] ?? collect();
    $recentActivity = $s['recentActivity'] ?? collect();
    $avatar = !empty($stu->upload) ? asset('storage/'.$stu->upload) : URL::to('assets/img/profiles/avatar-01.jpg');
    $studentIdDisplay = $s['studentIdDisplay'] ?? ($stu->admission_id ?? 'N/A');

    $enrollmentApplication = $stu->enrollmentApplication ?? null;
    $requiredDocuments = [
        'birth_certificate' => 'Birth Certificate',
        'sf9' => 'SF9 (Learner\'s Permanent Record)',
        'sf10' => 'SF10 (Report Card)',
        'good_moral' => 'Certificate of Good Moral Character',
        'id_photo' => 'ID Photo (2x2)',
        'parent_guardian_id' => 'Parent/Guardian ID',
    ];
    $uploadedTypes = $enrollmentApplication ? $enrollmentApplication->documents->pluck('document_type')->toArray() : [];
    $missingDocuments = $enrollmentApplication ? array_diff(array_keys($requiredDocuments), $uploadedTypes) : [];
    $docComplete = $enrollmentApplication ? $enrollmentApplication->documents->count() : 0;
    $docPct = (int) round(($docComplete / 6) * 100);

    $gradeSubjects = [];
    if ($enrollmentApplication && $enrollmentApplication->grade_level_applying_for) {
        $gradeSubjects = \App\Helpers\GradeSubjectsHelper::getSubjectsForGrade($enrollmentApplication->grade_level_applying_for) ?: [];
    }
?>

<div class="sd-dashboard">
    <div class="sd-welcome">
        <div>
            <h1><?php echo e($greeting); ?>, <?php echo e($firstName); ?> <span aria-hidden="true">👋</span></h1>
            <p>Here&rsquo;s what&rsquo;s happening with your studies today.</p>
        </div>
        <div class="sd-term-chip">
            <span class="sd-term-label">Academic Year</span>
            <strong><?php echo e($ay->name ?? '—'); ?></strong>
            <span class="sd-term-sep">·</span>
            <span><?php echo e($sem->name ?? '—'); ?></span>
        </div>
    </div>

    <div class="sd-top-grid">
        <article class="sd-profile-card">
            <img src="<?php echo e($avatar); ?>" alt="<?php echo e($fullName); ?>" class="sd-avatar">
            <div class="sd-profile-body">
                <h2><?php echo e($fullName); ?></h2>
                <div class="sd-profile-meta">
                    <span><i class="far fa-id-badge"></i> ID <?php echo e($studentIdDisplay); ?></span>
                    <span><i class="fas fa-graduation-cap"></i> <?php echo e($stu->year_level ?? 'Not set'); ?></span>
                    <span><i class="far fa-users"></i> <?php echo e($section->name ?? ($stu->section ?? 'Not assigned')); ?></span>
                </div>
            </div>
            <a href="<?php echo e(route('user/profile/page')); ?>" class="sd-btn-outline">View Profile</a>
        </article>

        <div class="sd-stats">
            <article class="sd-stat">
                <div class="sd-stat-icon sd-stat-green"><i class="far fa-calendar-check"></i></div>
                <div>
                    <div class="sd-stat-value"><?php echo e(isset($stats['attendance_pct']) && $stats['attendance_pct'] !== null ? $stats['attendance_pct'].'%' : '—'); ?></div>
                    <div class="sd-stat-label">Attendance</div>
                    <div class="sd-stat-meta"><?php echo e($stats['attendance_label'] ?? ''); ?></div>
                </div>
            </article>
            <article class="sd-stat">
                <div class="sd-stat-icon"><i class="far fa-book-open"></i></div>
                <div>
                    <div class="sd-stat-value"><?php echo e($stats['classes'] ?? $enrollments->count()); ?></div>
                    <div class="sd-stat-label">Classes</div>
                    <div class="sd-stat-meta">Active subjects</div>
                </div>
            </article>
            <article class="sd-stat">
                <div class="sd-stat-icon sd-stat-amber"><i class="far fa-clipboard"></i></div>
                <div>
                    <div class="sd-stat-value"><?php echo e($stats['assignments_due'] ?? 0); ?></div>
                    <div class="sd-stat-label">Assignments</div>
                    <div class="sd-stat-meta">Due this week</div>
                </div>
            </article>
            <article class="sd-stat">
                <div class="sd-stat-icon"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="sd-stat-value"><?php echo e(isset($stats['progress_pct']) && $stats['progress_pct'] !== null ? $stats['progress_pct'].'%' : '—'); ?></div>
                    <div class="sd-stat-label">Academic Progress</div>
                    <div class="sd-stat-meta">Overall progress</div>
                </div>
            </article>
        </div>
    </div>

    <div class="sd-layout">
        <div class="sd-main">
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Today&rsquo;s Classes</h2>
                    <?php if($enrollments->isNotEmpty()): ?>
                        <a href="<?php echo e(route('student.class.detail', $enrollments->first()->id)); ?>" class="sd-link">View all classes</a>
                    <?php endif; ?>
                </div>
                <div class="sd-class-list">
                    <?php $__empty_1 = true; $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $subj = $enrollment->subject->subject_name ?? 'Subject';
                            $desc = $enrollment->subject->description ?? 'Lesson description';
                        ?>
                        <article class="sd-class-row">
                            <div class="sd-class-icon"><i class="far fa-bookmark"></i></div>
                            <div class="sd-class-info">
                                <h3><?php echo e($subj); ?></h3>
                                <p><?php echo e(\Illuminate\Support\Str::limit($desc, 80)); ?></p>
                                <div class="sd-class-meta">
                                    <span><i class="far fa-clock"></i> <?php echo e($enrollment->academicYear->name ?? ($ay->name ?? '—')); ?></span>
                                    <span><?php echo e($enrollment->semester->name ?? ($sem->name ?? '')); ?></span>
                                </div>
                            </div>
                            <span class="sd-badge sd-badge-ok">Active</span>
                            <a href="<?php echo e(route('student.class.detail', $enrollment->id)); ?>" class="sd-btn-ghost">View class</a>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="sd-empty">No classes enrolled yet. Enrollments will appear here once approved.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>My Courses</h2>
                    <a href="<?php echo e(route('student.grades')); ?>" class="sd-link">Academic records</a>
                </div>
                <div class="sd-course-grid">
                    <?php $__empty_1 = true; $__currentLoopData = $courseCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="sd-course-card">
                            <div class="sd-course-top">
                                <div class="sd-class-icon sm"><i class="far fa-book"></i></div>
                                <span class="sd-badge sd-badge-ok"><?php echo e($course->status); ?></span>
                            </div>
                            <h3><?php echo e($course->subject_name); ?></h3>
                            <p class="sd-muted"><?php echo e($course->teacher ? 'Teacher: '.$course->teacher : ($course->semester ?: 'Current term')); ?></p>
                            <div class="sd-progress-label">
                                <span>Progress</span>
                                <strong><?php echo e($course->progress !== null ? $course->progress.'%' : '—'); ?></strong>
                            </div>
                            <div class="sd-progress">
                                <div class="sd-progress-bar" style="width: <?php echo e(min(100, (int) ($course->progress ?? 0))); ?>%"></div>
                            </div>
                            <p class="sd-meta"><?php echo e($course->academic_year); ?><?php if($course->semester): ?> · <?php echo e($course->semester); ?><?php endif; ?></p>
                            <a href="<?php echo e(route('student.class.detail', $course->enrollment_id)); ?>" class="sd-btn-outline block">Open course</a>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="sd-empty">No courses yet.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Upcoming Assignments</h2>
                    <a href="<?php echo e(route('student.assignments.index')); ?>" class="sd-link">View all</a>
                </div>
                <div class="sd-assign-list">
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="sd-assign-row">
                            <div class="sd-assign-main">
                                <h3><?php echo e($asg->title); ?></h3>
                                <p class="sd-muted"><?php echo e($asg->subject); ?> · Due <?php echo e($asg->due_label); ?></p>
                            </div>
                            <span class="sd-badge sd-badge-<?php echo e($asg->priority === 'due_soon' ? 'warn' : ($asg->priority === 'completed' ? 'ok' : 'soft')); ?>">
                                <?php echo e(str_replace('_', ' ', ucfirst($asg->priority))); ?>

                            </span>
                            <a href="<?php echo e(route('student.assignments.show', $asg->id)); ?>" class="sd-btn-ghost">View assignment</a>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="sd-empty">No upcoming assignments.</div>
                    <?php endif; ?>
                </div>
            </section>

            <?php if(!empty($gradeSubjects)): ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>My Subjects</h2>
                    <span class="sd-muted small"><?php echo e($enrollmentApplication->grade_level_applying_for); ?></span>
                </div>
                <div class="sd-subject-grid">
                    <?php $__currentLoopData = $gradeSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subjectName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="sd-subject-card">
                            <div class="sd-class-icon sm"><i class="far fa-bookmark"></i></div>
                            <div>
                                <h3><?php echo e($subjectName); ?></h3>
                                <p class="sd-muted">Active · Current term</p>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
            <?php elseif($enrollments->isNotEmpty()): ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>My Subjects</h2>
                </div>
                <div class="sd-subject-grid">
                    <?php $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="sd-subject-card">
                            <div class="sd-class-icon sm"><i class="far fa-bookmark"></i></div>
                            <div>
                                <h3><?php echo e($enrollment->subject->subject_name ?? 'Subject'); ?></h3>
                                <p class="sd-muted">Active</p>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
            <?php endif; ?>
        </div>

        <aside class="sd-side">
            <?php if($enrollmentApplication): ?>
            <section class="sd-panel sd-enroll">
                <div class="sd-panel-head">
                    <h2>Enrollment Application</h2>
                </div>
                <div class="sd-enroll-status">
                    <span class="sd-muted">Application status</span>
                    <strong>
                        <?php if(count($missingDocuments) > 0): ?>
                            Needs documents
                        <?php else: ?>
                            <?php echo e(ucfirst(str_replace('_', ' ', $enrollmentApplication->status))); ?>

                        <?php endif; ?>
                    </strong>
                </div>
                <div class="sd-progress-label">
                    <span>Document completion</span>
                    <strong><?php echo e($docComplete); ?>/6 Complete</strong>
                </div>
                <div class="sd-progress">
                    <div class="sd-progress-bar <?php echo e(count($missingDocuments) ? 'warn' : ''); ?>" style="width: <?php echo e(min(100, $docPct)); ?>%"></div>
                </div>
                <?php if(count($missingDocuments) > 0): ?>
                    <div class="sd-attention">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <p><strong><?php echo e(count($missingDocuments)); ?> document<?php echo e(count($missingDocuments) > 1 ? 's are' : ' is'); ?> still required</strong></p>
                            <p class="sd-muted">Missing: <?php echo e($requiredDocuments[array_key_first($missingDocuments)] ?? 'Document'); ?><?php if(count($missingDocuments) > 1): ?> +<?php echo e(count($missingDocuments) - 1); ?> more@endif</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="sd-attention ok">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <p><strong>All required documents uploaded</strong></p>
                            <p class="sd-muted">Your application is ready for review.</p>
                        </div>
                    </div>
                <?php endif; ?>
                <button type="button" class="sd-btn-primary block" data-bs-toggle="modal" data-bs-target="#studentEnrollmentModal">
                    View application details
                </button>
            </section>
            <?php endif; ?>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Upcoming Events</h2>
                    <a href="<?php echo e(route('calendar.index')); ?>" class="sd-link">View calendar</a>
                </div>
                <div class="sd-event-list">
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="sd-event-row">
                            <div class="sd-event-date">
                                <span class="d"><?php echo e(optional($event->start_time)->format('d')); ?></span>
                                <span class="m"><?php echo e(optional($event->start_time)->format('M')); ?></span>
                            </div>
                            <div>
                                <h3><?php echo e($event->title ?? 'Event'); ?></h3>
                                <p class="sd-muted"><?php echo e($event->subject->subject_name ?? 'School event'); ?></p>
                                <p class="sd-meta"><?php echo e(optional($event->start_time)->format('g:i A')); ?></p>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="sd-empty">No upcoming events.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Announcements</h2>
                    <a href="<?php echo e(route('announcements.index')); ?>" class="sd-link">See all</a>
                </div>
                <div class="sd-announce-list">
                    <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ann): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="sd-announce-row <?php echo e($loop->first ? 'unread' : ''); ?>">
                            <h3><?php echo e($ann->title); ?></h3>
                            <p><?php echo e(\Illuminate\Support\Str::limit(strip_tags($ann->content ?? ''), 90)); ?></p>
                            <span class="sd-meta"><?php echo e(optional($ann->created_at)->format('M j, Y')); ?></span>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="sd-empty">No announcements right now.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Recent Activity</h2>
                </div>
                <div class="sd-activity">
                    <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="sd-activity-item">
                            <div class="sd-activity-icon"><i class="fas <?php echo e($act->icon); ?>"></i></div>
                            <div>
                                <p><?php echo e($act->text); ?></p>
                                <span class="sd-meta"><?php echo e($act->time); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="sd-empty">No recent activity yet.</div>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>

    <footer class="sd-footer">
        <div>
            <strong>Panorama Montessori School</strong>
            <span>Student Information System</span>
        </div>
        <div class="sd-footer-links">
            <a href="<?php echo e(route('announcements.index')); ?>">Help &amp; Support</a>
            <a href="<?php echo e(route('user/profile/page')); ?>">Privacy</a>
            <span>Terms</span>
        </div>
    </footer>
</div>

<?php if($enrollmentApplication): ?>
<div class="modal fade" id="studentEnrollmentModal" tabindex="-1" aria-labelledby="studentEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content sd-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="studentEnrollmentModalLabel">Enrollment Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-<?php echo e($enrollmentApplication->status === 'approved' ? 'success' : ($enrollmentApplication->status === 'rejected' ? 'danger' : 'info')); ?>">
                    <p class="mb-1"><strong>Status:</strong>
                        <span class="badge bg-<?php echo e($enrollmentApplication->status === 'approved' ? 'success' : ($enrollmentApplication->status === 'rejected' ? 'danger' : 'warning')); ?>">
                            <?php echo e(ucfirst(str_replace('_', ' ', $enrollmentApplication->status))); ?>

                        </span>
                    </p>
                    <p class="mb-1"><strong>Application Number:</strong> <?php echo e($enrollmentApplication->application_number); ?></p>
                    <p class="mb-0"><strong>Submitted:</strong> <?php echo e($enrollmentApplication->created_at->format('M d, Y g:i A')); ?></p>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">Personal Information</h6></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Full Name:</strong> <?php echo e($enrollmentApplication->full_name); ?></p>
                                <p><strong>Date of Birth:</strong> <?php echo e(\Carbon\Carbon::parse($enrollmentApplication->date_of_birth)->format('M d, Y')); ?></p>
                                <p><strong>Gender:</strong> <?php echo e($enrollmentApplication->gender); ?></p>
                                <p class="mb-0"><strong>Email:</strong> <?php echo e($enrollmentApplication->email); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> <?php echo e($enrollmentApplication->phone_number); ?></p>
                                <p><strong>Address:</strong> <?php echo e($enrollmentApplication->address); ?></p>
                                <p><strong>Grade Level:</strong> <?php echo e($enrollmentApplication->grade_level_applying_for); ?></p>
                                <p class="mb-0"><strong>Previous School:</strong> <?php echo e($enrollmentApplication->previous_school ?: 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">Parent / Guardian</h6></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo e($enrollmentApplication->parent_name); ?></p>
                                <p class="mb-0"><strong>Email:</strong> <?php echo e($enrollmentApplication->parent_email); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> <?php echo e($enrollmentApplication->parent_phone); ?></p>
                                <p class="mb-0"><strong>Relationship:</strong> <?php echo e($enrollmentApplication->parent_relationship ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">Documents (<?php echo e($enrollmentApplication->documents->count()); ?>/6)</h6></div>
                    <div class="card-body">
                        <?php if($enrollmentApplication->documents->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>File</th>
                                            <th>Status</th>
                                            <th>Uploaded</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $enrollmentApplication->documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e(\App\Models\EnrollmentDocument::DOCUMENT_TYPES[$doc->document_type] ?? $doc->document_type); ?></td>
                                                <td><?php echo e($doc->file_name); ?></td>
                                                <td><?php echo e(ucfirst($doc->status)); ?></td>
                                                <td><?php echo e($doc->created_at->format('M d, Y')); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No documents uploaded yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($enrollmentApplication->notes): ?>
                    <div class="alert alert-info"><strong>Registrar notes:</strong> <?php echo e($enrollmentApplication->notes); ?></div>
                <?php endif; ?>
                <?php if($enrollmentApplication->rejection_reason): ?>
                    <div class="alert alert-danger"><strong>Rejection reason:</strong> <?php echo e($enrollmentApplication->rejection_reason); ?></div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>


<style>
/* Student dashboard — modern SIS styles */

:root {
    --sd-bg: #f5f6f8;
    --sd-card: #ffffff;
    --sd-text: #111827;
    --sd-muted: #6b7280;
    --sd-border: #e5e7eb;
    --sd-primary: #4f46e5;
    --sd-primary-soft: #eef2ff;
    --sd-primary-dark: #3730a3;
    --sd-green: #059669;
    --sd-green-soft: #ecfdf5;
    --sd-amber: #d97706;
    --sd-amber-soft: #fffbeb;
    --sd-radius: 14px;
    --sd-shadow: 0 1px 2px rgba(16, 24, 40, 0.04), 0 1px 3px rgba(16, 24, 40, 0.06);
}

.page-wrapper .content.container-fluid {
    background: var(--sd-bg) !important;
    max-width: none !important;
    width: 100% !important;
    padding-left: 1.5rem !important;
    padding-right: 1.25rem !important;
    font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
}

.sd-dashboard {
    color: var(--sd-text);
    padding-bottom: 1.5rem;
    max-width: 1400px;
}

.sd-welcome {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}
.sd-welcome h1 {
    font-size: 1.65rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}
.sd-welcome p { margin: 0; color: var(--sd-muted); font-size: 0.95rem; }
.sd-term-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--sd-card);
    border: 1px solid var(--sd-border);
    border-radius: 999px;
    padding: 0.5rem 0.95rem;
    font-size: 0.85rem;
    box-shadow: var(--sd-shadow);
    color: var(--sd-muted);
}
.sd-term-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; }
.sd-term-chip strong { color: var(--sd-text); }
.sd-term-sep { opacity: 0.5; }

.sd-top-grid {
    display: grid;
    grid-template-columns: minmax(280px, 360px) 1fr;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.sd-profile-card {
    background: var(--sd-card);
    border: 1px solid var(--sd-border);
    border-radius: var(--sd-radius);
    box-shadow: var(--sd-shadow);
    padding: 1.1rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    align-items: flex-start;
}
.sd-avatar {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    object-fit: cover;
    border: 1px solid var(--sd-border);
}
.sd-profile-body h2 { font-size: 1.1rem; font-weight: 700; margin: 0 0 0.45rem; }
.sd-profile-meta {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: var(--sd-muted);
}
.sd-profile-meta i { width: 16px; color: var(--sd-primary); margin-right: 0.25rem; }

.sd-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.85rem;
}
.sd-stat {
    background: var(--sd-card);
    border: 1px solid var(--sd-border);
    border-radius: var(--sd-radius);
    box-shadow: var(--sd-shadow);
    padding: 1rem 1.05rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}
.sd-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--sd-primary-soft);
    color: var(--sd-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}
.sd-stat-green { background: var(--sd-green-soft); color: var(--sd-green); }
.sd-stat-amber { background: var(--sd-amber-soft); color: var(--sd-amber); }
.sd-stat-value { font-size: 1.45rem; font-weight: 700; line-height: 1.1; }
.sd-stat-label { font-size: 0.88rem; font-weight: 600; margin-top: 0.1rem; }
.sd-stat-meta { font-size: 0.74rem; color: var(--sd-muted); margin-top: 0.1rem; }

.sd-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(300px, 360px);
    gap: 1.15rem;
    align-items: start;
}
.sd-panel {
    background: var(--sd-card);
    border: 1px solid var(--sd-border);
    border-radius: var(--sd-radius);
    box-shadow: var(--sd-shadow);
    padding: 1.1rem 1.15rem;
    margin-bottom: 1.1rem;
}
.sd-panel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.9rem;
}
.sd-panel-head h2 { font-size: 1.05rem; font-weight: 700; margin: 0; letter-spacing: -0.01em; }
.sd-link { color: var(--sd-primary); font-size: 0.84rem; font-weight: 600; text-decoration: none; }
.sd-link:hover { color: var(--sd-primary-dark); text-decoration: underline; }
.sd-muted { color: var(--sd-muted); margin: 0; }
.sd-meta { font-size: 0.78rem; color: var(--sd-muted); margin: 0.35rem 0 0; }
.sd-empty { color: var(--sd-muted); font-size: 0.9rem; padding: 0.75rem 0; }
.small { font-size: 0.8rem; }

.sd-class-list { display: flex; flex-direction: column; gap: 0.65rem; }
.sd-class-row {
    display: grid;
    grid-template-columns: 44px 1fr auto auto;
    gap: 0.85rem;
    align-items: center;
    padding: 0.85rem;
    border: 1px solid var(--sd-border);
    border-radius: 12px;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.sd-class-row:hover { border-color: #c7d2fe; background: #fafbff; }
.sd-class-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--sd-primary-soft);
    color: var(--sd-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.sd-class-icon.sm { width: 36px; height: 36px; border-radius: 10px; font-size: 0.9rem; }
.sd-class-info h3 { font-size: 0.95rem; font-weight: 650; margin: 0 0 0.15rem; }
.sd-class-info p { font-size: 0.82rem; color: var(--sd-muted); margin: 0 0 0.35rem; }
.sd-class-meta { display: flex; flex-wrap: wrap; gap: 0.65rem; font-size: 0.75rem; color: var(--sd-muted); }
.sd-class-meta i { margin-right: 0.2rem; color: var(--sd-primary); }

.sd-course-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
}
.sd-course-card {
    border: 1px solid var(--sd-border);
    border-radius: 12px;
    padding: 0.95rem;
    background: #fff;
}
.sd-course-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.55rem; }
.sd-course-card h3 { font-size: 0.98rem; font-weight: 700; margin: 0 0 0.2rem; }
.sd-progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    color: var(--sd-muted);
    margin-top: 0.65rem;
}
.sd-progress {
    height: 6px;
    background: #f3f4f6;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 0.35rem;
}
.sd-progress-bar {
    height: 100%;
    background: var(--sd-primary);
    border-radius: 999px;
}
.sd-progress-bar.warn { background: var(--sd-amber); }

.sd-assign-list { display: flex; flex-direction: column; }
.sd-assign-row {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 0.75rem;
    align-items: center;
    padding: 0.8rem 0;
    border-bottom: 1px solid var(--sd-border);
}
.sd-assign-row:last-child { border-bottom: none; }
.sd-assign-main h3 { font-size: 0.92rem; font-weight: 650; margin: 0 0 0.15rem; }

.sd-subject-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.65rem;
}
.sd-subject-card {
    display: flex;
    gap: 0.7rem;
    align-items: center;
    border: 1px solid var(--sd-border);
    border-radius: 12px;
    padding: 0.75rem 0.85rem;
}
.sd-subject-card h3 { font-size: 0.9rem; margin: 0 0 0.1rem; font-weight: 650; }

.sd-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.55rem;
    border-radius: 999px;
    white-space: nowrap;
    text-transform: capitalize;
}
.sd-badge-ok { background: var(--sd-green-soft); color: var(--sd-green); }
.sd-badge-warn { background: var(--sd-amber-soft); color: var(--sd-amber); }
.sd-badge-soft { background: #f3f4f6; color: #4b5563; }

.sd-btn-primary, .sd-btn-outline, .sd-btn-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 600;
    border-radius: 10px;
    padding: 0.45rem 0.85rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.sd-btn-primary { background: var(--sd-primary); color: #fff; }
.sd-btn-primary:hover { background: var(--sd-primary-dark); color: #fff; }
.sd-btn-outline {
    border: 1px solid var(--sd-border);
    color: var(--sd-text);
    background: #fff;
}
.sd-btn-outline:hover { border-color: var(--sd-primary); color: var(--sd-primary); background: var(--sd-primary-soft); }
.sd-btn-ghost { background: var(--sd-primary-soft); color: var(--sd-primary); }
.sd-btn-ghost:hover { background: #e0e7ff; color: var(--sd-primary-dark); }
.block { width: 100%; margin-top: 0.65rem; }

.sd-enroll-status { margin-bottom: 0.85rem; }
.sd-enroll-status span { display: block; font-size: 0.75rem; margin-bottom: 0.15rem; }
.sd-enroll-status strong { font-size: 1rem; }
.sd-attention {
    display: flex;
    gap: 0.65rem;
    margin: 0.9rem 0;
    padding: 0.75rem 0.85rem;
    border-radius: 12px;
    background: var(--sd-amber-soft);
    border: 1px solid #fde68a;
    color: #92400e;
}
.sd-attention.ok {
    background: var(--sd-green-soft);
    border-color: #a7f3d0;
    color: #065f46;
}
.sd-attention i { margin-top: 0.15rem; }
.sd-attention p { margin: 0; font-size: 0.85rem; }

.sd-event-row {
    display: flex;
    gap: 0.75rem;
    padding: 0.65rem 0;
    border-bottom: 1px solid var(--sd-border);
}
.sd-event-row:last-child { border-bottom: none; }
.sd-event-date {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--sd-primary-soft);
    color: var(--sd-primary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sd-event-date .d { font-size: 1rem; font-weight: 700; line-height: 1; }
.sd-event-date .m { font-size: 0.65rem; text-transform: uppercase; font-weight: 600; }
.sd-event-row h3 { font-size: 0.9rem; margin: 0 0 0.1rem; font-weight: 650; }

.sd-announce-row {
    padding: 0.7rem 0;
    border-bottom: 1px solid var(--sd-border);
}
.sd-announce-row:last-child { border-bottom: none; }
.sd-announce-row.unread h3 { color: var(--sd-primary-dark); }
.sd-announce-row h3 { font-size: 0.9rem; margin: 0 0 0.25rem; font-weight: 650; }
.sd-announce-row p { font-size: 0.82rem; color: var(--sd-muted); margin: 0 0 0.25rem; }

.sd-activity-item {
    display: flex;
    gap: 0.7rem;
    padding: 0.6rem 0;
    border-bottom: 1px solid var(--sd-border);
}
.sd-activity-item:last-child { border-bottom: none; }
.sd-activity-item p { margin: 0; font-size: 0.86rem; }
.sd-activity-icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--sd-primary-soft);
    color: var(--sd-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
}

.sd-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.5rem;
    padding: 1rem 0.25rem;
    border-top: 1px solid var(--sd-border);
    color: var(--sd-muted);
    font-size: 0.8rem;
}
.sd-footer strong { display: block; color: var(--sd-text); font-size: 0.85rem; }
.sd-footer-links { display: flex; gap: 1rem; }
.sd-footer-links a { color: var(--sd-muted); text-decoration: none; }
.sd-footer-links a:hover { color: var(--sd-primary); }

.sd-empty-state {
    text-align: center;
    background: var(--sd-card);
    border: 1px solid var(--sd-border);
    border-radius: var(--sd-radius);
    padding: 3rem 1.5rem;
    max-width: 480px;
    margin: 2rem auto;
}
.sd-empty-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    border-radius: 16px;
    background: var(--sd-primary-soft);
    color: var(--sd-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.sd-modal .modal-header { border-bottom-color: var(--sd-border); }
.sd-modal .card-header { background: #f9fafb; font-weight: 600; }

@media (max-width: 1100px) {
    .sd-top-grid { grid-template-columns: 1fr; }
    .sd-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sd-layout { grid-template-columns: 1fr; }
}
@media (max-width: 720px) {
    .page-wrapper .content.container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .sd-welcome h1 { font-size: 1.35rem; }
    .sd-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sd-course-grid, .sd-subject-grid { grid-template-columns: 1fr; }
    .sd-class-row {
        grid-template-columns: 44px 1fr;
    }
    .sd-assign-row { grid-template-columns: 1fr; }
}
</style>

<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\partials\student_dashboard.blade.php ENDPATH**/ ?>