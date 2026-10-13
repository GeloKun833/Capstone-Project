<?php $__env->startSection('content'); ?>
<?php
    $rankGrade = function ($grade) {
        $label = strtolower(trim((string) $grade));
        if (str_contains($label, 'nursery')) return 0;
        if (str_contains($label, 'kinder')) return 1;
        if (preg_match('/(\d+)/', $label, $match)) return 10 + (int) $match[1];
        return 80;
    };

    $byType = ['schedule' => [], 'subject' => [], 'section' => [], 'grade' => [], 'adviser' => []];
    foreach ($rows as $row) {
        $type = $row['type'] ?? 'schedule';
        $byType[$type][] = $row;
    }

    $panels = [];
    $put = function (string $grade, string $section, array $item) use (&$panels) {
        $grade = $grade !== '' && $grade !== '—' ? $grade : 'Unassigned grade';
        $section = $section !== '' && $section !== '—' ? $section : 'Unassigned section';
        $key = $grade.'|'.$section;
        if (! isset($panels[$key])) {
            $panels[$key] = ['key' => $key, 'grade' => $grade, 'section' => $section, 'items' => []];
        }
        $panels[$key]['items'][] = $item;
    };

    foreach ($byType['schedule'] as $row) {
        $put($row['grade'], $row['section'], ['row' => $row, 'primary' => $row['subject'], 'meta' => $row['schedule']]);
    }
    foreach ($byType['section'] as $row) {
        $put($row['grade'], $row['section'], ['row' => $row, 'primary' => 'Section assignment', 'meta' => 'No weekly class']);
    }
    foreach ($byType['adviser'] as $row) {
        $put($row['grade'], $row['section'], ['row' => $row, 'primary' => 'Section adviser', 'meta' => 'Adviser']);
    }

    uasort($panels, function ($a, $b) use ($rankGrade) {
        return $rankGrade($a['grade']) <=> $rankGrade($b['grade'])
            ?: strnatcasecmp($a['section'], $b['section']);
    });

    $otherItems = [];
    foreach ($byType['subject'] as $row) {
        $otherItems[] = ['row' => $row, 'primary' => $row['subject'], 'meta' => $row['grade'] !== '—' ? $row['grade'].' · no weekly class' : 'No weekly class'];
    }
    foreach ($byType['grade'] as $row) {
        $otherItems[] = ['row' => $row, 'primary' => $row['grade'], 'meta' => 'Whole grade'];
    }

    $scheduleCount = count($byType['schedule']);
    $otherCount = count($rows) - $scheduleCount;
