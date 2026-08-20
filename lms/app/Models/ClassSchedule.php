<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'subject_id',
        'teacher_id',
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
        'class_type',
        'color',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the section for this schedule
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the subject for this schedule
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher for this schedule
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the room for this schedule
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Scope to get only active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get schedules by day of week
     */
    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', strtolower($day));
    }

    /**
     * Scope to get schedules by section
     */
    public function scopeBySection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope to get schedules by teacher
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Get the duration of the class in minutes
     */
    public function getDurationAttribute()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        return $start->diffInMinutes($end);
    }

    /**
     * Get the formatted time range
     */
    public function getTimeRangeAttribute()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        return $start->format('h:i A') . ' - ' . $end->format('h:i A');
    }

    /**
     * Get the day name in proper case
     */
    public function getDayNameAttribute()
    {
        return ucfirst($this->day_of_week);
    }

    /**
     * Get the formatted class type display name
     */
    public function getClassTypeDisplayAttribute()
    {
        $types = [
            'lecture' => 'Lecture',
            'laboratory' => 'Laboratory',
            'tutorial' => 'Tutorial',
            'exam' => 'Exam',
            'other' => 'Other'
        ];

        return $types[$this->class_type] ?? 'Lecture';
    }

    /**
     * Get the subject color or default color
     */
    public function getSubjectColorAttribute()
    {
        // You can customize this based on subject or use the stored color
        $subjectColors = [
            'Mathematics' => '#dc3545',
            'Science' => '#28a745',
            'English' => '#17a2b8',
            'History' => '#ffc107',
            'Geography' => '#6f42c1',
            'Physics' => '#fd7e14',
            'Chemistry' => '#20c997',
            'Biology' => '#198754',
            'Computer Science' => '#0d6efd',
            'Literature' => '#e83e8c'
        ];

        $subjectName = $this->subject->subject_name ?? '';
        return $subjectColors[$subjectName] ?? $this->color;
    }

    /**
     * Get student schedules for a specific student
     */
    public static function getStudentSchedules($studentId)
    {
        $student = Student::find($studentId);
        if (!$student) {
            Log::info('Student not found', ['student_id' => $studentId]);
            return collect();
        }

        // Get the student's sections (many-to-many relationship)
        $sectionIds = $student->sections()->pluck('sections.id');
        
        Log::info('Student sections debug', [
            'student_id' => $studentId,
            'student_name' => $student->full_name,
            'section_ids' => $sectionIds->toArray(),
            'section_count' => $sectionIds->count()
        ]);
        
        if ($sectionIds->isEmpty()) {
            Log::info('No sections found for student', ['student_id' => $studentId]);
            return collect();
        }

        $schedules = self::with(['subject', 'teacher', 'room'])
            ->whereIn('section_id', $sectionIds)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        Log::info('Schedules found for student', [
            'student_id' => $studentId,
            'schedule_count' => $schedules->count(),
            'section_ids_queried' => $sectionIds->toArray()
        ]);

        return $schedules;
    }

    /**
     * Get weekly schedule for a student
     */
    public static function getWeeklySchedule($studentId, $startDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->startOfWeek();
        }

        $schedules = self::getStudentSchedules($studentId);
        $weeklySchedule = [];

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            $daySchedules = $schedules->where('day_of_week', $day)->sortBy('start_time');
            $weeklySchedule[$day] = $daySchedules;
        }

        return $weeklySchedule;
    }

    /**
     * Get today's schedule for a student
     */
    public static function getTodaySchedule($studentId)
    {
        $today = Carbon::now()->format('l');
        $dayOfWeek = strtolower($today);

        $student = Student::find($studentId);
        if (!$student) {
            return collect();
        }

        // Get the student's sections (many-to-many relationship)
        $sectionIds = $student->sections()->pluck('sections.id');
        
        if ($sectionIds->isEmpty()) {
            return collect();
        }

        return self::with(['subject', 'teacher', 'room'])
            ->whereIn('section_id', $sectionIds)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get next 5-7 days schedule for a student
     */
    public static function getNextDaysSchedule($studentId, $days = 7)
    {
        $schedules = self::getStudentSchedules($studentId);
        $nextDaysSchedule = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->addDays($i);
            $dayOfWeek = strtolower($date->format('l'));
            
            $daySchedules = $schedules->where('day_of_week', $dayOfWeek)->sortBy('start_time');
            
            $nextDaysSchedule[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'day_of_week' => $dayOfWeek,
                'schedules' => $daySchedules
            ];
        }

        return $nextDaysSchedule;
    }
}
