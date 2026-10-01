<?php $__env->startSection('content'); ?>

<?php
    $indicators = $analytics['performance_indicators'] ?? [];
    $gradeTrends = collect($analytics['grade_trends'] ?? []);
    $attendanceRows = collect($analytics['attendance_summary'] ?? []);
    $subjects = collect($analytics['subject_performance'] ?? []);
    $activities = collect($analytics['recent_activities'] ?? []);
    $gpaTrend = collect($analytics['gpa_trend'] ?? []);
    $averageScore = (float) ($indicators['average_score'] ?? 0);
    $performanceLevel = $indicators['performance_level'] ?? '—';
    $passingPercentage = $passingPercentage ?? 75;
    $latestAttendance = $attendanceRows->last();
    $attendanceRate = $attendanceRows->avg('percentage');
    $attendanceRate = $attendanceRate !== null ? round($attendanceRate, 1) : null;
    $filterYear = $academicYears->firstWhere('id', $academicYearId);
    $filterSemester = $semesters->firstWhere('id', $semesterId);
    $periodLabel = collect([
        $filterYear->name ?? null,
        $filterSemester->name ?? null,
    ])->filter()->implode(' · ') ?: 'All recorded terms';
    $photo = $student->photoUrl();
    $displayName = trim(($student->first_name ?? '').' '.($student->last_name ?? '')) ?: 'Student';

    $levelClass = match ($performanceLevel) {
        'Excellent' => 'is-excellent',
        'Good' => 'is-good',
        'Average' => 'is-average',
        default => 'is-low',
    };

    $subjectLevel = function ($score) {
        if ($score >= 90) return ['Excellent', 'is-excellent'];
        if ($score >= 80) return ['Good', 'is-good'];
        if ($score >= 75) return ['Passing', 'is-average'];
        return ['Needs improvement', 'is-low'];
    };
