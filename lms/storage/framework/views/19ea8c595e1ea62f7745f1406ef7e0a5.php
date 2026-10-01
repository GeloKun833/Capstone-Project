<?php
    $studentId = $studentId ?? null;
    if (!$studentId && Auth::user()->role_name === 'Student') {
        $student = Auth::user()->student;
        if ($student) {
            $studentId = $student->id;
        }
    }
    
    if ($studentId) {
        $nextDaysSchedule = App\Models\ClassSchedule::getNextDaysSchedule($studentId, 5);
        $todaySchedule = App\Models\ClassSchedule::getTodaySchedule($studentId);
    } else {
        $nextDaysSchedule = collect();
        $todaySchedule = collect();
    }
?>

<div class="card flex-fill comman-shadow">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title">My Weekly Schedule</h5>
        <ul class="chart-list-out student-ellips">
            <li class="star-menus">
                <a href="<?php echo e(route('schedule.index')); ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-external-link-alt"></i> View Full Schedule
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <?php if(count($nextDaysSchedule) > 0): ?>
            <div class="schedule-widget">
                <?php $__currentLoopData = array_slice($nextDaysSchedule, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="day-schedule-item mb-3">
                        <div class="day-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-day text-primary"></i>
                                <?php echo e($dayData['day_name']); ?>

                            </h6>
                            <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($dayData['date'])->format('M j')); ?></small>
                        </div>
                        
                        <?php if($dayData['schedules']->count() > 0): ?>
                            <div class="schedule-items">
                                <?php $__currentLoopData = $dayData['schedules']->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="schedule-item" 
                                         data-bs-toggle="tooltip" 
                                         data-bs-placement="top" 
                                         title="<?php echo e($schedule->subject->subject_name); ?> - <?php echo e($schedule->teacher->full_name); ?> - <?php echo e($schedule->room ? $schedule->room->room_name : 'TBD'); ?>">
                                        <div class="schedule-time">
                                            <small class="text-muted"><?php echo e($schedule->time_range); ?></small>
                                        </div>
                                        <div class="schedule-subject">
                                            <span class="subject-badge" style="background-color: <?php echo e($schedule->subject_color); ?>; color: white;">
                                                <?php echo e($schedule->subject->subject_name); ?>

                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if($dayData['schedules']->count() > 3): ?>
                                    <div class="schedule-more">
                                        <small class="text-muted">
                                            +<?php echo e($dayData['schedules']->count() - 3); ?> more classes
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-classes">
                                <small class="text-muted">No classes scheduled</small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                <?php if(count($nextDaysSchedule) > 3): ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('schedule.index')); ?>" class="btn btn-sm btn-outline-primary">
                            View Next <?php echo e(count($nextDaysSchedule) - 3); ?> Days
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                <h6>No Schedule Available</h6>
                <p class="text-muted small">Your class schedule will appear here once it's set up.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.schedule-widget {
    max-height: 400px;
    overflow-y: auto;
}

.day-schedule-item {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 15px;
}

.day-schedule-item:last-child {
    border-bottom: none;
}

.day-header {
    margin-bottom: 10px;
}

.schedule-items {
    margin-left: 20px;
}

.schedule-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    padding: 5px 0;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.schedule-item:hover {
    background-color: #f8f9fa;
}

.schedule-time {
    min-width: 80px;
    margin-right: 10px;
}

.schedule-subject {
    flex: 1;
}

.subject-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.schedule-more {
    margin-top: 5px;
    margin-left: 90px;
}

.no-classes {
    margin-left: 20px;
    font-style: italic;
}

.schedule-widget::-webkit-scrollbar {
    width: 4px;
}

.schedule-widget::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.schedule-widget::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.schedule-widget::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script> <?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\components\student-schedule-widget.blade.php ENDPATH**/ ?>