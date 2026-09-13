<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalendarEventController extends Controller
{
    /**
     * Block teachers from editing/deleting events they did not create.
     */
    protected function authorizeEventManagement(CalendarEvent $calendarEvent, Request $request = null)
    {
        $user = Auth::user();
        if ($calendarEvent->canBeManagedBy($user)) {
            return null;
        }

        $message = 'You can only edit or delete events that you created.';

        if ($request && ($request->ajax() || $request->wantsJson())) {
            return response()->json([
                'success' => false,
                'error' => $message,
            ], 403);
        }

        abort(403, $message);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'month');
        $subjectId = $request->get('subject_id');
        $teacherId = $request->get('teacher_id');
        $roomId = $request->get('room_id');
        $eventType = $request->get('event_type');
        $filterGroup = $request->get('filter_group');
        $search = $request->get('search');
        $startDate = $request->get('start');
        $endDate = $request->get('end');
        $childId = $request->get('child_id');
        $user = Auth::user();

        $isStudent = $user && $user->role_name === 'Student';
        $isParent = $user && $user->role_name === 'Parent';
        $isViewerOnly = $isStudent || $isParent;

        $student = null;
        $children = collect();
        $selectedChildren = collect();

        if ($isStudent) {
            $student = $user->student;
            if (!$student) {
                abort(403, 'Student profile not found.');
            }
            $selectedChildren = collect([$student]);
        }

        if ($isParent) {
            $portal = app(\App\Services\ParentPortalService::class);
            $children = $portal->getChildrenForParent($user);
            if ($children->isEmpty()) {
                abort(403, 'No children linked to this parent account.');
            }

            if ($childId) {
                $selectedChildren = collect([
                    $portal->resolveChildForParent($user, (int) $childId),
                ]);
            } else {
                $selectedChildren = $children;
            }
        }

        $eventsQuery = CalendarEvent::with(['subject:id,subject_name,class', 'teacher:id,full_name', 'room:id,room_name,room_number', 'createdBy:id,name'])
            ->when($isStudent, fn ($query) => $query->visibleToStudent($student))
            ->when($isParent, fn ($query) => $query->visibleToStudents($selectedChildren))
            ->when($subjectId, fn ($query) => $query->bySubject($subjectId))
            ->when(!$isViewerOnly && $teacherId, fn ($query) => $query->byTeacher($teacherId))
            ->when(!$isViewerOnly && $roomId, fn ($query) => $query->where('room_id', $roomId))
            ->when($eventType, fn ($query) => $query->byType($eventType))
            ->when($filterGroup, function ($query) use ($filterGroup) {
                return match ($filterGroup) {
                    'exams' => $query->byType('exam'),
                    'activities' => $query->byType('activity'),
                    'deadlines' => $query->byType('deadline'),
                    'holidays' => $query->byType('holiday'),
                    'school' => $query->whereIn('event_type', ['meeting', 'other'])->whereNull('subject_id'),
                    'subjects' => $query->whereNotNull('subject_id')->where('event_type', '!=', 'holiday'),
                    default => $query,
                };
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($startDate && $endDate, fn ($query) => $query->inDateRange($startDate, $endDate))
            ->orderBy('start_time');

        if ($request->ajax() || $request->wantsJson() || $request->boolean('json')) {
            $formattedEvents = $eventsQuery->get()->map(function ($event) use ($user, $isViewerOnly, $isParent, $selectedChildren) {
                $canManage = !$isViewerOnly && $event->canBeManagedBy($user);
                $relevant = $isParent
                    ? $event->relevantChildrenAmong($selectedChildren)
                    : collect();

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_time->toIso8601String(),
                    'end' => $event->end_time->toIso8601String(),
                    'color' => $event->event_color,
                    'allDay' => (bool) $event->is_all_day,
                    'editable' => $canManage,
                    'startEditable' => $canManage,
                    'durationEditable' => $canManage,
                    'extendedProps' => [
                        'description' => $event->description,
                        'event_type' => $event->event_type,
                        'subject' => $event->subject?->subject_name,
                        'subject_id' => $event->subject_id,
                        'subject_class' => $event->subject?->class,
                        'teacher' => $event->teacher?->full_name,
                        'teacher_id' => $event->teacher_id,
                        'room' => $event->room?->full_name ?? $event->room?->room_name,
                        'room_id' => $event->room_id,
                        'is_all_day' => (bool) $event->is_all_day,
                        'is_recurring' => (bool) $event->is_recurring,
                        'recurrence_pattern' => $event->recurrence_pattern,
                        'recurrence_end_date' => optional($event->recurrence_end_date)?->format('Y-m-d'),
                        'start_local' => $event->start_time->format('Y-m-d\TH:i'),
                        'end_local' => $event->end_time->format('Y-m-d\TH:i'),
                        'created_by' => $event->created_by,
                        'organizer' => $event->createdBy?->name,
                        'can_manage' => $canManage,
                        'children' => $relevant->map(fn ($c) => [
                            'id' => $c->id,
                            'name' => $c->full_name,
                            'grade' => $c->year_level ?: $c->class,
                            'section' => $c->section,
                        ])->values()->all(),
                        'child_names' => $relevant->pluck('full_name')->implode(', '),
                    ],
                ];
            });

            return response()->json($formattedEvents);
        }

        if ($isStudent) {
            $subjects = $student->subjects()->orderBy('subject_name')->get(['subjects.id', 'subjects.subject_name']);
            $eventTypes = ['exam', 'activity', 'meeting', 'deadline', 'holiday', 'other'];

            return view('calendar.student', compact('subjects', 'view', 'eventTypes', 'student'));
        }

        if ($isParent) {
            $subjectIds = [];
            foreach ($children as $child) {
                $subjectIds = array_merge($subjectIds, $child->subjects()->pluck('subjects.id')->all());
            }
            $subjects = Subject::whereIn('id', array_unique($subjectIds) ?: [0])
                ->orderBy('subject_name')
                ->get(['id', 'subject_name']);

            $upcomingEvents = CalendarEvent::with(['subject:id,subject_name', 'teacher:id,full_name'])
                ->visibleToStudents($selectedChildren)
                ->where('start_time', '>=', now()->startOfDay())
                ->orderBy('start_time')
                ->limit(6)
                ->get();

            $eventTypes = ['exam', 'activity', 'meeting', 'deadline', 'holiday', 'other'];
            $selectedChildId = $childId ? (int) $childId : null;

            return view('calendar.parent', compact(
                'subjects',
                'view',
                'eventTypes',
                'children',
                'selectedChildId',
                'upcomingEvents'
            ));
        }

        $subjects = Subject::orderBy('subject_name')->get(['id', 'subject_name']);
        $teachers = Teacher::orderBy('full_name')->get(['id', 'full_name']);
        $rooms = Room::active()->orderBy('room_name')->get();
        $eventTypes = ['exam', 'activity', 'meeting', 'deadline', 'holiday', 'other'];
        $isAdmin = $user && $user->role_name === 'Admin';

        return view('calendar.index', compact('subjects', 'teachers', 'rooms', 'view', 'eventTypes', 'isAdmin'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (in_array(Auth::user()?->role_name, ['Student', 'Parent'], true)) {
            abort(403, 'You are not allowed to create events.');
        }

        $subjects = Subject::all();
        $teachers = Teacher::all();
        $rooms = Room::active()->get();
        $eventTypes = ['exam', 'activity', 'meeting', 'deadline', 'holiday', 'other'];

        return view('calendar.create', compact('subjects', 'teachers', 'rooms', 'eventTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (in_array(Auth::user()?->role_name, ['Student', 'Parent'], true)) {
            abort(403, 'You are not allowed to create events.');
        }

        $request->merge([
            'subject_id' => $request->filled('subject_id') ? $request->subject_id : null,
            'teacher_id' => $request->filled('teacher_id') ? $request->teacher_id : null,
            'room_id' => $request->filled('room_id') ? $request->room_id : null,
            'recurrence_pattern' => $request->boolean('is_recurring') ? $request->recurrence_pattern : null,
            'recurrence_end_date' => $request->boolean('is_recurring') ? $request->recurrence_end_date : null,
        ]);

        $validator = Validator::make($request->all(), [
            'title' => \App\Support\FormRules::TITLE_NO_EMOJI,
            'description' => 'nullable|string|max:2000',
            'event_type' => 'required|in:exam,activity,meeting,deadline,holiday,other',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'room_id' => 'nullable|exists:rooms,id',
            'is_all_day' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,monthly,custom',
            'recurrence_end_date' => 'nullable|date|after:start_time'
        ], \App\Support\FormRules::messages());

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    'error' => 'Unable to create event. Please check the highlighted fields.',
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // School events: max 3 calendar days
        if ($tooLong = $this->rejectIfEventTooLong($request)) {
            return $tooLong;
        }

        // Check for scheduling conflicts
        $conflicts = CalendarEvent::checkConflicts(
            $request->start_time,
            $request->end_time,
            $request->teacher_id,
            $request->room_id,
            null,
            $request->subject_id
        );

        if (!empty($conflicts['teacher']) || !empty($conflicts['room'])) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Schedule conflict detected. Please select another time or resource.',
                    'conflicts' => $conflicts,
                    'available_slots' => CalendarEvent::getAvailableTimeSlots(
                        $request->start_time,
                        $request->teacher_id,
                        $request->room_id
                    )
                ], 409);
            }

            return redirect()->back()
                ->with('error', 'Schedule conflict detected. Please select another time or resource.')
                ->withInput();
        }

        $event = CalendarEvent::create([
            'title' => $request->title,
            'description' => $request->description,
            'event_type' => $request->event_type,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'room_id' => $request->room_id,
            'created_by' => Auth::id(),
            'is_all_day' => $request->boolean('is_all_day'),
            'is_recurring' => $request->boolean('is_recurring'),
            'recurrence_pattern' => $request->recurrence_pattern,
            'recurrence_end_date' => $request->recurrence_end_date
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Event created successfully.',
                'event' => $event->load(['subject', 'teacher', 'room'])
            ]);
        }

        return redirect()->route('calendar.index')->with('success', 'Event created successfully.')->with('refresh_calendar', true);
    }

    /**
     * Display the specified resource.
     */
    public function show(CalendarEvent $calendarEvent)
    {
        $user = Auth::user();

        if ($user && $user->role_name === 'Student') {
            $student = $user->student;
            if (!$student || !$calendarEvent->isVisibleToStudent($student)) {
                abort(403, 'You are not allowed to view this event.');
            }
        }

        if ($user && $user->role_name === 'Parent') {
            $portal = app(\App\Services\ParentPortalService::class);
            $children = $portal->getChildrenForParent($user);
            if ($children->isEmpty() || $calendarEvent->relevantChildrenAmong($children)->isEmpty()) {
                abort(403, 'You are not allowed to view this event.');
            }
        }

        $calendarEvent->load(['subject', 'teacher', 'room', 'createdBy']);
        $canManage = $calendarEvent->canBeManagedBy($user);

        if ($user && $user->role_name === 'Student') {
            return view('calendar.student-show', compact('calendarEvent'));
        }

        if ($user && $user->role_name === 'Parent') {
            $portal = app(\App\Services\ParentPortalService::class);
            $children = $portal->getChildrenForParent($user);
            $relevantChildren = $calendarEvent->relevantChildrenAmong($children);

            return view('calendar.parent-show', compact('calendarEvent', 'relevantChildren'));
        }

        return view('calendar.show', compact('calendarEvent', 'canManage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CalendarEvent $calendarEvent)
    {
        if ($denied = $this->authorizeEventManagement($calendarEvent, request())) {
            return $denied;
        }

        $subjects = Subject::all();
        $teachers = Teacher::all();
        $rooms = Room::active()->get();
        $eventTypes = ['exam', 'activity', 'meeting', 'deadline', 'holiday', 'other'];

        return view('calendar.edit', compact('calendarEvent', 'subjects', 'teachers', 'rooms', 'eventTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        if ($denied = $this->authorizeEventManagement($calendarEvent, $request)) {
            return $denied;
        }

        // Drag/resize from calendar only sends new dates
        if ($request->boolean('dates_only') || (!$request->filled('title') && $request->filled('start_time') && $request->filled('end_time'))) {
            $request->validate([
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
            ]);

            if ($tooLong = $this->rejectIfEventTooLong($request)) {
                return $tooLong;
            }

            $conflicts = CalendarEvent::checkConflicts(
                $request->start_time,
                $request->end_time,
                $calendarEvent->teacher_id,
                $calendarEvent->room_id,
                $calendarEvent->id
            );

            if (!empty($conflicts['teacher']) || !empty($conflicts['room'])) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Schedule conflict detected. Please select another time or resource.',
                        'conflicts' => $conflicts,
                    ], 409);
                }
                return redirect()->back()->with('error', 'Schedule conflict detected.');
            }

            $calendarEvent->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event updated successfully.',
                    'event' => $calendarEvent->fresh()->load(['subject', 'teacher', 'room']),
                ]);
            }

            return redirect()->route('calendar.index')->with('success', 'Event updated successfully.');
        }

        $request->merge([
            'subject_id' => $request->filled('subject_id') ? $request->subject_id : null,
            'teacher_id' => $request->filled('teacher_id') ? $request->teacher_id : null,
            'room_id' => $request->filled('room_id') ? $request->room_id : null,
            'recurrence_pattern' => $request->boolean('is_recurring') ? $request->recurrence_pattern : null,
            'recurrence_end_date' => $request->boolean('is_recurring') ? $request->recurrence_end_date : null,
        ]);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:exam,activity,meeting,deadline,holiday,other',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'room_id' => 'nullable|exists:rooms,id',
            'is_all_day' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,monthly,custom',
            'recurrence_end_date' => 'nullable|date|after:start_time'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors(), 'error' => 'Unable to update event. Please check the highlighted fields.'], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($tooLong = $this->rejectIfEventTooLong($request)) {
            return $tooLong;
        }

        // Check for scheduling conflicts (excluding current event)
        $conflicts = CalendarEvent::checkConflicts(
            $request->start_time,
            $request->end_time,
            $request->teacher_id,
            $request->room_id,
            $calendarEvent->id,
            $request->subject_id
        );

        if (!empty($conflicts['teacher']) || !empty($conflicts['room'])) {
            $conflictMessage = 'Schedule conflict detected. Please select another time or resource.';

            if ($request->ajax()) {
                return response()->json([
                    'error' => $conflictMessage,
                    'conflicts' => $conflicts,
                    'available_slots' => CalendarEvent::getAvailableTimeSlots(
                        $request->start_time,
                        $request->teacher_id,
                        $request->room_id
                    )
                ], 409);
            }

            return redirect()->back()
                ->with('error', $conflictMessage)
                ->withInput();
        }

        $calendarEvent->update([
            'title' => $request->title,
            'description' => $request->description,
            'event_type' => $request->event_type,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'room_id' => $request->room_id,
            'is_all_day' => $request->boolean('is_all_day'),
            'is_recurring' => $request->boolean('is_recurring'),
            'recurrence_pattern' => $request->recurrence_pattern,
            'recurrence_end_date' => $request->recurrence_end_date
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully.',
                'event' => $calendarEvent->fresh()->load(['subject', 'teacher', 'room']),
            ]);
        }

        return redirect()->route('calendar.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalendarEvent $calendarEvent, Request $request)
    {
        if ($denied = $this->authorizeEventManagement($calendarEvent, $request)) {
            return $denied;
        }

        $calendarEvent->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully.',
            ]);
        }

        return redirect()->route('calendar.index')->with('success', 'Event deleted successfully.');
    }

    /**
     * Get available time slots for a given date
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'teacher_id' => 'nullable|exists:teachers,id',
            'room_id' => 'nullable|exists:rooms,id',
            'duration' => 'nullable|integer|min:15|max:480'
        ]);

        $slots = CalendarEvent::getAvailableTimeSlots(
            $request->date,
            $request->teacher_id,
            $request->room_id,
            $request->duration ?? 60
        );

        return response()->json($slots);
    }

    /**
     * Preferred teacher/room for a subject based on past events
     */
    public function subjectPreferences(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        return response()->json(
            CalendarEvent::preferredResourcesForSubject($request->subject_id)
        );
    }

    /**
     * Daily workload for teacher/room
     */
    public function workload(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'teacher_id' => 'nullable|exists:teachers,id',
            'room_id' => 'nullable|exists:rooms,id',
            'exclude_event_id' => 'nullable|exists:calendar_events,id',
        ]);

        return response()->json(
            CalendarEvent::workloadForDate(
                $request->date,
                $request->teacher_id,
                $request->room_id,
                $request->exclude_event_id
            )
        );
    }

    /**
     * Check for conflicts (includes suggestions + workload)
     */
    public function checkConflicts(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'teacher_id' => 'nullable|exists:teachers,id',
            'room_id' => 'nullable|exists:rooms,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'exclude_event_id' => 'nullable|exists:calendar_events,id'
        ]);

        $duration = max(15, Carbon::parse($request->start_time)->diffInMinutes(Carbon::parse($request->end_time)));

        $conflicts = CalendarEvent::checkConflicts(
            $request->start_time,
            $request->end_time,
            $request->teacher_id,
            $request->room_id,
            $request->exclude_event_id,
            $request->subject_id
        );

        $blocking = !empty($conflicts['teacher']) || !empty($conflicts['room']);
        $availableSlots = CalendarEvent::getAvailableTimeSlots(
            $request->start_time,
            $request->teacher_id,
            $request->room_id,
            $duration
        );

        $suggestions = $blocking
            ? CalendarEvent::suggestNextAvailableSlots(
                $request->start_time,
                $request->end_time,
                $request->teacher_id,
                $request->room_id,
                5,
                7
            )
            : [];

        $workload = CalendarEvent::workloadForDate(
            $request->start_time,
            $request->teacher_id,
            $request->room_id,
            $request->exclude_event_id
        );

        return response()->json([
            'has_conflicts' => !empty($conflicts),
            'blocking' => $blocking,
            'message' => $blocking
                ? 'Schedule conflict detected. Please select another time or resource.'
                : (empty($conflicts) ? 'No scheduling conflicts detected.' : 'Related schedule overlaps found.'),
            'conflicts' => $conflicts,
            'available_slots' => $availableSlots,
            'suggestions' => $suggestions,
            'workload' => $workload,
        ]);
    }

    /**
     * Normal school events may span at most 3 calendar days.
     */
    protected function rejectIfEventTooLong(Request $request)
    {
        try {
            $start = Carbon::parse($request->start_time)->startOfDay();
            $end = Carbon::parse($request->end_time)->startOfDay();
            if ($start->diffInDays($end) > 2) {
                $msg = 'Event duration cannot exceed 3 days. Use a single day or short multi-day event.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => $msg, 'errors' => ['end_time' => [$msg]]], 422);
                }
                return redirect()->back()->withErrors(['end_time' => $msg])->withInput();
            }
        } catch (\Throwable $e) {
            // ignore parse issues; other validators will catch
        }

        return null;
    }

    /**
     * Display a paginated list of all events with filters
     */
    public function eventsList(Request $request)
    {
        $query = CalendarEvent::with(['subject', 'teacher', 'room']);
        
        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        
        // Filter by date range
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('start_time', today());
                    break;
                case 'week':
                    $query->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('start_time', now()->month)
                          ->whereYear('start_time', now()->year);
                    break;
                case 'future':
                    $query->where('start_time', '>=', now());
                    break;
            }
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('subject', function($sq) use ($search) {
                      $sq->where('subject_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('teacher', function($sq) use ($search) {
                      $sq->where('full_name', 'like', "%{$search}%");
                  });
            });
        }
        
        $events = $query->orderBy('start_time', 'desc')->paginate(15);
        $isAdmin = Auth::user() && Auth::user()->role_name === 'Admin';

        return view('calendar.events-list', compact('events', 'isAdmin'));
    }
}
