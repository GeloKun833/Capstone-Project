
<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <h3 class="page-title">Teacher Consultations</h3>
            <p class="text-muted mb-0">Request a meeting with a teacher assigned to one of your current classes.</p>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0">Request a Consultation</h5></div>
                    <div class="card-body">
                        <?php if($subjects->isEmpty()): ?>
                            <p class="text-muted mb-0">No teacher is assigned to your current subject and section yet.</p>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('student.consultations.store')); ?>">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label for="consultation_subject" class="form-label">Subject</label>
                                    <select id="consultation_subject" name="subject_id" class="form-control <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                        <option value="">Choose a subject</option>
                                        <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($subject->id); ?>" <?php if((string) old('subject_id') === (string) $subject->id): echo 'selected'; endif; ?>>
                                                <?php echo e($subject->subject_name); ?><?php echo e($subject->class ? ' · '.$subject->class : ''); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="mb-3">
                                    <label for="consultation_teacher" class="form-label">Teacher</label>
                                    <select id="consultation_teacher" name="teacher_id" class="form-control <?php $__errorArgs = ['teacher_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required disabled>
                                        <option value="">Choose a subject first</option>
                                    </select>
                                    <?php $__errorArgs = ['teacher_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="mb-3">
                                    <label for="requested_start_at" class="form-label">Preferred date and time</label>
                                    <input type="datetime-local" id="requested_start_at" name="requested_start_at"
                                           class="form-control <?php $__errorArgs = ['requested_start_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           min="<?php echo e($minimumRequestTime->format('Y-m-d\TH:i')); ?>"
                                           value="<?php echo e(old('requested_start_at', $minimumRequestTime->format('Y-m-d\TH:i'))); ?>" required>
                                    <?php $__errorArgs = ['requested_start_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <small class="text-muted">School time: <?php echo e(config('app.school_timezone', 'Asia/Manila')); ?>. Your teacher will confirm the appointment.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="duration_minutes" class="form-label">Requested duration</label>
                                    <select id="duration_minutes" name="duration_minutes" class="form-control" required>
                                        <?php $__currentLoopData = [15, 30, 45, 60]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $minutes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($minutes); ?>" <?php if((int) old('duration_minutes', 30) === $minutes): echo 'selected'; endif; ?>><?php echo e($minutes); ?> minutes</option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="student_message" class="form-label">What would you like to discuss?</label>
                                    <textarea id="student_message" name="student_message" rows="3" maxlength="1500"
                                              class="form-control <?php $__errorArgs = ['student_message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                              placeholder="Add a short note for your teacher"><?php echo e(old('student_message')); ?></textarea>
                                    <?php $__errorArgs = ['student_message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Request</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">My Requests and Appointments</h5></div>
                    <div class="card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <h6 class="mb-1"><?php echo e($consultation->subject->subject_name); ?> · <?php echo e($consultation->teacher->full_name); ?></h6>
                                        <div class="small text-muted">Requested <?php echo e($consultation->requested_start_at->format('M j, Y g:i A')); ?>–<?php echo e($consultation->requested_end_at->format('g:i A')); ?></div>
                                    </div>
                                    <span class="badge bg-<?php echo e($consultation->status === 'approved' ? 'success' : ($consultation->status === 'pending' ? 'warning' : 'secondary')); ?>"><?php echo e(ucfirst($consultation->status)); ?></span>
                                </div>
                                <?php if($consultation->status === 'approved'): ?>
                                    <p class="small text-success mt-2 mb-1"><strong>Appointment:</strong> <?php echo e($consultation->scheduled_start_at->format('M j, Y g:i A')); ?>–<?php echo e($consultation->scheduled_end_at->format('g:i A')); ?></p>
                                <?php endif; ?>
                                <?php if($consultation->student_message): ?><p class="small mb-1"><?php echo e($consultation->student_message); ?></p><?php endif; ?>
                                <?php if($consultation->teacher_response): ?><p class="small text-muted mb-1"><strong>Teacher:</strong> <?php echo e($consultation->teacher_response); ?></p><?php endif; ?>
                                <?php if($consultation->status === 'pending'): ?>
                                    <form method="POST" action="<?php echo e(route('student.consultations.cancel', $consultation)); ?>" class="mt-2">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Cancel Request</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-muted mb-0">You haven’t requested a consultation yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subjectSelect = document.getElementById('consultation_subject');
    const teacherSelect = document.getElementById('consultation_teacher');
    if (!subjectSelect || !teacherSelect) return;

    const teachersBySubject = <?php echo json_encode($subjects->mapWithKeys(fn ($subject) => [(string) $subject->id => $subject->consultationTeachers->map(fn ($teacher) => ['id' => $teacher->id, 'name' => $teacher->full_name])->values()]), 512) ?>;
    const oldTeacherId = <?php echo json_encode(old('teacher_id'), 15, 512) ?>;

    function updateTeachers() {
        const choices = teachersBySubject[subjectSelect.value] || [];
        teacherSelect.replaceChildren(new Option(choices.length ? 'Choose a teacher' : 'No assigned teacher', ''));
        choices.forEach(function (teacher) {
            const option = new Option(teacher.name, teacher.id, false, String(teacher.id) === String(oldTeacherId));
            teacherSelect.add(option);
        });
        teacherSelect.disabled = choices.length === 0;
    }

    subjectSelect.addEventListener('change', updateTeachers);
    updateTeachers();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\consultations\student.blade.php ENDPATH**/ ?>