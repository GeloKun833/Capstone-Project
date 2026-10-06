
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Class Schedules</h3>
                    <p class="dir-subtitle">Class times stay on record. Schedules for the current year stay active. A completed year keeps its schedules instead of deleting them.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Class Schedules</li>
                    </ul>
                    <a href="<?php echo e(route('admin.schedules.create')); ?>" class="btn btn-primary dir-btn">
                        <i class="fas fa-plus me-1"></i> Create Schedule
                    </a>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="dir-card dir-filters">
            <form method="GET" action="<?php echo e(route('admin.schedules.index')); ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-control">
                            <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($year->id); ?>" <?php echo e((int) ($viewYear?->id) === (int) $year->id ? 'selected' : ''); ?>>
                                    <?php echo e($year->displayName()); ?><?php echo e($year->isCurrent() ? ' (Current)' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-control">
                            <option value="">All Sections</option>
                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>" <?php echo e((int) request('section_id') === (int) $section->id ? 'selected' : ''); ?>>
                                    <?php echo e($section->grade_level); ?> – <?php echo e($section->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-control">
                            <option value="">All Teachers</option>
                            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teacher->id); ?>" <?php echo e((int) request('teacher_id') === (int) $teacher->id ? 'selected' : ''); ?>>
                                    <?php echo e($teacher->full_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-control">
                            <option value="">All Subjects</option>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($subject->id); ?>" <?php echo e((int) request('subject_id') === (int) $subject->id ? 'selected' : ''); ?>>
                                    <?php echo e($subject->subject_name); ?><?php if($subject->class): ?> (<?php echo e($subject->class); ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Day of Week</label>
                        <select name="day_of_week" class="form-control">
                            <option value="">All Days</option>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($day); ?>" <?php echo e(request('day_of_week') == $day ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($day)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 pb-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">
                                <i class="fas fa-filter me-1"></i> View
                            </button>
                            <a href="<?php echo e(route('admin.schedules.index')); ?>" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php
            $focus = now();
            if (request()->filled('week')) {
                try { $focus = \Carbon\Carbon::parse(request('week')); } catch (\Exception $e) { $focus = now(); }
            }
            $weekStart = $focus->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            $isCurrentWeek = $weekStart->isSameDay(now()->startOfWeek(\Carbon\Carbon::MONDAY));
            $kept = request()->except('week');
            $prevWeekUrl = route('admin.schedules.index', array_merge($kept, ['week' => $weekStart->copy()->subWeek()->toDateString()]));
            $nextWeekUrl = route('admin.schedules.index', array_merge($kept, ['week' => $weekStart->copy()->addWeek()->toDateString()]));
            $currentWeekUrl = route('admin.schedules.index', $kept);
            $gridDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            if ($schedules->contains(fn ($schedule) => $schedule->day_of_week === 'saturday')) {
                $gridDays[] = 'saturday';
            }
            $startHour = 7;
            $endHour = 16;
            foreach ($schedules as $schedule) {
                $startHour = min($startHour, (int) \Carbon\Carbon::parse($schedule->start_time)->format('G'));
                $endHour = max($endHour, (int) \Carbon\Carbon::parse($schedule->end_time)->format('G'));
            }
            $endHour = max($endHour, $startHour);
        ?>

        <?php if($selectedSection): ?>
            <div class="dir-card mb-3">
                <div class="px-3 py-3">
                    <strong><?php echo e($selectedSection->grade_level); ?> – <?php echo e($selectedSection->name); ?></strong>
                    <span class="dir-person-meta d-block">Academic Year <?php echo e($viewYear?->displayName()); ?></span>
                    <div class="mt-2">
                        Schedule status:
                        <?php if(($selectedSection->schedule_readiness['status'] ?? '') === 'ready'): ?>
                            <strong class="text-success">Ready for enrollment</strong>
                        <?php else: ?>
                            <strong>Not ready — <?php echo e($selectedSection->schedule_readiness['label'] ?? 'Not Plotted'); ?></strong>
                        <?php endif; ?>
                    </div>
                    <div class="dir-person-meta mt-1">
                        Subjects scheduled: <?php echo e($selectedSection->subjects_scheduled); ?> / <?php echo e($selectedSection->subjects_required); ?>

                        · Conflicts: <?php echo e($selectedSection->conflict_count); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="dir-card">
            <div class="dir-toolbar d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="dir-toolbar-title mb-0">Weekly timetable</h5>
                    <span class="dir-count mt-1 d-block"><?php echo e($viewYear?->displayName()); ?> · <?php echo e($schedules->count()); ?> class<?php echo e($schedules->count() === 1 ? '' : 'es'); ?> · repeats every week</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo e($prevWeekUrl); ?>" class="btn btn-outline-secondary btn-sm">Previous</a>
                    <a href="<?php echo e($currentWeekUrl); ?>" class="btn btn-outline-primary btn-sm <?php echo e($isCurrentWeek ? 'disabled' : ''); ?>">This week</a>
                    <a href="<?php echo e($nextWeekUrl); ?>" class="btn btn-outline-secondary btn-sm">Next</a>
                </div>
            </div>
            <div class="sched-scroll">
                <div class="sched-grid" style="--sched-days: <?php echo e(count($gridDays)); ?>;">
                    <div class="sched-corner">Time</div>
                    <?php $__currentLoopData = $gridDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="sched-day">
                            <strong><?php echo e(ucfirst($day)); ?></strong>
                            <span><?php echo e($weekStart->copy()->addDays($index)->format('M j')); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php for($hour = $startHour; $hour <= $endHour; $hour++): ?>
                        <div class="sched-time"><?php echo e(\Carbon\Carbon::createFromTime($hour)->format('g:i A')); ?></div>
                        <?php $__currentLoopData = $gridDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="sched-cell">
                                <?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($schedule->day_of_week === $day && (int) \Carbon\Carbon::parse($schedule->start_time)->format('G') === $hour): ?>
                                        <?php
                                            $hasConflict = isset($conflictIds[$schedule->id]);
                                            $finalLabel = $schedule->is_finalized ? 'Finalized' : ($schedule->is_active ? 'Not finalized' : 'Unpublished');
                                        ?>
                                        <button type="button"
                                            class="sched-block <?php echo e($hasConflict ? 'is-conflict' : ''); ?> <?php echo e($schedule->is_active ? '' : 'is-off'); ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#scheduleDetailModal"
                                            data-subject="<?php echo e($schedule->subject->subject_name ?? 'Subject'); ?>"
                                            data-teacher="<?php echo e($schedule->teacher->full_name ?? 'Unassigned'); ?>"
                                            data-section="<?php echo e(trim(($schedule->section->grade_level ?? '').' – '.($schedule->section->name ?? ''))); ?>"
                                            data-day="<?php echo e(ucfirst($schedule->day_of_week)); ?>"
                                            data-time="<?php echo e(\Carbon\Carbon::parse($schedule->start_time)->format('g:i A')); ?> – <?php echo e(\Carbon\Carbon::parse($schedule->end_time)->format('g:i A')); ?>"
                                            data-room="<?php echo e($schedule->room->room_name ?? 'Not assigned'); ?>"
                                            data-year="<?php echo e($schedule->academicYear?->displayName() ?? ($viewYear?->displayName() ?? '')); ?>"
                                            data-status="<?php echo e($finalLabel); ?>"
                                            data-conflict="<?php echo e($hasConflict ? '1' : '0'); ?>"
                                            data-edit="<?php echo e(route('admin.schedules.edit', $schedule)); ?>">
                                            <strong><?php echo e($schedule->subject->subject_name ?? 'Subject'); ?></strong>
                                            <span><?php echo e(\Carbon\Carbon::parse($schedule->start_time)->format('g:i A')); ?> – <?php echo e(\Carbon\Carbon::parse($schedule->end_time)->format('g:i A')); ?></span>
                                            <span><?php echo e($schedule->teacher->full_name ?? 'Teacher'); ?></span>
                                            <?php if(!request('section_id')): ?>
                                                <span><?php echo e($schedule->section->grade_level ?? ''); ?> <?php echo e($schedule->section->name ?? ''); ?></span>
                                            <?php endif; ?>
                                            <?php if($schedule->room): ?>
                                                <span><?php echo e($schedule->room->room_name); ?></span>
                                            <?php endif; ?>
                                            <?php if($hasConflict): ?>
                                                <span class="sched-warn">Schedule conflict</span>
                                            <?php endif; ?>
                                        </button>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endfor; ?>
                </div>
            </div>
            <?php if($schedules->isEmpty()): ?>
                <div class="dir-empty py-4">
                    <h5 class="mt-2 mb-1">No class schedules for <?php echo e($viewYear?->displayName() ?? 'this year'); ?></h5>
                    <p class="mb-0">Schedules from other academic years are not shown here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="scheduleDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong id="schedDetailSubject">Subject</strong></p>
                <p class="mb-1">Teacher: <span id="schedDetailTeacher"></span></p>
                <p class="mb-1">Section: <span id="schedDetailSection"></span></p>
                <p class="mb-1">Day: <span id="schedDetailDay"></span></p>
                <p class="mb-1">Time: <span id="schedDetailTime"></span></p>
                <p class="mb-1">Room: <span id="schedDetailRoom"></span></p>
                <p class="mb-1">Academic Year: <span id="schedDetailYear"></span></p>
                <p class="mb-1">Status: <span id="schedDetailStatus"></span></p>
                <p class="mb-0 text-danger d-none" id="schedDetailConflict">This class overlaps another class for the same teacher, section, or room.</p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" id="schedDetailEdit">Edit</a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914c">
<style>
.sched-scroll { overflow-x: auto; }
.sched-grid {
    display: grid;
    grid-template-columns: 88px repeat(var(--sched-days), minmax(140px, 1fr));
    min-width: 760px;
}
.sched-corner, .sched-day, .sched-time, .sched-cell {
    border-bottom: 1px solid #e5e7eb;
    border-right: 1px solid #e5e7eb;
    padding: .45rem;
}
.sched-corner, .sched-day {
    background: #f8fafc;
    position: sticky;
    top: 0;
    z-index: 1;
}
.sched-day { display: flex; flex-direction: column; font-size: .85rem; }
.sched-day span, .sched-time { color: #6b7280; font-size: .78rem; }
.sched-time { font-weight: 600; }
.sched-block {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    width: 100%;
    text-align: left;
    border: 1px solid #c7d2fe;
    background: #eef2ff;
    color: #1e1b4b;
    border-radius: 8px;
    padding: .4rem .5rem;
    margin-bottom: .35rem;
    font-size: .78rem;
    line-height: 1.3;
}
.sched-block strong { font-size: .84rem; }
.sched-block.is-conflict { border-color: #f59e0b; background: #fff7ed; }
.sched-block.is-off { opacity: .6; }
.sched-warn { color: #b45309; font-weight: 700; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('scheduleDetailModal')?.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    if (!button) return;
    const set = function (id, value) {
        const node = document.getElementById(id);
        if (node) node.textContent = value || '';
    };
    set('schedDetailSubject', button.getAttribute('data-subject'));
    set('schedDetailTeacher', button.getAttribute('data-teacher'));
    set('schedDetailSection', button.getAttribute('data-section'));
    set('schedDetailDay', button.getAttribute('data-day'));
    set('schedDetailTime', button.getAttribute('data-time'));
    set('schedDetailRoom', button.getAttribute('data-room'));
    set('schedDetailYear', button.getAttribute('data-year'));
    set('schedDetailStatus', button.getAttribute('data-status'));
    document.getElementById('schedDetailConflict')?.classList.toggle('d-none', button.getAttribute('data-conflict') !== '1');
    const edit = document.getElementById('schedDetailEdit');
    if (edit) edit.href = button.getAttribute('data-edit') || '#';
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/admin/schedules/index.blade.php ENDPATH**/ ?>