?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page xfer-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Transfer assignments</h3>
                    <p class="dir-subtitle">Move every current-year duty to another teacher before this account is deactivated.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(url('view/user/edit/'.$user->user_id)); ?>">Edit Teacher</a></li>
                        <li class="breadcrumb-item active">Transfer</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="xfer-identity">
            <div>
                <span class="xfer-kicker">Leaving teacher</span>
                <strong><?php echo e($teacher->full_name ?: $user->name); ?></strong>
            </div>
            <div>
                <span class="xfer-kicker">Academic year</span>
                <strong><?php echo e($year->displayName()); ?></strong>
            </div>
            <div class="xfer-counts">
                <span><?php echo e($scheduleCount); ?> <?php echo e($scheduleCount === 1 ? 'class' : 'classes'); ?></span>
                <?php if($otherCount): ?>
                    <span><?php echo e($otherCount); ?> other <?php echo e($otherCount === 1 ? 'assignment' : 'assignments'); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if(session('error')): ?>
            <div class="alert alert-warning"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php if($blocked): ?>
            <div class="alert alert-danger mb-3">
                No active teacher can take over every assignment. Activate or add another teacher, then return to this page.
            </div>
        <?php else: ?>
            <p class="xfer-lead">Choose a section assigned to this teacher. Only that section’s subjects and class times are shown. Historical years stay with <?php echo e($teacher->full_name ?: $user->name); ?>.</p>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('teacher.deactivate.review', $user->user_id)); ?>" id="transferForm">
            <?php echo csrf_field(); ?>

            <div class="xfer-picker">
                <label for="xferSectionPick">Section assigned to this teacher</label>
                <select id="xferSectionPick" class="form-control">
                    <option value="">Choose a section</option>
                    <?php $__currentLoopData = $panels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $panel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($panel['key']); ?>"><?php echo e($panel['grade']); ?> – <?php echo e($panel['section']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($otherItems !== []): ?>
                        <option value="__other">Other assignments</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="xfer-empty" id="xferEmpty">
                <strong>Choose a section</strong>
                <p>The subjects and class times assigned to that section will appear here.</p>
            </div>

            <?php $__currentLoopData = $panels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $panel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="xfer-card is-hidden" data-panel="<?php echo e($panel['key']); ?>" data-section-card>
                    <header class="xfer-card-head">
                        <div>
                            <strong><?php echo e($panel['grade']); ?> – <?php echo e($panel['section']); ?></strong>
                            <span><?php echo e(count($panel['items'])); ?> <?php echo e(count($panel['items']) === 1 ? 'assignment' : 'assignments'); ?></span>
                        </div>
                        <?php if (! ($blocked)): ?>
                            <label class="xfer-apply">
                                <span>Same teacher for this section</span>
                                <select class="form-control" data-apply-section>
                                    <option value="">Choose to fill this section</option>
                                    <?php $__currentLoopData = $panel['items'][0]['row']['choices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $choice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(! $choice['eligible']) continue; ?>
                                        <option value="<?php echo e($choice['id']); ?>"><?php echo e($choice['name']); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                        <?php endif; ?>
                    </header>
                    <ul class="xfer-list">
                        <?php $__currentLoopData = $panel['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('usermanagement.partials.transfer-row', ['row' => $item['row'], 'blocked' => $blocked, 'primary' => $item['primary'], 'meta' => $item['meta']], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($otherItems !== []): ?>
                <article class="xfer-card is-hidden" data-panel="__other">
                    <header class="xfer-card-head">
                        <div>
                            <strong>Other assignments</strong>
                            <span>Subjects and grade levels that are not tied to one section schedule.</span>
                        </div>
                    </header>
                    <ul class="xfer-list">
                        <?php $__currentLoopData = $otherItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('usermanagement.partials.transfer-row', ['row' => $item['row'], 'blocked' => $blocked, 'primary' => $item['primary'], 'meta' => $item['meta']], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </article>
            <?php endif; ?>

            <div class="xfer-actions">
                <a href="<?php echo e(url('view/user/edit/'.$user->user_id)); ?>" class="btn btn-outline-secondary dir-btn">Cancel</a>
                <?php if (! ($blocked)): ?>
                    <button type="submit" class="btn btn-primary dir-btn">Review transfer</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20261013a">
<style>
.xfer-page { padding-bottom: 2rem; }
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
.xfer-counts { display: flex; gap: 0.5rem; margin-left: auto; flex-wrap: wrap; }
.xfer-counts span {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    padding: 0.35rem 0.75rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: #374151;
}
.xfer-lead { color: #6b7280; margin: 0 0 1rem; }
.xfer-picker {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    padding: 1rem 1.15rem;
    margin-bottom: 1rem;
    max-width: 460px;
}
.xfer-picker label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 0.35rem;
}
.xfer-empty {
    background: #fff;
    border: 1px dashed #d1d5db;
    border-radius: 16px;
    padding: 1.5rem 1.15rem;
    margin-bottom: 1rem;
    color: #6b7280;
}
.xfer-empty strong { display: block; color: #111827; margin-bottom: 0.25rem; }
.xfer-empty p { margin: 0; }
.xfer-card.is-hidden, .xfer-empty.is-hidden { display: none; }
.xfer-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    margin-bottom: 0.85rem;
    overflow: hidden;
}
.xfer-card-head {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
    padding: 0.9rem 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}
.xfer-card-head strong { display: block; color: #111827; }
.xfer-card-head span { color: #6b7280; font-size: 0.82rem; }
.xfer-apply { margin: 0; min-width: min(100%, 280px); }
.xfer-apply span {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 0.25rem;
}
.xfer-list { list-style: none; margin: 0; padding: 0; }
.xfer-row {
    display: grid;
    grid-template-columns: minmax(180px, 1fr) minmax(220px, 320px);
    gap: 0.75rem 1rem;
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
.xfer-page .form-control {
    height: 45px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}
.xfer-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
}
@media (max-width: 720px) {
    .xfer-row { grid-template-columns: 1fr; }
    .xfer-counts { margin-left: 0; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('[data-apply-section]').forEach(function (applySelect) {
    applySelect.addEventListener('change', function () {
        var teacherId = applySelect.value;
        if (!teacherId) return;
        var card = applySelect.closest('[data-section-card]');
        card.querySelectorAll('select[name^="replacements"]').forEach(function (select) {
            var option = select.querySelector('option[value="' + CSS.escape(teacherId) + '"]:not([disabled])');
            if (option) select.value = teacherId;
        });
    });
});

(function () {
    var pick = document.getElementById('xferSectionPick');
    var empty = document.getElementById('xferEmpty');
    var form = document.getElementById('transferForm');
    if (!pick) return;

    function show(key) {
        document.querySelectorAll('[data-panel]').forEach(function (panel) {
            panel.classList.toggle('is-hidden', panel.getAttribute('data-panel') !== key);
        });
        if (empty) empty.classList.toggle('is-hidden', key !== '');
    }

    pick.addEventListener('change', function () {
        show(pick.value);
    });

    var errorRow = document.querySelector('.xfer-row .text-danger');
    if (errorRow) {
        var panel = errorRow.closest('[data-panel]');
        if (panel) pick.value = panel.getAttribute('data-panel');
    } else if (pick.options.length === 2) {
        pick.selectedIndex = 1;
    }
    show(pick.value);

    if (form) {
        form.addEventListener('submit', function (event) {
            var missing = Array.prototype.find.call(form.querySelectorAll('select[name^="replacements"]'), function (select) {
                return !select.disabled && !select.value;
            });
            if (!missing) return;
            event.preventDefault();
            var owner = missing.closest('[data-panel]');
            if (owner) {
                pick.value = owner.getAttribute('data-panel');
                show(pick.value);
            }
            missing.focus();
        });
    }
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/usermanagement/teacher_transfer.blade.php ENDPATH**/ ?>