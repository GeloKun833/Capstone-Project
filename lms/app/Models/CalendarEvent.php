<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_time',
        'end_time',
        'subject_id',
        'teacher_id',
        'room_id',
        'created_by',
        'color',
        'is_all_day',
        'is_recurring',
        'recurrence_pattern',
        'recurrence_end_date'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'recurrence_end_date' => 'date'
    ];

    /**
     * Get the subject for this event
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher for this event
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the room for this event
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the user who created this event
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get events by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope to get events by teacher
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope to get events by subject
     */
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope to get events in a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->where('start_time', '>=', $startDate)
              ->where('start_time', '<=', $endDate);
        });
    }

    /**
     * Get the event color based on type
     */
    public function getEventColorAttribute()
    {
        $colors = [
            'exam' => '#dc3545',
            'activity' => '#28a745',
            'meeting' => '#17a2b8',
            'deadline' => '#ffc107',
            'holiday' => '#6c757d',
            'other' => '#3d5ee1'
        ];

        return $colors[$this->event_type] ?? $this->color;
    }

    /**
     * Check for scheduling conflicts
     */
    public static function checkConflicts($startTime, $endTime, $teacherId = null, $roomId = null, $excludeEventId = null)
    {
        $conflicts = [];

        // Check teacher conflicts
        if ($teacherId) {
            $teacherConflicts = self::where('teacher_id', $teacherId)
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                    });
                });

            if ($excludeEventId) {
                $teacherConflicts->where('id', '!=', $excludeEventId);
            }

            if ($teacherConflicts->count() > 0) {
                $conflicts['teacher'] = $teacherConflicts->get();
            }
        }

        // Check room conflicts
        if ($roomId) {
            $roomConflicts = self::where('room_id', $roomId)
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                    });
                });

            if ($excludeEventId) {
                $roomConflicts->where('id', '!=', $excludeEventId);
            }

            if ($roomConflicts->count() > 0) {
                $conflicts['room'] = $roomConflicts->get();
            }
        }

        return $conflicts;
    }

    /**
     * Get available time slots for a given date
     */
    public static function getAvailableTimeSlots($date, $teacherId = null, $roomId = null, $duration = 60)
    {
        $date = Carbon::parse($date);
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Get all events for the day
        $events = self::where(function ($q) use ($startOfDay, $endOfDay) {
            $q->where('start_time', '>=', $startOfDay)
              ->where('start_time', '<=', $endOfDay);
        });

        if ($teacherId) {
            $events->where('teacher_id', $teacherId);
        }

        if ($roomId) {
            $events->where('room_id', $roomId);
        }

        $events = $events->orderBy('start_time')->get();

        // Generate time slots
        $timeSlots = [];
        $currentTime = $startOfDay->copy()->addHours(8); // Start at 8 AM
        $endTime = $endOfDay->copy()->subHours(1); // End at 5 PM

        while ($currentTime->addMinutes($duration) <= $endTime) {
            $slotStart = $currentTime->copy()->subMinutes($duration);
            $slotEnd = $currentTime->copy();

            // Check if slot is available
            $isAvailable = true;
            foreach ($events as $event) {
                if ($event->start_time < $slotEnd && $event->end_time > $slotStart) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $timeSlots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'available' => true
                ];
            }

            $currentTime->addMinutes(30); // Move to next slot
        }

        return $timeSlots;
    }
}
