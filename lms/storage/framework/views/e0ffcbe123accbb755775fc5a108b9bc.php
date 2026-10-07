<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Edit attendance</h3>
                    <p class="dir-subtitle">The date stays on today. Choose Present or Absent, and set the time when the student is present.</p>
                </div>
            </div>
        </div>
        <form action="<?php echo e(route('attendance.update', $attendance)); ?>" method="POST" class="att-edit">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <p><strong>Student:</strong> <?php echo e($attendance->student->first_name); ?> <?php echo e($attendance->student->last_name); ?></p>
            <p><strong>Student Number:</strong> <?php echo e($attendance->student->admission_id ?: '—'); ?></p>
            <p><strong>Subject:</strong> <?php echo e($attendance->subject->subject_name ?? 'N/A'); ?></p>
            <p><strong>Date:</strong> <?php echo e($attendance->date?->format('F j, Y')); ?></p>
            <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="present" <?php echo e($attendance->status === 'present' ? 'selected' : ''); ?>>Present</option>
                    <option value="absent" <?php echo e($attendance->status === 'absent' ? 'selected' : ''); ?>>Absent</option>
                </select>
            </div>
            <?php
                $recordedTime = ($attendance->status === 'present' && $attendance->time_in)
                    ? \Carbon\Carbon::parse($attendance->time_in)->format('g:i A')
                    : '—';
                $recordedTimeValue = ($attendance->status === 'present' && $attendance->time_in)
                    ? substr((string) $attendance->time_in, 0, 5)
                    : '';
            ?>
            <div class="mb-3">
                <label class="form-label">Time present</label>
                <p class="mb-0" id="timeLabel"><?php echo e($recordedTime); ?></p>
                <input type="hidden" name="time_in" id="time_in" value="<?php echo e($recordedTimeValue); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="remarks">Remarks</label>
                <input type="text" name="remarks" id="remarks" class="form-control" value="<?php echo e($attendance->remarks); ?>" maxlength="255">
            </div>
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="<?php echo e(route('attendance.index')); ?>" class="btn btn-outline-secondary">Back</a>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914m">
<style>
.att-edit { background: #fff; border: 1px solid #e7e5e4; border-radius: 12px; padding: 1rem 1.1rem; max-width: 640px; }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('status').addEventListener('change', function () {
    const label = document.getElementById('timeLabel');
    const field = document.getElementById('time_in');
    if (this.value !== 'present') {
        label.textContent = '—';
        field.value = '';
        return;
    }
    if (field.value) {
        return;
    }
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Manila',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23'
    }).formatToParts(new Date());
    const hour = parts.find(function (part) { return part.type === 'hour'; }).value;
    const minute = parts.find(function (part) { return part.type === 'minute'; }).value;
    let hour12 = parseInt(hour, 10) % 12;
    if (hour12 === 0) {
        hour12 = 12;
    }
    field.value = hour + ':' + minute;
    label.textContent = hour12 + ':' + minute + ' ' + (parseInt(hour, 10) >= 12 ? 'PM' : 'AM');
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/attendance/edit.blade.php ENDPATH**/ ?>