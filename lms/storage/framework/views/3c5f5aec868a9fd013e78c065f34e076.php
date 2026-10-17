<?php $__env->startSection('content'); ?>
<?php
    $back = $teacherView ? route('teacher.course-outlines.index') : route('curriculum.index');
    $fileRoute = $teacherView ? 'teacher.course-outlines.material' : 'curriculum.demo.material';
?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo e($outline->title); ?></h3>
                    <p class="mb-0"><span class="badge bg-warning text-dark"><?php echo e($outline->document_type); ?></span></p>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e($back); ?>" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if(str_contains($outline->document_type, 'DEMO')): ?>
            <div class="alert alert-warning">
                Demonstration only. This page is not an official DepEd curriculum, an approved school curriculum, or an official OPTLB document.
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Academic year:</strong> <?php echo e($outline->academicYear->name ?? '—'); ?></p>
                <p><strong>Grade:</strong> <?php echo e($outline->grade_level); ?></p>
                <p><strong>Subject:</strong> <?php echo e($outline->subject->subject_name ?? '—'); ?></p>
                <p><strong>Description:</strong> <?php echo e($outline->description); ?></p>
                <p><strong>Learning objectives:</strong> <?php echo e($outline->learning_objectives); ?></p>
                <p class="mb-0"><strong>Sequence:</strong> <?php echo e($outline->sequence_note); ?></p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="card-title mb-0">Topics</h5></div>
            <div class="card-body">
                <?php $__currentLoopData = $outline->topics ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-3">
                        <h6>Unit <?php echo e($topic['unit'] ?? ''); ?>: <?php echo e($topic['title'] ?? ''); ?></h6>
                        <p class="mb-1"><?php echo e($topic['competencies'] ?? ''); ?></p>
                        <p class="mb-1"><?php echo e($topic['activities'] ?? ''); ?></p>
                        <p class="text-muted mb-0"><?php echo e($topic['duration'] ?? ''); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <p class="mb-0"><strong>Assessment:</strong> <?php echo e($outline->assessment_methods); ?></p>
            </div>
        </div>

        <?php if($outline->optlbRecord): ?>
            <div class="card mb-3" id="optlb">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0"><?php echo e($outline->optlbRecord->title); ?></h5>
                    <span class="badge bg-warning text-dark"><?php echo e($outline->optlbRecord->demo_label); ?></span>
                </div>
                <div class="card-body">
                    <p><?php echo e($outline->optlbRecord->learning_objectives); ?></p>
                    <p style="white-space: pre-line;"><?php echo e($outline->optlbRecord->topic_sequence); ?></p>
                    <p><?php echo e($outline->optlbRecord->suggested_activities); ?></p>
                    <p><?php echo e($outline->optlbRecord->assessment); ?></p>
                    <p class="mb-0"><?php echo e($outline->optlbRecord->references); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Demo materials</h5></div>
            <div class="card-body">
                <?php $__empty_1 = true; $__currentLoopData = $outline->materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-3">
                        <p class="mb-1"><strong><?php echo e($material->file_name); ?></strong> · <?php echo e($material->demo_label); ?></p>
                        <iframe src="<?php echo e(route($fileRoute, $material)); ?>" title="<?php echo e($material->file_name); ?>" style="width:100%;height:420px;border:1px solid #e5e7eb;border-radius:8px;"></iframe>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0">No demo file is attached.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/curriculum/demo-show.blade.php ENDPATH**/ ?>