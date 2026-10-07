<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AttendanceExport;
use App\Models\User;
use App\Models\Section;
use App\Services\TeacherClassAssignmentService;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole(User::ROLE_TEACHER) && !auth()->user()->hasRole(User::ROLE_ADMIN)) {
                abort(403, 'Only teachers and administrators can manage attendance.');
            }
            return $next($request);
        })->except(['studentView', 'parentView']);
    }

    /**
     * Teacher attendance workspace: pick a class + date, mark present/absent.
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $isAdmin = auth()->user()->hasRole(User::ROLE_ADMIN);
        $classes = collect();
        $students = collect();
        $existing = [];
        $daySummary = [
            'total' => 0, 'present' => 0, 'absent' => 0, 'unmarked' => 0, 'percentage' => 0,
        ];

        $academicYear = AcademicYear::active();
        $yearId = $academicYear?->id;
        $clock = now('Asia/Manila');

        $sectionId = (int) $request->input('section_id');
        $subjectId = (int) $request->input('subject_id');
        $gradeFilter = (string) $request->input('grade_level', '');
        $teacherFilter = (int) $request->input('teacher_id');
        $search = trim((string) $request->input('search', ''));
        $date = $clock->toDateString();
        $nowTime = $clock->format('H:i');

        if ($request->filled('class') && str_contains($request->input('class'), '_')) {
            [$sectionId, $subjectId] = array_map('intval', explode('_', $request->input('class'), 2));
        }

        if ($teacher && $yearId) {
            $options = app(TeacherClassAssignmentService::class)->optionsFor($teacher, $yearId);
            $subjectIds = collect($options['subjectsBySection'])->flatten()->unique()->filter()->values();
            $sectionIds = collect(array_keys($options['subjectsBySection']))->map(fn ($id) => (int) $id);
            $allowedSectionIds = Section::forAcademicYear($yearId)->whereIn('id', $sectionIds)->pluck('id')->map(fn ($id) => (int) $id)->all();

            $subjectModels = Subject::whereIn('id', $subjectIds)->orderBy('subject_name')->get()->keyBy('id');
            $sectionModels = Section::whereIn('id', $allowedSectionIds)->orderBy('name')->get()->keyBy('id');

            foreach ($options['subjectsBySection'] as $sid => $subjIds) {
                $section = $sectionModels->get((int) $sid);
                if (! $section) {
                    continue;
                }
                foreach ($subjIds as $subId) {
                    $subject = $subjectModels->get((int) $subId);
                    if (! $subject) {
                        continue;
                    }
                    $classes->push([
                        'key' => $section->id . '_' . $subject->id,
                        'section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'grade' => (string) ($section->grade_level ?? ''),
                        'section_name' => $section->name,
                        'subject_name' => $subject->subject_name,
                        'teacher_id' => $teacher->id,
                        'teacher_name' => '',
                        'label' => trim(($section->grade_level ? $section->grade_level . ' · ' : '') . $section->name . ' · ' . $subject->subject_name),
                    ]);
                }
            }
            $classes = $classes->sortBy('label')->values();
        } elseif ($isAdmin && $yearId) {
            $pairs = ClassSchedule::query()
                ->with(['teacher.user:user_id,name'])
                ->where('academic_year_id', $yearId)
                ->where('is_active', true)
                ->get()
                ->unique(fn ($row) => $row->section_id.'-'.$row->subject_id);
            $sectionModels = Section::whereIn('id', $pairs->pluck('section_id')->filter()->unique())->orderBy('name')->get()->keyBy('id');
            $subjectModels = Subject::whereIn('id', $pairs->pluck('subject_id')->filter()->unique())->orderBy('subject_name')->get()->keyBy('id');
            foreach ($pairs as $pair) {
                $section = $sectionModels->get((int) $pair->section_id);
                $subject = $subjectModels->get((int) $pair->subject_id);
                if (! $section || ! $subject) {
                    continue;
                }
                $teacherName = trim((string) ($pair->teacher?->user?->name ?? ''));
                $classes->push([
                    'key' => $section->id . '_' . $subject->id,
                    'section_id' => $section->id,
                    'subject_id' => $subject->id,
                    'grade' => (string) ($section->grade_level ?? ''),
                    'section_name' => $section->name,
                    'subject_name' => $subject->subject_name,
                    'teacher_id' => (int) $pair->teacher_id,
                    'teacher_name' => $teacherName,
                    'label' => trim(($section->grade_level ? $section->grade_level . ' · ' : '') . $section->name . ' · ' . $subject->subject_name . ($teacherName ? ' · ' . $teacherName : '')),
                ]);
            }
            $classes = $classes->sortBy('label')->values();
        }

        $grades = $classes->pluck('grade')->filter()->unique()->sort()->values();
        $teacherOptions = $classes
            ->filter(fn ($class) => $class['teacher_id'] && $class['teacher_name'] !== '')
            ->unique('teacher_id')
            ->sortBy('teacher_name')
            ->values();
        if ($gradeFilter !== '') {
            $classes = $classes->filter(fn ($class) => $class['grade'] === $gradeFilter)->values();
        }
        if ($isAdmin && $teacherFilter) {
            $classes = $classes->filter(fn ($class) => (int) $class['teacher_id'] === $teacherFilter)->values();
        }

        if ($classes->count() === 1 && ! $sectionId && ! $subjectId) {
            $sectionId = (int) $classes->first()['section_id'];
            $subjectId = (int) $classes->first()['subject_id'];
        }

        $dateOutsideYear = false;
        if ($academicYear?->start_date && $date < $academicYear->start_date->toDateString()) {
            $dateOutsideYear = true;
        }
        if ($academicYear?->end_date && $date > $academicYear->end_date->toDateString()) {
            $dateOutsideYear = true;
        }

        $selectedKey = ($sectionId && $subjectId) ? $sectionId . '_' . $subjectId : '';
        $classAllowed = $classes->contains(fn ($class) => $class['key'] === $selectedKey);
        $ready = $sectionId > 0 && $subjectId > 0 && $classAllowed && $yearId;

        if ($ready) {
            $students = Student::whereHas('sections', function ($query) use ($sectionId, $yearId) {
                $query->where('sections.id', $sectionId)
                    ->where('student_section_assignments.academic_year_id', $yearId);
            })->orderBy('last_name')->orderBy('first_name')->get();

            $dayRecords = $dateOutsideYear
                ? collect()
                : Attendance::query()
                ->where('subject_id', $subjectId)
                ->whereDate('date', $date)
                ->whereIn('student_id', $students->pluck('id')->all() ?: [0])
                ->get()
                ->keyBy('student_id');

            foreach ($dayRecords as $studentId => $row) {
                $existing[$studentId] = [
                    'status' => in_array($row->status, ['present', 'absent'], true) ? $row->status : '',
                    'remarks' => $row->remarks,
                    'id' => $row->id,
                    'time_in' => $row->time_in ? substr((string) $row->time_in, 0, 5) : '',
                ];
            }

            foreach ($students as $student) {
                $status = $existing[$student->id]['status'] ?? null;
                $daySummary['total']++;
                if ($status && isset($daySummary[$status])) {
                    $daySummary[$status]++;
                } else {
                    $daySummary['unmarked']++;
                }
            }
            $daySummary['percentage'] = $daySummary['total'] > 0
                ? round(($daySummary['present'] / $daySummary['total']) * 100, 1)
                : 0;
        }

        $selectedSection = $sectionId ? (Section::find($sectionId)?->name) : null;
        $selectedSubject = $subjectId ? (Subject::find($subjectId)?->subject_name) : null;

        return view('attendance.index', compact(
            'classes',
            'students',
            'existing',
            'daySummary',
            'sectionId',
            'subjectId',
            'date',
            'selectedKey',
            'ready',
            'selectedSection',
            'selectedSubject',
            'academicYear',
            'nowTime',
            'grades',
            'gradeFilter',
            'teacherOptions',
            'teacherFilter',
            'search',
            'isAdmin',
            'dateOutsideYear'
        ));
    }

    /**
     * Mark attendance now lives on the index page.
     */
    public function create(Request $request)
    {
        return redirect()->route('attendance.index', $request->only(['section_id', 'subject_id', 'date', 'class']));
    }

    public function report(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'period' => 'nullable|in:weekly,monthly',
        ]);

        $period = $request->input('period') === 'monthly' ? 'monthly' : 'weekly';
        $academicYear = AcademicYear::active();
        if (! $academicYear) {
            return redirect()->route('attendance.index')->with('error', 'Set a current academic year before viewing attendance.');
        }

        $sectionId = (int) $request->section_id;
        $subjectId = (int) $request->subject_id;
        $user = auth()->user();
        $teacher = $user->teacher;
        $allowed = false;

        if ($teacher) {
            $pairs = app(TeacherClassAssignmentService::class)->optionsFor($teacher, $academicYear->id);
            $allowedSubjects = $pairs['subjectsBySection'][$sectionId] ?? [];
            $allowed = in_array($subjectId, array_map('intval', $allowedSubjects), true);
        } elseif ($user->hasRole(User::ROLE_ADMIN)) {
            $allowed = ClassSchedule::query()
                ->where('section_id', $sectionId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $academicYear->id)
                ->where('is_active', true)
                ->exists();
        }

        if (! $allowed || ! Section::forAcademicYear($academicYear->id)->whereKey($sectionId)->exists()) {
            abort(403, 'You can only view attendance reports for classes in the current academic year.');
        }

        $clock = now('Asia/Manila');
        $rangeStart = $period === 'weekly'
            ? $clock->copy()->startOfWeek(\Carbon\Carbon::MONDAY)
            : $clock->copy()->startOfMonth();
        $rangeEnd = $period === 'weekly'
            ? $clock->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)
            : $clock->copy()->endOfMonth();

        if ($academicYear->start_date && $rangeStart->lt($academicYear->start_date)) {
            $rangeStart = $academicYear->start_date->copy()->startOfDay();
        }
        if ($academicYear->end_date && $rangeEnd->gt($academicYear->end_date)) {
            $rangeEnd = $academicYear->end_date->copy()->endOfDay();
        }

        $days = [];
        for ($day = $rangeStart->copy()->startOfDay(); $day->lte($rangeEnd->copy()->startOfDay()); $day->addDay()) {
            $days[] = $day->copy();
        }

        $section = Section::find($sectionId);
        $subject = Subject::find($subjectId);
        $students = Student::whereHas('sections', function ($query) use ($sectionId, $academicYear) {
            $query->where('sections.id', $sectionId)
                ->where('student_section_assignments.academic_year_id', $academicYear->id);
        })->orderBy('last_name')->orderBy('first_name')->get();

        $records = Attendance::query()
            ->where('subject_id', $subjectId)
            ->whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->whereIn('student_id', $students->pluck('id')->all() ?: [0])
            ->get()
            ->groupBy(fn ($row) => $row->student_id.'|'.$row->date->toDateString());

        $rows = [];
        foreach ($students as $student) {
            $present = 0;
            $absent = 0;
            $cells = [];
            foreach ($days as $day) {
                $record = $records->get($student->id.'|'.$day->toDateString())?->first();
                $cells[$day->toDateString()] = $record;
                if ($record?->status === 'present') {
                    $present++;
                } elseif ($record?->status === 'absent') {
                    $absent++;
                }
            }
            $marked = $present + $absent;
            $rows[] = [
                'student' => $student,
                'cells' => $cells,
                'present' => $present,
                'absent' => $absent,
                'rate' => $marked > 0 ? round(($present / $marked) * 100, 1) : 0,
            ];
        }

        return view('attendance.report', compact(
            'period',
            'academicYear',
            'section',
            'subject',
            'days',
            'rows',
            'rangeStart',
            'rangeEnd',
            'sectionId',
            'subjectId'
        ));
    }

    public function studentView(Request $request)
    {
        $student = auth()->user()->student;
        if (!$student) {
            abort(403, 'Only students can view their attendance.');
        }

        $academicYears = AcademicYear::query()->orderByDesc('start_date')->orderByDesc('id')->get();
        $academicYear = $request->filled('academic_year_id')
            ? $academicYears->firstWhere('id', (int) $request->input('academic_year_id'))
            : AcademicYear::active();

        $subjects = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->when($academicYear, fn ($query) => $query->where('academic_year_id', $academicYear->id))
            ->with('subject:id,subject_name,class')
            ->get()
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->sortBy('subject_name')
            ->values();

        $month = $request->input('month', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', (string) $month)) {
            $month = now()->format('Y-m');
        }
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $query = Attendance::where('student_id', $student->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->when($academicYear?->start_date, fn ($rows) => $rows->whereDate('date', '>=', $academicYear->start_date))
            ->when($academicYear?->end_date, fn ($rows) => $rows->whereDate('date', '<=', $academicYear->end_date))
            ->when($academicYear, function ($rows) use ($student, $academicYear) {
                $sectionSubjects = \App\Models\ClassSchedule::query()
                    ->where('academic_year_id', $academicYear->id)
                    ->whereIn('section_id', \Illuminate\Support\Facades\DB::table('student_section_assignments')
                        ->where('student_id', $student->id)
                        ->where('academic_year_id', $academicYear->id)
                        ->select('section_id'))
                    ->select('subject_id');
                $rows->where(function ($match) use ($student, $academicYear, $sectionSubjects) {
                    $match->whereIn('subject_id', Enrollment::query()
                        ->where('student_id', $student->id)
                        ->where('academic_year_id', $academicYear->id)
                        ->where('status', 'active')
                        ->select('subject_id'))
                        ->orWhereIn('subject_id', $sectionSubjects);
                });
            })
            ->with(['subject', 'teacher']);

        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        $attendances = $query->orderBy('date', 'desc')->get();
        $summary = Attendance::summarize($attendances);

        return view('attendance.student_view', compact('student', 'subjects', 'attendances', 'summary', 'month', 'academicYear', 'academicYears'));
    }

    public function parentView(Request $request)
    {
        $parent = auth()->user();
        if (!$parent->hasRole(User::ROLE_PARENT)) {
            abort(403, 'Only parents can view their children\'s attendance.');
        }

        $children = Student::query()->forParent($parent)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        $academicYears = AcademicYear::query()->orderByDesc('start_date')->orderByDesc('id')->get();
        $academicYear = $request->filled('academic_year_id')
            ? $academicYears->firstWhere('id', (int) $request->input('academic_year_id'))
            : AcademicYear::active();

        $subjects = collect();
        $selectedStudent = null;
        $attendances = collect();
        $summary = [];

        if ($studentId = $request->input('student_id')) {
            $selectedStudent = $children->find($studentId);
            if ($selectedStudent) {
                $enrolledSubjectIds = Enrollment::where('student_id', $selectedStudent->id)
                    ->where('status', 'active')
                    ->when($academicYear, fn ($query) => $query->where('academic_year_id', $academicYear->id))
                    ->pluck('subject_id');
                $subjects = $enrolledSubjectIds->isEmpty()
                    ? collect()
                    : Subject::select('id', 'subject_name')
                        ->whereIn('id', $enrolledSubjectIds)
                        ->orderBy('subject_name')
                        ->get();
                $month = $request->input('month', now()->format('Y-m'));
                $year = substr($month, 0, 4);
                $monthNum = substr($month, 5, 2);

                $query = Attendance::where('student_id', $studentId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum)
                    ->when($academicYear?->start_date, fn ($rows) => $rows->whereDate('date', '>=', $academicYear->start_date))
                    ->when($academicYear?->end_date, fn ($rows) => $rows->whereDate('date', '<=', $academicYear->end_date))
                    ->when($academicYear, function ($rows) use ($selectedStudent, $academicYear) {
                        $sectionSubjects = \App\Models\ClassSchedule::query()
                            ->where('academic_year_id', $academicYear->id)
                            ->whereIn('section_id', \Illuminate\Support\Facades\DB::table('student_section_assignments')
                                ->where('student_id', $selectedStudent->id)
                                ->where('academic_year_id', $academicYear->id)
                                ->select('section_id'))
                            ->select('subject_id');
                        $rows->where(function ($match) use ($selectedStudent, $academicYear, $sectionSubjects) {
                            $match->whereIn('subject_id', Enrollment::query()
                                ->where('student_id', $selectedStudent->id)
                                ->where('academic_year_id', $academicYear->id)
                                ->where('status', 'active')
                                ->select('subject_id'))
                                ->orWhereIn('subject_id', $sectionSubjects);
                        });
                    })
                    ->with(['subject', 'teacher']);

                if ($subjectId = $request->input('subject_id')) {
                    $query->where('subject_id', $subjectId);
                }

                $attendances = $query->orderBy('date', 'desc')->get();
                $summary = Attendance::summarize($attendances);
            }
        }

        return view('attendance.parent_view', compact('children', 'subjects', 'selectedStudent', 'attendances', 'summary', 'academicYear', 'academicYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent',
            'attendance.*.time_in' => 'nullable|date_format:H:i',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        $teacher = auth()->user()->teacher;
        $isAdmin = auth()->user()->hasRole(User::ROLE_ADMIN);
        $year = AcademicYear::active();
        $attendanceDate = now('Asia/Manila')->toDateString();
        if (! $year) {
            return back()->with('error', 'Set a current academic year before recording attendance.');
        }

        if ($year->start_date && $attendanceDate < $year->start_date->toDateString()) {
            return back()->with('error', 'Today is outside ' . $year->name . '.');
        }
        if ($year->end_date && $attendanceDate > $year->end_date->toDateString()) {
            return back()->with('error', 'Today is outside ' . $year->name . '.');
        }

        $sectionAllowed = Section::forAcademicYear($year->id)->whereKey($request->section_id)->exists();
        if (! $sectionAllowed) {
            return back()->with('error', 'That section is not part of ' . $year->name . '.');
        }

        $recorderId = $teacher?->id;
        $hasAccess = false;
        if ($teacher) {
            $hasAccess = ClassSchedule::where('teacher_id', $teacher->id)
                ->where('subject_id', $request->subject_id)
                ->where('section_id', $request->section_id)
                ->where('is_active', true)
                ->where('academic_year_id', $year->id)
                ->exists();

            if (! $hasAccess) {
                $pairs = app(TeacherClassAssignmentService::class)->optionsFor($teacher, $year->id);
                $allowedSubjects = $pairs['subjectsBySection'][(int) $request->section_id] ?? [];
                $hasAccess = in_array((int) $request->subject_id, array_map('intval', $allowedSubjects), true);
            }
        } elseif ($isAdmin) {
            $scheduleTeacherId = ClassSchedule::where('subject_id', $request->subject_id)
                ->where('section_id', $request->section_id)
                ->where('is_active', true)
                ->where('academic_year_id', $year->id)
                ->value('teacher_id');
            $recorderId = $scheduleTeacherId ? (int) $scheduleTeacherId : null;
            $hasAccess = $recorderId !== null;
        }

        if (! $hasAccess || ! $recorderId) {
            return back()->with('error', 'You do not have permission to mark attendance for this class.');
        }

        $enrolledIds = Student::whereHas('sections', function ($query) use ($request, $year) {
            $query->where('sections.id', (int) $request->section_id)
                ->where('student_section_assignments.academic_year_id', $year->id);
        })->pluck('id')->map(fn ($id) => (int) $id)->all();

        $rows = [];
        $absentStudentIds = [];
        $now = now();
        foreach ($request->attendance as $studentId => $data) {
            $studentId = (int) $studentId;
            if (! in_array($studentId, $enrolledIds, true)) {
                return back()->with('error', 'Attendance can only be saved for students enrolled in this section for ' . $year->name . '.');
            }
            $timeIn = null;
            if (($data['status'] ?? '') === 'present') {
                $postedTime = $data['time_in'] ?? null;
                $timeIn = $postedTime ? $postedTime . ':00' : now('Asia/Manila')->format('H:i:s');
            }
            $rows[] = [
                'student_id' => $studentId,
                'subject_id' => (int) $request->subject_id,
                'date' => $attendanceDate,
                'status' => $data['status'],
                'time_in' => $timeIn,
                'remarks' => $data['remarks'] ?? null,
                'teacher_id' => $recorderId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (($data['status'] ?? '') === 'absent') {
                $absentStudentIds[] = $studentId;
            }
        }

        if (! empty($rows)) {
            $studentIds = array_column($rows, 'student_id');
            // 2 queries total (delete+insert) instead of N×updateOrCreate — no unique index required.
            DB::transaction(function () use ($rows, $request, $studentIds, $attendanceDate) {
                Attendance::where('subject_id', (int) $request->subject_id)
                    ->whereDate('date', $attendanceDate)
                    ->whereIn('student_id', $studentIds)
                    ->delete();
                Attendance::insert($rows);
            });
        }

        // Notify after the response so save feels instant on remote MySQL.
        if (! empty($absentStudentIds)) {
            $subjectId = (int) $request->subject_id;
            $date = $attendanceDate;
            $teacherId = $recorderId;
            dispatch(function () use ($absentStudentIds, $subjectId, $date, $teacherId) {
                $subject = \App\Models\Subject::find($subjectId);
                $students = \App\Models\Student::with('user')
                    ->whereIn('id', $absentStudentIds)
                    ->get()
                    ->keyBy('id');

                $missedCounts = Attendance::query()
                    ->selectRaw('student_id, COUNT(*) as c')
                    ->whereIn('student_id', $absentStudentIds)
                    ->where('status', 'absent')
                    ->whereMonth('date', now()->month)
                    ->groupBy('student_id')
                    ->pluck('c', 'student_id');

                $parentEmails = $students->pluck('parent_email')->filter()->unique()->values();
                $parents = $parentEmails->isEmpty()
                    ? collect()
                    : \App\Models\User::where('role_name', 'Parent')
                        ->whereIn('email', $parentEmails->all())
                        ->get()
                        ->keyBy('email');

                foreach ($absentStudentIds as $studentId) {
                    $student = $students->get($studentId);
                    if (! $student) {
                        continue;
                    }
                    $attendance = Attendance::where([
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'date' => $date,
                    ])->first();
                    if (! $attendance) {
                        continue;
                    }
                    $missedDays = (int) ($missedCounts[$studentId] ?? 0);
                    $notification = new \App\Notifications\AttendanceAlertNotification(
                        $attendance, $student, $subject, $missedDays
                    );
                    if ($student->user) {
                        $student->user->notify($notification);
                    }
                    if ($student->parent_email && ($parent = $parents->get($student->parent_email))) {
                        $parent->notify($notification);
                    }
                }
            })->afterResponse();
        }

        $redirectParams = [
            'class' => $request->section_id . '_' . $request->subject_id,
            'section_id' => $request->section_id,
            'subject_id' => $request->subject_id,
            'date' => $attendanceDate,
        ];

        return redirect()->route('attendance.index', $redirectParams)
            ->with('success', 'Attendance saved.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['student', 'subject', 'teacher']);
        return view('attendance.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        $this->authorizeAttendanceRecord($attendance);
        $attendance->load(['student', 'subject']);
        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->authorizeAttendanceRecord($attendance);

        $request->validate([
            'status' => 'required|in:present,absent',
            'time_in' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:255',
        ]);

        $timeIn = null;
        if ($request->status === 'present') {
            $timeIn = $request->filled('time_in')
                ? $request->time_in . ':00'
                : now('Asia/Manila')->format('H:i:s');
        }

        $attendance->update([
            'status' => $request->status,
            'time_in' => $timeIn,
            'remarks' => $request->input('remarks'),
        ]);

        return redirect()->route('attendance.index')->with('success', 'Attendance record updated.');
    }

    public function destroy(Attendance $attendance)
    {
        $this->authorizeAttendanceRecord($attendance);
        $attendance->delete();
        return redirect()->route('attendance.index')->with('success', 'Attendance record deleted.');
    }

    private function authorizeAttendanceRecord(Attendance $attendance): void
    {
        $user = auth()->user();
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return;
        }
        $teacherId = $user->teacher?->id;
        if (! $teacherId || (int) $attendance->teacher_id !== (int) $teacherId) {
            abort(403, 'You can only change attendance for your own classes.');
        }
    }

    /**
     * Export attendance summary to PDF or Excel.
     */
    public function export(Request $request)
    {
        $teacher = auth()->user()->teacher;
        if (!$teacher && !auth()->user()->hasRole(User::ROLE_ADMIN)) {
            return back()->with('error', 'You do not have permission to export attendance.');
        }

        $teacherSectionIds = [];
        if ($teacher) {
            $subjectsBySection = app(TeacherClassAssignmentService::class)->optionsFor($teacher, AcademicYear::active()?->id)['subjectsBySection'];
            $teacherSectionIds = array_map('intval', array_keys($subjectsBySection));
            $assignedSubjectIds = collect($subjectsBySection)->flatten()->map(fn ($id) => (int) $id)->unique()->all();

            if ($request->filled('section_id') && ! in_array((int) $request->input('section_id'), $teacherSectionIds, true)) {
                abort(403, 'You can only export attendance for your assigned sections.');
            }
            if ($request->filled('subject_id') && ! in_array((int) $request->input('subject_id'), $assignedSubjectIds, true)) {
                abort(403, 'You can only export attendance for your assigned subjects.');
            }
            if ($request->filled('section_id') && $request->filled('subject_id')
                && ! in_array((int) $request->input('subject_id'), array_map('intval', $subjectsBySection[(int) $request->input('section_id')] ?? []), true)) {
                abort(403, 'This subject is not assigned to you in the selected section.');
            }
        }

        $subjects = Subject::orderBy('subject_name')->get();
        $subjectId = $request->input('subject_id');
        $sectionId = $request->input('section_id');
        $month = $request->input('month', now()->format('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
        $days = range(1, $daysInMonth);

        // Get students for the subject/section or all
        $studentsQuery = Student::query();

        if ($teacher) {
            $studentsQuery->whereHas('sections', function ($query) use ($teacherSectionIds) {
                $query->whereIn('sections.id', $teacherSectionIds);
            });
        }
        
        if ($sectionId) {
            $studentsQuery->whereHas('sections', function($q) use ($sectionId) {
                $q->where('sections.id', $sectionId);
            });
        }
        
        if ($subjectId) {
            $studentsQuery->whereHas('subjects', function($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            });
        }
        
        $students = $studentsQuery->orderBy('first_name')->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students found for the selected criteria.');
        }

        // Get attendance records for the month/subject
        $attendanceQuery = Attendance::whereYear('date', $year)->whereMonth('date', $monthNum);
        if ($subjectId) {
            $attendanceQuery->where('subject_id', $subjectId);
        }
        if ($teacher) {
            $attendanceQuery->where('teacher_id', $teacher->id);
        }
        $attendances = $attendanceQuery->get();

        // Map: [student_id][day] => status
        $attendanceMap = [];
        foreach ($attendances as $attendance) {
            $day = (int)date('j', strtotime($attendance->date));
            $attendanceMap[$attendance->student_id][$day] = $attendance->status;
        }

        // Summary per student
        $summary = [];
        $exportData = [];
        foreach ($students as $student) {
            $present = 0;
            $total = 0;
            $row = [$student->first_name . ' ' . $student->last_name];
            foreach ($days as $day) {
                $status = $attendanceMap[$student->id][$day] ?? null;
                $row[] = match ($status) {
                    'present' => 'P',
                    'absent' => 'A',
                    'late' => 'L',
                    'excused' => 'E',
                    default => '-',
                };
                if ($status) {
                    $total++;
                    if (Attendance::countsAsPresent($status)) {
                        $present++;
                    }
                }
            }
            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
            $row[] = $present;
            $row[] = $total;
            $row[] = $percentage;
            $exportData[] = $row;
            $summary[$student->id] = [
                'present' => $present,
                'total' => $total,
                'percentage' => $percentage,
            ];
        }

        $format = $request->input('format', 'pdf');
        if ($format !== 'pdf') {
            $format = 'pdf';
        }

        $filename = 'attendance_summary_' . $month . ($subjectId ? '_subject_' . $subjectId : '') . '.pdf';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('attendance.export_pdf', [
                'students' => $students,
                'days' => $days,
                'attendanceMap' => $attendanceMap,
                'summary' => $summary,
            ]);
            return $pdf->download($filename);
        }

        return back()->with('error', 'Export format not supported.');
    }
}
