
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        <?php if($user->role_name === 'Admin'): ?>
            <?php echo $__env->make('partials.admin_dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php elseif($user->role_name === 'Teacher'): ?>
            <?php echo $__env->make('partials.teacher_dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php elseif($user->role_name === 'Student'): ?>
            <?php echo $__env->make('partials.student_dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php elseif($user->role_name === 'Parent'): ?>
            <?php echo $__env->make('partials.parent_dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php elseif($user->role_name === 'Registrar'): ?>
            <?php echo $__env->make('partials.registrar_dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem; opacity: 0.6;"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-3">Role Not Recognized</h4>
                            <p class="text-muted mb-4">Your user role is not recognized. Please contact the administrator.</p>
                            <a href="<?php echo e(route('home')); ?>" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Return to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php echo $__env->make('partials.dashboard_contact_cards', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>
<?php if($user->role_name === 'Admin' && isset($admin['performanceData'], $admin['studentsChartData'])): ?>
<?php
    $adminPerformanceJson = $admin['performanceData'] ?? [
        'labels' => [],
        'averages' => [],
        'mode' => 'empty',
    ];
    $adminDistributionJson = $admin['studentsChartData'] ?? [
        'labels' => [],
        'totals' => [],
        'boysData' => [],
        'girlsData' => [],
    ];
?>
<script>
(function () {
    'use strict';

    if (typeof ApexCharts === 'undefined') {
        console.warn('ApexCharts is not loaded; admin dashboard charts skipped.');
        return;
    }

    var performance = <?php echo json_encode($adminPerformanceJson, 15, 512) ?>;
    var distribution = <?php echo json_encode($adminDistributionJson, 15, 512) ?>;

    function hideSkeleton(id) {
        var el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
        }
    }

    var performanceEl = document.querySelector('#academic-performance-chart');
    if (performanceEl && performance.labels && performance.labels.length) {
        new ApexCharts(performanceEl, {
            chart: {
                height: 320,
                type: 'bar',
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            series: [{
                name: 'Average Grade',
                data: performance.averages || []
            }],
            colors: ['#ea580c'],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '48%',
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return (Number(val) || 0).toFixed(1) + '%';
                },
                offsetY: -18,
                style: {
                    fontSize: '11px',
                    colors: ['#64748b']
                }
            },
            xaxis: {
                categories: performance.labels,
                labels: {
                    rotate: -35,
                    style: { colors: '#64748b', fontSize: '12px' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                max: 100,
                title: {
                    text: 'Average Grade (%)',
                    style: { color: '#94a3b8', fontSize: '12px', fontWeight: 500 }
                },
                labels: {
                    style: { colors: '#94a3b8', fontSize: '12px' }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                padding: { top: 10 }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return (Number(val) || 0).toFixed(1) + '%';
                    }
                }
            },
            legend: { show: false }
        }).render().then(function () {
            hideSkeleton('academic-performance-skeleton');
        });
    } else {
        hideSkeleton('academic-performance-skeleton');
    }

    var studentsEl = document.querySelector('#student-distribution-chart');
    if (studentsEl && distribution.labels && distribution.labels.length) {
        var totals = distribution.totals && distribution.totals.length
            ? distribution.totals
            : (distribution.labels || []).map(function (_, i) {
                return (Number((distribution.boysData || [])[i]) || 0) + (Number((distribution.girlsData || [])[i]) || 0);
            });

        new ApexCharts(studentsEl, {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            series: [{
                name: 'Students',
                data: totals
            }],
            colors: ['#0284c7'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 7,
                    barHeight: '62%',
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.floor(val || 0);
                },
                offsetX: 18,
                style: {
                    fontSize: '12px',
                    colors: ['#475569']
                }
            },
            xaxis: {
                categories: distribution.labels,
                labels: {
                    formatter: function (val) {
                        return Math.floor(val || 0);
                    },
                    style: { colors: '#94a3b8', fontSize: '12px' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#475569', fontSize: '12px', fontWeight: 500 }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } }
            },
            tooltip: {
                y: {
                    formatter: function (val, opts) {
                        var i = opts.dataPointIndex;
                        var boys = Number((distribution.boysData || [])[i]) || 0;
                        var girls = Number((distribution.girlsData || [])[i]) || 0;
                        return (val || 0) + ' students (Boys: ' + boys + ', Girls: ' + girls + ')';
                    }
                }
            },
            legend: { show: false }
        }).render().then(function () {
            hideSkeleton('student-distribution-skeleton');
        });
    } else {
        hideSkeleton('student-distribution-skeleton');
    }
})();
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/dashboard.blade.php ENDPATH**/ ?>