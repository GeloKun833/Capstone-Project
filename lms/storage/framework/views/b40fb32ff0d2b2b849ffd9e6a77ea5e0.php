<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Demo course outlines</h3>
                    <p class="text-muted mb-0">DEMO — NOT OFFICIAL. These samples are for testing only. They are not a DepEd curriculum, an approved school curriculum, or an official OPTLB document.</p>
                </div>
                <div class="col-auto">
                    <?php if($teacherView): ?>
                        <a href="<?php echo e(route('lessons.index')); ?>" class="btn btn-outline-secondary">My Lessons</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('curriculum.index')); ?>" class="btn btn-outline-secondary">Curriculum</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade => $outlines): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <h5 class="mt-3"><?php echo e($grade); ?></h5>
            <div class="row g-3 mb-3">
                <?php $__currentLoopData = $outlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $outline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-6 col-xl-4">
                        <a class="card h-100 text-decoration-none text-dark"
                            href="<?php echo e($teacherView ? route('teacher.course-outlines.show', $outline) : route('curriculum.demo.show', $outline)); ?>">
                            <div class="card-body">
                                <span class="badge bg-warning text-dark mb-2"><?php echo e($outline->document_type); ?></span>
                                <h6 class="mb-1"><?php echo e($outline->subject->subject_name ?? 'Subject'); ?></h6>
                                <p class="text-muted small mb-2"><?php echo e($outline->academicYear->name ?? '2026–2027'); ?> · <?php echo e($outline->status); ?></p>
                                <p class="small mb-0"><?php echo e($outline->topics[0]['title'] ?? 'Sample topics'); ?> and <?php echo e(max(count($outline->topics) - 1, 0)); ?> more</p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="alert alert-light border">No published demo outlines are available for your assigned subjects.</div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/curriculum/demo-index.blade.php ENDPATH**/ ?>