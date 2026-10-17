<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid hub-page">
        <div class="hub-header">
            <div>
                <h3 class="hub-title">School Reports</h3>
                <p class="hub-lead">Reports for enrollment, grades, attendance, classes, and school records.</p>
            </div>
            <div class="hub-year">
                <span>Academic year</span>
                <strong><?php echo e($year->name ?? 'No current academic year'); ?></strong>
            </div>
        </div>

        <label class="hub-search">
            <span class="visually-hidden">Find a report</span>
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" id="reportSearch" placeholder="Find a report" autocomplete="off">
        </label>

        <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <section class="hub-card report-group">
                <h4><?php echo e($category); ?></h4>
                <div class="report-grid">
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="report-link"
                           href="<?php echo e($item['href'] ? route($item['href']) : route('reports.school.show', $item['key'])); ?>">
                            <strong><?php echo e($item['title']); ?></strong>
                            <span><?php echo e($item['description']); ?></span>
                            <?php if($item['priority'] === 'high'): ?>
                                <em>High priority</em>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="hub-empty">No reports are available for this account.</p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.hub-page { color: #292524; }
.hub-header { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; align-items: flex-end; margin-bottom: 1rem; }
.hub-title { margin: 0; font-size: 1.7rem; font-weight: 700; }
.hub-lead, .report-link span { color: #57534e; }
.hub-year { background: #fff; border: 1px solid #e7e5e4; border-radius: 12px; padding: .55rem .8rem; }
.hub-year span { display: block; font-size: .75rem; color: #78716c; }
.hub-card { background: #fff; border: 1px solid #e7e5e4; border-radius: 14px; padding: 1rem 1.1rem; margin-bottom: 1rem; }
.hub-search { display: flex; align-items: center; gap: .6rem; border: 1px solid #d6d3d1; border-radius: 12px; padding: .65rem .85rem; margin-bottom: 1rem; background: #fff; }
.hub-search input { border: 0; width: 100%; min-width: 0; outline: none; }
.report-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: .75rem; }
.report-link { display: block; border: 1px solid #e7e5e4; border-radius: 12px; padding: .8rem; color: inherit; text-decoration: none; background: #fafaf9; }
.report-link:hover { border-color: #fdba74; background: #fff7ed; }
.report-link strong { display: block; margin-bottom: .25rem; }
.report-link em { display: inline-block; margin-top: .45rem; font-style: normal; color: #c2410c; font-size: .75rem; font-weight: 700; }
.report-group.is-hidden { display: none; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('reportSearch').addEventListener('input', function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.report-group').forEach(function (group) {
        let visible = 0;
        group.querySelectorAll('.report-link').forEach(function (link) {
            const show = link.textContent.toLowerCase().includes(term);
            link.hidden = !show;
            if (show) visible++;
        });
        group.classList.toggle('is-hidden', visible === 0);
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/reports/school-index.blade.php ENDPATH**/ ?>