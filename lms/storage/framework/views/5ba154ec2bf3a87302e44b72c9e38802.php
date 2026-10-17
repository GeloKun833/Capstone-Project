<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create course outline and OPTLB</h3>
                    <p class="text-muted mb-0"><?php echo e($curriculum->grade_level); ?> · <?php echo e($year->name); ?></p>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('curriculum.index')); ?>" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">Check the highlighted fields and save again.</div>
        <?php endif; ?>

        <?php if($subjects->isEmpty()): ?>
            <div class="alert alert-light border">Add subjects for <?php echo e($curriculum->grade_level); ?> before creating a course outline.</div>
        <?php else: ?>
            <form method="POST" action="<?php echo e(route('curriculum.demo.store', $curriculum)); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="card mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Course outline</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="subject_id">Subject</label>
                                <select name="subject_id" id="subject_id" class="form-control" required>
                                    <option value="">Select a subject</option>
                                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($subject->id); ?>" <?php if(old('subject_id') == $subject->id): echo 'selected'; endif; ?>><?php echo e($subject->subject_name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="title">Course outline title</label>
                                <input type="text" name="title" id="title" class="form-control" value="<?php echo e(old('title')); ?>" required>
                                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="description">Course description</label>
                            <textarea name="description" id="description" rows="3" class="form-control" required><?php echo e(old('description')); ?></textarea>
                            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="learning_objectives">Learning objectives</label>
                            <textarea name="learning_objectives" id="learning_objectives" rows="3" class="form-control" required><?php echo e(old('learning_objectives')); ?></textarea>
                            <?php $__errorArgs = ['learning_objectives'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="topic_1">Topic unit 1</label>
                                <input type="text" name="topic_1" id="topic_1" class="form-control" value="<?php echo e(old('topic_1')); ?>" required>
                                <?php $__errorArgs = ['topic_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="topic_2">Topic unit 2</label>
                                <input type="text" name="topic_2" id="topic_2" class="form-control" value="<?php echo e(old('topic_2')); ?>" required>
                                <?php $__errorArgs = ['topic_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="topic_3">Topic unit 3</label>
                                <input type="text" name="topic_3" id="topic_3" class="form-control" value="<?php echo e(old('topic_3')); ?>" required>
                                <?php $__errorArgs = ['topic_3'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="assessment_methods">Assessment methods</label>
                            <textarea name="assessment_methods" id="assessment_methods" rows="2" class="form-control" required><?php echo e(old('assessment_methods')); ?></textarea>
                            <?php $__errorArgs = ['assessment_methods'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="outline_file">Course outline file</label>
                            <input type="file" name="outline_file" id="outline_file" class="form-control" accept="application/pdf">
                            <div class="form-text">Optional PDF. A file is attached automatically when this is left empty.</div>
                            <?php $__errorArgs = ['outline_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">OPTLB</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="optlb_title">OPTLB title</label>
                            <input type="text" name="optlb_title" id="optlb_title" class="form-control" value="<?php echo e(old('optlb_title')); ?>" required>
                            <?php $__errorArgs = ['optlb_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_objectives">Learning objectives</label>
                            <textarea name="optlb_objectives" id="optlb_objectives" rows="3" class="form-control" required><?php echo e(old('optlb_objectives')); ?></textarea>
                            <?php $__errorArgs = ['optlb_objectives'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_sequence">Topic or lesson sequence</label>
                            <textarea name="optlb_sequence" id="optlb_sequence" rows="3" class="form-control" required><?php echo e(old('optlb_sequence')); ?></textarea>
                            <?php $__errorArgs = ['optlb_sequence'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_activities">Suggested learning activities</label>
                            <textarea name="optlb_activities" id="optlb_activities" rows="3" class="form-control" required><?php echo e(old('optlb_activities')); ?></textarea>
                            <?php $__errorArgs = ['optlb_activities'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_assessment">Assessment or evaluation</label>
                            <textarea name="optlb_assessment" id="optlb_assessment" rows="2" class="form-control" required><?php echo e(old('optlb_assessment')); ?></textarea>
                            <?php $__errorArgs = ['optlb_assessment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_references">References or learning resources</label>
                            <textarea name="optlb_references" id="optlb_references" rows="2" class="form-control" required><?php echo e(old('optlb_references')); ?></textarea>
                            <?php $__errorArgs = ['optlb_references'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="optlb_file">OPTLB file</label>
                            <input type="file" name="optlb_file" id="optlb_file" class="form-control" accept="application/pdf">
                            <div class="form-text">Optional PDF. A file is attached automatically when this is left empty.</div>
                            <?php $__errorArgs = ['optlb_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save course outline and OPTLB</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/curriculum/demo-create-outline.blade.php ENDPATH**/ ?>