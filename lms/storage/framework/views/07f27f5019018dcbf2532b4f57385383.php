

<?php $__env->startSection('title', 'Enrollment Analytics'); ?>

<?php $__env->startSection('topbar-actions'); ?>
<a href="<?php echo e(route('enrollment.registrar.index')); ?>" class="ep-btn ep-btn-outline ep-btn-sm"><i class="fas fa-list"></i> Applications</a>
<a href="<?php echo e(route('enrollment.portal.index')); ?>" target="_blank" class="ep-btn ep-btn-primary ep-btn-sm"><i class="fas fa-external-link"></i> Portal</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $approvalRate = $stats['total_applications'] > 0 ? round(($stats['approved'] / $stats['total_applications']) * 100, 1) : 0;
    $thisMonth = \App\Models\EnrollmentApplication::whereMonth('created_at', now()->month)->count();
?>

<div class="mb-4">
    <h1 class="ep-page-title">Enrollment Analytics</h1>
    <p class="ep-page-subtitle">Track application volume, approval rates, and trends across grade levels.</p>
</div>

<div class="ep-stat-grid mb-4">
    <div class="ep-stat-card">
        <div class="ep-stat-icon blue"><i class="fas fa-file-alt"></i></div>
        <div class="ep-stat-value"><?php echo e($stats['total_applications']); ?></div>
        <div class="ep-stat-label">Total Applications</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon amber"><i class="fas fa-clock"></i></div>
        <div class="ep-stat-value"><?php echo e($stats['pending']); ?></div>
        <div class="ep-stat-label">Pending Review</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon blue"><i class="fas fa-search"></i></div>
        <div class="ep-stat-value"><?php echo e($stats['under_review']); ?></div>
        <div class="ep-stat-label">Under Review</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="ep-stat-value"><?php echo e($stats['approved']); ?></div>
        <div class="ep-stat-label">Approved</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="ep-stat-value"><?php echo e($stats['rejected']); ?></div>
        <div class="ep-stat-label">Rejected</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon amber"><i class="fas fa-file-circle-plus"></i></div>
        <div class="ep-stat-value"><?php echo e($stats['needs_documents']); ?></div>
        <div class="ep-stat-label">Needs Documents</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon green"><i class="fas fa-percentage"></i></div>
        <div class="ep-stat-value"><?php echo e($approvalRate); ?>%</div>
        <div class="ep-stat-label">Approval Rate</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon blue"><i class="fas fa-calendar"></i></div>
        <div class="ep-stat-value"><?php echo e($thisMonth); ?></div>
        <div class="ep-stat-label">This Month</div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="ep-card h-100">
            <div class="ep-card-header"><h3><i class="fas fa-chart-pie me-2"></i>Status Distribution</h3></div>
            <div class="ep-card-body">
                <div class="ep-chart-wrap"><canvas id="statusChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ep-card h-100">
            <div class="ep-card-header"><h3><i class="fas fa-chart-line me-2"></i>Applications by Month</h3></div>
            <div class="ep-card-body">
                <div class="ep-chart-wrap"><canvas id="monthlyChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header"><h3><i class="fas fa-layer-group me-2"></i>Applications by Grade Level</h3></div>
    <div class="ep-card-body p-0">
        <div class="ep-table-wrap">
            <table class="ep-table">
                <thead>
                    <tr>
                        <th>Grade Level</th>
                        <th>Total</th>
                        <th>Approved</th>
                        <th>Pending</th>
                        <th>Rejected</th>
                        <th>Approval Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $gradeLevels = \App\Models\EnrollmentApplication::select('grade_level_applying_for')
                            ->selectRaw('COUNT(*) as total')
                            ->selectRaw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved')
                            ->selectRaw('SUM(CASE WHEN status = "pending" OR status = "under_review" THEN 1 ELSE 0 END) as pending')
                            ->selectRaw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected')
                            ->groupBy('grade_level_applying_for')
                            ->orderBy('grade_level_applying_for')
                            ->get();
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($grade->grade_level_applying_for); ?></strong></td>
                            <td><?php echo e($grade->total); ?></td>
                            <td><span class="ep-chip ep-chip-approved"><?php echo e($grade->approved); ?></span></td>
                            <td><span class="ep-chip ep-chip-pending"><?php echo e($grade->pending); ?></span></td>
                            <td><span class="ep-chip ep-chip-rejected"><?php echo e($grade->rejected); ?></span></td>
                            <td>
                                <?php if($grade->total > 0): ?>
                                    <span class="ep-chip ep-chip-review"><?php echo e(round(($grade->approved / $grade->total) * 100, 1)); ?>%</span>
                                <?php else: ?>
                                    <span class="ep-chip ep-chip-docs">0%</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No grade level data yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="ep-card">
    <div class="ep-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="mb-0"><i class="fas fa-clock-rotate-left me-2"></i>Recent Applications</h3>
        <a href="<?php echo e(route('enrollment.registrar.index')); ?>" class="ep-btn ep-btn-outline ep-btn-sm">View All</a>
    </div>
    <div class="ep-card-body p-0">
        <div class="ep-table-wrap">
            <table class="ep-table">
                <thead>
                    <tr>
                        <th>Application #</th>
                        <th>Student</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $recentApplications = \App\Models\EnrollmentApplication::with('reviewer')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get();
                        $chipMap = ['pending'=>'ep-chip-pending','under_review'=>'ep-chip-review','approved'=>'ep-chip-approved','rejected'=>'ep-chip-rejected','needs_documents'=>'ep-chip-docs'];
                    ?>
                    <?php $__currentLoopData = $recentApplications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e($app->application_number); ?></strong></td>
                            <td><?php echo e($app->full_name); ?></td>
                            <td><?php echo e($app->grade_level_applying_for); ?></td>
                            <td><span class="ep-chip <?php echo e($chipMap[$app->status] ?? 'ep-chip-docs'); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $app->status))); ?></span></td>
                            <td><?php echo e($app->created_at->format('M d, Y')); ?></td>
                            <td>
                                <a href="<?php echo e(route('enrollment.registrar.show', $app->id)); ?>" class="ep-btn ep-btn-primary ep-btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.body.classList.contains('ep-dark');
    const gridColor = isDark ? 'rgba(148,163,184,.2)' : 'rgba(0,0,0,.06)';
    const textColor = isDark ? '#94A3B8' : '#64748B';

    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Under Review', 'Approved', 'Rejected', 'Needs Documents'],
            datasets: [{
                data: [
                    <?php echo e($stats['pending']); ?>,
                    <?php echo e($stats['under_review']); ?>,
                    <?php echo e($stats['approved']); ?>,
                    <?php echo e($stats['rejected']); ?>,
                    <?php echo e($stats['needs_documents']); ?>

                ],
                backgroundColor: ['#F59E0B', '#3B82F6', '#22C55E', '#EF4444', '#94A3B8'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { color: textColor, padding: 16 } } }
        }
    });

    <?php
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = \App\Models\EnrollmentApplication::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyData[] = ['month' => $date->format('M Y'), 'count' => $count];
        }
    ?>

    new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: [<?php $__currentLoopData = $monthlyData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>'<?php echo e($data['month']); ?>',<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>],
            datasets: [{
                label: 'Applications',
                data: [<?php $__currentLoopData = $monthlyData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($data['count']); ?>,<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>],
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37,99,235,.12)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#2563EB'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor } },
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 } }
            }
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.enrollment-registrar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\enrollment\registrar\statistics.blade.php ENDPATH**/ ?>