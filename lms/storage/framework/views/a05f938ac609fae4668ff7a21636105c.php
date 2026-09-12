
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">
                            <?php if(Auth::user()->role_name === 'Parent'): ?>
                                Child's Class Schedule
                            <?php else: ?>
                                My Class Schedule
                            <?php endif; ?>
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Class Schedule</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary" id="weekView">Week View</button>
                            <button type="button" class="btn btn-outline-primary" id="todayView">Today</button>
                            <button type="button" class="btn btn-outline-primary" id="listView">List View</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(Auth::user()->role_name === 'Parent'): ?>
                <!-- Child Selector for Parents -->
                <?php
                    $children = \App\Models\Student::where('parent_email', Auth::user()->email)->get();
                ?>
                <?php if($children->count() > 1): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0 me-3">Select Child:</h5>
                                        <select class="form-control w-auto" id="childSelector" onchange="switchChild(this.value)">
                                            <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($child->id); ?>" <?php echo e($student->id == $child->id ? 'selected' : ''); ?>>
                                                    <?php echo e($child->full_name); ?> - <?php echo e($child->sections->first()->name ?? 'No Section'); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Student Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Student Information</h5>
                                    <p><strong>Name:</strong> <?php echo e($student->full_name ?? 'N/A'); ?></p>
                                    <p><strong>Section:</strong> <?php echo e($student->sections->first()->name ?? 'N/A'); ?></p>
                                    <p><strong>Grade Level:</strong> <?php echo e($student->sections->first()->grade_level ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Schedule Summary</h5>
                                    <p><strong>Total Classes:</strong> <?php echo e(collect($weeklySchedule)->flatten()->count()); ?></p>
                                    <p><strong>Today's Classes:</strong> <?php echo e($todaySchedule->count()); ?></p>
                                    <p><strong>Active Schedule:</strong> Yes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div class="row" id="calendarView">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Weekly Schedule</h5>
                        </div>
                        <div class="card-body">
                            <div id="scheduleCalendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="row" id="todayView" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Today's Schedule - <?php echo e(Carbon\Carbon::now()->format('l, F j, Y')); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if($todaySchedule->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Room</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $todaySchedule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo e($schedule->time_range); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge" style="background-color: <?php echo e($schedule->subject_color); ?>; color: white;">
                                                            <?php echo e($schedule->subject->subject_name); ?>

                                                        </span>
                                                    </td>
                                                    <td><?php echo e($schedule->teacher->full_name); ?></td>
                                                    <td><?php echo e($schedule->room ? $schedule->room->room_name : 'TBD'); ?></td>
                                                    <td><?php echo e($schedule->duration); ?> min</td>
                                                    <td>
                                                        <?php
                                                            $now = Carbon\Carbon::now();
                                                            $startTime = Carbon\Carbon::parse($schedule->start_time);
                                                            $endTime = Carbon\Carbon::parse($schedule->end_time);
                                                            $today = Carbon\Carbon::today();
                                                            $scheduleStart = $today->copy()->setTimeFrom($startTime);
                                                            $scheduleEnd = $today->copy()->setTimeFrom($endTime);
                                                        ?>
                                                        
                                                        <?php if($now < $scheduleStart): ?>
                                                            <span class="badge bg-info">Upcoming</span>
                                                        <?php elseif($now >= $scheduleStart && $now <= $scheduleEnd): ?>
                                                            <span class="badge bg-success">In Progress</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Completed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <h5>No classes scheduled for today</h5>
                                    <p class="text-muted">Enjoy your free time!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div class="row" id="listView" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Weekly Schedule List</h5>
                        </div>
                        <div class="card-body">
                            <?php $__currentLoopData = $nextDaysSchedule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="day-schedule mb-4">
                                    <h6 class="day-header">
                                        <i class="fas fa-calendar-day"></i>
                                        <?php echo e($dayData['day_name']); ?> - <?php echo e(Carbon\Carbon::parse($dayData['date'])->format('M j, Y')); ?>

                                    </h6>
                                    
                                    <?php if($dayData['schedules']->count() > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Time</th>
                                                        <th>Subject</th>
                                                        <th>Teacher</th>
                                                        <th>Room</th>
                                                        <th>Duration</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $dayData['schedules']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><strong><?php echo e($schedule->time_range); ?></strong></td>
                                                            <td>
                                                                <span class="badge" style="background-color: <?php echo e($schedule->subject_color); ?>; color: white;">
                                                                    <?php echo e($schedule->subject->subject_name); ?>

                                                                </span>
                                                            </td>
                                                            <td><?php echo e($schedule->teacher->full_name); ?></td>
                                                            <td><?php echo e($schedule->room ? $schedule->room->room_name : 'TBD'); ?></td>
                                                            <td><?php echo e($schedule->duration); ?> min</td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <p class="text-muted mb-0">No classes scheduled</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject Legend -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Subject Legend</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                    $subjects = collect($weeklySchedule)->flatten()->pluck('subject')->unique();
                                ?>
                                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="subject-legend" style="background-color: <?php echo e($subject->color ?? '#3d5ee1'); ?>;"></div>
                                            <span class="ms-2"><?php echo e($subject->subject_name); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
.subject-legend {
    width: 20px;
    height: 20px;
    border-radius: 3px;
    display: inline-block;
}

.day-header {
    background-color: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 15px;
    border-left: 4px solid #3d5ee1;
}

.day-schedule {
    border: 1px solid #e9ecef;
    border-radius: 5px;
    padding: 15px;
}

.fc-event {
    cursor: pointer;
}

.fc-event:hover {
    opacity: 0.8;
}

.fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 600;
}

.fc-button-primary {
    background-color: #3d5ee1 !important;
    border-color: #3d5ee1 !important;
}

.fc-button-primary:hover {
    background-color: #2d4ed1 !important;
    border-color: #2d4ed1 !important;
}

.fc-daygrid-day.fc-day-today {
    background-color: rgba(61, 94, 225, 0.1) !important;
}

.btn-group .btn.active {
    background-color: #3d5ee1;
    border-color: #3d5ee1;
    color: white;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
let calendar;

document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    setupViewButtons();
});