?>

    <div class="page-wrapper">
        <div class="content container-fluid">
        <div class="sa-page">
            <header class="sa-hero">
                <div class="sa-hero-who">
                    <img src="<?php echo e($photo); ?>" alt="<?php echo e($displayName); ?>" class="sa-avatar" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                    <div>
                        <h1>My Analytics</h1>
                        <p><?php echo e($displayName); ?> · <?php echo e($student->year_level ?? 'Student'); ?> · <?php echo e($periodLabel); ?></p>
                    </div>
                </div>
                <div class="sa-hero-actions">
                    <button type="button" class="sa-btn-outline" onclick="exportReport()">
                        <i class="fas fa-download"></i> Export report
                    </button>
            </div>
            </header>

            <form class="sa-filters" onsubmit="applyFilters(); return false;">
                <div class="sa-field">
                    <label for="academic_year_filter">Academic year</label>
                    <select id="academic_year_filter" name="academic_year_id">
                        <option value="">All academic years</option>
                                <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($year->id); ?>" <?php if($academicYearId == $year->id): echo 'selected'; endif; ?>><?php echo e($year->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                <div class="sa-field">
                    <label for="semester_filter">Semester / quarter</label>
                    <select id="semester_filter" name="semester_id">
                        <option value="">All terms</option>
                                <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($semester->id); ?>" <?php if($semesterId == $semester->id): echo 'selected'; endif; ?>><?php echo e($semester->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                <div class="sa-field sa-field-action">
                    <button type="submit" class="sa-btn-primary">
                        <i class="fas fa-filter"></i> Apply
                            </button>
                        </div>
            </form>

            <?php if(!empty($indicators['improvement_needed'])): ?>
                <div class="sa-alert">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Below the <?php echo e(number_format($passingPercentage, 0)); ?>% passing mark</strong>
                        <p><?php echo e($indicators['low_grades_count']); ?> recorded score(s) are below passing. Review those subjects and ask your teacher if you need help.</p>
                    </div>
                </div>
            <?php endif; ?>

            <section class="sa-kpis">
                <article class="sa-kpi">
                    <div class="sa-ring" style="--pct: <?php echo e(max(0, min(100, $averageScore))); ?>">
                        <span><?php echo e(number_format($averageScore, 1)); ?></span>
            </div>
                                <div>
                        <div class="sa-kpi-value"><?php echo e(number_format($averageScore, 1)); ?>%</div>
                        <div class="sa-kpi-label">Average score</div>
                        <div class="sa-kpi-meta">Passing mark is <?php echo e(number_format($passingPercentage, 0)); ?>%</div>
                                </div>
                </article>
                <article class="sa-kpi">
                    <div class="sa-kpi-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?php echo e($indicators['total_assignments'] ?? 0); ?></div>
                        <div class="sa-kpi-label">Graded records</div>
                        <div class="sa-kpi-meta">Assignments and written work</div>
                    </div>
                </article>
                <article class="sa-kpi">
                    <div class="sa-kpi-icon sa-kpi-icon-star"><i class="fas fa-star"></i></div>
                                <div>
                        <div class="sa-kpi-value"><?php echo e($indicators['excellent_grades_count'] ?? 0); ?></div>
                        <div class="sa-kpi-label">Excellent scores</div>
                        <div class="sa-kpi-meta">90% and above</div>
                    </div>
                </article>
                <article class="sa-kpi">
                    <div class="sa-kpi-icon sa-kpi-icon-<?php echo e($levelClass); ?>"><i class="fas fa-award"></i></div>
                                <div>
                        <div class="sa-kpi-value sa-kpi-value-sm"><?php echo e($performanceLevel); ?></div>
                        <div class="sa-kpi-label">Performance level</div>
                        <div class="sa-kpi-meta"><?php echo e($attendanceRate !== null ? 'Attendance '.$attendanceRate.'%' : 'Based on recorded grades'); ?></div>
                    </div>
                </article>
            </section>

            <div class="sa-grid">
                <section class="sa-panel">
                    <div class="sa-panel-head">
                                <div>
                            <h2>Grade trends</h2>
                            <p>Scores over time. The dashed line is the <?php echo e(number_format($passingPercentage, 0)); ?>% passing mark.</p>
                        </div>
                    </div>
                    <?php if($gradeTrends->isEmpty()): ?>
                        <div class="sa-empty">
                            <i class="fas fa-chart-line"></i>
                            <p>No grade history yet for this period.</p>
                        </div>
                    <?php else: ?>
                        <div class="sa-chart"><canvas id="gradeTrendsChart"></canvas></div>
                    <?php endif; ?>
                </section>

                <section class="sa-panel">
                    <div class="sa-panel-head">
                        <div>
                            <h2>Attendance</h2>
                            <p>
                                <?php if($latestAttendance): ?>
                                    Latest month: <?php echo e($latestAttendance['month']); ?> · <?php echo e(number_format($latestAttendance['percentage'], 1)); ?>% present
                                <?php else: ?>
                                    Present days for the selected period
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <?php if($attendanceRows->isEmpty()): ?>
                        <div class="sa-empty">
                            <i class="fas fa-user-check"></i>
                            <p>No attendance records yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="sa-chart"><canvas id="attendanceChart"></canvas></div>
                    <?php endif; ?>
                </section>
            </div>

            <section class="sa-panel">
                <div class="sa-panel-head">
                    <div>
                        <h2>Subject performance</h2>
                        <p>Average score per subject for the selected period.</p>
                    </div>
                </div>
                <?php if($subjects->isEmpty()): ?>
                    <div class="sa-empty">
                        <i class="fas fa-chart-bar"></i>
                        <p>No subject grades have been posted yet.</p>
            </div>
                <?php else: ?>
                    <div class="sa-chart sa-chart-wide"><canvas id="subjectPerformanceChart"></canvas></div>
                    <div class="sa-table-wrap">
                        <table class="sa-table">
                            <thead>
                                            <tr>
                                                <th>Subject</th>
                                    <th>Average</th>
                                    <th>Records</th>
                                    <th>Highest</th>
                                    <th>Lowest</th>
                                    <th>Standing</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php [$label, $badge] = $subjectLevel($subject['average_score']); ?>
                                                <tr>
                                                    <td>
                                            <strong><?php echo e($subject['subject']); ?></strong>
                                            <div class="sa-bar">
                                                <span style="width: <?php echo e(max(0, min(100, $subject['average_score']))); ?>%"></span>
                                            </div>
                                                    </td>
                                        <td><?php echo e(number_format($subject['average_score'], 1)); ?>%</td>
                                                    <td><?php echo e($subject['assignments_count']); ?></td>
                                        <td><?php echo e(number_format($subject['highest_score'], 0)); ?>%</td>
                                        <td><?php echo e(number_format($subject['lowest_score'], 0)); ?>%</td>
                                        <td><span class="sa-badge <?php echo e($badge); ?>"><?php echo e($label); ?></span></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
            </section>

            <?php if($gpaTrend->isNotEmpty()): ?>
                <section class="sa-panel">
                    <div class="sa-panel-head">
                        <div>
                            <h2>GPA history</h2>
                            <p>Official GPA by term.</p>
                        </div>
                    </div>
                    <div class="sa-gpa-list">
                        <?php $__currentLoopData = $gpaTrend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="sa-gpa-item">
                                <span><?php echo e($row['period']); ?></span>
                                <strong><?php echo e($row['gpa'] !== null ? number_format($row['gpa'], 2) : '—'); ?></strong>
                                <em><?php echo e($row['letter_grade'] ?? ''); ?></em>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="sa-panel">
                <div class="sa-panel-head">
                                <div>
                        <h2>Recent submitted work</h2>
                        <p>Latest activities from your classes.</p>
                                </div>
                            </div>
                <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="sa-activity">
                        <div>
                            <h3><?php echo e($activity['activity']); ?></h3>
                            <p><?php echo e($activity['subject']); ?> · <?php echo e($activity['submitted_at']); ?></p>
                        </div>
                        <div class="sa-activity-meta">
                            <?php
                                $status = $activity['status'] ?? 'pending';
                                $statusClass = $status === 'graded' ? 'is-excellent' : ($status === 'submitted' ? 'is-average' : 'is-low');
                            ?>
                            <span class="sa-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span>
                            <strong><?php echo e($activity['score'] !== '-' ? $activity['score'] : '—'); ?></strong>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="sa-empty">
                        <i class="fas fa-inbox"></i>
                        <p>No submitted activities to show yet.</p>
                </div>
                <?php endif; ?>
            </section>
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
.sa-field select {
    width: 100%; border: 1px solid var(--sa-border); border-radius: 10px; padding: .55rem .75rem;
    background: #fff; color: var(--sa-text); height: 42px;
}
.sa-btn-primary, .sa-btn-outline {
    display: inline-flex; align-items: center; gap: .45rem; border-radius: 10px; font-weight: 600;
    padding: .55rem 1rem; height: 42px; border: 1px solid transparent; cursor: pointer;
}
.sa-btn-primary { background: var(--sa-accent); color: #fff; }
.sa-btn-primary:hover { background: var(--sa-accent-dark); color: #fff; }
.sa-btn-outline { background: #fff; color: var(--sa-accent); border-color: #f0d3bb; }
.sa-btn-outline:hover { background: var(--sa-accent-soft); color: var(--sa-accent-dark); }
.sa-alert {
    display: flex; gap: .85rem; align-items: flex-start; background: #fff7ed; border: 1px solid #fed7aa;
    border-radius: var(--sa-radius); padding: .9rem 1rem; margin-bottom: 1.15rem; color: #9a3412;
}
.sa-alert i { margin-top: .15rem; }
.sa-alert p { margin: .15rem 0 0; font-size: .9rem; }
.sa-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.15rem; }
.sa-kpi {
    background: var(--sa-card); border: 1px solid var(--sa-border); border-radius: var(--sa-radius);
    box-shadow: var(--sa-shadow); padding: 1.05rem 1.1rem; display: flex; gap: .85rem; align-items: center;
}
.sa-kpi-icon {
    width: 42px; height: 42px; border-radius: 10px; background: var(--sa-accent-soft); color: var(--sa-accent);
    display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sa-kpi-icon-star { background: #fff7ed; color: #d97706; }
.sa-kpi-icon-is-excellent { background: #ecfdf5; color: #047857; }
.sa-kpi-icon-is-good { background: #eff6ff; color: #1d4ed8; }
.sa-kpi-icon-is-average { background: #fff7ed; color: #c2410c; }
.sa-kpi-icon-is-low { background: #fef2f2; color: #b91c1c; }
.sa-kpi-value { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
.sa-kpi-value-sm { font-size: 1.2rem; }
.sa-kpi-label { font-size: .9rem; font-weight: 600; margin-top: .15rem; }
.sa-kpi-meta { font-size: .75rem; color: var(--sa-muted); margin-top: .15rem; }
.sa-ring {
    --pct: 0;
    width: 52px; height: 52px; border-radius: 50%; flex-shrink: 0;
    background: conic-gradient(var(--sa-accent) calc(var(--pct) * 1%), #eceff3 0);
    display: grid; place-items: center;
}
.sa-ring span {
    width: 38px; height: 38px; border-radius: 50%; background: #fff; display: grid; place-items: center;
    font-size: .68rem; font-weight: 700; color: var(--sa-text);
}
.sa-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 1.15rem; margin-bottom: 1.15rem; }
.sa-panel {
    background: var(--sa-card); border: 1px solid var(--sa-border); border-radius: var(--sa-radius);
    box-shadow: var(--sa-shadow); padding: 1.15rem 1.2rem; margin-bottom: 1.15rem;
}
.sa-grid .sa-panel { margin-bottom: 0; }
.sa-panel-head { margin-bottom: .85rem; }
.sa-panel-head h2 { font-size: 1.05rem; font-weight: 700; margin: 0 0 .2rem; }
.sa-panel-head p { margin: 0; color: var(--sa-muted); font-size: .82rem; }
.sa-chart { height: 260px; position: relative; }
.sa-chart-wide { height: 220px; margin-bottom: 1rem; }
.sa-empty { text-align: center; padding: 2.2rem 1rem; color: var(--sa-muted); }
.sa-empty i { display: block; font-size: 1.6rem; color: #f0c9a6; margin-bottom: .5rem; }
.sa-empty p { margin: 0; }
.sa-table-wrap { overflow-x: auto; }
.sa-table { width: 100%; border-collapse: collapse; }
.sa-table th { font-size: .75rem; letter-spacing: .03em; text-transform: uppercase; color: var(--sa-muted); font-weight: 600; padding: .55rem 0; border-bottom: 1px solid var(--sa-border); }
.sa-table td { padding: .85rem 0; border-bottom: 1px solid #f1f3f5; vertical-align: middle; font-size: .92rem; }
.sa-table tr:last-child td { border-bottom: none; }
.sa-bar { height: 6px; background: #f1f3f5; border-radius: 999px; margin-top: .4rem; overflow: hidden; max-width: 220px; }
.sa-bar span { display: block; height: 100%; background: var(--sa-accent); border-radius: 999px; }
.sa-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: .2rem .6rem; font-size: .72rem; font-weight: 700; }
.sa-badge.is-excellent { background: #ecfdf5; color: #047857; }
.sa-badge.is-good { background: #eff6ff; color: #1d4ed8; }
.sa-badge.is-average { background: #fff7ed; color: #c2410c; }
.sa-badge.is-low { background: #fef2f2; color: #b91c1c; }
.sa-activity {
    display: flex; justify-content: space-between; gap: 1rem; align-items: center;
    padding: .85rem 0; border-bottom: 1px solid #f1f3f5;
}
.sa-activity:last-child { border-bottom: none; }
.sa-activity h3 { font-size: .95rem; margin: 0 0 .15rem; font-weight: 650; }
.sa-activity p { margin: 0; color: var(--sa-muted); font-size: .82rem; }
.sa-activity-meta { display: flex; align-items: center; gap: .65rem; }
.sa-gpa-list { display: grid; gap: .55rem; }
.sa-gpa-item { display: grid; grid-template-columns: 1fr auto auto; gap: .75rem; align-items: center; padding: .55rem 0; border-bottom: 1px solid #f1f3f5; }
.sa-gpa-item:last-child { border-bottom: none; }
.sa-gpa-item em { font-style: normal; color: var(--sa-muted); font-size: .85rem; }
@media (max-width: 1100px) {
    .sa-kpis, .sa-grid, .sa-filters { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 720px) {
    .sa-kpis, .sa-grid, .sa-filters { grid-template-columns: 1fr; }
    .sa-hero h1 { font-size: 1.35rem; }
    .sa-field-action { justify-self: stretch; }
    .sa-field-action .sa-btn-primary { width: 100%; justify-content: center; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const saPassing = <?php echo e((float) $passingPercentage); ?>;
const saTrends = <?php echo json_encode($gradeTrends->values(), 15, 512) ?>;
const saAttendance = <?php echo json_encode($attendanceRows->values(), 15, 512) ?>;
const saSubjects = <?php echo json_encode($subjects->values(), 15, 512) ?>;

function saChartDefaults() {
    Chart.defaults.font.family = 'inherit';
    Chart.defaults.color = '#6b7280';
    Chart.defaults.plugins.legend.display = false;
}

function applyFilters() {
    const params = new URLSearchParams();
    const year = document.getElementById('academic_year_filter').value;
    const semester = document.getElementById('semester_filter').value;
    if (year) params.append('academic_year_id', year);
    if (semester) params.append('semester_id', semester);
    window.location.href = <?php echo json_encode(route('analytics.student-dashboard'), 15, 512) ?> + (params.toString() ? '?' + params.toString() : '');
}

function exportReport() {
    const params = new URLSearchParams();
    const year = document.getElementById('academic_year_filter').value;
    const semester = document.getElementById('semester_filter').value;
    if (year) params.append('academic_year_id', year);
    if (semester) params.append('semester_id', semester);
    window.location.href = <?php echo json_encode(route('analytics.export-report'), 15, 512) ?> + (params.toString() ? '?' + params.toString() : '');
}

document.addEventListener('DOMContentLoaded', function () {
    saChartDefaults();

    const gradeCanvas = document.getElementById('gradeTrendsChart');
    if (gradeCanvas && saTrends.length) {
        new Chart(gradeCanvas.getContext('2d'), {
        type: 'line',
        data: {
                labels: saTrends.map((row) => row.period),
                datasets: [
                    {
                        label: 'Score',
                        data: saTrends.map((row) => row.score),
                        borderColor: '#e67e22',
                        backgroundColor: 'rgba(230, 126, 34, 0.12)',
                        tension: 0.35,
                        fill: true,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#e67e22'
                    },
                    {
                        label: 'Passing mark',
                        data: saTrends.map(() => saPassing),
                        borderColor: '#f59e0b',
                        borderDash: [6, 4],
                        pointRadius: 0,
                        borderWidth: 1.5,
                        fill: false
                    }
                ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
            scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: '#f1f3f5' }, ticks: { callback: (v) => v + '%' } },
                    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 6 } }
                }
            }
        });
    }

    const attendanceCanvas = document.getElementById('attendanceChart');
    if (attendanceCanvas && saAttendance.length) {
        new Chart(attendanceCanvas.getContext('2d'), {
        type: 'bar',
        data: {
                labels: saAttendance.map((row) => row.month),
            datasets: [{
                    label: 'Present',
                    data: saAttendance.map((row) => row.percentage),
                    backgroundColor: '#e67e22',
                    borderRadius: 8,
                    maxBarThickness: 42
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: '#f1f3f5' }, ticks: { callback: (v) => v + '%' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const subjectCanvas = document.getElementById('subjectPerformanceChart');
    if (subjectCanvas && saSubjects.length) {
        new Chart(subjectCanvas.getContext('2d'), {
        type: 'bar',
        data: {
                labels: saSubjects.map((row) => row.subject),
            datasets: [{
                    label: 'Average',
                    data: saSubjects.map((row) => row.average_score),
                    backgroundColor: ['#e67e22', '#f4a261', '#2a9d8f', '#e76f51', '#264653', '#e9c46a'],
                    borderRadius: 8,
                    maxBarThickness: 48
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: '#f1f3f5' }, ticks: { callback: (v) => v + '%' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?> 

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\analytics\student-dashboard.blade.php ENDPATH**/ ?>