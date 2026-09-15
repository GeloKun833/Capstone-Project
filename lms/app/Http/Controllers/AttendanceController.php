<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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
        $classes = collect();
        $students = collect();
        $existing = [];
        $summary = [];

        $sectionId = (int) $request->input('section_id');
        $subjectId = (int) $request->input('subject_id');
        $date = $request->input('date', now()->toDateString());
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            $date = now()->toDateString();
        }

        if ($request->filled('class') && str_contains($request->input('class'), '_')) {
            [$sectionId, $subjectId] = array_map('intval', explode('_', $request->input('class'), 2));
        }

        if ($teacher) {
            $options = app(TeacherClassAssignmentService::class)->optionsFor($teacher);
            $subjectIds = collect($options['subjectsBySection'])->flatten()->unique()->filter()->values();
            $sectionIds = collect(array_keys($options['subjectsBySection']));

            $subjectModels = Subject::whereIn('id', $subjectIds)->orderBy('subject_name')->get()->keyBy('id');
            $sectionModels = Section::whereIn('id', $sectionIds)->orderBy('name')->get()->keyBy('id');

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
                        'label' => $section->name . ' · ' . $subject->subject_name,
                    ]);
                }
            }
            $classes = $classes->sortBy('label')->values();
        } else {
            $sectionModels = Section::orderBy('name')->get()->keyBy('id');
            $subjectModels = Subject::orderBy('subject_name')->get()->keyBy('id');
            foreach ($sectionModels as $section) {
                foreach ($subjectModels as $subject) {
                    $classes->push([
                        'key' => $section->id . '_' . $subject->id,
                        'section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'label' => $section->name . ' · ' . $subject->subject_name,
                    ]);
                }
            }
        }

        if ($classes->count() === 1 && ! $sectionId && ! $subjectId) {
            $sectionId = (int) $classes->first()['section_id'];
            $subjectId = (int) $classes->first()['subject_id'];
        }

        $selectedKey = ($sectionId && $subjectId) ? $sectionId . '_' . $subjectId : '';
        $ready = $sectionId > 0 && $subjectId > 0;

        if ($ready) {
            $students = Student::whereHas('sections', function ($query) use ($sectionId) {
                $query->where('sections.id', $sectionId);
            })->orderBy('last_name')->orderBy('first_name')->get();

            $dayRecords = Attendance::query()
                ->where('subject_id', $subjectId)
                ->whereDate('date', $date)
                ->when($teacher, fn ($q) => $q->where('teacher_id', $teacher->id))
                ->whereIn('student_id', $students->pluck('id')->all() ?: [0])
                ->get()
                ->keyBy('student_id');

            foreach ($dayRecords as $studentId => $row) {
                $existing[$studentId] = [
                    'status' => $row->status,
                    'remarks' => $row->remarks,
                ];
            }

            $monthStart = \Carbon\Carbon::parse($date)->startOfMonth()->toDateString();
            $monthEnd = \Carbon\Carbon::parse($date)->endOfMonth()->toDateString();
            $monthRows = Attendance::query()
                ->where('subject_id', $subjectId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->when($teacher, fn ($q) => $q->where('teacher_id', $teacher->id))
                ->whereIn('student_id', $students->pluck('id')->all() ?: [0])
                ->get()
                ->groupBy('student_id');

            foreach ($students as $student) {
                $rows = $monthRows->get($student->id, collect());
                $summary[$student->id] = Attendance::summarize($rows);
            }
        }

        $selectedSection = $sectionId ? (Section::find($sectionId)?->name) : null;
        $selectedSubject = $subjectId ? (Subject::find($subjectId)?->subject_name) : null;

        return view('attendance.index', compact(
            'classes',
            'students',
            'existing',
            'summary',
            'sectionId',
            'subjectId',
            'date',
            'selectedKey',
            'ready',
            'selectedSection',
            'selectedSubject'
        ));
    }

    /**
     * Mark attendance now lives on the index page.
     */
    public function create(Request $request)
    {
        return redirect()->route('attendance.index', $request->only(['section_id', 'subject_id', 'date', 'class']));
    }

    public function studentView(Request $request)
    {
        $student = auth()->user()->student;
        if (!$student) {
            abort(403, 'Only students can view their attendance.');
        }

        $subjects = $student->subjects;
        $month = $request->input('month', now()->format('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $query = Attendance::where('student_id', $student->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->with(['subject', 'teacher']);

        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        $attendances = $query->orderBy('date', 'desc')->get();
        $summary = Attendance::summarize($attendances);

        return view('attendance.student_view', compact('subjects', 'attendances', 'summary'));
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
        $subjects = Subject::select('id', 'subject_name')->orderBy('subject_name')->get();
        $selectedStudent = null;
        $attendances = collect();
        $summary = [];

        if ($studentId = $request->input('student_id')) {
            $selectedStudent = $children->find($studentId);
            if ($selectedStudent) {
                $month = $request->input('month', now()->format('Y-m'));
                $year = substr($month, 0, 4);
                $monthNum = substr($month, 5, 2);

                $query = Attendance::where('student_id', $studentId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum)
                    ->with(['subject', 'teacher']);

                if ($subjectId = $request->input('subject_id')) {
                    $query->where('subject_id', $subjectId);
                }

                $attendances = $query->orderBy('date', 'desc')->get();
                $summary = Attendance::summarize($attendances);
            }
        }

        return view('attendance.parent_view', compact('children', 'subjects', 'selectedStudent', 'attendances', 'summary'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return back()->with('error', 'Only teachers can mark attendance.');
        }

        $hasAccess = ClassSchedule::where('teacher_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->where('section_id', $request->section_id)
            ->where('is_active', true)
            ->exists();

        if (! $hasAccess) {
            $pairs = app(TeacherClassAssignmentService::class)->optionsFor($teacher);
            $allowedSubjects = $pairs['subjectsBySection'][(int) $request->section_id] ?? [];
            $hasAccess = in_array((int) $request->subject_id, array_map('intval', $allowedSubjects), true);
        }

        if (! $hasAccess && ! auth()->user()->hasRole(User::ROLE_ADMIN)) {
            return back()->with('error', 'You do not have permission to mark attendance for this class.');
        }

        // One permission check (2 queries max), then batch upsert — no per-student round-trips.
        $rows = [];
        $absentStudentIds = [];
        $now = now();
        foreach ($request->attendance as $studentId => $data) {
            $studentId = (int) $studentId;
            $rows[] = [
                'student_id' => $studentId,
                'subject_id' => (int) $request->subject_id,
                'date' => $request->date,
                'status' => $data['status'],
                'remarks' => $data['remarks'] ?? null,
                'teacher_id' => $teacher->id,
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
            DB::transaction(function () use ($rows, $request, $studentIds) {
                Attendance::where('subject_id', (int) $request->subject_id)
                    ->whereDate('date', $request->date)
                    ->whereIn('student_id', $studentIds)
                    ->delete();
                Attendance::insert($rows);
            });
        }

        // Notify after the response so save feels instant on remote MySQL.
        if (! empty($absentStudentIds)) {
            $subjectId = (int) $request->subject_id;
            $date = $request->date;
            $teacherId = $teacher->id;
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
            'date' => $request->date,
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
        $attendance->load(['student', 'subject']);
        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,excused',
            'remarks' => 'nullable|string|max:255',
        ]);

        $attendance->update($request->only(['status', 'remarks']));

        return redirect()->route('attendance.index')->with('success', 'Attendance record updated.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendance.index')->with('success', 'Attendance record deleted.');
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

        $format = $request->input('format', 'excel');
        $filename = 'attendance_summary_' . $month . ($subjectId ? '_subject_' . $subjectId : '') . '.' . ($format === 'excel' ? 'xlsx' : 'pdf');

        if ($format === 'excel') {
            return Excel::download(new AttendanceExport($exportData, $days), $filename);
        } elseif ($format === 'pdf') {
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
