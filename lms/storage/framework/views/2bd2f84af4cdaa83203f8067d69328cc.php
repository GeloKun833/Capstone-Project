<?php $__env->startSection('content'); ?>
<?php
    $rankGrade = function ($grade) {
        $label = strtolower(trim((string) $grade));
        if (str_contains($label, 'nursery')) return 0;
        if (str_contains($label, 'kinder')) return 1;
        if (preg_match('/(\d+)/', $label, $match)) return 10 + (int) $match[1];
        return 80;
    };
    $classes = [];
    $others = [];
    foreach ($summary as $item) {
        if (($item['type'] ?? '') === 'schedule') {
            $classes[$item['grade'] ?? 'Grade'][$item['section'] ?? 'Section'][] = $item;
        } else {
            $others[] = $item;
        }
    }
    uksort($classes, fn ($a, $b) => $rankGrade($a) <=> $rankGrade($b) ?: strnatcasecmp($a, $b));
?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page xfer-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Review transfer</h3>
                    <p class="dir-subtitle">Confirm who receives each assignment. <?php echo e($user->name); ?> will then be marked Inactive.</p>
                </div>
            </div>
        </div>

        <div class="xfer-identity">
            <div>
                <span class="xfer-kicker">Leaving teacher</span>
                <strong><?php echo e($user->name); ?></strong>
            </div>
            <div>
                <span class="xfer-kicker">Academic year</span>
                <strong><?php echo e($year->displayName()); ?></strong>
            </div>
            <div class="xfer-counts">
                <span><?php echo e(count($summary)); ?> <?php echo e(count($summary) === 1 ? 'assignment' : 'assignments'); ?></span>
            </div>
        </div>

        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade => $sections): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <h4 class="xfer-heading"><?php echo e($grade); ?></h4>
            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionName => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="xfer-card">
                    <header class="xfer-card-head">
                        <div>
                            <strong><?php echo e($sectionName); ?></strong>
                            <span><?php echo e(count($items)); ?> <?php echo e(count($items) === 1 ? 'class' : 'classes'); ?></span>
                        </div>
                    </header>
                    <ul class="xfer-list">
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="xfer-row">
                                <div>
                                    <div class="xfer-subject"><?php echo e($item['subject'] ?? $item['label']); ?></div>
                                    <?php if(!empty($item['schedule']) && $item['schedule'] !== '—'): ?>
                                        <span class="xfer-meta"><?php echo e($item['schedule']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="xfer-receiver"><?php echo e($item['teacher']); ?></div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($others !== []): ?>
            <h4 class="xfer-heading">Other assignments</h4>
            <article class="xfer-card">
                <ul class="xfer-list">
                    <?php $__currentLoopData = $others; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="xfer-row">
                            <div>
                                <div class="xfer-subject"><?php echo e($item['subject'] ?? $item['label']); ?></div>
                                <span class="xfer-meta"><?php echo e(trim(($item['grade'] ?? '').' '.($item['section'] ?? ''))); ?></span>
                            </div>
                            <div class="xfer-receiver"><?php echo e($item['teacher']); ?></div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </article>
        <?php endif; ?>

        <div class="alert alert-info">
            Earlier academic years stay unchanged. Only <?php echo e($year->displayName()); ?> moves to the teachers shown above.
        </div>

        <form method="POST" action="<?php echo e(route('teacher.deactivate.confirm', $user->user_id)); ?>">
            <?php echo csrf_field(); ?>
            <?php $__currentLoopData = $replacements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $teacherId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <input type="hidden" name="replacements[<?php echo e($key); ?>]" value="<?php echo e($teacherId); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <div class="xfer-actions">
                <a href="<?php echo e(route('teacher.deactivate.transfer', $user->user_id)); ?>" class="btn btn-outline-secondary dir-btn">Back</a>
                <button type="submit" class="btn btn-danger dir-btn">Confirm transfer and deactivate</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20261013a">
<style>
.xfer-identity {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem 2rem;
    align-items: flex-end;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    padding: 1rem 1.15rem;
    margin-bottom: 1rem;
}
.xfer-kicker {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 0.15rem;
}
.xfer-identity strong { font-size: 1.05rem; color: #111827; }
.xfer-counts { margin-left: auto; }
.xfer-counts span {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    padding: 0.35rem 0.75rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: #374151;
}
.xfer-heading { margin: 1.1rem 0 0.55rem; font-size: 0.95rem; color: #111827; }
.xfer-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    margin-bottom: 0.85rem;
    overflow: hidden;
}
.xfer-card-head {
    padding: 0.9rem 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}
.xfer-card-head strong { display: block; }
.xfer-card-head span { color: #6b7280; font-size: 0.82rem; }
.xfer-list { list-style: none; margin: 0; padding: 0; }
.xfer-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: center;
    padding: 0.85rem 1rem;
    border-top: 1px solid #eef2f7;
}
.xfer-list .xfer-row:first-child { border-top: 0; }
.xfer-subject { font-weight: 700; color: #111827; }
.xfer-meta {
    display: inline-block;
    margin-top: 0.2rem;
    color: #4b5563;
    background: #f3f4f6;
    border-radius: 999px;
    padding: 0.15rem 0.55rem;
    font-size: 0.78rem;
}
.xfer-receiver { font-weight: 700; color: #314dcc; }
.xfer-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
@media (max-width: 720px) {
    .xfer-row { grid-template-columns: 1fr; }
    .xfer-counts { margin-left: 0; }
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/usermanagement/teacher_transfer_confirm.blade.php ENDPATH**/ ?>