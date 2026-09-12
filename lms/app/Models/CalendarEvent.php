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
     * Whether the given user may edit/delete this event.
     * Admins manage all events; teachers manage only events they created.
     * Students never manage events.
     */
    public function canBeManagedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->role_name === 'Student' || $user->role_name === 'Parent') {
            return false;
        }

        if ($user->role_name === 'Admin') {
            return true;
        }

        if ($user->role_name === 'Teacher') {
            return (int) $this->created_by === (int) $user->id;
        }

        return false;
    }

    /**
     * Whether a student may view this event.
     */
    public function isVisibleToStudent(?Student $student): bool
    {
        if (!$student) {
            return false;
        }

        // Holidays and unassigned (school-wide) events
        if ($this->event_type === 'holiday' || !$this->subject_id) {
            return true;
        }

        $subjectIds = $student->subjects()->pluck('subjects.id')->map(fn ($id) => (int) $id)->all();
        if (in_array((int) $this->subject_id, $subjectIds, true)) {
            return true;
        }

        // Grade / class match via subject.class
        $subject = $this->subject;
        if (!$subject || !$subject->class) {
            return false;
        }

        $subjectClass = strtolower(trim((string) $subject->class));
        $studentHints = array_filter([
            strtolower(trim((string) $student->class)),
            strtolower(trim((string) $student->year_level)),
            strtolower(trim((string) $student->section)),
        ]);

        foreach ($studentHints as $hint) {
            if ($hint !== '' && (str_contains($subjectClass, $hint) || str_contains($hint, $subjectClass))) {
                return true;
            }
        }

        // Section assignments: subject linked to student's sections
        $sectionIds = $student->sections()->pluck('sections.id')->all();
        if (!empty($sectionIds) && method_exists($subject, 'sections')) {
            return $subject->sections()->whereIn('sections.id', $sectionIds)->exists();
        }

        return false;
    }

    /**
     * Scope events visible to any of the given students (for parent multi-child views).
     */
    public function scopeVisibleToStudents($query, $students)
    {
        $students = collect($students)->filter();
        if ($students->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $subjectIds = [];
        $sectionIds = [];
        $classHints = [];

        foreach ($students as $student) {
            $subjectIds = array_merge(
                $subjectIds,
                $student->subjects()->pluck('subjects.id')->filter()->all()
            );
            $sectionIds = array_merge(
                $sectionIds,
                $student->sections()->pluck('sections.id')->filter()->all()
            );
            $classHints[] = $student->class;
            $classHints[] = $student->year_level;
        }

        $subjectIds = array_values(array_unique($subjectIds));
        $sectionIds = array_values(array_unique($sectionIds));
        $classHints = array_values(array_unique(array_filter($classHints)));

        return $query->where(function ($q) use ($subjectIds, $sectionIds, $classHints) {
            $q->whereNull('subject_id')
                ->orWhere('event_type', 'holiday');

            if (!empty($subjectIds)) {
                $q->orWhereIn('subject_id', $subjectIds);
            }

            if (!empty($classHints) || !empty($sectionIds)) {
                $q->orWhereHas('subject', function ($sq) use ($classHints, $sectionIds) {
                    $sq->where(function ($inner) use ($classHints, $sectionIds) {
                        foreach ($classHints as $hint) {
                            $inner->orWhere('class', $hint)
                                ->orWhere('class', 'like', '%' . $hint . '%');
                        }
                        if (!empty($sectionIds)) {
                            $inner->orWhereHas('sections', function ($sec) use ($sectionIds) {
                                $sec->whereIn('sections.id', $sectionIds);
                            });
                        }
                    });
                });
            }
        });
    }

    /**
     * Children (from a list) for whom this event is relevant.
     */
    public function relevantChildrenAmong($students)
    {
        return collect($students)->filter(fn ($student) => $this->isVisibleToStudent($student))->values();
    }

    /**
     * Scope events visible to a single student.
     */
    public function scopeVisibleToStudent($query, Student $student)
    {
        return $query->visibleToStudents(collect([$student]));
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
     * Scope to get events overlapping a date range (for calendar period fetch)
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_time', '<', $endDate)
            ->where('end_time', '>', $startDate);
    }

    /**
     * Get the event color based on type
     */
    public function getEventColorAttribute()
    {
        $colors = [
            'exam' => '#dc2626',
            'activity' => '#ea580c',
            'meeting' => '#0f766e',
            'deadline' => '#ca8a04',
            'holiday' => '#64748b',
            'other' => '#475569',
        ];

        return $colors[$this->event_type] ?? ($this->color ?: '#ea580c');
    }

    /**
     * Check for scheduling conflicts (teacher, room, and subject when provided)
     */
    public static function checkConflicts($startTime, $endTime, $teacherId = null, $roomId = null, $excludeEventId = null, $subjectId = null)
    {
        $conflicts = [];

        $overlap = function ($query) use ($startTime, $endTime, $excludeEventId) {
            $query->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime);

            if ($excludeEventId) {
                $query->where('id', '!=', $excludeEventId);
            }

            return $query->with(['subject:id,subject_name', 'teacher:id,full_name', 'room:id,room_name,room_number'])
                ->orderBy('start_time')
                ->get()
                ->map(fn ($event) => self::formatConflictEvent($event))
                ->values()
                ->all();
        };

        if ($teacherId) {
            $items = $overlap(self::where('teacher_id', $teacherId));
            if (!empty($items)) {
                $conflicts['teacher'] = $items;
            }
        }

        if ($roomId) {
            $items = $overlap(self::where('room_id', $roomId));
            if (!empty($items)) {
                $conflicts['room'] = $items;
            }
        }

        if ($subjectId) {
            $items = $overlap(self::where('subject_id', $subjectId));
            if (!empty($items)) {
                $conflicts['subject'] = $items;
            }
        }

        return $conflicts;
    }

    /**
     * Normalize conflict event payload for API responses
     */
    public static function formatConflictEvent(self $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'event_type' => $event->event_type,
            'start_time' => $event->start_time->format('Y-m-d H:i'),
            'end_time' => $event->end_time->format('Y-m-d H:i'),
            'start_display' => $event->start_time->format('g:i A'),
            'end_display' => $event->end_time->format('g:i A'),
            'subject' => $event->subject?->subject_name,
            'teacher' => $event->teacher?->full_name,
            'room' => $event->room?->full_name ?? $event->room?->room_name,
        ];
    }

    /**
     * Get available / unavailable time slots for a given date
     */
    public static function getAvailableTimeSlots($date, $teacherId = null, $roomId = null, $duration = 60)
    {
        $date = Carbon::parse($date)->startOfDay();
        $dayStart = $date->copy()->setTime(8, 0);
        $dayEnd = $date->copy()->setTime(17, 0);
        $duration = max(15, (int) $duration);

        $eventsQuery = self::where('start_time', '<', $dayEnd)
            ->where('end_time', '>', $dayStart);

        if ($teacherId) {
            $eventsQuery->where('teacher_id', $teacherId);
        }

        if ($roomId) {
            $eventsQuery->where('room_id', $roomId);
        }

        $events = $eventsQuery->orderBy('start_time')->get();

        $available = [];
        $unavailable = [];
        $cursor = $dayStart->copy();

        while ($cursor->copy()->addMinutes($duration)->lte($dayEnd)) {
            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addMinutes($duration);

            $conflictTitle = null;
            foreach ($events as $event) {
                if ($event->start_time < $slotEnd && $event->end_time > $slotStart) {
                    $conflictTitle = $event->title;
                    break;
                }
            }

            $slot = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'start_display' => $slotStart->format('g:i A'),
                'end_display' => $slotEnd->format('g:i A'),
                'available' => $conflictTitle === null,
            ];

            if ($conflictTitle === null) {
                $available[] = $slot;
            } else {
                $slot['conflict'] = $conflictTitle;
                $unavailable[] = $slot;
            }

            $cursor->addMinutes(30);
        }

        return [
            'available' => $available,
            'unavailable' => $unavailable,
            // Backward compatibility for older UI that expects a flat available list
            'slots' => $available,
        ];
    }

    /**
     * Suggest next free slots starting from a requested time (same day, then following days).
     */
    public static function suggestNextAvailableSlots($startTime, $endTime = null, $teacherId = null, $roomId = null, $limit = 5, $daysToSearch = 7)
    {
        $start = Carbon::parse($startTime);
        $duration = 60;

        if ($endTime) {
            $duration = max(15, Carbon::parse($startTime)->diffInMinutes(Carbon::parse($endTime)));
        }

        $suggestions = [];
        $day = $start->copy()->startOfDay();

        for ($d = 0; $d < $daysToSearch && count($suggestions) < $limit; $d++) {
            $slots = self::getAvailableTimeSlots($day->toDateString(), $teacherId, $roomId, $duration);
            foreach ($slots['available'] as $slot) {
                $slotStart = Carbon::parse($day->toDateString() . ' ' . $slot['start']);

                // On the requested day, prefer times at/after the original start
                if ($d === 0 && $slotStart->lt($start)) {
                    continue;
                }

                $suggestions[] = [
                    'date' => $day->toDateString(),
                    'date_display' => $day->format('M j, Y'),
                    'start' => $slot['start'],
                    'end' => $slot['end'],
                    'start_display' => $slot['start_display'],
                    'end_display' => $slot['end_display'],
                    'label' => $day->format('D, M j') . ' · ' . $slot['start_display'] . ' – ' . $slot['end_display'],
                ];

                if (count($suggestions) >= $limit) {
                    break;
                }
            }

            $day->addDay();
        }

        return $suggestions;
    }

    /**
     * Most-used teacher/room for a subject based on past calendar events.
     */
    public static function preferredResourcesForSubject($subjectId): array
    {
        if (!$subjectId) {
            return [
                'teacher_id' => null,
                'teacher_name' => null,
                'room_id' => null,
                'room_name' => null,
                'teacher_uses' => 0,
                'room_uses' => 0,
            ];
        }

        $teacherRow = self::query()
            ->where('subject_id', $subjectId)
            ->whereNotNull('teacher_id')
            ->selectRaw('teacher_id, COUNT(*) as uses')
            ->groupBy('teacher_id')
            ->orderByDesc('uses')
            ->first();

        $roomRow = self::query()
            ->where('subject_id', $subjectId)
            ->whereNotNull('room_id')
            ->selectRaw('room_id, COUNT(*) as uses')
            ->groupBy('room_id')
            ->orderByDesc('uses')
            ->first();

        $teacher = $teacherRow ? Teacher::find($teacherRow->teacher_id) : null;
        $room = $roomRow ? Room::find($roomRow->room_id) : null;

        return [
            'teacher_id' => $teacher?->id,
            'teacher_name' => $teacher?->full_name,
            'room_id' => $room?->id,
            'room_name' => $room?->full_name ?? $room?->room_name,
            'teacher_uses' => (int) ($teacherRow->uses ?? 0),
            'room_uses' => (int) ($roomRow->uses ?? 0),
        ];
    }

    /**
     * Daily workload summary for teacher and/or room.
     */
    public static function workloadForDate($date, $teacherId = null, $roomId = null, $excludeEventId = null): array
    {
        $day = Carbon::parse($date)->startOfDay();
        $dayEnd = $day->copy()->endOfDay();
        $eventWarnAt = 5;
        $hoursWarnAt = 6.0;

        $summarize = function ($field, $id) use ($day, $dayEnd, $excludeEventId, $eventWarnAt, $hoursWarnAt) {
            if (!$id) {
                return null;
            }

            $query = self::where($field, $id)
                ->where('start_time', '<', $dayEnd)
                ->where('end_time', '>', $day);

            if ($excludeEventId) {
                $query->where('id', '!=', $excludeEventId);
            }

            $events = $query->orderBy('start_time')->get(['id', 'title', 'start_time', 'end_time']);
            $minutes = $events->sum(fn ($e) => $e->start_time->diffInMinutes($e->end_time));
            $hours = round($minutes / 60, 1);
            $count = $events->count();
            $level = 'ok';

            if ($count >= $eventWarnAt || $hours >= $hoursWarnAt) {
                $level = 'high';
            } elseif ($count >= max(3, $eventWarnAt - 2) || $hours >= ($hoursWarnAt - 2)) {
                $level = 'moderate';
            }

            return [
                'count' => $count,
                'hours' => $hours,
                'level' => $level,
                'warn' => $level !== 'ok',
                'message' => $level === 'high'
                    ? "High load: {$count} event(s), {$hours} hour(s) on this day."
                    : ($level === 'moderate'
                        ? "Moderate load: {$count} event(s), {$hours} hour(s) on this day."
                        : "{$count} event(s), {$hours} hour(s) scheduled on this day."),
                'events' => $events->take(5)->map(fn ($e) => [
                    'title' => $e->title,
                    'time' => $e->start_time->format('g:i A') . ' – ' . $e->end_time->format('g:i A'),
                ])->values()->all(),
            ];
        };

        $teacher = $summarize('teacher_id', $teacherId);
        $room = $summarize('room_id', $roomId);

        return [
            'date' => $day->toDateString(),
            'date_display' => $day->format('M j, Y'),
            'teacher' => $teacher,
            'room' => $room,
            'has_warning' => ($teacher['warn'] ?? false) || ($room['warn'] ?? false),
        ];
    }
}
