

<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page dir-report-view">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <a class="dir-report-back" href="<?php echo e(route('reports.index')); ?>">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Reports &amp; Documents
                    </a>
                    <h3 class="page-title mb-1"><?php echo e($report['title']); ?></h3>
                    <p class="dir-subtitle"><?php echo e($note); ?></p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('reports.index')); ?>">Reports</a></li>
                        <li class="breadcrumb-item active">View Report</li>
                    </ul>
                    <div class="dir-report-year">
                        <span>Academic year</span>
                        <strong><?php echo e($year->name ?? 'No current academic year'); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php if($loadError): ?>
            <div class="alert alert-danger d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><?php echo e($note); ?></span>
                <a class="btn btn-sm btn-outline-danger" href="<?php echo e(request()->fullUrl()); ?>">Try again</a>
            </div>
        <?php endif; ?>

        <?php if($summary): ?>
            <div class="dir-report-kpis">
                <?php $__currentLoopData = $summary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="dir-report-kpi">
                        <span><?php echo e($label); ?></span>
                        <strong><?php echo e(is_numeric($value) ? number_format($value) : $value); ?></strong>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="GET" action="<?php echo e(route('reports.school.show', $report['key'])); ?>" class="dir-card dir-report-toolbar">
            <div class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="form-label" for="reportSearch">Search</label>
                    <input id="reportSearch" class="form-control" type="search" name="q" value="<?php echo e($search); ?>" maxlength="80" placeholder="Search names, grades, or sections">
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label" for="reportSection">Section</label>
                    <select id="reportSection" class="form-select" name="section_id">
                        <option value="">All sections</option>
                        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($section->id); ?>" <?php if((int) $sectionId === (int) $section->id): echo 'selected'; endif; ?>>
                                <?php echo e($section->grade_level ? $section->grade_level.' — ' : ''); ?><?php echo e($section->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6 col-lg-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary dir-btn">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                    <?php if (! ($loadError)): ?>
                        <?php
                            $downloadQuery = array_filter(
                                request()->only(['q', 'section_id']),
                                fn ($value) => $value !== null && $value !== ''
                            );
                        ?>
                        <a class="btn btn-outline-primary dir-btn" href="<?php echo e(route('reports.school.download', ['report' => $report['key']])); ?><?php echo e($downloadQuery ? '?'.http_build_query($downloadQuery) : ''); ?>">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    <?php endif; ?>
                    <?php if($search !== '' || $sectionId): ?>
                        <a class="btn btn-outline-secondary dir-btn" href="<?php echo e(route('reports.school.show', $report['key'])); ?>">Clear</a>
                    <?php endif; ?>
                </div>
            </div>
            <p class="dir-report-meta">
                <?php if($rows->total()): ?>
                    Showing <?php echo e(number_format($rows->firstItem())); ?>–<?php echo e(number_format($rows->lastItem())); ?> of <?php echo e(number_format($rows->total())); ?> <?php echo e($rows->total() === 1 ? 'row' : 'rows'); ?>

                <?php else: ?>
                    0 rows
                <?php endif; ?>
            </p>
        </form>

        <section class="dir-card">
            <div class="table-responsive">
                <table class="table dir-table mb-0">
                    <thead>
                        <tr>
                            <?php $__empty_1 = true; $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <th><?php echo e($column); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <th>Report</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td><?php echo e($cell); ?></td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e(max(1, count($columns))); ?>">
                                    <div class="dir-empty">
                                        <i class="fas fa-folder-open d-block"></i>
                                        <?php if($search !== '' || $sectionId): ?>
                                            <p class="mb-2">No rows match this search.</p>
                                            <a class="btn btn-outline-secondary dir-btn" href="<?php echo e(route('reports.school.show', $report['key'])); ?>">Clear filters</a>
                                        <?php else: ?>
                                            <p class="mb-0">No records for this report.</p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($rows->hasPages()): ?>
                <div class="dir-report-pager">
                    <?php echo e($rows->links('pagination.bootstrap-limited')); ?>

                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20261018a">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('reportSection')?.addEventListener('change', function () {
    this.form.submit();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/reports/school-show.blade.php ENDPATH**/ ?>