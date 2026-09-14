<?php $__env->startSection('content'); ?>

<?php
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $allSchedules = collect($weeklySchedule ?? [])->flatten();
    $classCount = $allSchedules->count();
    $today = now();
    $todayKey = strtolower($today->format('l'));

    try {
        $focus = request()->filled('week') ? \Carbon\Carbon::parse(request('week')) : $today->copy();
    } catch (\Exception $e) {
        $focus = $today->copy();
    }
    $thisWeekStart = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $weekStart = $focus->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $weekEnd = $weekStart->copy()->addDays(5);
    $isCurrentWeek = $weekStart->isSameDay($thisWeekStart);
    $prevWeek = $weekStart->copy()->subWeek()->toDateString();
    $nextWeek = $weekStart->copy()->addWeek()->toDateString();

    $clockMinutes = function ($value): int {
        if ($value instanceof \DateTimeInterface) {
            return ((int) $value->format('G')) * 60 + (int) $value->format('i');
        }
        if (preg_match('/(\d{1,2}):(\d{2})/', (string) $value, $match)) {
            return ((int) $match[1]) * 60 + (int) $match[2];
        }
        $parsed = \Carbon\Carbon::parse($value);
        return ((int) $parsed->format('G')) * 60 + (int) $parsed->format('i');
    };

    $formatMinutes = function (int $minutes): string {
        $minutes = (($minutes % 1440) + 1440) % 1440;
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    };

    $gridStart = 7 * 60;
    $gridEnd = 16 * 60;
    if ($allSchedules->isNotEmpty()) {
        $minMin = $allSchedules->min(fn ($s) => $clockMinutes($s->start_time));
        $maxMin = $allSchedules->max(fn ($s) => $clockMinutes($s->end_time));
        $gridStart = min($gridStart, max(6 * 60, intdiv($minMin, 60) * 60));
        $gridEnd = max($gridEnd, min(20 * 60, (int) ceil($maxMin / 60) * 60));
    }
    $spanMinutes = max(60, $gridEnd - $gridStart);
    $hourSlots = [];
    for ($minute = $gridStart; $minute < $gridEnd; $minute += 60) {
        $hourSlots[] = $minute;
    }

    $todayClasses = collect($weeklySchedule[$todayKey] ?? [])->sortBy(fn ($s) => $clockMinutes($s->start_time));
    $legend = $allSchedules
        ->groupBy(fn ($s) => $s->subject->id ?? $s->subject_id)
        ->map(function ($items) {
            $first = $items->first();
            return [
                'name' => $first->subject->subject_name ?? 'Subject',
                'color' => $first->color ?: '#7c8cff',
                'count' => $items->count(),
            ];
        })
        ->values();
