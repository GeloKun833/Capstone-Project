
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Calendar Event</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('calendar.index')); ?>">Calendar</a></li>
                            <li class="breadcrumb-item active">Edit Event</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Event Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo e(route('calendar.update', $calendarEvent->id)); ?>" method="POST" id="eventForm">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Event Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" value="<?php echo e(old('title', $calendarEvent->title)); ?>" required>
                                            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="text-danger"><?php echo e($message); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Event Type <span class="text-danger">*</span></label>
                                            <select class="form-control" name="event_type" required>
                                                <option value="">Select Event Type</option>
                                                <?php $__currentLoopData = $eventTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($type); ?>" <?php echo e(old('event_type', $calendarEvent->event_type) == $type ? 'selected' : ''); ?>>
                                                        <?php echo e(ucfirst($type)); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['event_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="text-danger"><?php echo e($message); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Start Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control" name="start_time" 
                                                   value="<?php echo e(old('start_time', $calendarEvent->start_time->format('Y-m-d\TH:i'))); ?>" required>
                                            <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="text-danger"><?php echo e($message); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control" name="end_time" 
                                                   value="<?php echo e(old('end_time', $calendarEvent->end_time->format('Y-m-d\TH:i'))); ?>" required>
                                            <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="text-danger"><?php echo e($message); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Subject</label>
                                            <select class="form-control" name="subject_id">
                                                <option value="">Select Subject</option>
                                                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($subject->id); ?>" <?php echo e(old('subject_id', $calendarEvent->subject_id) == $subject->id ? 'selected' : ''); ?>>
                                                        <?php echo e($subject->subject_name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="text-danger"><?php echo e($message); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Teacher</label>
                                            <select class="form-control" name="teacher_id" id="teacher_id">
                                                <option value="">Select Teacher</option>
                                                <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($teacher->id); ?>" <?php echo e(old('teacher_id', $calendarEvent->teacher_id) == $teacher->id ? 'selected' : ''); ?>>
                                                        <?php echo e($teacher->full_name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['teacher_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="text-danger"><?php echo e($message); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Room</label>
                                            <select class="form-control" name="room_id" id="room_id">
                                                <option value="">Select Room</option>
                                                <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($room->id); ?>" <?php echo e(old('room_id', $calendarEvent->room_id) == $room->id ? 'selected' : ''); ?>>
                                                        <?php echo e($room->full_name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['room_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="text-danger"><?php echo e($message); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="form-control" name="description" rows="3"><?php echo e(old('description', $calendarEvent->description)); ?></textarea>
                                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="text-danger"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_all_day" value="1" id="is_all_day" 
                                                   <?php echo e(old('is_all_day', $calendarEvent->is_all_day) ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="is_all_day">
                                                All Day Event
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="is_recurring" 
                                                   <?php echo e(old('is_recurring', $calendarEvent->is_recurring) ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="is_recurring">
                                                Recurring Event
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="recurrence_options" style="display: <?php echo e($calendarEvent->is_recurring ? 'block' : 'none'); ?>;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Recurrence Pattern</label>
                                            <select class="form-control" name="recurrence_pattern">
                                                <option value="">Select Pattern</option>
                                                <option value="daily" <?php echo e(old('recurrence_pattern', $calendarEvent->recurrence_pattern) == 'daily' ? 'selected' : ''); ?>>Daily</option>
                                                <option value="weekly" <?php echo e(old('recurrence_pattern', $calendarEvent->recurrence_pattern) == 'weekly' ? 'selected' : ''); ?>>Weekly</option>
                                                <option value="monthly" <?php echo e(old('recurrence_pattern', $calendarEvent->recurrence_pattern) == 'monthly' ? 'selected' : ''); ?>>Monthly</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date</label>
                                            <input type="date" class="form-control" name="recurrence_end_date" 
                                                   value="<?php echo e(old('recurrence_end_date', $calendarEvent->recurrence_end_date?->format('Y-m-d'))); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Update Event</button>
                                        <a href="<?php echo e(route('calendar.index')); ?>" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Event Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <p><strong>Created by:</strong> <?php echo e($calendarEvent->createdBy->name); ?></p>
                                    <p><strong>Created on:</strong> <?php echo e($calendarEvent->created_at->format('M d, Y H:i')); ?></p>
                                    <p><strong>Last updated:</strong> <?php echo e($calendarEvent->updated_at->format('M d, Y H:i')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Conflict Check</h5>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-warning btn-sm" onclick="checkConflicts()">
                                Check for Conflicts
                            </button>
                            <div id="conflict_results" class="mt-3">
                                <!-- Conflict results will be displayed here -->
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo e(route('calendar.destroy', $calendarEvent->id)); ?>" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this event?')" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete Event
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Show/hide recurrence options
    $('#is_recurring').change(function() {
        if ($(this).is(':checked')) {
            $('#recurrence_options').show();
        } else {
            $('#recurrence_options').hide();
        }
    });

    // Show/hide time inputs for all-day events
    $('#is_all_day').change(function() {
        if ($(this).is(':checked')) {
            $('input[name="start_time"], input[name="end_time"]').attr('type', 'date');
        } else {
            $('input[name="start_time"], input[name="end_time"]').attr('type', 'datetime-local');
        }
    });

    // Form validation
    $('#eventForm').submit(function(e) {
        const startTime = new Date($('input[name="start_time"]').val());
        const endTime = new Date($('input[name="end_time"]').val());
        
        if (endTime <= startTime) {
            e.preventDefault();
            toastr.error('End time must be after start time');
            return false;
        }
    });
});

function checkConflicts() {
    const startTime = $('input[name="start_time"]').val();
    const endTime = $('input[name="end_time"]').val();
    const teacherId = $('#teacher_id').val();
    const roomId = $('#room_id').val();
    const eventId = <?php echo e($calendarEvent->id); ?>;

    if (!startTime || !endTime) {
        toastr.error('Please set start and end times first');
        return;
    }

    $.ajax({
        url: '<?php echo e(route("calendar.check-conflicts")); ?>',
        method: 'GET',
        data: {
            start_time: startTime,
            end_time: endTime,
            teacher_id: teacherId,
            room_id: roomId,
            exclude_event_id: eventId
        },
        success: function(response) {
            let html = '<h6>Conflict Check Results:</h6>';
            if (response.has_conflicts) {
                html += '<div class="alert alert-danger">';
                html += '<strong>Conflicts detected!</strong><br>';
                if (response.conflicts.teacher) {
                    html += 'Teacher has conflicting events.<br>';
                }
                if (response.conflicts.room) {
                    html += 'Room is already booked.<br>';
                }
                html += '</div>';
                
                if (response.available_slots && response.available_slots.length > 0) {
                    html += '<h6>Suggested Available Slots:</h6>';
                    html += '<ul class="list-unstyled">';
                    response.available_slots.slice(0, 5).forEach(function(slot) {
                        html += `<li><span class="badge bg-info">${slot.start} - ${slot.end}</span></li>`;
                    });
                    html += '</ul>';
                }
            } else {
                html += '<div class="alert alert-success">No conflicts detected!</div>';
            }
            $('#conflict_results').html(html);
        },
        error: function() {
            toastr.error('Failed to check conflicts');
        }
    });
}
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\calendar\edit.blade.php ENDPATH**/ ?>