function initializeCalendar() {
    const calendarEl = document.getElementById('scheduleCalendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay'
        },
        height: 'auto',
        editable: false,
        selectable: false,
        dayMaxEvents: true,
        weekends: false, // Hide weekends for school schedule
        slotMinTime: '07:00:00',
        slotMaxTime: '18:00:00',
        events: {
            url: '<?php echo e(Auth::user()->role_name === "Parent" ? route("parent.schedule") : route("schedule.index")); ?>',
            method: 'GET',
            extraParams: {
                view: 'week',
                student_id: '<?php echo e($student->id ?? ""); ?>'
            }
        },
        eventClick: function(arg) {
            showEventDetails(arg.event);
        },
        loading: function(isLoading) {
            if (isLoading) {
                // Show loading indicator
            } else {
                // Hide loading indicator
            }
        }
    });
    
    calendar.render();
}

function setupViewButtons() {
    const buttons = ['weekView', 'todayView', 'listView'];
    
    buttons.forEach(buttonId => {
        document.getElementById(buttonId).addEventListener('click', function() {
            // Remove active class from all buttons
            buttons.forEach(id => {
                document.getElementById(id).classList.remove('active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show/hide appropriate views
            const views = ['calendarView', 'todayView', 'listView'];
            views.forEach(viewId => {
                document.getElementById(viewId).style.display = 'none';
            });
            
            if (buttonId === 'weekView') {
                document.getElementById('calendarView').style.display = 'block';
            } else if (buttonId === 'todayView') {
                document.getElementById('todayView').style.display = 'block';
            } else if (buttonId === 'listView') {
                document.getElementById('listView').style.display = 'block';
            }
        });
    });
    
    // Set week view as active by default
    document.getElementById('weekView').classList.add('active');
}

function showEventDetails(event) {
    const details = event.extendedProps;
    
    const modalHtml = `
        <div class="modal fade" id="eventModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${event.title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Time:</strong> ${details.time_range}</p>
                                <p><strong>Duration:</strong> ${details.duration}</p>
                                <p><strong>Teacher:</strong> ${details.teacher}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Room:</strong> ${details.room}</p>
                                <p><strong>Day:</strong> ${event.start.toLocaleDateString('en-US', { weekday: 'long' })}</p>
                                ${details.notes ? `<p><strong>Notes:</strong> ${details.notes}</p>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('eventModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add new modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('eventModal'));
    modal.show();
}

<?php if(Auth::user()->role_name === 'Parent'): ?>
function switchChild(childId) {
    window.location.href = '<?php echo e(route("parent.schedule")); ?>?student_id=' + childId;
}
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\schedule\index.blade.php ENDPATH**/ ?>