
<?php $__env->startSection('content'); ?>

<?php
    $month = $month ?? request('month', now()->format('Y-m'));
    try {
        $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y');
    } catch (\Exception $e) {
        $monthLabel = now()->format('F Y');
    }
    $photo = $student?->photoUrl() ?? asset('images/photo_defaults.jpg');
    $displayName = trim(($student->first_name ?? '').' '.($student->last_name ?? '')) ?: 'Student';
    $rate = (float) ($summary['percentage'] ?? 0);
    $total = (int) ($summary['total'] ?? 0);
    $present = (int) ($summary['present'] ?? 0);
    $late = (int) ($summary['late'] ?? 0);
    $absent = (int) ($summary['absent'] ?? 0);
    $excused = (int) ($summary['excused'] ?? 0);
    $selectedSubject = $subjects->firstWhere('id', (int) request('subject_id'));
    $scopeLabel = $selectedSubject?->subject_name ?? 'All subjects';
    $defaultPhoto = asset('images/photo_defaults.jpg');

    $statusMeta = function (?string $status) {
        return match ($status) {
            'present' => ['Present', 'is-excellent', 'fa-check'],
            'late' => ['Late', 'is-average', 'fa-clock'],
            'excused' => ['Excused', 'is-good', 'fa-info-circle'],
            default => ['Absent', 'is-low', 'fa-times'],
        };
    };
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="sa-page">
            <header class="sa-hero">
                <div class="sa-hero-who">
                    <img src="<?php echo e($photo); ?>" alt="<?php echo e($displayName); ?>" class="sa-avatar" onerror="this.onerror=null;this.src='<?php echo e($defaultPhoto); ?>';">
                    <div>
                        <h1>Attendance</h1>
                        <p><?php echo e($displayName); ?> · <?php echo e($monthLabel); ?> · <?php echo e($scopeLabel); ?></p>
                    </div>
                </div>
            </header>

            <form method="GET" action="<?php echo e(route('attendance.student')); ?>" class="sa-filters">
                <div class="sa-field">
                    <label for="subject_id">Subject</label>
                    <select name="subject_id" id="subject_id">
                        <option value="">All subjects</option>
                        <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($subject->id); ?>" <?php if(request('subject_id') == $subject->id): echo 'selected'; endif; ?>>
                                <?php echo e($subject->subject_name); ?><?php echo e(!empty($subject->class) ? ' ('.$subject->class.')' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="sa-field">
                    <label for="month">Month</label>
                    <input type="month" name="month" id="month" value="<?php echo e($month); ?>">
                </div>
                <div class="sa-field sa-field-action">
                    <button type="submit" class="sa-btn-primary">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <a href="<?php echo e(route('attendance.student')); ?>" class="sa-btn-outline">Reset</a>
                </div>
            </form>

            <section class="sa-kpis">
                <article class="sa-kpi">
                    <div class="sa-ring" style="--pct: <?php echo e(max(0, min(100, $rate))); ?>">
                        <span><?php echo e(number_format($rate, 0)); ?>%</span>
                    </div>
                    <div>
                        <div class="sa-kpi-value"><?php echo e(number_format($rate, 1)); ?>%</div>
                        <div class="sa-kpi-label">Attendance rate</div>
                        <div class="sa-kpi-meta">Present and late count as attended</div>
                    </div>
                </article>
                <article class="sa-kpi">
                    <div class="sa-kpi-icon sa-kpi-icon-is-excellent"><i class="fas fa-check"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?php echo e($present); ?></div>
                        <div class="sa-kpi-label">Present</div>
                        <div class="sa-kpi-meta">On time</div>
                    </div>
                </article>
                <article class="sa-kpi">
                    <div class="sa-kpi-icon sa-kpi-icon-is-average"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?php echo e($late); ?></div>
                        <div class="sa-kpi-label">Late</div>
                        <div class="sa-kpi-meta">Counted as attended</div>
                    </div>
                </article>
                <article class="sa-kpi">
                    <div class="sa-kpi-icon sa-kpi-icon-is-low"><i class="fas fa-times"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?php echo e($absent); ?></div>
                        <div class="sa-kpi-label">Absent</div>
                        <div class="sa-kpi-meta"><?php echo e($excused); ?> excused</div>
                    </div>
                </article>
            </section>

            <div class="sa-grid">
                <section class="sa-panel">
                    <div class="sa-panel-head">
                        <div>
                            <h2>Daily records</h2>
                            <p><?php echo e($total); ?> <?php echo e(\Illuminate\Support\Str::plural('class', $total)); ?> this month.</p>
                        </div>
                    </div>
                    <?php if($attendances->isEmpty()): ?>
                        <div class="sa-empty">
                            <i class="fas fa-user-check"></i>
                            <p>No attendance records for this month and subject.</p>
                        </div>
                    <?php else: ?>
                        <div class="sa-table-wrap">
                            <table class="sa-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Marked by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            [$label, $badge, $icon] = $statusMeta($attendance->status);
                                            $date = $attendance->date ? \Carbon\Carbon::parse($attendance->date) : null;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($date ? $date->format('M j, Y') : '—'); ?></strong>
                                                <div class="sa-sub"><?php echo e($date ? $date->format('l') : ''); ?></div>
                                            </td>
                                            <td><?php echo e($attendance->subject?->subject_name ?? '—'); ?></td>
                                            <td>
                                                <span class="sa-badge <?php echo e($badge); ?>">
                                                    <i class="fas <?php echo e($icon); ?>"></i> <?php echo e($label); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e($attendance->remarks ?: '—'); ?></td>
                                            <td><?php echo e($attendance->teacher?->full_name ?? '—'); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="sa-panel">
                    <div class="sa-panel-head">
                        <div>
                            <h2>This month</h2>
                            <p>Breakdown of <?php echo e($monthLabel); ?>.</p>
                        </div>
                    </div>
                    <?php if($total === 0): ?>
                        <div class="sa-empty">
                            <i class="fas fa-chart-pie"></i>
                            <p>Summary appears after attendance is recorded.</p>
                        </div>
                    <?php else: ?>
                        <div class="sa-chart"><canvas id="attendanceChart" data-values="<?php echo e(json_encode([$present, $late, $absent, $excused])); ?>"></canvas></div>
                        <ul class="sa-legend">
                            <li><span class="dot is-excellent"></span> Present <?php echo e($present); ?></li>
                            <li><span class="dot is-average"></span> Late <?php echo e($late); ?></li>
                            <li><span class="dot is-low"></span> Absent <?php echo e($absent); ?></li>
                            <li><span class="dot is-good"></span> Excused <?php echo e($excused); ?></li>
                        </ul>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
:root {
    --sa-accent: #e67e22;
    --sa-accent-dark: #d35400;
    --sa-accent-soft: #fff4eb;
    --sa-bg: #f5f6f8;
    --sa-card: #ffffff;
    --sa-text: #1f2937;
    --sa-muted: #6b7280;
    --sa-border: #e8eaed;
    --sa-radius: 12px;
    --sa-shadow: 0 1px 3px rgba(16,24,40,.06), 0 1px 2px rgba(16,24,40,.04);
}
.page-wrapper .content.container-fluid {
    background: var(--sa-bg);
    max-width: none !important;
    width: 100% !important;
    padding-left: 1.75rem !important;
    padding-right: 1.25rem !important;
}
.sa-page { color: var(--sa-text); padding-bottom: 1.5rem; }
.sa-hero { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; margin-bottom: 1.15rem; flex-wrap: wrap; }
.sa-hero-who { display: flex; align-items: center; gap: .9rem; }
.sa-avatar { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: var(--sa-shadow); }
.sa-hero h1 { font-size: 1.65rem; font-weight: 700; margin: 0 0 .2rem; letter-spacing: -0.02em; }
.sa-hero p { margin: 0; color: var(--sa-muted); font-size: .95rem; }
.sa-filters {
    display: grid; grid-template-columns: 1fr 1fr auto; gap: .85rem; align-items: end;
    background: var(--sa-card); border: 1px solid var(--sa-border); border-radius: var(--sa-radius);
    box-shadow: var(--sa-shadow); padding: 1rem 1.1rem; margin-bottom: 1.15rem;
}
.sa-field label { display: block; font-size: .78rem; font-weight: 600; color: var(--sa-muted); margin-bottom: .35rem; }
.sa-field select, .sa-field input {
    width: 100%; border: 1px solid var(--sa-border); border-radius: 10px; padding: .55rem .75rem;
    background: #fff; color: var(--sa-text); height: 42px;
}
.sa-field-action { display: flex; gap: .5rem; }
.sa-btn-primary, .sa-btn-outline {
    display: inline-flex; align-items: center; justify-content: center; gap: .45rem; border-radius: 10px; font-weight: 600;
    padding: .55rem 1rem; height: 42px; border: 1px solid transparent; cursor: pointer; text-decoration: none;
}
.sa-btn-primary { background: var(--sa-accent); color: #fff; }
.sa-btn-primary:hover { background: var(--sa-accent-dark); color: #fff; }
.sa-btn-outline { background: #fff; color: var(--sa-accent); border-color: #f0d3bb; }
.sa-btn-outline:hover { background: var(--sa-accent-soft); color: var(--sa-accent-dark); }
.sa-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.15rem; }
.sa-kpi {
    background: var(--sa-card); border: 1px solid var(--sa-border); border-radius: var(--sa-radius);
    box-shadow: var(--sa-shadow); padding: 1.05rem 1.1rem; display: flex; gap: .85rem; align-items: center;
}
.sa-kpi-icon {
    width: 42px; height: 42px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sa-kpi-icon-is-excellent { background: #ecfdf5; color: #047857; }
.sa-kpi-icon-is-good { background: #eff6ff; color: #1d4ed8; }
.sa-kpi-icon-is-average { background: #fff7ed; color: #c2410c; }
.sa-kpi-icon-is-low { background: #fef2f2; color: #b91c1c; }
.sa-kpi-value { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
.sa-kpi-label { font-size: .9rem; font-weight: 600; margin-top: .15rem; }
.sa-kpi-meta { font-size: .75rem; color: var(--sa-muted); margin-top: .15rem; }
.sa-ring {
    --pct: 0; width: 52px; height: 52px; border-radius: 50%; flex-shrink: 0;
    background: conic-gradient(var(--sa-accent) calc(var(--pct) * 1%), #eceff3 0);
    display: grid; place-items: center;
}
.sa-ring span {
    width: 38px; height: 38px; border-radius: 50%; background: #fff; display: grid; place-items: center;
    font-size: .62rem; font-weight: 700; color: var(--sa-text);
}
.sa-grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(260px, .7fr); gap: 1.15rem; align-items: start; }
.sa-panel {
    background: var(--sa-card); border: 1px solid var(--sa-border); border-radius: var(--sa-radius);
    box-shadow: var(--sa-shadow); padding: 1.15rem 1.2rem;
}
.sa-panel-head { margin-bottom: .85rem; }
.sa-panel-head h2 { font-size: 1.05rem; font-weight: 700; margin: 0 0 .2rem; }
.sa-panel-head p { margin: 0; color: var(--sa-muted); font-size: .82rem; }
.sa-chart { height: 220px; position: relative; }
.sa-empty { text-align: center; padding: 2.2rem 1rem; color: var(--sa-muted); }
.sa-empty i { display: block; font-size: 1.6rem; color: #f0c9a6; margin-bottom: .5rem; }
.sa-empty p { margin: 0; }
.sa-table-wrap { overflow-x: auto; }
.sa-table { width: 100%; border-collapse: collapse; }
.sa-table th { font-size: .75rem; letter-spacing: .03em; text-transform: uppercase; color: var(--sa-muted); font-weight: 600; padding: .55rem 0; border-bottom: 1px solid var(--sa-border); text-align: left; }
.sa-table td { padding: .85rem 0; border-bottom: 1px solid #f1f3f5; vertical-align: middle; font-size: .92rem; }
.sa-table tr:last-child td { border-bottom: none; }
.sa-sub { font-size: .78rem; color: var(--sa-muted); margin-top: .15rem; }
.sa-badge { display: inline-flex; align-items: center; gap: .3rem; border-radius: 999px; padding: .2rem .6rem; font-size: .72rem; font-weight: 700; }
.sa-badge.is-excellent { background: #ecfdf5; color: #047857; }
.sa-badge.is-good { background: #eff6ff; color: #1d4ed8; }
.sa-badge.is-average { background: #fff7ed; color: #c2410c; }
.sa-badge.is-low { background: #fef2f2; color: #b91c1c; }
.sa-legend { list-style: none; padding: 0; margin: .85rem 0 0; display: grid; gap: .4rem; }
.sa-legend li { display: flex; align-items: center; gap: .5rem; font-size: .85rem; color: var(--sa-muted); }
.sa-legend .dot { width: 10px; height: 10px; border-radius: 50%; }
.sa-legend .dot.is-excellent { background: #047857; }
.sa-legend .dot.is-average { background: #d97706; }
.sa-legend .dot.is-low { background: #b91c1c; }
.sa-legend .dot.is-good { background: #1d4ed8; }
@media (max-width: 1100px) {
    .sa-kpis, .sa-filters { grid-template-columns: 1fr 1fr; }
    .sa-grid { grid-template-columns: 1fr; }
}
@media (max-width: 720px) {
    .sa-kpis, .sa-filters { grid-template-columns: 1fr; }
    .sa-hero h1 { font-size: 1.35rem; }
    .sa-field-action { display: grid; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php if($total > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('attendanceChart');
    if (!canvas) return;
    new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Late', 'Absent', 'Excused'],
            datasets: [{
                data: JSON.parse(canvas.getAttribute('data-values') || '[0,0,0,0]'),
                backgroundColor: ['#047857', '#d97706', '#b91c1c', '#1d4ed8'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\attendance\student_view.blade.php ENDPATH**/ ?>