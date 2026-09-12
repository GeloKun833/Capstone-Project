<?php
    $studentId = $studentId ?? null;
    if (!$studentId && Auth::user()->role_name === 'Student') {
        $student = Auth::user()->student;
        if ($student) {
            $studentId = $student->id;
        }
    }
    
    if ($studentId) {
        $weeklySchedule = App\Models\ClassSchedule::getWeeklySchedule($studentId);
    } else {
        $weeklySchedule = [];
    }
    
    // Generate time slots from 6:00 AM to 8:00 PM in 30-minute increments
    $timeSlots = [];
    $startTime = \Carbon\Carbon::createFromTime(6, 0, 0);
    $endTime = \Carbon\Carbon::createFromTime(20, 0, 0);
    
    while ($startTime <= $endTime) {
        $timeSlots[] = $startTime->format('g:i A');
        $startTime->addMinutes(30);
    }
    
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $dayNames = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];
?>

<div class="card flex-fill comman-shadow">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title">My Schedule</h5>
    </div>
    <div class="card-body">
        <div class="schedule-container">
            <div class="schedule-grid">
                <!-- Headers Row -->
                <div class="time-header">Time</div>
                <?php $__currentLoopData = $dayNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="day-header"><?php echo e($dayName); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                <!-- Time slots and schedule blocks -->
                <?php $__currentLoopData = $timeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slotIndex => $timeSlot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $time = \Carbon\Carbon::createFromFormat('g:i A', $timeSlot);
                        $timeStr = $time->format('H:i:s');
                    ?>
                    
                    <!-- Time label -->
                    <div class="time-slot"><?php echo e($timeSlot); ?></div>
                    
                    <!-- Each day's slot for this time -->
                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            static $renderedSchedules = [];
                            if ($slotIndex === 0) {
                                $renderedSchedules[$day] = [];
                            }
                            
                            $daySchedules = $weeklySchedule[$day] ?? collect();
                            
                            // Find if a schedule starts exactly at this time slot
                            $schedule = $daySchedules->first(function($s) use ($timeStr) {
                                $start = \Carbon\Carbon::parse($s->start_time);
                                $slotTime = \Carbon\Carbon::parse($timeStr);
                                return $start->format('H:i') === $slotTime->format('H:i');
                            });
                            
                            // Check if this slot is already occupied
                            $isOccupied = false;
                            foreach ($renderedSchedules[$day] ?? [] as $rendered) {
                                $renderedStart = \Carbon\Carbon::parse($rendered['start']);
                                $renderedEnd = \Carbon\Carbon::parse($rendered['end']);
                                $currentSlot = \Carbon\Carbon::parse($timeStr);
                                
                                if ($currentSlot->between($renderedStart, $renderedEnd, false)) {
                                    $isOccupied = true;
                                    break;
                                }
                            }
                        ?>
                        
                        <?php if($schedule && !$isOccupied): ?>
                            <?php
                                $startTime = \Carbon\Carbon::parse($schedule->start_time);
                                $endTime = \Carbon\Carbon::parse($schedule->end_time);
                                $duration = $startTime->diffInMinutes($endTime);
                                $rowSpan = max(1, ceil($duration / 30));
                                $renderedSchedules[$day][] = [
                                    'id' => $schedule->id,
                                    'start' => $schedule->start_time,
                                    'end' => $schedule->end_time
                                ];
                                
                                // Debug info
                                $debugInfo = "Start: {$startTime->format('h:i A')}, End: {$endTime->format('h:i A')}, Duration: {$duration} mins, Rows: {$rowSpan}";
                            ?>
                            <div class="schedule-block" 
                                 style="background-color: <?php echo e($schedule->color); ?>; grid-row: span <?php echo e($rowSpan); ?>;"
                                 data-bs-toggle="tooltip" 
                                 data-bs-placement="top" 
                                 title="<?php echo e($schedule->subject->subject_name); ?> - <?php echo e($debugInfo); ?> - <?php echo e($schedule->room ? $schedule->room->room_name : 'TBD'); ?>">
                                <div class="schedule-content">
                                    <div class="subject-code"><?php echo e($schedule->subject->subject_id ?? 'N/A'); ?></div>
                                    <div class="subject-name"><?php echo e($schedule->subject->subject_name); ?></div>
                                    <div class="class-type">(<?php echo e(ucfirst($schedule->class_type ?? 'Lecture')); ?>)</div>
                                    <div class="teacher-name"><?php echo e($schedule->teacher->full_name ?? 'TBA'); ?></div>
                                    <div class="room-name"><?php echo e($schedule->room ? $schedule->room->room_name : 'TBD'); ?></div>
                                </div>
                            </div>
                        <?php elseif(!$isOccupied): ?>
                            <div class="empty-slot"></div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>

