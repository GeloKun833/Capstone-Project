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
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        // Get all subjects and sections the teacher teaches
        $subjects = collect();
        $sections = collect();
        $students = collect();
        
        if ($teacher) {
            // Get all sections where this teacher has active class schedules
            $sections = Section::whereHas('classSchedules', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                      ->where('is_active', true);
            })->get();
            
            // Get all subjects the teacher teaches
            $subjects = Subject::whereHas('classSchedules', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                      ->where('is_active', true);
            })->get();
        } else {
            // Admin view
            $subjects = Subject::select('id', 'subject_name')->orderBy('subject_name')->get();
            $sections = Section::select('id', 'name')->orderBy('name')->get();
        }
        
        $subjectId = $request->input('subject_id');
        $sectionId = $request->input('section_id');
        $month = $request->input('month', now()->format('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
        $days = range(1, $daysInMonth);

        // Get students based on teacher's assigned sections/subjects
        if ($teacher) {
            if ($subjectId) {
                // Get sections where teacher teaches the selected subject
                $sectionsForSubject = Section::whereHas('classSchedules', function($query) use ($teacher, $subjectId) {
                    $query->where('teacher_id', $teacher->id)
                          ->where('subject_id', $subjectId)
                          ->where('is_active', true);
                })->pluck('id');
                
                // Get students from these sections
                $studentsQuery = Student::whereHas('sections', function($query) use ($sectionsForSubject, $sectionId) {
                    $query->whereIn('sections.id', $sectionsForSubject);
                    if ($sectionId) {
                        $query->where('sections.id', $sectionId);
                    }
                });
                
                $students = $studentsQuery->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();
            } else {
                // Show all students from all sections where teacher teaches
                $allTeacherSections = Section::whereHas('classSchedules', function($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id)
                          ->where('is_active', true);
                })->pluck('id');
                
                if ($allTeacherSections->isNotEmpty()) {
                    $studentsQuery = Student::whereHas('sections', function($query) use ($allTeacherSections, $sectionId) {
                        $query->whereIn('sections.id', $allTeacherSections);
                        if ($sectionId) {
                            $query->where('sections.id', $sectionId);
                        }
                    });
                    
                    $students = $studentsQuery->orderBy('last_name')
                        ->orderBy('first_name')
                        ->get();
                }
            }
        } else {
            // Admin view - original logic
            if ($sectionId) {
                $students = Student::whereHas('sections', function($q) use ($sectionId) {
                    $q->where('sections.id', $sectionId);
                });
                if ($subjectId) {
                    $students->whereHas('subjects', function($q) use ($subjectId) {
                        $q->where('subjects.id', $subjectId);
                    });
                }
                $students = $students->orderBy('first_name')->get();
            }
        }

        // Get attendance records
        $attendanceQuery = Attendance::whereYear('date', $year)->whereMonth('date', $monthNum);
        if ($subjectId) {
            $attendanceQuery->where('subject_id', $subjectId);
        }
        if ($teacher) {
            $attendanceQuery->where('teacher_id', $teacher->id);
        }
        $attendances = $attendanceQuery->get();

        // Build attendance map and summary
        $attendanceMap = [];
        foreach ($attendances as $attendance) {
            $day = (int)date('j', strtotime($attendance->date));
            $attendanceMap[$attendance->student_id][$day] = $attendance->status;
        }

        $summary = [];
        foreach ($students as $student) {
            $present = 0;
            $total = 0;
            foreach ($days as $day) {
                if (isset($attendanceMap[$student->id][$day])) {
                    $total++;
                    if ($attendanceMap[$student->id][$day] === 'present') {
                        $present++;
                    }
                }
            }
            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : null;
            $summary[$student->id] = [
                'present' => $present,
                'total' => $total,
                'percentage' => $percentage,
            ];
        }

        return view('attendance.index', compact('subjects', 'sections', 'students', 'days', 'attendanceMap', 'summary', 'subjectId', 'sectionId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        // Get all subjects the teacher teaches (from class schedules)
        $subjects = collect();
        $sections = collect();
        $students = collect();
        $existing = [];
        
        if ($teacher) {
            // Get all sections where this teacher has active class schedules
            $teacherSections = Section::whereHas('classSchedules', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                      ->where('is_active', true);
            })->get();
            
            // Get all subjects the teacher teaches
            $teacherSubjects = Subject::whereHas('classSchedules', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                      ->where('is_active', true);
            })->get();
            
            $subjects = $teacherSubjects;
            $sections = $teacherSections;
            
            // Get subject and section from request (for filtering)
            $subjectId = $request->input('subject_id');
            $sectionId = $request->input('section_id');
            $date = $request->input('date', now()->toDateString());
            
            // If subject is selected, get students from sections where teacher teaches that subject
            if ($subjectId) {
                // Get sections where teacher teaches the selected subject
                $sectionsForSubject = Section::whereHas('classSchedules', function($query) use ($teacher, $subjectId) {
                    $query->where('teacher_id', $teacher->id)
                          ->where('subject_id', $subjectId)
                          ->where('is_active', true);
                })->pluck('id');
                
                // Get students from these sections
                $studentsQuery = Student::whereHas('sections', function($query) use ($sectionsForSubject, $sectionId) {
                    $query->whereIn('sections.id', $sectionsForSubject);
                    if ($sectionId) {
                        $query->where('sections.id', $sectionId);
                    }
                });
                
                $students = $studentsQuery->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();
                
                // Get existing attendance records for the selected date and subject
                if ($students->isNotEmpty()) {
                    $existing = Attendance::where('subject_id', $subjectId)
                        ->where('date', $date)
                        ->whereIn('student_id', $students->pluck('id'))
                        ->pluck('status', 'student_id')
                        ->toArray();
                }
            } else {
                // If no subject selected, show all students from all sections where teacher teaches
                $allTeacherSections = Section::whereHas('classSchedules', function($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id)
                          ->where('is_active', true);
                })->pluck('id');
                
                if ($allTeacherSections->isNotEmpty()) {
                    $students = Student::whereHas('sections', function($query) use ($allTeacherSections) {
                        $query->whereIn('sections.id', $allTeacherSections);
                    })->orderBy('last_name')
                      ->orderBy('first_name')
                      ->get();
                }
            }
        } else {
            // Admin view - show all
            $subjects = Subject::select('id', 'subject_name')->orderBy('subject_name')->get();
            $sections = Section::select('id', 'name')->orderBy('name')->get();
            $subjectId = $request->input('subject_id');
            $sectionId = $request->input('section_id');
            $date = $request->input('date', now()->toDateString());
            
            if ($sectionId && $subjectId) {
                $students = Student::whereHas('sections', function($q) use ($sectionId) {
                    $q->where('sections.id', $sectionId);
                })->whereHas('subjects', function($q) use ($subjectId) {
                    $q->where('subjects.id', $subjectId);
                })->get();
                
                $existing = Attendance::where('subject_id', $subjectId)
                    ->where('date', $date)
                    ->pluck('status', 'student_id')
                    ->toArray();
            }
        }
        
        $subjectId = $request->input('subject_id');
        $sectionId = $request->input('section_id');
        $date = $request->input('date', now()->toDateString());

        return view('attendance.create', compact('subjects', 'sections', 'students', 'subjectId', 'sectionId', 'date', 'existing'));
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

        // Calculate summary
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $absent = $total - $present;
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        $summary = [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'percentage' => $percentage,
        ];

        return view('attendance.student_view', compact('subjects', 'attendances', 'summary'));
    }

    public function parentView(Request $request)
    {
        $parent = auth()->user();
        if (!$parent->hasRole(User::ROLE_PARENT)) {
            abort(403, 'Only parents can view their children\'s attendance.');
        }

        // Get children (you'll need to implement the relationship between parents and students)
        $children = Student::where('parent_email', $parent->email)
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

                // Calculate summary
                $total = $attendances->count();
                $present = $attendances->where('status', 'present')->count();
                $absent = $total - $present;
                $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

                $summary = [
                    'total' => $total,
                    'present' => $present,
                    'absent' => $absent,
                    'percentage' => $percentage,
                ];
            }
        }

        return view('attendance.parent_view', compact('children', 'subjects', 'selectedStudent', 'attendances', 'summary'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return back()->with('error', 'Only teachers can mark attendance.');
        }

        // Verify teacher has access to this section/subject
        $hasAccess = $teacher->subjects()->where('subjects.id', $request->subject_id)->exists() ||
                    Section::where('id', $request->section_id)
                        ->where('adviser_id', $teacher->id)
                        ->exists();

        if (!$hasAccess && !auth()->user()->hasRole(User::ROLE_ADMIN)) {
            return back()->with('error', 'You do not have permission to mark attendance for this section/subject.');
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
            'subject_id' => $request->subject_id,
            'date' => $request->date,
        ];
        
        if ($request->section_id) {
            $redirectParams['section_id'] = $request->section_id;
        }
        
        return redirect()->route('attendance.create', $redirectParams)
            ->with('success', 'Attendance saved successfully.');
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
            'status' => 'required|in:present,absent',
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
                $row[] = $status === 'present' ? 'P' : ($status === 'absent' ? 'A' : ($status === 'late' ? 'L' : '-'));
                if ($status) {
                    $total++;
                    if ($status === 'present') {
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
