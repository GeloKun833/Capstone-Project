
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <h3 class="page-title">Consultation Requests</h3>
            <p class="text-muted mb-0">Review student requests and schedule or reschedule appointments.</p>
        </div>

        <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
        <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>

        <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0"><?php echo e($consultation->student->full_name); ?> · <?php echo e($consultation->subject->subject_name); ?></h5>
                        <small class="text-muted">Requested <?php echo e($consultation->requested_start_at->format('M j, Y g:i A')); ?>–<?php echo e($consultation->requested_end_at->format('g:i A')); ?></small>
                    </div>
                    <span class="badge bg-<?php echo e($consultation->status === 'approved' ? 'success' : ($consultation->status === 'pending' ? 'warning' : 'secondary')); ?>"><?php echo e(ucfirst($consultation->status)); ?></span>
                </div>
                <div class="card-body">
                    <?php if($consultation->student_message): ?><p><strong>Student note:</strong> <?php echo e($consultation->student_message); ?></p><?php endif; ?>
                    <?php if($consultation->teacher_response): ?><p><strong>Your response:</strong> <?php echo e($consultation->teacher_response); ?></p><?php endif; ?>

                    <?php if(in_array($consultation->status, ['pending', 'approved'], true)): ?>
                        <?php
                            $appointmentStart = $consultation->scheduled_start_at ?: $consultation->requested_start_at;
                            $appointmentEnd = $consultation->scheduled_end_at ?: $consultation->requested_end_at;
                        ?>
                        <form method="POST" action="<?php echo e(route('teacher.consultations.respond', $consultation)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="scheduled_start_<?php echo e($consultation->id); ?>">Appointment start</label>
                                    <input type="datetime-local" class="form-control <?php $__errorArgs = ['scheduled_start_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="scheduled_start_<?php echo e($consultation->id); ?>" name="scheduled_start_at"
                                           value="<?php echo e(old('scheduled_start_at', $appointmentStart->format('Y-m-d\TH:i'))); ?>" required>
                                    <?php $__errorArgs = ['scheduled_start_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="scheduled_end_<?php echo e($consultation->id); ?>">Appointment end</label>
                                    <input type="datetime-local" class="form-control <?php $__errorArgs = ['scheduled_end_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="scheduled_end_<?php echo e($consultation->id); ?>" name="scheduled_end_at"
                                           value="<?php echo e(old('scheduled_end_at', $appointmentEnd->format('Y-m-d\TH:i'))); ?>" required>
                                    <?php $__errorArgs = ['scheduled_end_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="teacher_response_<?php echo e($consultation->id); ?>">Response or reschedule note</label>
                                    <input type="text" class="form-control" id="teacher_response_<?php echo e($consultation->id); ?>"
                                           name="teacher_response" maxlength="1500" value="<?php echo e(old('teacher_response', $consultation->teacher_response)); ?>">
                                </div>
                                <div class="col-12 d-flex flex-wrap gap-2">
                                    <button class="btn btn-success" type="submit" name="action" value="approve">
                                        <i class="fas fa-check me-1"></i><?php echo e($consultation->status === 'approved' ? 'Save / Reschedule' : 'Approve Appointment'); ?>

                                    </button>
                                    <button class="btn btn-outline-danger" type="submit" name="action" value="decline"
                                            formnovalidate onclick="return confirm('Decline this consultation request?')">
                                        Decline
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php elseif($consultation->status === 'approved'): ?>
                        <p class="mb-0"><strong>Scheduled:</strong> <?php echo e($consultation->scheduled_start_at->format('M j, Y g:i A')); ?>–<?php echo e($consultation->scheduled_end_at->format('g:i A')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card"><div class="card-body text-center text-muted">No consultation requests yet.</div></div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\consultations\teacher.blade.php ENDPATH**/ ?>