<?php $__env->startSection('content'); ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Event Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('calendar.index')); ?>">Calendar &amp; Events</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('calendar.index')); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Calendar
                    </a>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius:12px; border:1px solid #e5e7eb;">
            <div class="card-body">
                <span class="badge mb-2" style="background:<?php echo e($calendarEvent->event_color); ?>; color:#fff;">
                    <?php echo e(ucfirst($calendarEvent->event_type)); ?>

                </span>
                <?php if($calendarEvent->is_recurring): ?>
                    <span class="badge bg-secondary mb-2">Recurring</span>
                <?php endif; ?>

                <h3 class="mb-2"><?php echo e($calendarEvent->title); ?></h3>
                <p class="text-muted mb-4">
                    <?php echo e($calendarEvent->start_time->format('F j, Y')); ?><br>
                    <strong>Start:</strong>
                    <?php if($calendarEvent->is_all_day): ?>
                        All day
                    <?php else: ?>
                        <?php echo e($calendarEvent->start_time->format('g:i A')); ?>

                    <?php endif; ?>
                    <br>
                    <strong>End:</strong>
                    <?php if($calendarEvent->is_all_day): ?>
                        All day
                    <?php else: ?>
                        <?php echo e($calendarEvent->end_time->format('g:i A')); ?>

                    <?php endif; ?>
                </p>

                <?php if($relevantChildren->isNotEmpty()): ?>
                    <p><strong>Child:</strong> <?php echo e($relevantChildren->pluck('full_name')->implode(', ')); ?></p>
                    <?php if($relevantChildren->count() === 1): ?>
                        <?php $child = $relevantChildren->first(); ?>
                        <?php if($child->year_level || $child->class): ?>
                            <p><strong>Grade:</strong> <?php echo e($child->year_level ?: $child->class); ?></p>
                        <?php endif; ?>
                        <?php if($child->section): ?>
                            <p><strong>Section:</strong> <?php echo e($child->section); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($calendarEvent->subject): ?>
                    <p><strong>Subject:</strong> <?php echo e($calendarEvent->subject->subject_name); ?></p>
                <?php endif; ?>
                <?php if($calendarEvent->teacher): ?>
                    <p><strong>Teacher:</strong> <?php echo e($calendarEvent->teacher->full_name); ?></p>
                <?php endif; ?>
                <?php if($calendarEvent->room): ?>
                    <p><strong>Room:</strong> <?php echo e($calendarEvent->room->full_name ?? $calendarEvent->room->room_name); ?></p>
                <?php endif; ?>
                <?php if($calendarEvent->createdBy): ?>
                    <p><strong>Organizer:</strong> <?php echo e($calendarEvent->createdBy->name); ?></p>
                <?php endif; ?>
                <?php if($calendarEvent->description): ?>
                    <p><strong>Description:</strong> <?php echo e($calendarEvent->description); ?></p>
                <?php endif; ?>
                <?php if($calendarEvent->is_recurring): ?>
                    <p class="mb-0"><strong>Repeats:</strong>
                        <?php echo e(ucfirst($calendarEvent->recurrence_pattern ?? '')); ?>

                        <?php if($calendarEvent->recurrence_end_date): ?>
                            until <?php echo e($calendarEvent->recurrence_end_date->format('M j, Y')); ?>

                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\calendar\parent-show.blade.php ENDPATH**/ ?>