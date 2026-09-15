<?php
    $t = $teacher;
    $displayName = $t['teacherDisplayName'] ?? ($t['teacher']->full_name ?? 'Teacher');
    $greeting = $t['greeting'] ?? 'Hello';
    $firstName = explode(' ', $displayName)[0] ?? 'Teacher';
    $progress = $t['teachingProgress'] ?? [];
?>

<div class="td-dashboard">
    
    <div class="td-welcome">
        <div class="td-welcome-text">
            <h1><?php echo e($greeting); ?>, Teacher <?php echo e($firstName); ?>!</h1>
            <p>Here&rsquo;s what&rsquo;s happening with your classes today.</p>
        </div>
        <div class="td-welcome-date">
            <i class="far fa-calendar-alt"></i>
            <span><?php echo e(now()->format('l, F j, Y')); ?></span>
        </div>
    </div>

    
    <div class="td-stats">
        <div class="td-stat-card">
            <div class="td-stat-icon"><i class="fas fa-chalkboard"></i></div>
            <div>
                <div class="td-stat-value"><?php echo e($t['classCardCount'] ?? $t['totalClasses']); ?></div>
                <div class="td-stat-label">My Classes</div>
                <div class="td-stat-meta">Assigned teaching loads</div>
            </div>
        </div>
        <div class="td-stat-card">
            <div class="td-stat-icon"><i class="fas fa-user-graduate"></i></div>
            <div>
                <div class="td-stat-value"><?php echo e($t['totalStudents']); ?></div>
                <div class="td-stat-label">Total Students</div>
                <div class="td-stat-meta">Across your subjects</div>
            </div>
        </div>
        <div class="td-stat-card">
            <div class="td-stat-icon"><i class="fas fa-book-open"></i></div>
            <div>
                <div class="td-stat-value"><?php echo e($t['activeLessonsCount'] ?? $t['totalLessons']); ?></div>
                <div class="td-stat-label">Active Lessons</div>
                <div class="td-stat-meta">Published lesson plans</div>
            </div>
        </div>
        <div class="td-stat-card">
            <div class="td-stat-icon td-stat-icon-warn"><i class="fas fa-clipboard-list"></i></div>
            <div>
                <div class="td-stat-value"><?php echo e($t['pendingTasksCount'] ?? 0); ?></div>
                <div class="td-stat-label">Pending Tasks</div>
                <div class="td-stat-meta">Needs your attention</div>
            </div>
        </div>
    </div>

    <div class="td-layout">
        
        <div class="td-main">
            
            <section class="td-panel">
                <div class="td-panel-head">
                    <h2>My Classes</h2>
                    <a href="<?php echo e(route('teacher.classes')); ?>" class="td-link">View all</a>
                </div>
                <div class="td-class-grid">
                    <?php $__empty_1 = true; $__currentLoopData = ($t['myClasses'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="td-class-card">
                            <div class="td-class-top">
                                <span class="td-pill"><?php echo e($class->grade_level); ?></span>
                                <span class="td-status"><?php echo e($class->status); ?></span>
                            </div>
                            <h3><?php echo e($class->subject_name); ?></h3>
                            <p class="td-muted">Section <?php echo e($class->section_name); ?> · <?php echo e($class->student_count); ?> Students</p>
                            <p class="td-schedule-line"><i class="far fa-clock"></i> <?php echo e($class->schedule_label); ?></p>
                            <?php if(!empty($class->room)): ?>
                                <p class="td-muted small"><i class="fas fa-door-open"></i> <?php echo e($class->room); ?></p>
                            <?php endif; ?>
                            <a href="<?php echo e(route('teacher.classes')); ?>" class="td-btn-outline">View Class</a>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="td-empty">No classes assigned yet.</div>
                    <?php endif; ?>
                </div>
            </section>

            
            <section class="td-panel">
                <div class="td-panel-head">
                    <h2>Upcoming Lessons</h2>
                    <a href="<?php echo e(route('lessons.index')); ?>" class="td-link">Manage lessons</a>
                </div>
                <div class="td-list">
                    <?php
                        $lessonRows = ($t['upcomingLessonPlans'] ?? collect());
                        if ($lessonRows->isEmpty()) {
                            $lessonRows = $t['upcomingLessons'] ?? collect();
                        }
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $lessonRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isPlan = $lesson instanceof \App\Models\Lesson;
                            $title = $lesson->title ?? 'Lesson';
                            $subject = $lesson->subject->subject_name ?? 'Subject';
                            $classLabel = $isPlan
                                ? trim(($lesson->section->grade_level ?? '').' '.($lesson->section->name ?? ''))
                                : '';
                            $dateLabel = $isPlan
                                ? (optional($lesson->lesson_date)->format('F j, Y') ?? 'Date TBD')
                                : (optional($lesson->start_time)->format('F j, Y') ?? '—');
                            $timeLabel = $isPlan
                                ? '—'
                                : (optional($lesson->start_time)->format('g:i A') ?? '—');
                            $status = $isPlan ? ucfirst($lesson->status ?? 'planned') : 'Confirmed';
                        ?>
                        <div class="td-list-item">
                            <div class="td-list-main">
                                <h4><?php echo e($title); ?></h4>
                                <p class="td-muted"><?php echo e($subject); ?><?php if($classLabel): ?> · <?php echo e($classLabel); ?><?php endif; ?></p>
                                <p class="td-meta"><i class="far fa-calendar"></i> <?php echo e($dateLabel); ?>

                                    <?php if($timeLabel !== '—'): ?> · <i class="far fa-clock"></i> <?php echo e($timeLabel); ?><?php endif; ?>
                                </p>
                            </div>
                            <span class="td-badge td-badge-<?php echo e(strtolower($status) === 'draft' ? 'muted' : 'ok'); ?>"><?php echo e($status); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="td-empty">No upcoming lessons scheduled.</div>
                    <?php endif; ?>
                </div>
            </section>

            
            <section class="td-panel">
                <div class="td-panel-head">
                    <h2>Assignments &amp; Grading</h2>
                    <a href="<?php echo e(route('assignments.index')); ?>" class="td-link">View all</a>
                </div>
                <div class="td-list">
                    <?php $__empty_1 = true; $__currentLoopData = ($t['assignmentWorkload'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="td-list-item td-asg-item">
                            <div class="td-list-main">
                                <h4><?php echo e($asg->title); ?></h4>
                                <p class="td-muted"><?php echo e($asg->subject_name); ?> · <?php echo e($asg->class_label); ?></p>
                                <p class="td-meta">
                                    Due <?php echo e($asg->due_date ? $asg->due_date->format('M j, Y') : '—'); ?>

                                    · <?php echo e($asg->submitted); ?> submissions · <?php echo e($asg->graded); ?> graded ·
                                    <strong><?php echo e($asg->pending); ?> pending</strong>
                                </p>
                                <div class="td-progress">
                                    <div class="td-progress-bar" style="width: <?php echo e($asg->progress_pct); ?>%"></div>
                                </div>
                            </div>
                            <a href="<?php echo e(route('assignments.show', $asg->id)); ?>" class="td-btn-ghost">Open</a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="td-empty">No assignments yet.
                            <a href="<?php echo e(route('assignments.create')); ?>" class="td-link">Create one</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            
            <section class="td-panel">
                <div class="td-panel-head">
                    <h2>Teaching Progress</h2>
                </div>
                <div class="td-progress-grid">
                    <div class="td-progress-card">
                        <div class="td-progress-label">Lessons completed</div>
                        <div class="td-progress-value"><?php echo e($progress['lessons_completed'] ?? 0); ?></div>
                        <div class="td-progress"><div class="td-progress-bar" style="width: <?php echo e($progress['lesson_progress_pct'] ?? 0); ?>%"></div></div>
                        <div class="td-muted small"><?php echo e($progress['lessons_remaining'] ?? 0); ?> remaining</div>
                    </div>
                    <div class="td-progress-card">
                        <div class="td-progress-label">Assignments graded</div>
                        <div class="td-progress-value"><?php echo e($progress['assignments_graded'] ?? 0); ?></div>
                        <div class="td-progress"><div class="td-progress-bar" style="width: <?php echo e($progress['grading_progress_pct'] ?? 0); ?>%"></div></div>
                        <div class="td-muted small"><?php echo e($progress['assignments_pending'] ?? 0); ?> pending</div>
                    </div>
                    <div class="td-progress-card">
                        <div class="td-progress-label">Attendance recorded</div>
                        <div class="td-progress-value"><?php echo e($progress['attendance_recorded'] ?? 0); ?></div>
                        <div class="td-progress"><div class="td-progress-bar" style="width: <?php echo e(min(100, $progress['attendance_pct'] ?? 0)); ?>%"></div></div>
                        <div class="td-muted small"><?php echo e($progress['attendance_pct'] ?? 0); ?>% present rate</div>
                    </div>
                    <div class="td-progress-card">
                        <div class="td-progress-label">Semester progress</div>
                        <div class="td-progress-value"><?php echo e($t['semesterProgress']); ?>%</div>
                        <div class="td-progress"><div class="td-progress-bar" style="width: <?php echo e($t['semesterProgress']); ?>%"></div></div>
                        <div class="td-muted small">Based on this month&rsquo;s sessions</div>
                    </div>
                </div>
            </section>

            
            <section class="td-panel">
                <div class="td-panel-head">
                    <h2>Recent Activity</h2>
                </div>
                <div class="td-activity">
                    <?php $__empty_1 = true; $__currentLoopData = ($t['recentActivity'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="td-activity-item">
                            <div class="td-activity-icon"><i class="fas <?php echo e($act->icon); ?>"></i></div>
                            <div>
                                <p><?php echo e($act->text); ?></p>
                                <span class="td-muted small"><?php echo e($act->time); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="td-empty">No recent activity yet.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        
        <aside class="td-side">
            <section class="td-panel">
                <div class="td-panel-head">
                    <h2>Today&rsquo;s Schedule</h2>
                    <a href="<?php echo e(route('teacher.my-schedule')); ?>" class="td-link">Full week</a>
                </div>
                <div class="td-timeline">
                    <?php $__empty_1 = true; $__currentLoopData = ($t['todaysSchedule'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="td-timeline-item">
                            <div class="td-timeline-time"><?php echo e($slot->start_label); ?></div>
                            <div class="td-timeline-body">
                                <h4><?php echo e($slot->subject_name); ?></h4>
                                <p class="td-muted"><?php echo e($slot->grade_level); ?> — Section <?php echo e($slot->section_name); ?></p>
                                <p class="td-meta"><?php echo e($slot->room); ?> · <?php echo e($slot->student_count); ?> Students</p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="td-empty">No classes scheduled for today.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="td-panel">
                <div class="td-panel-head">
                    <h2>Upcoming Events</h2>
                    <a href="<?php echo e(route('calendar.index')); ?>" class="td-link">Calendar</a>
                </div>
                <div class="td-list td-list-compact">
                    <?php $__empty_1 = true; $__currentLoopData = ($t['upcomingEvents'] ?? collect())->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="td-list-item">
                            <div class="td-list-main">
                                <h4><?php echo e($event->title ?? 'Event'); ?></h4>
                                <p class="td-muted"><?php echo e($event->subject->subject_name ?? 'School Event'); ?></p>
                                <p class="td-meta">
                                    <?php echo e(optional($event->start_time)->format('F j, Y')); ?>

                                    · <?php echo e(optional($event->start_time)->format('g:i A')); ?>

                                </p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="td-empty">No upcoming events.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="td-panel">
                <div class="td-panel-head">
                    <h2>Calendar</h2>
                </div>
                <div class="td-calendar-wrap">
                    <div id="calendar-doctor" class="calendar-container td-calendar"></div>
                </div>
            </section>

            <section class="td-panel td-panel-soft">
                <div class="td-panel-head">
                    <h2>Quick links</h2>
                </div>
                <div class="td-quick-links">
                    <a href="<?php echo e(route('assignments.create')); ?>"><i class="fas fa-plus"></i> Create Assignment</a>
                    <a href="<?php echo e(route('teacher.grading.grade-entry')); ?>"><i class="fas fa-edit"></i> Grade Entry</a>
                    <a href="<?php echo e(route('attendance.index')); ?>"><i class="fas fa-user-check"></i> Attendance</a>
                    <a href="<?php echo e(route('class-posts.create')); ?>"><i class="fas fa-bullhorn"></i> Class Post</a>
                    <a href="<?php echo e(route('chat.index')); ?>"><i class="fas fa-comments"></i> Chat</a>
                </div>
            </section>
        </aside>
    </div>
</div>

<style>
:root {
    --td-orange: #e67e22;
    --td-orange-dark: #d35400;
    --td-orange-soft: #fff4eb;
    --td-bg: #f5f6f8;
    --td-card: #ffffff;
    --td-text: #1f2937;
    --td-muted: #6b7280;
    --td-border: #e8eaed;
    --td-radius: 12px;
    --td-shadow: 0 1px 3px rgba(16,24,40,.06), 0 1px 2px rgba(16,24,40,.04);
}
.page-wrapper .content.container-fluid {
    background: var(--td-bg);
    max-width: none !important;
    width: 100% !important;
    padding-left: 1.75rem !important;
    padding-right: 1.25rem !important;
}
.td-dashboard {
    color: var(--td-text);
    max-width: none;
    width: 100%;
    margin: 0;
    padding-bottom: 1.5rem;
}
.td-welcome {
    display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem;
    margin-bottom: 1.25rem; flex-wrap: wrap;
}
.td-welcome h1 { font-size: 1.65rem; font-weight: 700; margin: 0 0 .25rem; letter-spacing: -0.02em; }
.td-welcome p { margin: 0; color: var(--td-muted); font-size: .95rem; }
.td-welcome-date {
    display: inline-flex; align-items: center; gap: .5rem;
    background: var(--td-card); border: 1px solid var(--td-border); border-radius: 999px;
    padding: .45rem .9rem; font-size: .875rem; color: var(--td-muted); box-shadow: var(--td-shadow);
}
.td-welcome-date i { color: var(--td-orange); }

.td-stats {
    display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem;
}
.td-stat-card {
    background: var(--td-card); border-radius: var(--td-radius); box-shadow: var(--td-shadow);
    border: 1px solid var(--td-border); padding: 1.1rem 1.15rem;
    display: flex; gap: .9rem; align-items: flex-start;
}
.td-stat-icon {
    width: 42px; height: 42px; border-radius: 10px; background: var(--td-orange-soft);
    color: var(--td-orange); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.td-stat-icon-warn { background: #fff0e8; }
.td-stat-value { font-size: 1.55rem; font-weight: 700; line-height: 1.1; }
.td-stat-label { font-size: .9rem; font-weight: 600; margin-top: .15rem; }
.td-stat-meta { font-size: .75rem; color: var(--td-muted); margin-top: .15rem; }

.td-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(300px, 380px); gap: 1.25rem; align-items: start; }
.td-panel {
    background: var(--td-card); border: 1px solid var(--td-border); border-radius: var(--td-radius);
    box-shadow: var(--td-shadow); padding: 1.1rem 1.2rem; margin-bottom: 1.15rem;
}
.td-panel-soft { background: #fcfcfd; }
.td-panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .9rem; gap: .75rem; }
.td-panel-head h2 { font-size: 1.05rem; font-weight: 700; margin: 0; }
.td-link { color: var(--td-orange); font-size: .85rem; font-weight: 600; text-decoration: none; }
.td-link:hover { color: var(--td-orange-dark); text-decoration: underline; }
.td-muted { color: var(--td-muted); margin: 0; }
.td-meta { font-size: .8rem; color: var(--td-muted); margin: .35rem 0 0; }
.td-empty { color: var(--td-muted); font-size: .9rem; padding: .75rem 0; }

.td-class-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    width: 100%;
}
.td-class-card {
    border: 1px solid var(--td-border); border-radius: 10px; padding: .9rem 1rem; background: #fff;
    width: 100%; min-width: 0;
}
.td-class-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: .45rem; }
.td-pill {
    background: var(--td-orange-soft); color: var(--td-orange-dark); font-size: .72rem; font-weight: 700;
    padding: .2rem .5rem; border-radius: 999px; text-transform: uppercase; letter-spacing: .03em;
}
.td-status { font-size: .72rem; color: #059669; font-weight: 600; }
.td-class-card h3 { font-size: 1rem; margin: 0 0 .25rem; font-weight: 700; }
.td-schedule-line { font-size: .8rem; color: var(--td-text); margin: .45rem 0; }
.td-schedule-line i { color: var(--td-orange); margin-right: .25rem; }
.td-btn-outline, .td-btn-ghost {
    display: inline-block; margin-top: .4rem; font-size: .8rem; font-weight: 600;
    border-radius: 8px; padding: .35rem .7rem; text-decoration: none;
}
.td-btn-outline {
    border: 1px solid var(--td-orange); color: var(--td-orange); background: transparent;
}
.td-btn-outline:hover { background: var(--td-orange); color: #fff; }
.td-btn-ghost { color: var(--td-orange); background: var(--td-orange-soft); border: none; }
.td-btn-ghost:hover { background: #ffe4d1; color: var(--td-orange-dark); }

.td-list-item {
    display: flex; justify-content: space-between; align-items: flex-start; gap: .75rem;
    padding: .75rem 0; border-bottom: 1px solid var(--td-border);
}
.td-list-item:last-child { border-bottom: none; }
.td-list-item h4 { font-size: .95rem; margin: 0 0 .15rem; font-weight: 650; }
.td-badge {
    font-size: .7rem; font-weight: 700; padding: .25rem .55rem; border-radius: 999px; white-space: nowrap;
}
.td-badge-ok { background: #ecfdf5; color: #047857; }
.td-badge-muted { background: #f3f4f6; color: #6b7280; }
.td-progress {
    height: 6px; background: #f0f1f3; border-radius: 999px; overflow: hidden; margin-top: .5rem; max-width: 280px;
}
.td-progress-bar { height: 100%; background: var(--td-orange); border-radius: 999px; }

.td-progress-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .85rem; }
.td-progress-card { border: 1px solid var(--td-border); border-radius: 10px; padding: .85rem; }
.td-progress-label { font-size: .78rem; color: var(--td-muted); text-transform: uppercase; letter-spacing: .03em; }
.td-progress-value { font-size: 1.35rem; font-weight: 700; margin: .2rem 0 .45rem; }

.td-activity-item { display: flex; gap: .75rem; padding: .65rem 0; border-bottom: 1px solid var(--td-border); }
.td-activity-item:last-child { border-bottom: none; }
.td-activity-item p { margin: 0; font-size: .9rem; }
.td-activity-icon {
    width: 34px; height: 34px; border-radius: 9px; background: var(--td-orange-soft); color: var(--td-orange);
    display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}

.td-grade-block { margin-bottom: .85rem; }
.td-grade-block h4 { font-size: .85rem; color: var(--td-muted); margin: 0 0 .4rem; text-transform: uppercase; letter-spacing: .04em; }
.td-subject-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
.td-chip {
    background: #f8f9fb; border: 1px solid var(--td-border); border-radius: 8px;
    padding: .35rem .6rem; font-size: .8rem; font-weight: 600;
}
.td-chip em { font-style: normal; color: var(--td-muted); font-weight: 500; margin-left: .35rem; }

.td-timeline-item { display: grid; grid-template-columns: 72px 1fr; gap: .65rem; padding: .65rem 0; position: relative; }
.td-timeline-item:not(:last-child)::before {
    content: ''; position: absolute; left: 66px; top: 1.6rem; bottom: -.1rem; width: 2px; background: #f0e4d8;
}
.td-timeline-time { font-size: .78rem; font-weight: 700; color: var(--td-orange); padding-top: .1rem; }
.td-timeline-body h4 { font-size: .92rem; margin: 0 0 .15rem; }
.td-timeline-body { border-left: 2px solid transparent; padding-left: .15rem; }

.td-calendar-wrap { min-height: 260px; }
.td-calendar .simple-calendar,
.td-calendar-wrap .calendar-container { border: none !important; box-shadow: none !important; }
.td-quick-links { display: flex; flex-direction: column; gap: .35rem; }
.td-quick-links a {
    display: flex; align-items: center; gap: .55rem; padding: .55rem .65rem; border-radius: 8px;
    color: var(--td-text); text-decoration: none; font-size: .875rem; font-weight: 550;
    border: 1px solid transparent;
}
.td-quick-links a i { color: var(--td-orange); width: 16px; text-align: center; }
.td-quick-links a:hover { background: var(--td-orange-soft); border-color: #f3d5bb; }

@media (max-width: 1100px) {
    .td-layout { grid-template-columns: 1fr; }
    .td-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .td-class-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .page-wrapper .content.container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .td-stats { display: flex; overflow-x: auto; gap: .75rem; padding-bottom: .25rem; }
    .td-stat-card { min-width: 200px; flex: 0 0 auto; }
    .td-progress-grid { grid-template-columns: 1fr; }
    .td-class-grid { grid-template-columns: 1fr; }
    .td-welcome h1 { font-size: 1.35rem; }
}
</style>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/partials/teacher_dashboard.blade.php ENDPATH**/ ?>