<style>
.schedule-container {
    overflow-x: hidden;
    overflow-y: auto;
    min-height: calc(100vh - 350px);
    max-height: calc(100vh - 300px);
}

.schedule-grid {
    display: grid;
    grid-template-columns: 100px repeat(7, 1fr);
    grid-auto-rows: 60px;
    width: 100%;
    border: 1px solid #e0e0e0;
    background: white;
}

.time-header {
    background-color: #3d5ee1;
    color: white;
    padding: 10px 5px;
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    border-bottom: 2px solid #e0e0e0;
    border-right: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.day-header {
    background-color: #3d5ee1;
    color: white;
    padding: 10px 5px;
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    border-bottom: 2px solid #e0e0e0;
    border-right: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.time-slot {
    padding: 8px 5px;
    font-size: 0.85rem;
    color: #333;
    background-color: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    border-right: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.empty-slot {
    border-bottom: 1px solid #e0e0e0;
    border-right: 1px solid #e0e0e0;
}

.schedule-block {
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-right: 1px solid #e0e0e0;
    border-radius: 6px;
    margin: 2px;
    padding: 8px 4px;
    color: white;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.schedule-block:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
    z-index: 5;
}

.schedule-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    width: 100%;
}

.subject-code {
    font-weight: 700;
    font-size: 0.75rem;
    line-height: 1.2;
    margin-bottom: 3px;
}

.subject-name {
    font-weight: 700;
    font-size: 0.9rem;
    line-height: 1.3;
    margin-bottom: 3px;
}

.class-type {
    font-size: 0.75rem;
    opacity: 0.95;
    line-height: 1.2;
    margin-bottom: 2px;
}

.teacher-name {
    font-size: 0.8rem;
    opacity: 0.95;
    line-height: 1.2;
    margin-bottom: 2px;
}

.room-name {
    font-size: 0.75rem;
    opacity: 0.95;
    line-height: 1.2;
}

@media (max-width: 768px) {
    .schedule-grid {
        grid-template-columns: 70px repeat(7, 1fr);
        grid-auto-rows: 50px;
    }
    
    .schedule-container {
        overflow-x: hidden;
    }
    
    .time-header, .day-header {
        font-size: 0.75rem;
        padding: 6px 2px;
    }
    
    .time-slot {
        font-size: 0.7rem;
        padding: 4px 2px;
    }
    
    .schedule-block {
        padding: 4px 2px;
    }
    
    .subject-code {
        font-size: 0.65rem;
    }
    
    .subject-name {
        font-size: 0.75rem;
    }
    
    .class-type {
        font-size: 0.65rem;
    }
    
    .teacher-name {
        font-size: 0.65rem;
    }
    
    .room-name {
        font-size: 0.6rem;
    }
}

/* Vertical scrollbar only */
.schedule-container::-webkit-scrollbar {
    width: 12px;
    height: 0px; /* Hide horizontal scrollbar */
}

.schedule-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

.schedule-container::-webkit-scrollbar-thumb {
    background: #3d5ee1;
    border-radius: 6px;
}

.schedule-container::-webkit-scrollbar-thumb:hover {
    background: #2d4ed1;
}

/* Hide horizontal scrollbar for all browsers */
.schedule-container {
    scrollbar-width: thin; /* Firefox */
    scrollbar-color: #3d5ee1 #f1f1f1; /* Firefox */
}

.schedule-container::-webkit-scrollbar:horizontal {
    display: none; /* Chrome/Safari/Edge */
}

/* Make card take full width */
.card.flex-fill.comman-shadow {
    height: auto;
}

.card.flex-fill.comman-shadow .card-body {
    padding: 1.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    document.querySelectorAll('.schedule-block').forEach(function(block) {
        block.addEventListener('click', function() {
            console.log('Schedule block clicked:', this.getAttribute('title'));
        });
    });
});
</script> <?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\components\my-schedule.blade.php ENDPATH**/ ?>