?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page plan-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">My Teaching Schedule</h3>
                    <p class="dir-subtitle">Weekly planner for your assigned classes.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Schedule</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="plan-shell">
            <aside class="plan-side">
                <div class="plan-side-card">
                    <div class="plan-month"><?php echo e($weekStart->format('F Y')); ?></div>
                    <div class="plan-weekdays" style="grid-template-columns: repeat(<?php echo e(count($days)); ?>, 1fr);">
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $date = $weekStart->copy()->addDays($index); ?>
                            <div class="plan-weekday <?php echo e($date->isSameDay($today) ? 'is-today' : ''); ?>">
                                <span><?php echo e($dayNames[$index][0]); ?></span>
                                <strong><?php echo e($date->format('j')); ?></strong>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div class="plan-side-card">
                    <h6>Today</h6>
                    <?php $__empty_1 = true; $__currentLoopData = $todayClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="plan-today-row">
                            <i style="background: <?php echo e($item->color ?: '#7c8cff'); ?>"></i>
                            <div>
                                <strong><?php echo e($item->subject->subject_name); ?></strong>
                                <span><?php echo e($formatMinutes($clockMinutes($item->start_time))); ?> – <?php echo e($formatMinutes($clockMinutes($item->end_time))); ?></span>
                                <span><?php echo e($item->section->name); ?> · <?php echo e($item->room->room_name ?? 'TBD'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="plan-empty-note">No classes today.</p>
                    <?php endif; ?>
                </div>

                <div class="plan-side-card">
                    <h6>Subjects</h6>
                    <?php $__empty_1 = true; $__currentLoopData = $legend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="plan-cat">
                            <i style="background: <?php echo e($item['color']); ?>"></i>
                            <span><?php echo e($item['name']); ?></span>
                            <em><?php echo e($item['count']); ?></em>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="plan-empty-note">No subjects scheduled.</p>
                    <?php endif; ?>
                </div>
            </aside>

            <section class="plan-board">
                <div class="plan-board-bar">
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?php echo e(route('teacher.my-schedule', ['week' => $prevWeek])); ?>" class="plan-week-btn" title="Previous week"><i class="fas fa-chevron-left"></i></a>
                        <a href="<?php echo e(route('teacher.my-schedule', ['week' => $nextWeek])); ?>" class="plan-week-btn" title="Next week"><i class="fas fa-chevron-right"></i></a>
                        <div>
                            <div class="plan-board-title"><?php echo e($weekStart->format('M j')); ?> – <?php echo e($weekEnd->format('M j, Y')); ?></div>
                            <div class="plan-board-sub"><?php echo e($classCount); ?> class<?php echo e($classCount === 1 ? '' : 'es'); ?> · Monday to Saturday</div>
                        </div>
                    </div>
                    <?php if(!$isCurrentWeek): ?>
                        <a href="<?php echo e(route('teacher.my-schedule')); ?>" class="plan-week-now">This week</a>
                    <?php endif; ?>
                </div>

                <?php if($classCount === 0): ?>
                    <div class="dir-empty">
                        <i class="far fa-calendar-alt d-block"></i>
                        <h5 class="mt-2 mb-1">No classes scheduled</h5>
                        <p class="mb-0">Your weekly planner will appear here after Admin assigns a class schedule to you.</p>
                    </div>
                <?php else: ?>
                    <div class="plan-cal">
                        <div class="plan-heads" style="--days: <?php echo e(count($days)); ?>;">
                            <div class="plan-head plan-head-time"></div>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $date = $weekStart->copy()->addDays($index); ?>
                                <div class="plan-head <?php echo e($date->isSameDay($today) ? 'is-today' : ''); ?>">
                                    <span><?php echo e(strtoupper($dayNames[$index])); ?></span>
                                    <strong><?php echo e($date->format('j')); ?></strong>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="plan-body" style="--days: <?php echo e(count($days)); ?>; --hours: <?php echo e(count($hourSlots)); ?>;">
                            <div class="plan-times">
                                <?php $__currentLoopData = $hourSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="plan-time"><span><?php echo e($formatMinutes($slot)); ?></span></div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $date = $weekStart->copy()->addDays((int) array_search($day, $days, true)); ?>
                                <div class="plan-col <?php echo e($date->isSameDay($today) ? 'is-today' : ''); ?>">
                                    <?php $__currentLoopData = ($weeklySchedule[$day] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $startM = $clockMinutes($schedule->start_time);
                                            $endM = $clockMinutes($schedule->end_time);
                                            if ($endM <= $startM) {
                                                $endM = $startM + 30;
                                            }
                                            $topPct = (($startM - $gridStart) / $spanMinutes) * 100;
                                            $heightPct = (($endM - $startM) / $spanMinutes) * 100;
                                            $color = $schedule->color ?: '#7c8cff';
                                            $startLabel = $formatMinutes($startM);
                                            $endLabel = $formatMinutes($endM);
                                        ?>
                                        <article class="plan-event"
                                                 style="top: <?php echo e($topPct); ?>%; --block-h: calc(<?php echo e($heightPct); ?>% - 4px); --event: <?php echo e($color); ?>;">
                                            <?php if($schedule->subject->subject_id): ?>
                                                <div class="plan-event-code"><?php echo e($schedule->subject->subject_id); ?></div>
                                            <?php endif; ?>
                                            <div class="plan-event-name"><?php echo e($schedule->subject->subject_name); ?></div>
                                            <div class="plan-event-time"><?php echo e($startLabel); ?> – <?php echo e($endLabel); ?></div>
                                            <div class="plan-event-meta"><?php echo e($schedule->section->name); ?><?php if($schedule->section?->grade_level): ?> · <?php echo e($schedule->section->grade_level); ?><?php endif; ?></div>
                                            <div class="plan-event-meta"><?php echo e($schedule->room->room_name ?? 'Room TBD'); ?></div>
                                        </article>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914h">
<style>
.plan-page .page-header { margin-bottom: 0.75rem; }
.plan-shell {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    gap: 0.9rem;
    height: calc(100vh - 175px);
    min-height: 640px;
    align-items: stretch;
}
.plan-side {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    min-height: 0;
    overflow: auto;
}
.plan-side-card,
.plan-board {
    background: #fff;
    border: 1px solid #e8eef7;
    border-radius: 20px;
    box-shadow: 0 14px 32px rgba(79, 114, 205, 0.05);
}
.plan-side-card { padding: 0.95rem 1rem; }
.plan-month {
    font-size: 0.98rem;
    font-weight: 750;
    color: #1e293b;
    margin-bottom: 0.7rem;
}
.plan-weekdays {
    display: grid;
    gap: 0.1rem;
    text-align: center;
}
.plan-weekday span,
.plan-head span {
    display: block;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #94a3b8;
}
.plan-weekday strong {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    margin-top: 0.15rem;
    border-radius: 50%;
    font-size: 0.78rem;
    color: #334155;
}
.plan-weekday.is-today strong {
    background: #7c8cff;
    color: #fff;
}
.plan-side-card h6 {
    font-size: 0.72rem;
    font-weight: 750;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #94a3b8;
    margin: 0 0 0.65rem;
}
.plan-today-row,
.plan-cat {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
}
.plan-today-row:last-child,
.plan-cat:last-child { margin-bottom: 0; }
.plan-today-row i,
.plan-cat i {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 0.35rem;
    flex-shrink: 0;
}
.plan-today-row strong,
.plan-cat span {
    display: block;
    font-size: 0.84rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.25;
}
.plan-today-row span {
    display: block;
    font-size: 0.7rem;
    color: #94a3b8;
}
.plan-cat { align-items: center; justify-content: space-between; }
.plan-cat i { margin-top: 0; }
.plan-cat em {
    font-style: normal;
    font-size: 0.72rem;
    color: #94a3b8;
}
.plan-empty-note { margin: 0; font-size: 0.8rem; color: #94a3b8; }
.plan-board {
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: auto;
}
.plan-board-bar {
    flex: 0 0 auto;
    padding: 0.85rem 1.15rem 0.35rem;
}
.plan-board-title { font-size: 1.05rem; font-weight: 750; color: #1e293b; }
.plan-board-sub { font-size: 0.75rem; color: #94a3b8; margin-top: 0.1rem; }
.plan-cal {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    padding: 0 0.35rem 0.65rem;
}
.plan-heads,
.plan-body {
    display: grid;
    grid-template-columns: 52px repeat(var(--days, 5), minmax(0, 1fr));
}
.plan-heads { flex: 0 0 auto; }
.plan-head {
    text-align: center;
    padding: 0.15rem 0 0.45rem;
}
.plan-head strong {
    display: block;
    margin-top: 0.08rem;
    font-size: 1.45rem;
    font-weight: 650;
    color: #c7d2fe;
    line-height: 1;
}
.plan-head.is-today span { color: #6366f1; }
.plan-head.is-today strong { color: #4f46e5; }
.plan-body {
    flex: 1;
    min-height: 0;
    position: relative;
}
.plan-times,
.plan-col { height: 100%; }
.plan-times { position: relative; }
.plan-time {
    height: calc(100% / var(--hours, 9));
    position: relative;
}
.plan-time span {
    position: absolute;
    right: 8px;
    top: 0;
    transform: translateY(-50%);
    font-size: 0.7rem;
    font-weight: 600;
    color: #94a3b8;
}
.plan-col {
    position: relative;
    overflow: visible;
    border-left: 1px solid #edf1f7;
    background-image:
        repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent calc(100% / var(--hours, 9) / 2 - 1px),
            #f1f4fa calc(100% / var(--hours, 9) / 2 - 1px),
            #f1f4fa calc(100% / var(--hours, 9) / 2),
            transparent calc(100% / var(--hours, 9) / 2),
            transparent calc(100% / var(--hours, 9) - 1px),
            #e6ecf5 calc(100% / var(--hours, 9) - 1px),
            #e6ecf5 calc(100% / var(--hours, 9))
        );
}
.plan-col.is-today { background-color: rgba(124, 140, 255, 0.035); }
.plan-event {
    position: absolute;
    left: 6px;
    right: 6px;
    z-index: 2;
    min-height: max(5.75rem, var(--block-h, 5.75rem));
    height: auto;
    padding: 0.5rem 0.65rem 0.5rem 0.75rem;
    border-radius: 12px;
    overflow: visible;
    background: color-mix(in srgb, var(--event) 18%, #fff);
    box-shadow: inset 4px 0 0 var(--event);
    color: color-mix(in srgb, var(--event) 42%, #1e293b);
}
.plan-event:hover { z-index: 8; }
.plan-event-code {
    font-size: 0.68rem;
    font-weight: 750;
    letter-spacing: 0.03em;
    opacity: 0.8;
    margin-bottom: 0.1rem;
}
.plan-event-name {
    font-size: 0.88rem;
    font-weight: 750;
    line-height: 1.3;
    margin-bottom: 0.18rem;
    white-space: normal;
    overflow: visible;
}
.plan-event-time,
.plan-event-meta {
    font-size: 0.74rem;
    font-weight: 650;
    opacity: 0.88;
    line-height: 1.35;
    white-space: normal;
    overflow: visible;
}
.plan-event-meta { opacity: 0.78; }
@media (max-width: 1100px) {
    .plan-shell {
        grid-template-columns: 1fr;
        height: auto;
        min-height: 0;
    }
    .plan-board { min-height: 70vh; }
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/schedule/teacher-schedule.blade.php ENDPATH**/ ?>