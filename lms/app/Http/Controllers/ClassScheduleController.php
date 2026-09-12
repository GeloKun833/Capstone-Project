<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClassScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $studentId = $request->get('student_id');
        $user = Auth::user();
        
        // Debug logging for user info
        Log::info('Schedule Access Attempt', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role_name,
            'requested_student_id' => $studentId
        ]);
        
        // Handle different user roles
        if ($user->role_name === 'Student') {
            // If no student_id provided, try to get current user's student record
            if (!$studentId) {
                $student = $user->student;
                if ($student) {
                    $studentId = $student->id;
                }
            }
        } elseif ($user->role_name === 'Parent') {
            // For parents, get their children and use the first one if no specific child selected
            $children = \App\Models\Student::where('parent_email', $user->email)->get();
            
            Log::info('Parent children found', [
                'parent_email' => $user->email,
                'children_count' => $children->count(),
                'children_ids' => $children->pluck('id')->toArray()
            ]);
            
            if ($children->isEmpty()) {
                Log::warning('No children linked to parent', ['parent_email' => $user->email]);
                return redirect()->route('dashboard')->with('error', 'No children are linked to your account. Please contact the school administration to link your children.');
            }
            
            if (!$studentId) {
                $studentId = $children->first()->id;
                Log::info('Using first child', ['student_id' => $studentId]);
            } else {
                // Verify the student belongs to this parent
                $child = $children->where('id', $studentId)->first();
                if (!$child) {
                    Log::warning('Parent trying to access unauthorized student', [
                        'parent_email' => $user->email,
                        'requested_student_id' => $studentId
                    ]);
                    return redirect()->route('dashboard')->with('error', 'Access denied. This student is not linked to your account.');
                }
            }
        }

        if (!$studentId) {
            Log::error('No student ID found', ['user_role' => $user->role_name]);
            
            if ($user->role_name === 'Parent') {
                return redirect()->route('dashboard')->with('error', 'Unable to find your child\'s information. Please contact the school administration.');
            }
            
            return redirect()->back()->with('error', 'Student not found');
        }

        $view = $request->get('view', 'week');
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now();

        $student = Student::with('sections')->find($studentId);
        if (!$student) {
            Log::error('Student not found in database', ['student_id' => $studentId]);
            
            if ($user->role_name === 'Parent') {
                return redirect()->route('dashboard')->with('error', 'Your child\'s information could not be found. Please contact the school administration.');
            }
            
            return redirect()->back()->with('error', 'Student not found');
        }

        Log::info('Student found', [
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'sections_count' => $student->sections->count(),
            'section_ids' => $student->sections->pluck('id')->toArray()
        ]);

        if ($student->sections->isEmpty()) {
            Log::warning('Student has no sections assigned', [
                'student_id' => $student->id,
                'student_name' => $student->full_name
            ]);
            
            if ($user->role_name === 'Parent') {
                return redirect()->route('dashboard')->with('error', 'Your child is not assigned to any section. Please contact the school administration.');
            }
            
            return redirect()->back()->with('error', 'Student is not assigned to any section');
        }

        // Debug logging
        Log::info('Schedule Debug', [
            'student_id' => $studentId,
            'student_name' => $student->full_name,
            'sections_count' => $student->sections->count(),
            'section_ids' => $student->sections->pluck('id')->toArray(),
            'user_role' => $user->role_name
        ]);

        if ($request->ajax()) {
            return $this->getScheduleData($studentId, $view, $startDate);
        }

        $weeklySchedule = ClassSchedule::getWeeklySchedule($studentId, $startDate);
        $todaySchedule = ClassSchedule::getTodaySchedule($studentId);
        $nextDaysSchedule = ClassSchedule::getNextDaysSchedule($studentId, 7);

        // Debug logging for schedules
        Log::info('Schedule Data', [
            'weekly_schedule_count' => collect($weeklySchedule)->flatten()->count(),
            'today_schedule_count' => $todaySchedule->count(),
            'next_days_schedule_count' => count($nextDaysSchedule)
        ]);

        return view('schedule.index', compact('student', 'weeklySchedule', 'todaySchedule', 'nextDaysSchedule', 'view', 'startDate'));
    }

    /**
     * Get schedule data for AJAX requests
     */
    private function getScheduleData($studentId, $view, $startDate)
    {
        switch ($view) {
            case 'week':
                $schedules = ClassSchedule::getWeeklySchedule($studentId, $startDate);
                return response()->json($this->formatWeeklyData($schedules, $startDate));
            
            case 'today':
                $schedules = ClassSchedule::getTodaySchedule($studentId);
                return response()->json($this->formatTodayData($schedules));
            
            case 'next_days':
                $schedules = ClassSchedule::getNextDaysSchedule($studentId, 7);
                return response()->json($this->formatNextDaysData($schedules));
            
            default:
                $schedules = ClassSchedule::getWeeklySchedule($studentId, $startDate);
                return response()->json($this->formatWeeklyData($schedules, $startDate));
        }
    }

    /**
     * Format weekly schedule data for FullCalendar
     */
    private function formatWeeklyData($schedules, $startDate)
    {
        $events = [];
        $weekStart = $startDate->copy()->startOfWeek();

        foreach ($schedules as $day => $daySchedules) {
            $dayDate = $weekStart->copy();
            
            // Find the correct day of the week
            $dayMap = ['monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 0];
            $targetDay = $dayMap[$day] ?? 1;
            
            while ($dayDate->dayOfWeek !== $targetDay) {
                $dayDate->addDay();
            }

            foreach ($daySchedules as $schedule) {
                $startDateTime = $dayDate->copy()->setTimeFrom($schedule->start_time);
                $endDateTime = $dayDate->copy()->setTimeFrom($schedule->end_time);

                $events[] = [
                    'id' => $schedule->id,
                    'title' => $schedule->subject->subject_name,
                    'start' => $startDateTime->toISOString(),
                    'end' => $endDateTime->toISOString(),
                    'color' => $schedule->subject_color,
                    'extendedProps' => [
                        'teacher' => $schedule->teacher->full_name,
                        'room' => $schedule->room ? $schedule->room->room_name : 'TBD',
                        'subject' => $schedule->subject->subject_name,
                        'time_range' => $schedule->time_range,
                        'duration' => $schedule->duration . ' min',
                        'notes' => $schedule->notes
                    ]
                ];
            }
        }

        return $events;
    }

    /**
     * Format today's schedule data
     */
    private function formatTodayData($schedules)
    {
        return $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'subject' => $schedule->subject->subject_name,
                'teacher' => $schedule->teacher->full_name,
                'room' => $schedule->room ? $schedule->room->room_name : 'TBD',
                'time_range' => $schedule->time_range,
                'duration' => $schedule->duration . ' min',
                'color' => $schedule->subject_color,
                'notes' => $schedule->notes
            ];
        });
    }

    /**
     * Format next days schedule data
     */
    private function formatNextDaysData($schedules)
    {
        return $schedules->map(function ($dayData) {
            return [
                'date' => $dayData['date'],
                'day_name' => $dayData['day_name'],
                'schedules' => $dayData['schedules']->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'subject' => $schedule->subject->subject_name,
                        'teacher' => $schedule->teacher->full_name,
                        'room' => $schedule->room ? $schedule->room->room_name : 'TBD',
                        'time_range' => $schedule->time_range,
                        'duration' => $schedule->duration . ' min',
                        'color' => $schedule->subject_color,
                        'notes' => $schedule->notes
                    ];
                })
            ];
        });
    }

    /**
     * Admin: Display all class schedules
     */
    public function adminIndex(Request $request)
    {
        $query = ClassSchedule::with(['subject', 'section', 'teacher', 'room']);
        
        // Filter by section if provided
        if ($request->has('section_id') && $request->section_id) {
            $query->where('section_id', $request->section_id);
        }
        
        // Filter by teacher if provided
        if ($request->has('teacher_id') && $request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }
        
        // Filter by day if provided
        if ($request->has('day_of_week') && $request->day_of_week) {
            $query->where('day_of_week', $request->day_of_week);
        }
        
        $schedules = $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(20);
        
        // Get filter data
        $sections = \App\Models\Section::orderBy('grade_level')->orderBy('name')->get();
        $teachers = \App\Models\Teacher::orderBy('full_name')->get();
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        return view('admin.schedules.index', compact('schedules', 'sections', 'teachers', 'days'));
    }

    /**
     * JSON: sections + subjects assigned to a teacher (for cascading schedule form).
     */
    public function teacherAssignments(\App\Models\Teacher $teacher)
    {
        $teacher->load(['subjects', 'sections', 'gradeLevels']);

        $sections = $teacher->sections
            ->sortBy(['grade_level', 'name'])
            ->values()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'grade_level' => $section->grade_level,
                    'label' => $section->name . ' (' . ($section->grade_level ?: 'N/A') . ')',
                ];
            });

        // Fallback: sections by assigned grade levels if no section_teacher rows
        if ($sections->isEmpty()) {
            $grades = $teacher->gradeLevels->pluck('grade_level')->filter()->unique()->values();
            if ($grades->isNotEmpty()) {
                $expanded = collect();
                foreach ($grades as $grade) {
                    foreach (\App\Services\GradeSubjectCatalogService::gradeAliases($grade) as $alias) {
                        $expanded->push($alias);
                    }
                }
                $sections = \App\Models\Section::query()
                    ->whereIn('grade_level', $expanded->unique()->all())
                    ->orderBy('grade_level')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($section) {
                        return [
                            'id' => $section->id,
                            'name' => $section->name,
                            'grade_level' => $section->grade_level,
                            'label' => $section->name . ' (' . ($section->grade_level ?: 'N/A') . ')',
                        ];
                    });
            }
        }

        $subjects = $teacher->subjects
            ->sortBy(['class', 'subject_name'])
            ->values()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'subject_name' => $subject->subject_name,
                    'class' => $subject->class,
                    'label' => $subject->subject_name . ' (' . ($subject->class ?: 'N/A') . ')',
                ];
            });

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->full_name,
            ],
            'sections' => $sections,
            'subjects' => $subjects,
            'grade_levels' => $teacher->gradeLevels->pluck('grade_level')->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teachers = \App\Models\Teacher::orderBy('full_name')->get();
        $rooms = \App\Models\Room::orderBy('room_name')->get();

        return view('admin.schedules.create', compact('teachers', 'rooms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'room_id' => 'nullable|exists:rooms,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'class_type' => 'required|in:lecture,laboratory,tutorial,exam,other',
            'color' => 'nullable|string|max:7',
            'notes' => 'nullable|string|max:500',
        ]);

        $assignmentError = $this->validateTeacherAssignment(
            (int) $validated['teacher_id'],
            (int) $validated['section_id'],
            (int) $validated['subject_id']
        );
        if ($assignmentError) {
            return redirect()->back()->withInput()->withErrors(['teacher_id' => $assignmentError]);
        }
        
        $validated['is_active'] = true;
        $validated['color'] = $validated['color'] ?? '#3d5ee1';
        
        $schedule = ClassSchedule::create($validated);
        
        Log::info('Schedule created by admin', [
            'schedule_id' => $schedule->id,
            'section_id' => $schedule->section_id,
            'subject_id' => $schedule->subject_id,
            'teacher_id' => $schedule->teacher_id,
            'day_of_week' => $schedule->day_of_week,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'is_active' => $schedule->is_active
        ]);
        
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Class schedule created successfully! Schedule will now appear for students, teachers, and parents.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassSchedule $schedule)
    {
        $schedule->load(['subject', 'section', 'teacher', 'room']);
        return view('admin.schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassSchedule $schedule)
    {
        $schedule->load(['subject', 'section', 'teacher', 'room']);
        $teachers = \App\Models\Teacher::orderBy('full_name')->get();
        $rooms = \App\Models\Room::orderBy('room_name')->get();

        return view('admin.schedules.edit', compact('schedule', 'teachers', 'rooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassSchedule $schedule)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'room_id' => 'nullable|exists:rooms,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'class_type' => 'required|in:lecture,laboratory,tutorial,exam,other',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $assignmentError = $this->validateTeacherAssignment(
            (int) $validated['teacher_id'],
            (int) $validated['section_id'],
            (int) $validated['subject_id']
        );
        if ($assignmentError) {
            return redirect()->back()->withInput()->withErrors(['teacher_id' => $assignmentError]);
        }

        $validated['is_active'] = $request->boolean('is_active');
        
        $schedule->update($validated);
        
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Class schedule updated successfully!');
    }

    /**
     * Ensure selected section/subject belong to the teacher assignments.
     */
    protected function validateTeacherAssignment(int $teacherId, int $sectionId, int $subjectId): ?string
    {
        $teacher = \App\Models\Teacher::with(['subjects', 'sections', 'gradeLevels'])->find($teacherId);
        if (!$teacher) {
            return 'Selected teacher was not found.';
        }

        $hasSubject = $teacher->subjects->contains('id', $subjectId);
        if (!$hasSubject) {
            return 'Selected subject is not assigned to this teacher. Assign it under Classes & Subjects first.';
        }

        $hasSection = $teacher->sections->contains('id', $sectionId);
        if (!$hasSection) {
            // allow grade-level fallback
            $section = \App\Models\Section::find($sectionId);
            $grades = $teacher->gradeLevels->pluck('grade_level')->filter()->all();
            $allowed = false;
            if ($section && !empty($grades)) {
                foreach ($grades as $grade) {
                    $aliases = \App\Services\GradeSubjectCatalogService::gradeAliases($grade);
                    if (in_array($section->grade_level, $aliases, true)) {
                        $allowed = true;
                        break;
                    }
                }
            }
            if (!$allowed) {
                return 'Selected section is not assigned to this teacher. Assign the section (or grade level) first.';
            }
        }

        return null;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassSchedule $schedule)
    {
        $schedule->delete();
        
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Class schedule deleted successfully!');
    }

    /**
     * Get student's dashboard schedule widget data
     */
    public function getDashboardSchedule($studentId = null)
    {
        if (!$studentId && Auth::user()->role_name === 'Student') {
            $student = Auth::user()->student;
            if ($student) {
                $studentId = $student->id;
            }
        }

        if (!$studentId) {
            return response()->json([]);
        }

        $nextDaysSchedule = ClassSchedule::getNextDaysSchedule($studentId, 5);
        
        return response()->json([
            'next_days' => $nextDaysSchedule,
            'today_schedule' => ClassSchedule::getTodaySchedule($studentId)
        ]);
    }

    /**
     * Display the student's personal schedule page
     */
    public function mySchedule()
    {
        if (Auth::user()->role_name !== 'Student') {
            return redirect()->back()->with('error', 'Access denied. This page is for students only.');
        }

        $student = Auth::user()->student;
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $weeklySchedule = ClassSchedule::getWeeklySchedule($student->id);
        $todaySchedule = ClassSchedule::getTodaySchedule($student->id);
        $nextDaysSchedule = ClassSchedule::getNextDaysSchedule($student->id, 7);

        return view('schedule.my-schedule', compact('student', 'weeklySchedule', 'todaySchedule', 'nextDaysSchedule'));
    }
    
    /**
     * Display the teacher's schedule page
     */
    public function teacherSchedule()
    {
        $teacher = Auth::user()->teacher;
        
        if (!$teacher) {
            return redirect()->back()->with('error', 'Teacher profile not found.');
        }
        
        // All active schedules for this teacher
        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $weeklySchedule = ClassSchedule::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->with(['subject', 'section', 'room'])
            ->get()
            ->sortBy(function ($schedule) use ($dayOrder) {
                $dayIndex = array_search($schedule->day_of_week, $dayOrder, true);
                $dayIndex = $dayIndex === false ? 99 : $dayIndex;
                $minutes = \Carbon\Carbon::parse($schedule->start_time)->secondsSinceMidnight();
                return ($dayIndex * 100000) + $minutes;
            })
            ->groupBy('day_of_week');
        
        return view('schedule.teacher-schedule', compact('teacher', 'weeklySchedule'));
    }
}
