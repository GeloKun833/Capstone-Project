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
        $format = $request->get('format', 'pdf');
        $viewData = $this->transcriptViewData($student, $request);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.transcript', $viewData);
            $filename = 'transcript_' . $this->safeName($student->last_name . '_' . $student->first_name) . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        $exportData = [];
        foreach ($viewData['transcriptData'] as $period) {
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

        $section = Section::with(['adviser'])->findOrFail($sectionId);
        $format = $request->get('format', 'pdf');
        $viewData = $this->classListViewData($section, $request);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.class_list', $viewData);
            $filename = 'class_list_' . $this->safeName($section->name) . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        $students = $viewData['students'];
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
        $format = $request->get('format', 'pdf');
        $viewData = $this->gradeSlipViewData($student, $request);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.grade_slip', $viewData);
            $filename = 'grade_slip_' . $this->safeName($student->last_name . '_' . $student->first_name) . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        $exportData = [];
        foreach ($viewData['subjectGrades'] as $sg) {
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
        $format = $request->get('format', 'pdf');
        $viewData = $this->progressSummaryViewData($student, $request);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.progress_summary', $viewData);
            $filename = 'progress_report_' . $this->safeName($student->last_name . '_' . $student->first_name) . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        $exportData = [];
        foreach ($viewData['subjectPerformance'] as $perf) {
            $exportData[] = [
                'Subject',
                $perf['subject']->subject_name ?? 'N/A',
                $perf['average'] . '%',
                GradeNarrativeHelper::subjectNarrative($perf['average'], $perf['subject']->subject_name ?? 'Subject'),
            ];
        }
        $overallGpaValue = $viewData['gpaRecords']->first()->gpa ?? 0;
        $exportData[] = ['Overall GPA', '', $overallGpaValue, GradeNarrativeHelper::overallNarrative($overallGpaValue)];
        $exportData[] = ['Attendance', '', $viewData['attendanceSummary']['percentage'] . '%', GradeNarrativeHelper::attendanceNarrative($viewData['attendanceSummary']['percentage'])];
        $filename = 'progress_report_' . $this->safeName($student->last_name) . '_' . date('Y-m-d') . '.xlsx';
        return Excel::download(new ProgressSummaryExport($exportData), $filename);
    }

    /**
     * Combined PDF of all enrolled students in a section (or grade).
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

        if ($gradeLevel === '' && !$sectionId) {
            return redirect()->route('reports.index')
                ->with('error', 'Please select a grade level or section.');
        }

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $documents = [];

        if ($type === 'class-list') {
            $sections = $this->sectionsForScope($gradeLevel, $sectionId);
            if ($sections->isEmpty()) {
                return redirect()->route('reports.index')
                    ->with('error', 'No section found for the selected filters.');
            }
            foreach ($sections as $section) {
                $documents[] = view('reports.class_list', $this->classListViewData($section, $request))->render();
            }
            $label = $sectionId
                ? $this->safeName(optional($sections->first())->name)
                : $this->safeName($gradeLevel);
            $title = 'Class List — ' . ($sectionId ? optional($sections->first())->name : $gradeLevel);
            $filename = 'class_list_' . $label . '_' . date('Y-m-d') . '.pdf';
        } else {
            $students = $this->studentsForScope($gradeLevel, $sectionId);
            if ($students->isEmpty()) {
                return redirect()->route('reports.index')
                    ->with('error', 'No enrolled students found for the selected section.');
            }

            foreach ($students as $student) {
                $student->loadMissing(['user', 'sections']);
                $viewData = match ($type) {
                    'transcript' => $this->transcriptViewData($student, $request),
                    'grade-slip' => $this->gradeSlipViewData($student, $request),
                    default => $this->progressSummaryViewData($student, $request),
                };
                $view = match ($type) {
                    'transcript' => 'reports.transcript',
                    'grade-slip' => 'reports.grade_slip',
                    default => 'reports.progress_summary',
                };
                $documents[] = view($view, $viewData)->render();
            }

            $prefix = match ($type) {
                'transcript' => 'transcript',
                'grade-slip' => 'grade_slip',
                default => 'progress_report',
            };
            $scopeLabel = $sectionId
                ? $this->safeName(optional(Section::find($sectionId))->name)
                : $this->safeName($gradeLevel);
            $title = ucfirst(str_replace('-', ' ', $type)) . ' — ' . ($sectionId ? optional(Section::find($sectionId))->name : $gradeLevel);
            $filename = $prefix . '_' . $scopeLabel . '_' . date('Y-m-d') . '.pdf';
        }

        return $this->downloadCombinedPdf($documents, $title, $filename);
    }

    protected function downloadCombinedPdf(array $htmlDocuments, string $title, string $filename)
    {
        $styles = '';
        $pages = [];
        foreach ($htmlDocuments as $html) {
            if ($styles === '' && preg_match('/<style[^>]*>(.*?)<\/style>/is', $html, $m)) {
                $styles = $m[1];
            }
            if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $m)) {
                $pages[] = $m[1];
            } else {
                $pages[] = $html;
            }
        }

        $pdf = Pdf::loadView('reports.combined-pdf', compact('title', 'styles', 'pages'))
            ->setPaper('a4');

        return $pdf->download($filename);
    }

    protected function resolvePeriod(Request $request): array
    {
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');
        if (!$academicYearId) {
            $academicYearId = optional(AcademicYear::latest('id')->first())->id;
        }
        if (!$semesterId) {
            $semesterId = optional(Semester::latest('id')->first())->id;
        }

        return [(int) $academicYearId ?: null, (int) $semesterId ?: null];
    }

    protected function transcriptViewData(Student $student, Request $request): array
    {
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $gradesQuery = Grade::where('student_id', $student->id)
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

        $transcriptData = [];
        foreach ($grades->groupBy('academic_year_id') as $yearId => $yearGrades) {
            $academicYear = $yearGrades->first()->academicYear;
            foreach ($yearGrades->groupBy('semester_id') as $semId => $semGrades) {
                $semester = $semGrades->first()->semester;
                $gpa = StudentGpa::where('student_id', $student->id)
                    ->where('academic_year_id', $yearId)
                    ->where('semester_id', $semId)
                    ->first();
                $subjectGrades = [];
                foreach ($semGrades->groupBy('subject_id') as $subjGrades) {
                    $subject = $subjGrades->first()->subject;
                    $avgPercentage = $subjGrades->avg('percentage');
                    $subjectGrades[] = [
                        'subject' => $subject,
                        'grades' => $subjGrades,
                        'average' => round($avgPercentage, 2),
                        'components' => $subjGrades->groupBy('component_id'),
                    ];
                }
                $transcriptData[] = [
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                    'subjects' => $subjectGrades,
                    'gpa' => $gpa,
                ];
            }
        }

        return [
            'student' => $student,
            'transcriptData' => $transcriptData,
            'overallGpa' => StudentGpa::where('student_id', $student->id)->orderBy('created_at', 'desc')->first(),
            'attendanceSummary' => $this->getAttendanceSummary($student->id, $academicYearId, $semesterId),
            'academicYearId' => $academicYearId,
            'semesterId' => $semesterId,
        ];
    }

    protected function gradeSlipViewData(Student $student, Request $request): array
    {
        [$academicYearId, $semesterId] = $this->resolvePeriod($request);

        $gradesQuery = Grade::where('student_id', $student->id)
            ->with(['subject', 'component', 'academicYear', 'semester']);
        if ($academicYearId) {
            $gradesQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gradesQuery->where('semester_id', $semesterId);
        }
        $grades = $gradesQuery->orderBy('subject_id')->get();

        $subjectGrades = [];
        foreach ($grades->groupBy('subject_id') as $subjGrades) {
            $subject = $subjGrades->first()->subject;
            $avgPercentage = $subjGrades->avg('percentage');
            $subjectGrades[] = [
                'subject' => $subject,
                'grades' => $subjGrades,
                'average' => round($avgPercentage, 2),
                'components' => $subjGrades->groupBy('component_id'),
            ];
        }

        $gpaQuery = StudentGpa::where('student_id', $student->id);
        if ($academicYearId) {
            $gpaQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gpaQuery->where('semester_id', $semesterId);
        }

        return [
            'student' => $student,
            'subjectGrades' => $subjectGrades,
            'gpa' => $gpaQuery->first(),
            'attendanceSummary' => $this->getAttendanceSummary($student->id, $academicYearId, $semesterId),
            'academicYear' => $academicYearId ? AcademicYear::find($academicYearId) : null,
            'semester' => $semesterId ? Semester::find($semesterId) : null,
        ];
    }

    protected function progressSummaryViewData(Student $student, Request $request): array
    {
        [$academicYearId, $semesterId] = $this->resolvePeriod($request);

        $gradesQuery = Grade::where('student_id', $student->id)
            ->with(['subject', 'component', 'academicYear', 'semester']);
        if ($academicYearId) {
            $gradesQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gradesQuery->where('semester_id', $semesterId);
        }
        $grades = $gradesQuery->get();

        $gpaQuery = StudentGpa::where('student_id', $student->id)->with(['academicYear', 'semester']);
        if ($academicYearId) {
            $gpaQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gpaQuery->where('semester_id', $semesterId);
        }
        $gpaRecords = $gpaQuery->orderBy('created_at', 'desc')->get();
        $attendanceSummary = $this->getAttendanceSummary($student->id, $academicYearId, $semesterId);

        $subjectPerformance = [];
        foreach ($grades->groupBy('subject_id') as $subjGrades) {
            $subject = $subjGrades->first()->subject;
            $subjectPerformance[] = [
                'subject' => $subject,
                'average' => round($subjGrades->avg('percentage'), 2),
                'max' => round($subjGrades->max('percentage'), 2),
                'min' => round($subjGrades->min('percentage'), 2),
                'count' => $subjGrades->count(),
            ];
        }

        $recentActivities = \App\Models\ActivitySubmission::where('student_id', $student->id)
            ->with(['activity.lesson.subject'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        $gradeAlerts = \App\Models\GradeAlert::where('student_id', $student->id)
            ->where('is_resolved', false)
            ->with(['subject'])
            ->get();

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

        return [
            'student' => $student,
            'subjectPerformance' => $subjectPerformance,
            'gpaRecords' => $gpaRecords,
            'attendanceSummary' => $attendanceSummary,
            'recentActivities' => $recentActivities,
            'gradeAlerts' => $gradeAlerts,
            'narratives' => $narratives,
            'academicYear' => $academicYearId ? AcademicYear::find($academicYearId) : null,
            'semester' => $semesterId ? Semester::find($semesterId) : null,
        ];
    }

    protected function classListViewData(Section $section, Request $request): array
    {
        [$academicYearId, $semesterId] = $this->resolvePeriod($request);
        $students = $this->studentsInSection($section->id, $academicYearId, $semesterId);
        $subjects = Subject::whereHas('classSchedules', function ($query) use ($section) {
            $query->where('section_id', $section->id);
        })->get();

        if ($subjects->isEmpty() && !empty($section->grade_level)) {
            $subjects = Subject::where('class', $section->grade_level)->orderBy('subject_name')->get();
        }

        return [
            'section' => $section,
            'students' => $students,
            'subjects' => $subjects,
            'academicYear' => $academicYearId ? AcademicYear::find($academicYearId) : null,
            'semester' => $semesterId ? Semester::find($semesterId) : null,
        ];
    }

    protected function sectionsForScope(string $gradeLevel, $sectionId)
    {
        $query = Section::query()->orderBy('name');
        if ($sectionId) {
            $query->where('id', $sectionId);
        } elseif ($gradeLevel !== '') {
            $aliases = \App\Services\GradeSubjectCatalogService::gradeAliases($gradeLevel);
            $query->where(function ($q) use ($gradeLevel, $aliases) {
                $q->where('grade_level', $gradeLevel);
                if (!empty($aliases)) {
                    $q->orWhereIn('grade_level', $aliases);
                }
            });
        }

        return $query->get();
    }

    protected function studentsForScope(string $gradeLevel, $sectionId)
    {
        if ($sectionId) {
            return $this->studentsInSection($sectionId);
        }

        $aliases = \App\Services\GradeSubjectCatalogService::gradeAliases($gradeLevel);
        $labels = !empty($aliases) ? $aliases : [$gradeLevel];
        $sectionIds = Section::query()
            ->where(function ($q) use ($labels) {
                $q->whereIn('grade_level', $labels);
            })
            ->pluck('id');

        $ids = collect();
        foreach ($sectionIds as $id) {
            $ids = $ids->merge($this->studentsInSection($id)->pluck('id'));
        }

        return Student::query()
            ->whereIn('id', $ids->unique()->filter()->all())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    protected function studentsInSection($sectionId, $academicYearId = null, $semesterId = null)
    {
        $section = Section::find($sectionId);
        $ids = collect();

        $assignmentQuery = \Illuminate\Support\Facades\DB::table('student_section_assignments')
            ->where('section_id', $sectionId);
        if ($academicYearId) {
            $assignmentQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $assignmentQuery->where('semester_id', $semesterId);
        }
        $assigned = $assignmentQuery->pluck('student_id');
        if ($assigned->isEmpty() && ($academicYearId || $semesterId)) {
            $assigned = \Illuminate\Support\Facades\DB::table('student_section_assignments')
                ->where('section_id', $sectionId)
                ->pluck('student_id');
        }
        $ids = $ids->merge($assigned);

        if (\Illuminate\Support\Facades\Schema::hasTable('section_student')) {
            $ids = $ids->merge(
                \Illuminate\Support\Facades\DB::table('section_student')->where('section_id', $sectionId)->pluck('student_id')
            );
        }

        $query = Student::query()->orderBy('last_name')->orderBy('first_name');
        $query->where(function ($q) use ($ids, $section) {
            if ($ids->filter()->isNotEmpty()) {
                $q->whereIn('id', $ids->unique()->all());
            }
            if ($section && $section->name) {
                $q->orWhere(function ($inner) use ($section) {
                    $inner->where('section', $section->name);
                    if ($section->grade_level) {
                        $inner->where(function ($g) use ($section) {
                            $g->where('year_level', $section->grade_level)
                                ->orWhere('class', $section->grade_level);
                        });
                    }
                });
            }
        });

        if ($ids->filter()->isEmpty() && !($section && $section->name)) {
            return collect();
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


