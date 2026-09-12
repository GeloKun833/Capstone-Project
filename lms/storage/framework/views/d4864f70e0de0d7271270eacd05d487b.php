<?php
    $adminUser = auth()->user();
    $fullName = $adminUser->name ?? Session::get('name') ?? 'Administrator';
    $firstName = explode(' ', trim($fullName))[0] ?: $fullName;
    $adminData = $admin ?? [];
    $performance = $adminData['performanceData'] ?? ['labels' => [], 'averages' => [], 'mode' => 'empty', 'title' => 'Academic Performance Overview'];
    $distribution = $adminData['studentsChartData'] ?? ['labels' => [], 'totals' => [], 'boysData' => [], 'girlsData' => []];
    $activities = $adminData['recentActivities'] ?? [];
    $events = $adminData['recentEvents'] ?? collect();
    $breakdown = $adminData['attendanceBreakdown'] ?? ['present' => 0, 'absent' => 0, 'late' => 0];
    $attendanceTotal = (int) ($adminData['attendanceStats']->total_records ?? 0);
    $hasPerformance = ($performance['mode'] ?? 'empty') !== 'empty' && !empty($performance['labels']);
    $hasDistribution = !empty($distribution['labels'] ?? []);
?>

<div class="admin-dash">
    
    <div class="admin-dash__welcome">
        <div>
            <h1 class="admin-dash__title">Welcome back, <?php echo e($firstName); ?></h1>
            <p class="admin-dash__subtitle">Here’s an overview of your school today.</p>
        </div>
        <div class="admin-dash__welcome-meta">
            <div class="admin-dash__date">
                <i class="far fa-calendar-alt"></i>
                <span><?php echo e(now()->format('l, M j, Y')); ?></span>
            </div>
        </div>
    </div>

    
    <div class="row g-3 admin-dash__stats">
        <div class="col-xl-3 col-sm-6">
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--orange">
                    <i class="fas fa-users"></i>
                </div>
                <div class="admin-stat-card__body">
                    <span class="admin-stat-card__label">Total Students</span>
                    <strong class="admin-stat-card__value"><?php echo e(number_format($adminData['totalStudents'] ?? 0)); ?></strong>
                    <span class="admin-stat-card__hint">Currently enrolled</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--green">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="admin-stat-card__body">
                    <span class="admin-stat-card__label">Total Teachers</span>
                    <strong class="admin-stat-card__value"><?php echo e(number_format($adminData['totalTeachers'] ?? 0)); ?></strong>
                    <span class="admin-stat-card__hint">Active teachers</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--slate">
                    <i class="fas fa-book"></i>
                </div>
                <div class="admin-stat-card__body">
                    <span class="admin-stat-card__label">Total Subjects</span>
                    <strong class="admin-stat-card__value"><?php echo e(number_format($adminData['totalSubjects'] ?? 0)); ?></strong>
                    <span class="admin-stat-card__hint">Current school year</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--blue">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="admin-stat-card__body">
                    <span class="admin-stat-card__label">Attendance Rate</span>
                    <strong class="admin-stat-card__value">
                        <?php if($attendanceTotal > 0): ?>
                            <?php echo e($adminData['attendancePercentage'] ?? 0); ?>%
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </strong>
                    <span class="admin-stat-card__hint">
                        <?php if($attendanceTotal > 0): ?>
                            Based on <?php echo e(number_format($attendanceTotal)); ?> records
                        <?php else: ?>
                            No attendance records yet
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mt-1">
        <div class="col-xl-7">
            <div class="admin-panel">
                <div class="admin-panel__header">
                    <div>
                        <h2 class="admin-panel__title"><?php echo e($performance['title'] ?? 'Academic Performance Overview'); ?></h2>
                        <p class="admin-panel__desc">Real grade averages from submitted records</p>
                    </div>
                </div>
                <div class="admin-panel__body">
                    <?php if($hasPerformance): ?>
                        <div class="admin-chart-skeleton" id="academic-performance-skeleton" aria-hidden="true"></div>
                        <div id="academic-performance-chart"></div>
                    <?php else: ?>
                        <div class="admin-empty">
                            <i class="fas fa-chart-bar"></i>
                            <h6>No academic data available yet</h6>
                            <p>Average grades will appear here once grade records are submitted.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="admin-panel">
                <div class="admin-panel__header">
                    <div>
                        <h2 class="admin-panel__title">Student Distribution</h2>
                        <p class="admin-panel__desc">Enrollment by grade level</p>
                    </div>
                </div>
                <div class="admin-panel__body">
                    <?php if($hasDistribution): ?>
                        <div class="admin-chart-skeleton" id="student-distribution-skeleton" aria-hidden="true"></div>
                        <div id="student-distribution-chart"></div>
                    <?php else: ?>
                        <div class="admin-empty">
                            <i class="fas fa-user-graduate"></i>
                            <h6>No student distribution yet</h6>
                            <p>Grade-level counts will appear when student records include year levels.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mt-1">
        <div class="col-xl-6">
            <div class="admin-panel admin-panel--fill">
                <div class="admin-panel__header">
                    <div>
                        <h2 class="admin-panel__title">Recent Activities</h2>
                        <p class="admin-panel__desc">Latest school system activity</p>
                    </div>
                    <?php if(Route::has('activity.log')): ?>
                        <a href="<?php echo e(route('activity.log')); ?>" class="admin-panel__link">View all</a>
                    <?php endif; ?>
                </div>
                <div class="admin-panel__body">
                    <?php if(count($activities) > 0): ?>
                        <ul class="admin-activity-list">
                            <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $at = !empty($activity['at']) ? \Carbon\Carbon::parse($activity['at']) : null;
                                    $tone = $activity['tone'] ?? 'neutral';
                                ?>
                                <li class="admin-activity-list__item">
                                    <span class="admin-activity-list__dot admin-activity-list__dot--<?php echo e($tone); ?>">
                                        <i class="fas <?php echo e($activity['icon'] ?? 'fa-circle'); ?>"></i>
                                    </span>
                                    <div class="admin-activity-list__content">
                                        <strong><?php echo e($activity['title'] ?? 'Activity'); ?></strong>
                                        <span><?php echo e($activity['meta'] ?? ''); ?><?php if($at): ?> · <?php echo e($at->diffForHumans()); ?><?php endif; ?></span>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        <div class="admin-empty admin-empty--compact">
                            <i class="fas fa-stream"></i>
                            <h6>No recent activities yet</h6>
                            <p>Enrollments, announcements, and system logs will show up here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="admin-panel admin-panel--fill">
                <div class="admin-panel__header">
                    <div>
                        <h2 class="admin-panel__title">Upcoming Events</h2>
                        <p class="admin-panel__desc">From the school calendar</p>
                    </div>
                    <?php if(Route::has('calendar.index')): ?>
                        <a href="<?php echo e(route('calendar.index')); ?>" class="admin-panel__link">View Calendar →</a>
                    <?php elseif(Route::has('calendar.events.list')): ?>
                        <a href="<?php echo e(route('calendar.events.list')); ?>" class="admin-panel__link">View Calendar →</a>
                    <?php endif; ?>
                </div>
                <div class="admin-panel__body">
                    <?php if($events && $events->count() > 0): ?>
                        <ul class="admin-event-list">
                            <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="admin-event-list__item">
                                    <div class="admin-event-list__date">
                                        <span class="admin-event-list__month"><?php echo e(optional($event->start_time)->format('M')); ?></span>
                                        <span class="admin-event-list__day"><?php echo e(optional($event->start_time)->format('d')); ?></span>
                                    </div>
                                    <div class="admin-event-list__content">
                                        <strong><?php echo e($event->title ?? 'Event'); ?></strong>
                                        <span>
                                            <?php echo e(optional($event->start_time)->format('g:i A')); ?>

                                            <?php if(!empty($event->subject?->subject_name)): ?>
                                                · <?php echo e($event->subject->subject_name); ?>

                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        <div class="admin-empty admin-empty--compact">
                            <i class="fas fa-calendar-alt"></i>
                            <h6>No upcoming events</h6>
                            <p>Scheduled calendar events will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mt-1">
        <div class="col-xl-5">
            <div class="admin-panel">
                <div class="admin-panel__header">
                    <div>
                        <h2 class="admin-panel__title">Attendance Overview</h2>
                        <p class="admin-panel__desc">Share of recorded attendance statuses</p>
                    </div>
                </div>
                <div class="admin-panel__body">
                    <?php if($attendanceTotal > 0): ?>
                        <div class="admin-attendance">
                            <div class="admin-attendance__row">
                                <div class="admin-attendance__label">
                                    <span class="admin-attendance__swatch admin-attendance__swatch--present"></span>
                                    Present
                                </div>
                                <div class="admin-attendance__bar">
                                    <span style="width: <?php echo e(min(100, $breakdown['present'])); ?>%"></span>
                                </div>
                                <strong><?php echo e($breakdown['present']); ?>%</strong>
                            </div>
                            <div class="admin-attendance__row">
                                <div class="admin-attendance__label">
                                    <span class="admin-attendance__swatch admin-attendance__swatch--absent"></span>
                                    Absent
                                </div>
                                <div class="admin-attendance__bar">
                                    <span class="is-absent" style="width: <?php echo e(min(100, $breakdown['absent'])); ?>%"></span>
                                </div>
                                <strong><?php echo e($breakdown['absent']); ?>%</strong>
                            </div>
                            <div class="admin-attendance__row">
                                <div class="admin-attendance__label">
                                    <span class="admin-attendance__swatch admin-attendance__swatch--late"></span>
                                    Late
                                </div>
                                <div class="admin-attendance__bar">
                                    <span class="is-late" style="width: <?php echo e(min(100, $breakdown['late'])); ?>%"></span>
                                </div>
                                <strong><?php echo e($breakdown['late']); ?>%</strong>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty admin-empty--compact">
                            <i class="fas fa-user-check"></i>
                            <h6>No attendance data yet</h6>
                            <p>Present, absent, and late rates will appear after attendance is submitted.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="admin-panel">
                <div class="admin-panel__header">
                    <div>
                        <h2 class="admin-panel__title">Top Students</h2>
                        <p class="admin-panel__desc">Highest GPA records on file</p>
                    </div>
                </div>
                <div class="admin-panel__body p-0">
                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th class="text-center">GPA</th>
                                    <th class="text-end">Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = ($adminData['topStudents'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gpaRecord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($gpaRecord->student->admission_id ?? 'STU'.$gpaRecord->student_id); ?></td>
                                        <td><?php echo e(trim(($gpaRecord->student->first_name ?? '').' '.($gpaRecord->student->last_name ?? '')) ?: 'Student'); ?></td>
                                        <td class="text-center"><span class="admin-badge"><?php echo e($gpaRecord->gpa ?? 'N/A'); ?></span></td>
                                        <td class="text-end"><?php echo e($gpaRecord->academicYear->name ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No GPA records available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dash {
    --ad-orange: #ea580c;
    --ad-orange-soft: #fff7ed;
    --ad-green: #16a34a;
    --ad-green-soft: #f0fdf4;
    --ad-blue: #0284c7;
    --ad-blue-soft: #f0f9ff;
    --ad-slate: #475569;
    --ad-slate-soft: #f8fafc;
    --ad-border: #e2e8f0;
    --ad-text: #0f172a;
    --ad-muted: #64748b;
    --ad-radius: 14px;
    color: var(--ad-text);
}

.admin-dash__welcome {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.admin-dash__title {
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0 0 .25rem;
    letter-spacing: -0.02em;
}

.admin-dash__subtitle {
    margin: 0;
    color: var(--ad-muted);
    font-size: .95rem;
}

.admin-dash__welcome-meta {
    display: flex;
    align-items: center;
    gap: .5rem;
}

.admin-dash__date {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: #fff;
    border: 1px solid var(--ad-border);
    border-radius: 999px;
    padding: .45rem .9rem;
    color: var(--ad-muted);
    font-size: .85rem;
}

.admin-stat-card {
    background: #fff;
    border: 1px solid var(--ad-border);
    border-radius: var(--ad-radius);
    padding: 1.15rem 1.2rem;
    height: 100%;
    display: flex;
    gap: .9rem;
    align-items: flex-start;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
}

.admin-stat-card:hover {
    border-color: #fdba74;
    box-shadow: 0 8px 20px rgba(234, 88, 12, .08);
    transform: translateY(-1px);
}

.admin-stat-card__icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.admin-stat-card__icon--orange { background: var(--ad-orange-soft); color: var(--ad-orange); }
.admin-stat-card__icon--green { background: var(--ad-green-soft); color: var(--ad-green); }
.admin-stat-card__icon--blue { background: var(--ad-blue-soft); color: var(--ad-blue); }
.admin-stat-card__icon--slate { background: var(--ad-slate-soft); color: var(--ad-slate); }

.admin-stat-card__label {
    display: block;
    font-size: .8rem;
    color: var(--ad-muted);
    font-weight: 500;
    margin-bottom: .15rem;
}

.admin-stat-card__value {
    display: block;
    font-size: 1.7rem;
    font-weight: 700;
    line-height: 1.15;
    letter-spacing: -0.03em;
}

.admin-stat-card__hint {
    display: block;
    margin-top: .2rem;
    font-size: .75rem;
    color: #94a3b8;
}

.admin-panel {
    background: #fff;
    border: 1px solid var(--ad-border);
    border-radius: var(--ad-radius);
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    height: 100%;
}

.admin-panel--fill { display: flex; flex-direction: column; }
.admin-panel--fill .admin-panel__body { flex: 1; }

.admin-panel__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.1rem 1.25rem .25rem;
}

.admin-panel__title {
    font-size: 1.05rem;
    font-weight: 650;
    margin: 0;
}

.admin-panel__desc {
    margin: .15rem 0 0;
    color: var(--ad-muted);
    font-size: .8rem;
}

.admin-panel__link {
    font-size: .82rem;
    font-weight: 600;
    color: var(--ad-orange);
    white-space: nowrap;
}

.admin-panel__link:hover { color: #c2410c; }

.admin-panel__body { padding: 1rem 1.25rem 1.25rem; }

.admin-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    color: var(--ad-muted);
}

.admin-empty--compact { padding: 1.75rem 1rem; }

.admin-empty i {
    font-size: 1.6rem;
    color: #cbd5e1;
    margin-bottom: .75rem;
}

.admin-empty h6 {
    margin: 0 0 .35rem;
    color: var(--ad-text);
    font-weight: 600;
}

.admin-empty p {
    margin: 0;
    font-size: .85rem;
}

.admin-chart-skeleton {
    height: 300px;
    border-radius: 12px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 37%, #f1f5f9 63%);
    background-size: 400% 100%;
    animation: adminSkeleton 1.2s ease infinite;
}

@keyframes adminSkeleton {
    0% { background-position: 100% 0; }
    100% { background-position: 0 0; }
}

.admin-activity-list,
.admin-event-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.admin-activity-list__item,
.admin-event-list__item {
    display: flex;
    gap: .85rem;
    padding: .85rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.admin-activity-list__item:last-child,
.admin-event-list__item:last-child { border-bottom: 0; }

.admin-activity-list__dot {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--ad-slate-soft);
    color: var(--ad-slate);
    font-size: .85rem;
}

.admin-activity-list__dot--success { background: var(--ad-green-soft); color: var(--ad-green); }
.admin-activity-list__dot--info { background: var(--ad-blue-soft); color: var(--ad-blue); }
.admin-activity-list__dot--neutral { background: var(--ad-orange-soft); color: var(--ad-orange); }

.admin-activity-list__content,
.admin-event-list__content {
    display: flex;
    flex-direction: column;
    gap: .15rem;
    min-width: 0;
}

.admin-activity-list__content strong,
.admin-event-list__content strong {
    font-size: .92rem;
    font-weight: 600;
}

.admin-activity-list__content span,
.admin-event-list__content span {
    font-size: .78rem;
    color: var(--ad-muted);
}

.admin-event-list__date {
    width: 52px;
    border-radius: 12px;
    background: var(--ad-orange-soft);
    color: var(--ad-orange);
    text-align: center;
    padding: .4rem .25rem;
    flex-shrink: 0;
}

.admin-event-list__month {
    display: block;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.admin-event-list__day {
    display: block;
    font-size: 1.15rem;
    font-weight: 700;
    line-height: 1.1;
}

.admin-attendance__row {
    display: grid;
    grid-template-columns: 90px 1fr 52px;
    gap: .75rem;
    align-items: center;
    margin-bottom: .9rem;
}

.admin-attendance__row:last-child { margin-bottom: 0; }

.admin-attendance__label {
    display: flex;
    align-items: center;
    gap: .45rem;
    font-size: .85rem;
    color: var(--ad-muted);
}

.admin-attendance__swatch {
    width: 10px;
    height: 10px;
    border-radius: 999px;
}

.admin-attendance__swatch--present { background: var(--ad-green); }
.admin-attendance__swatch--absent { background: #ef4444; }
.admin-attendance__swatch--late { background: var(--ad-orange); }

.admin-attendance__bar {
    height: 8px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}

.admin-attendance__bar span {
    display: block;
    height: 100%;
    background: var(--ad-green);
    border-radius: 999px;
}

.admin-attendance__bar span.is-absent { background: #ef4444; }
.admin-attendance__bar span.is-late { background: var(--ad-orange); }

.admin-table {
    font-size: .9rem;
}

.admin-table thead th {
    background: #f8fafc;
    border-bottom: 1px solid var(--ad-border);
    color: var(--ad-muted);
    font-weight: 600;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: .85rem 1.25rem;
}

.admin-table td {
    padding: .85rem 1.25rem;
    border-color: #f1f5f9;
    vertical-align: middle;
}

.admin-badge {
    display: inline-flex;
    min-width: 48px;
    justify-content: center;
    padding: .2rem .55rem;
    border-radius: 999px;
    background: var(--ad-orange-soft);
    color: var(--ad-orange);
    font-weight: 700;
    font-size: .82rem;
}

@media (max-width: 575.98px) {
    .admin-dash__title { font-size: 1.35rem; }
    .admin-stat-card__value { font-size: 1.45rem; }
    .admin-attendance__row {
        grid-template-columns: 1fr auto;
        grid-template-areas:
            "label value"
            "bar bar";
    }
    .admin-attendance__label { grid-area: label; }
    .admin-attendance__bar { grid-area: bar; }
    .admin-attendance__row strong { grid-area: value; }
}
</style>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\partials\admin_dashboard.blade.php ENDPATH**/ ?>