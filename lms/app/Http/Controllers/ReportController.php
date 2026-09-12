<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Section;
use App\Models\Grade;
use App\Models\StudentGpa;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Enrollment;
use App\Helpers\GradeNarrativeHelper;
use App\Exports\ClassListExport;
use App\Exports\GradeSlipExport;
use App\Exports\ProgressSummaryExport;
use App\Exports\GradesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user) {
                abort(403, 'Unauthorized action.');
            }
            
            // Only admins and teachers can access reports
            if (!in_array($user->role_name, ['Admin', 'Teacher'])) {
                abort(403, 'Unauthorized action.');
            }
            
            return $next($request);
        });
    }

    /**
     * Display reports index page
     */
    public function index()
    {
        $gradeLevels = \App\Services\GradeSubjectCatalogService::gradeLevels();

        $students = Student::query()
            ->with('sections')
            ->orderBy('year_level')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $sections = Section::query()
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        $studentsPayload = $students->map(function ($s) {
            $sectionIds = $s->sections->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            // Also include legacy section_student pivot if present
            if (empty($sectionIds) && method_exists($s, 'sections')) {
                // already loaded above
            }

            return [
                'id' => $s->id,
                'name' => trim($s->last_name . ', ' . $s->first_name . ($s->middle_name ? ' ' . $s->middle_name : '')),
                'grade' => $s->year_level ?: ($s->class ?: ($s->sections->first()->grade_level ?? 'Unassigned')),
                'section' => $s->section ?: ($s->sections->first()->name ?? null),
                'section_ids' => $sectionIds,
            ];
        })->values();

        $sectionsPayload = $sections->map(function ($sec) {
            return [
                'id' => $sec->id,
                'name' => $sec->name,
                'grade' => $sec->grade_level ?: 'Unassigned',
            ];
        })->values();

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('reports.index', compact(
            'students',
            'sections',
            'studentsPayload',
            'sectionsPayload',
            'gradeLevels',
            'academicYears',
            'semesters'
        ));
    }

    /**
     * Generate Student Transcript
     */
    public function generateTranscript(Request $request, $studentId)
    {
        $user = Auth::user();
        
        // Only admins, teachers, and the student themselves can generate transcript
        if (!in_array($user->role_name, ['Admin', 'Teacher'])) {
            if ($user->role_name === 'Student' && $user->student && $user->student->id != $studentId) {
                abort(403, 'Unauthorized access.');
            } elseif ($user->role_name !== 'Student') {
                abort(403, 'Unauthorized access.');
            }
        }

        $student = Student::with(['user', 'sections'])->findOrFail($studentId);
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');
        $format = $request->get('format', 'pdf');

        // Get all academic years if not specified
        $academicYears = $academicYearId 
            ? AcademicYear::where('id', $academicYearId)->get()
            : AcademicYear::orderBy('name', 'desc')->get();

        // Get all grades for the student
        $gradesQuery = Grade::where('student_id', $studentId)
            ->with(['subject', 'component', 'academicYear', 'semester', 'teacher']);

        if ($academicYearId) {
            $gradesQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gradesQuery->where('semester_id', $semesterId);
        }

        $grades = $gradesQuery->orderBy('academic_year_id', 'desc')
            ->orderBy('semester_id')
            ->orderBy('subject_id')
            ->get();

        // Group grades by academic year and semester
        $transcriptData = [];
        foreach ($grades->groupBy('academic_year_id') as $yearId => $yearGrades) {
            $academicYear = $yearGrades->first()->academicYear;
            foreach ($yearGrades->groupBy('semester_id') as $semId => $semGrades) {
                $semester = $semGrades->first()->semester;
                
                // Get GPA for this period
                $gpa = StudentGpa::where('student_id', $studentId)
                    ->where('academic_year_id', $yearId)
                    ->where('semester_id', $semId)
                    ->first();

                // Group by subject
                $subjectGrades = [];
                foreach ($semGrades->groupBy('subject_id') as $subjId => $subjGrades) {
                    $subject = $subjGrades->first()->subject;
                    $avgPercentage = $subjGrades->avg('percentage');
                    
                    $subjectGrades[] = [
                        'subject' => $subject,
                        'grades' => $subjGrades,
                        'average' => round($avgPercentage, 2),
                        'components' => $subjGrades->groupBy('component_id')
                    ];
                }

                $transcriptData[] = [
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                    'subjects' => $subjectGrades,
                    'gpa' => $gpa
                ];
            }
        }

        // Get overall GPA
        $overallGpa = StudentGpa::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get attendance summary
        $attendanceSummary = $this->getAttendanceSummary($studentId, $academicYearId, $semesterId);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.transcript', compact(
                'student', 
                'transcriptData', 
                'overallGpa', 
                'attendanceSummary',
                'academicYearId',
                'semesterId'
            ));
            $filename = 'transcript_' . $this->safeName($student->last_name . '_' . $student->first_name) . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        } else {
            $exportData = [];
            foreach ($transcriptData as $period) {
                foreach ($period['subjects'] as $subjectGrade) {
                    $exportData[] = [
                        $period['academic_year']->name ?? 'N/A',
                        $period['semester']->name ?? 'N/A',
                        $subjectGrade['subject']->subject_name ?? 'N/A',
                        $subjectGrade['average'],
                        GradeNarrativeHelper::subjectNarrative($subjectGrade['average'], $subjectGrade['subject']->subject_name ?? 'Subject'),
                    ];
                }
            }
            $filename = 'transcript_' . $this->safeName($student->last_name) . '_' . date('Y-m-d') . '.xlsx';
            return Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function __construct(private array $data) {}
                public function array(): array { return $this->data; }
                public function headings(): array { return ['Academic Year', 'Semester', 'Subject', 'Average', 'Narrative']; }
            }, $filename);
        }
    }

    /**
     * Generate Class List
     */
    public function generateClassList(Request $request, $sectionId)
    {
        $user = Auth::user();
        
        // Only admins and teachers can generate class lists
        if (!in_array($user->role_name, ['Admin', 'Teacher'])) {
            abort(403, 'Unauthorized access.');
        }

        $section = Section::with(['adviser', 'students'])->findOrFail($sectionId);
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');
        $format = $request->get('format', 'pdf');

        // Get current academic year and semester if not specified
        if (!$academicYearId) {
            $academicYear = AcademicYear::latest()->first();
            $academicYearId = $academicYear ? $academicYear->id : null;
        }
        if (!$semesterId) {
            $semester = Semester::latest()->first();
            $semesterId = $semester ? $semester->id : null;
        }

        // Get students assigned to this section for the academic period
        $query = Student::whereHas('sections', function($q) use ($sectionId) {
            $q->where('sections.id', $sectionId);
        });
        
        // Filter by academic year and semester if provided
        if ($academicYearId || $semesterId) {
            $query->whereHas('sections', function($q) use ($sectionId, $academicYearId, $semesterId) {
                $q->where('sections.id', $sectionId);
                if ($academicYearId) {
                    $q->where('student_section_assignments.academic_year_id', $academicYearId);
                }
                if ($semesterId) {
                    $q->where('student_section_assignments.semester_id', $semesterId);
                }
            });
        }
        
        $students = $query->orderBy('last_name')->orderBy('first_name')->get();

        // Get subjects for this section
        $subjects = Subject::whereHas('classSchedules', function($query) use ($sectionId) {
            $query->where('section_id', $sectionId);
        })->get();

        $academicYear = $academicYearId ? AcademicYear::find($academicYearId) : null;
        $semester = $semesterId ? Semester::find($semesterId) : null;

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.class_list', compact(
                'section', 
                'students', 
                'subjects', 
                'academicYear', 
                'semester'
            ));
            $filename = 'class_list_' . $this->safeName($section->name) . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        } else {
            $exportData = $students->values()->map(function ($student, $index) {
                return [
                    $index + 1,
                    $student->admission_id ?? $student->id,
                    $student->last_name,
                    $student->first_name,
                    $student->middle_name ?? '',
                    $student->gender ?? '',
                    $student->email ?? '',
                ];
            })->all();
            $filename = 'class_list_' . $this->safeName($section->name) . '_' . date('Y-m-d') . '.xlsx';
            return Excel::download(new ClassListExport($exportData, $section->name), $filename);
        }
    }

    /**
     * Generate Grade Slip
     */
    public function generateGradeSlip(Request $request, $studentId)
    {
        $user = Auth::user();
        
        // Only admins, teachers, students, and parents can generate grade slips
        if (!in_array($user->role_name, ['Admin', 'Teacher'])) {
            if ($user->role_name === 'Student' && $user->student && $user->student->id != $studentId) {
                abort(403, 'Unauthorized access.');
            } elseif ($user->role_name === 'Parent') {
                // Check if student is linked to parent
                $student = Student::findOrFail($studentId);
                if ($student->parent_email !== $user->email) {
                    abort(403, 'Unauthorized access.');
                }
            } elseif ($user->role_name !== 'Student' && $user->role_name !== 'Parent') {
                abort(403, 'Unauthorized access.');
            }
        }

        $student = Student::with(['user', 'sections'])->findOrFail($studentId);
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');
        $format = $request->get('format', 'pdf');

        // Get current academic year and semester if not specified
        if (!$academicYearId) {
            $academicYear = AcademicYear::latest()->first();
            $academicYearId = $academicYear ? $academicYear->id : null;
        }
        if (!$semesterId) {
            $semester = Semester::latest()->first();
            $semesterId = $semester ? $semester->id : null;
        }

        // Get grades for the specified period
        $gradesQuery = Grade::where('student_id', $studentId)
            ->with(['subject', 'component', 'academicYear', 'semester']);

        if ($academicYearId) {
            $gradesQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gradesQuery->where('semester_id', $semesterId);
        }

        $grades = $gradesQuery->orderBy('subject_id')->get();

        // Group by subject
        $subjectGrades = [];
        foreach ($grades->groupBy('subject_id') as $subjId => $subjGrades) {
            $subject = $subjGrades->first()->subject;
            $avgPercentage = $subjGrades->avg('percentage');
            
            $subjectGrades[] = [
                'subject' => $subject,
                'grades' => $subjGrades,
                'average' => round($avgPercentage, 2),
                'components' => $subjGrades->groupBy('component_id')
            ];
        }

        // Get GPA for this period
        $gpaQuery = StudentGpa::where('student_id', $studentId);
        if ($academicYearId) {
            $gpaQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gpaQuery->where('semester_id', $semesterId);
        }
        $gpa = $gpaQuery->first();

        // Get attendance summary
        $attendanceSummary = $this->getAttendanceSummary($studentId, $academicYearId, $semesterId);

        $academicYear = $academicYearId ? AcademicYear::find($academicYearId) : null;
        $semester = $semesterId ? Semester::find($semesterId) : null;

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.grade_slip', compact(
                'student', 
                'subjectGrades', 
                'gpa', 
                'attendanceSummary',
                'academicYear',
                'semester'
            ));
            $filename = 'grade_slip_' . $this->safeName($student->last_name . '_' . $student->first_name) . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        } else {
            $exportData = [];
            foreach ($subjectGrades as $sg) {
                foreach ($sg['grades'] as $grade) {
                    $exportData[] = [
                        $sg['subject']->subject_name ?? 'N/A',
                        $grade->component->name ?? 'N/A',
                        $grade->score,
                        $grade->max_score,
                        round($grade->percentage, 2) . '%',
                        $grade->remarks ?? GradeNarrativeHelper::subjectNarrative($sg['average'], $sg['subject']->subject_name ?? 'Subject'),
                    ];
                }
            }
            $filename = 'grade_slip_' . $this->safeName($student->last_name) . '_' . date('Y-m-d') . '.xlsx';
            return Excel::download(new GradeSlipExport($exportData), $filename);
        }
    }

    /**
     * Generate Progress Summary
     */
    public function generateProgressSummary(Request $request, $studentId)
    {
        $user = Auth::user();
        
        // Only admins, teachers, students, and parents can generate progress summaries
        if (!in_array($user->role_name, ['Admin', 'Teacher'])) {
            if ($user->role_name === 'Student' && $user->student && $user->student->id != $studentId) {
                abort(403, 'Unauthorized access.');
            } elseif ($user->role_name === 'Parent') {
                // Check if student is linked to parent
                $student = Student::findOrFail($studentId);
                if ($student->parent_email !== $user->email) {
                    abort(403, 'Unauthorized access.');
                }
            } elseif ($user->role_name !== 'Student' && $user->role_name !== 'Parent') {
                abort(403, 'Unauthorized access.');
            }
        }

        $student = Student::with(['user', 'sections'])->findOrFail($studentId);
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');
        $format = $request->get('format', 'pdf');

        // Get current academic year and semester if not specified
        if (!$academicYearId) {
            $academicYear = AcademicYear::latest()->first();
            $academicYearId = $academicYear ? $academicYear->id : null;
        }
        if (!$semesterId) {
            $semester = Semester::latest()->first();
            $semesterId = $semester ? $semester->id : null;
        }

        // Get grades
        $gradesQuery = Grade::where('student_id', $studentId)
            ->with(['subject', 'component', 'academicYear', 'semester']);

        if ($academicYearId) {
            $gradesQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gradesQuery->where('semester_id', $semesterId);
        }

        $grades = $gradesQuery->get();

        // Get GPA records
        $gpaQuery = StudentGpa::where('student_id', $studentId)
            ->with(['academicYear', 'semester']);
        if ($academicYearId) {
            $gpaQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gpaQuery->where('semester_id', $semesterId);
        }
        $gpaRecords = $gpaQuery->orderBy('created_at', 'desc')->get();

        // Get attendance summary
        $attendanceSummary = $this->getAttendanceSummary($studentId, $academicYearId, $semesterId);

        // Get subject performance
        $subjectPerformance = [];
        foreach ($grades->groupBy('subject_id') as $subjId => $subjGrades) {
            $subject = $subjGrades->first()->subject;
            $avgPercentage = $subjGrades->avg('percentage');
            $maxPercentage = $subjGrades->max('percentage');
            $minPercentage = $subjGrades->min('percentage');
            
            $subjectPerformance[] = [
                'subject' => $subject,
                'average' => round($avgPercentage, 2),
                'max' => round($maxPercentage, 2),
                'min' => round($minPercentage, 2),
                'count' => $subjGrades->count()
            ];
        }

        // Get recent activities
        $recentActivities = \App\Models\ActivitySubmission::where('student_id', $studentId)
            ->with(['activity.lesson.subject'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get grade alerts
        $gradeAlerts = \App\Models\GradeAlert::where('student_id', $studentId)
            ->where('is_resolved', false)
            ->with(['subject'])
            ->get();

        // Build narrative feedback
        $narratives = [];
        foreach ($subjectPerformance as $perf) {
            $narratives[] = GradeNarrativeHelper::subjectNarrative(
                $perf['average'],
                $perf['subject']->subject_name ?? 'Subject'
            );
        }
        $overallGpaValue = $gpaRecords->first()->gpa ?? 0;
        $narratives[] = GradeNarrativeHelper::overallNarrative($overallGpaValue);
        $narratives[] = GradeNarrativeHelper::attendanceNarrative($attendanceSummary['percentage']);

        $academicYear = $academicYearId ? AcademicYear::find($academicYearId) : null;
        $semester = $semesterId ? Semester::find($semesterId) : null;

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.progress_summary', compact(
                'student', 
                'subjectPerformance', 
                'gpaRecords', 
                'attendanceSummary',
                'recentActivities',
                'gradeAlerts',
                'narratives',
                'academicYear',
                'semester'
            ));
            $filename = 'progress_report_' . $this->safeName($student->last_name . '_' . $student->first_name) . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        } else {
            $exportData = [];
            foreach ($subjectPerformance as $perf) {
                $exportData[] = [
                    'Subject',
                    $perf['subject']->subject_name ?? 'N/A',
                    $perf['average'] . '%',
                    GradeNarrativeHelper::subjectNarrative($perf['average'], $perf['subject']->subject_name ?? 'Subject'),
                ];
            }
            $exportData[] = ['Overall GPA', '', $overallGpaValue, GradeNarrativeHelper::overallNarrative($overallGpaValue)];
            $exportData[] = ['Attendance', '', $attendanceSummary['percentage'] . '%', GradeNarrativeHelper::attendanceNarrative($attendanceSummary['percentage'])];
            $filename = 'progress_report_' . $this->safeName($student->last_name) . '_' . date('Y-m-d') . '.xlsx';
            return Excel::download(new ProgressSummaryExport($exportData), $filename);
        }
    }

    /**
     * Bulk download reports by grade level and/or section (ZIP of PDFs).
     * Types: transcript | grade-slip | progress-summary | class-list
     */
    public function generateBulk(Request $request, string $type)
    {
        $user = Auth::user();
        if (!in_array($user->role_name, ['Admin', 'Teacher'], true)) {
            abort(403, 'Unauthorized access.');
        }

        $type = strtolower($type);
        if (!in_array($type, ['transcript', 'grade-slip', 'progress-summary', 'class-list'], true)) {
            abort(404);
        }

        $gradeLevel = trim((string) $request->get('grade_level', ''));
        $sectionId = $request->get('section_id');
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');
        $format = $request->get('format', 'pdf');

        if ($gradeLevel === '' && !$sectionId) {
            return redirect()->route('reports.index')
                ->with('error', 'Please select a grade level or section for bulk download.');
        }

        if (!class_exists(\ZipArchive::class)) {
            return redirect()->route('reports.index')
                ->with('error', 'ZIP extension is not available on this server.');
        }

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $tmpZip = tempnam(sys_get_temp_dir(), 'reports_zip_');
        $zipPath = $tmpZip . '.zip';
        @unlink($tmpZip);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->route('reports.index')->with('error', 'Could not create ZIP archive.');
        }

        $added = 0;

        if ($type === 'class-list') {
            $sectionsQuery = Section::query()->orderBy('name');
            if ($sectionId) {
                $sectionsQuery->where('id', $sectionId);
            } elseif ($gradeLevel !== '') {
                $aliases = \App\Services\GradeSubjectCatalogService::gradeAliases($gradeLevel);
                $sectionsQuery->where(function ($q) use ($gradeLevel, $aliases) {
                    $q->where('grade_level', $gradeLevel);
                    if (!empty($aliases)) {
                        $q->orWhereIn('grade_level', $aliases);
                    }
                });
            }

            foreach ($sectionsQuery->get() as $section) {
                $subRequest = new Request([
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'format' => 'pdf',
                ]);
                $response = $this->generateClassList($subRequest, $section->id);
                $binary = $response->getContent();
                $name = 'class_list_' . $this->safeName($section->name) . '.pdf';
                $zip->addFromString($name, $binary);
                $added++;
            }
        } else {
            $students = $this->studentsForScope($gradeLevel, $sectionId);
            if ($students->isEmpty()) {
                $zip->close();
                @unlink($zipPath);
                return redirect()->route('reports.index')
                    ->with('error', 'No students found for the selected grade/section.');
            }

            foreach ($students as $student) {
                $subRequest = new Request([
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'format' => 'pdf',
                ]);

                $response = match ($type) {
                    'transcript' => $this->generateTranscript($subRequest, $student->id),
                    'grade-slip' => $this->generateGradeSlip($subRequest, $student->id),
                    default => $this->generateProgressSummary($subRequest, $student->id),
                };

                $prefix = match ($type) {
                    'transcript' => 'transcript',
                    'grade-slip' => 'grade_slip',
                    default => 'progress_report',
                };
                $name = $prefix . '_' . $this->safeName($student->last_name . '_' . $student->first_name) . '.pdf';
                $zip->addFromString($name, $response->getContent());
                $added++;
            }
        }

        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);
            return redirect()->route('reports.index')->with('error', 'Nothing to download for the selected filters.');
        }

        $label = $sectionId
            ? ('section_' . $sectionId)
            : ('grade_' . $this->safeName($gradeLevel));
        $downloadName = 'bulk_' . $this->safeName($type) . '_' . $label . '_' . date('Y-m-d') . '.zip';

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
    }

    protected function studentsForScope(string $gradeLevel, $sectionId)
    {
        $query = Student::query()->orderBy('last_name')->orderBy('first_name');

        if ($sectionId) {
            $query->whereHas('sections', function ($q) use ($sectionId) {
                $q->where('sections.id', $sectionId);
            });
        } elseif ($gradeLevel !== '') {
            $aliases = \App\Services\GradeSubjectCatalogService::gradeAliases($gradeLevel);
            $labels = !empty($aliases) ? $aliases : [$gradeLevel];
            $query->where(function ($q) use ($labels) {
                $q->whereIn('year_level', $labels)->orWhereIn('class', $labels);
            });
        }

        return $query->get();
    }

    protected function safeName(?string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9_\-]+/', '_', (string) $value);
        $value = trim($value, '_');
        return $value !== '' ? $value : 'file';
    }

    /**
     * Get attendance summary for a student
     */
    private function getAttendanceSummary($studentId, $academicYearId = null, $semesterId = null)
    {
        $query = Attendance::where('student_id', $studentId);

        // Filter by academic year and semester if provided
        if ($academicYearId && $semesterId) {
            $academicYear = AcademicYear::find($academicYearId);
            $semester = Semester::find($semesterId);
            
            if ($academicYear && $semester) {
                // Get date range for the semester
                $startDate = $semester->start_date ?? $academicYear->start_date ?? null;
                $endDate = $semester->end_date ?? $academicYear->end_date ?? null;
                
                if ($startDate && $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate]);
                }
            }
        } elseif ($academicYearId) {
            $academicYear = AcademicYear::find($academicYearId);
            if ($academicYear && $academicYear->start_date && $academicYear->end_date) {
                $query->whereBetween('date', [$academicYear->start_date, $academicYear->end_date]);
            }
        }

        $attendances = $query->get();
        
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $absent = $total - $present;
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'percentage' => $percentage
        ];
    }
}


