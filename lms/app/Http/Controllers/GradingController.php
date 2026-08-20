<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectComponent;
use App\Models\WeightSetting;
use App\Models\StudentGpa;
use App\Models\GradeAlert;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Section;
use App\Models\QuarterlyGrade;
use App\Exports\GradesExport;
use App\Exports\GpaExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GradingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Grade Entry Form - DepEd Report Card Format
    public function gradeEntryForm(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found. Please contact the administrator.');
        }
        
        // Get selected subject and academic year from request
        $selectedSubjectId = $request->get('subject_id');
        $selectedAcademicYearId = $request->get('academic_year_id');
        
        // Get all subjects assigned to this teacher via class schedules
        $subjects = Subject::whereHas('classSchedules', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id)
                  ->where('is_active', true);
        })->distinct()->orderBy('subject_name')->get();
        
        // If no subjects found, get all subjects (fallback)
        if ($subjects->isEmpty()) {
            $subjects = Subject::orderBy('subject_name')->get();
        }
        
        // Get all academic years
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $currentAcademicYear = $selectedAcademicYearId 
            ? AcademicYear::find($selectedAcademicYearId) 
            : $academicYears->first();
        
        $students = collect();
        $quarterlyGrades = collect();
        
        // If subject is selected, get students and their quarterly grades
        if ($selectedSubjectId && $currentAcademicYear) {
            // Get all sections where this teacher teaches the selected subject
            $teacherSections = Section::whereHas('classSchedules', function($query) use ($teacher, $selectedSubjectId) {
                $query->where('teacher_id', $teacher->id)
                      ->where('subject_id', $selectedSubjectId)
                      ->where('is_active', true);
            })->pluck('id');
            
            // Get all students assigned to these sections
            $students = Student::whereHas('sections', function($query) use ($teacherSections) {
                $query->whereIn('sections.id', $teacherSections);
            })->with(['sections' => function($query) use ($teacherSections) {
                $query->whereIn('sections.id', $teacherSections);
            }])
              ->orderBy('last_name')
              ->orderBy('first_name')
              ->get();
            
            // Get existing quarterly grades for these students
            if ($students->isNotEmpty()) {
                $quarterlyGrades = QuarterlyGrade::where('subject_id', $selectedSubjectId)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->where('teacher_id', $teacher->id)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get()
                    ->keyBy('student_id');
            }
        }

        return view('grading.grade-entry', compact(
            'students', 
            'subjects', 
            'academicYears',
            'currentAcademicYear',
            'selectedSubjectId',
            'quarterlyGrades'
        ));
    }
    
    // Load Students via AJAX (for loading existing grades)
    public function loadStudents(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found. Please contact the administrator.',
            ], 403);
        }
        
        // Make section_id optional for new workflow
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'nullable|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'student_id' => 'nullable|exists:students,id',
            'component_id' => 'nullable|exists:subject_components,id',
        ]);
        
        $subjectId = $request->subject_id;
        $sectionId = $request->section_id;
        $academicYearId = $request->academic_year_id;
        $semesterId = $request->semester_id;
        $componentId = $request->component_id;
        $studentId = $request->student_id;
        
        // Verify that the subject is assigned to this teacher
        $subjectAssigned = Subject::whereHas('classSchedules', function($query) use ($teacher, $subjectId) {
            $query->where('teacher_id', $teacher->id)
                  ->where('subject_id', $subjectId);
        })->exists();
        
        if (!$subjectAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'This subject is not assigned to you.'
            ], 403);
        }
        
        // Get all sections where this teacher teaches the selected subject
        $teacherSections = Section::whereHas('classSchedules', function($query) use ($teacher, $subjectId) {
            $query->where('teacher_id', $teacher->id)
                  ->where('subject_id', $subjectId)
                  ->where('is_active', true);
        })->pluck('id');
        
        Log::info('Load Students - Teacher Sections for Subject', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subjectId,
            'section_ids' => $teacherSections->toArray(),
            'section_count' => $teacherSections->count()
        ]);
        
        // Verify that the section is assigned to this teacher (only if section_id is provided)
        if ($sectionId) {
            if (!$teacherSections->contains($sectionId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This section is not assigned to you for this subject.'
                ], 403);
            }
        }
        
        // Verify component belongs to the subject if provided
        if ($componentId) {
            $componentValid = SubjectComponent::where('id', $componentId)
                ->where('subject_id', $subjectId)
                ->exists();
            
            if (!$componentValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected component does not belong to this subject.'
                ], 403);
            }
        }
        
        // Get students from sections where teacher teaches this subject
        // If section_id is provided, filter by that section; otherwise get all students from all teacher's sections
        $studentsQuery = Student::whereHas('sections', function($query) use ($sectionId, $teacherSections, $academicYearId, $semesterId) {
            if ($sectionId) {
                // Filter by specific section if provided
                $query->where('sections.id', $sectionId);
            } else {
                // Get all students from all sections where teacher teaches
                $query->whereIn('sections.id', $teacherSections);
            }
            
            // Filter by academic year and semester if provided (from section assignment)
            if ($academicYearId) {
                $query->where('student_section_assignments.academic_year_id', $academicYearId);
            }
            if ($semesterId) {
                $query->where('student_section_assignments.semester_id', $semesterId);
            }
        });
        
        $students = $studentsQuery->orderBy('last_name')
          ->orderBy('first_name')
          ->get();
        
        // If no students found with academic year/semester filter, try without it (more flexible)
        if ($students->isEmpty()) {
            $studentsQuery = Student::whereHas('sections', function($query) use ($sectionId, $teacherSections) {
                if ($sectionId) {
                    $query->where('sections.id', $sectionId);
                } else {
                    $query->whereIn('sections.id', $teacherSections);
                }
            });
            
            $students = $studentsQuery->orderBy('last_name')
              ->orderBy('first_name')
              ->get();
        }
        
        // Log for debugging
        Log::info('Grade Entry - Students Found', [
            'section_id' => $sectionId,
            'subject_id' => $subjectId,
            'academic_year_id' => $academicYearId,
            'semester_id' => $semesterId,
            'student_count' => $students->count(),
            'student_ids' => $students->pluck('id')->toArray()
        ]);
        
        // If still no students, provide helpful error message
        if ($students->isEmpty()) {
            // Check if sections have any students at all
            $allSectionStudents = Student::whereHas('sections', function($query) use ($sectionId, $teacherSections) {
                if ($sectionId) {
                    $query->where('sections.id', $sectionId);
                } else {
                    $query->whereIn('sections.id', $teacherSections);
                }
            })->count();
            
            $message = 'No students found for the selected criteria. ';
            if ($allSectionStudents > 0) {
                if ($sectionId) {
                    $message .= "The section has {$allSectionStudents} student(s), but none match the selected academic year/semester. ";
                } else {
                    $message .= "Your sections have {$allSectionStudents} student(s) total, but none match the selected academic year/semester. ";
                }
                $message .= "Try selecting different academic period or ensure students are assigned to sections for the selected period.";
            } else {
                if ($sectionId) {
                    $message .= "No students are assigned to this section. Please assign students to the section first.";
                } else {
                    $message .= "No students are assigned to your sections for this subject. Please assign students to sections first.";
                }
            }
            
            return response()->json([
                'success' => false,
                'count' => 0,
                'message' => $message,
                'students' => []
            ]);
        }
        
        // If single student requested, return just that student's grade
        if ($studentId && $componentId) {
            $grade = Grade::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->where('component_id', $componentId)
                ->first();
            
            $student = Student::find($studentId);
            
            return response()->json([
                'success' => true,
                'count' => 1,
                'students' => [[
                    'id' => $student->id,
                    'user_id' => $student->user_id,
                    'full_name' => $student->first_name . ' ' . $student->last_name,
                    'existing_score' => $grade ? $grade->score : null,
                    'existing_max_score' => $grade ? $grade->max_score : null,
                    'existing_remarks' => $grade ? $grade->remarks : null,
                ]]
            ]);
        }
        
        // Get existing grades if component is selected
        $existingGrades = [];
        if ($componentId) {
            $grades = Grade::where('subject_id', $subjectId)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->where('component_id', $componentId)
                ->get()
                ->keyBy('student_id');
            
            $existingGrades = $grades;
        }
        
        return response()->json([
            'success' => true,
            'count' => $students->count(),
            'message' => "Found {$students->count()} student(s) in this section.",
            'students' => $students->map(function($student) use ($existingGrades) {
                $grade = $existingGrades[$student->id] ?? null;
                return [
                    'id' => $student->id,
                    'user_id' => $student->user_id,
                    'full_name' => $student->first_name . ' ' . $student->last_name,
                    'existing_score' => $grade ? $grade->score : null,
                    'existing_max_score' => $grade ? $grade->max_score : null,
                    'existing_remarks' => $grade ? $grade->remarks : null,
                ];
            })
        ]);
    }

    // Store Quarterly Grades
    public function storeQuarterlyGrades(Request $request)
    {
        try {
            $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'grades' => 'required|array|min:1',
                'grades.*.student_id' => 'required|exists:students,id',
                'grades.*.quarter_1' => 'nullable|numeric|min:0|max:100',
                'grades.*.quarter_2' => 'nullable|numeric|min:0|max:100',
                'grades.*.quarter_3' => 'nullable|numeric|min:0|max:100',
                'grades.*.quarter_4' => 'nullable|numeric|min:0|max:100',
                'grades.*.remarks' => 'nullable|string|max:50',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error saving quarterly grades', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $teacher = Auth::user()->teacher;
        $subjectId = $request->subject_id;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found. Please contact the administrator.',
            ], 403);
        }
        
        // Verify that the subject is assigned to this teacher
        $subjectAssigned = Subject::whereHas('classSchedules', function($query) use ($teacher, $subjectId) {
            $query->where('teacher_id', $teacher->id)
                  ->where('subject_id', $subjectId);
        })->exists();
        
        if (!$subjectAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to save grades for this subject.'
            ], 403);
        }

        $semesterId = $request->semester_id
            ?? Semester::where('academic_year_id', $request->academic_year_id)->orderBy('id')->value('id')
            ?? Semester::latest()->value('id');

        $syncService = app(\App\Services\QuarterlyGradeSyncService::class);
        $savedQuarterlyGrades = [];

        DB::beginTransaction();
        try {
            $savedCount = 0;
            foreach ($request->grades as $gradeData) {
                // Only save if at least one quarter grade is provided
                if (isset($gradeData['quarter_1']) || isset($gradeData['quarter_2']) || 
                    isset($gradeData['quarter_3']) || isset($gradeData['quarter_4'])) {
                    
                    $quarterlyGrade = QuarterlyGrade::updateOrCreate(
                        [
                            'student_id' => $gradeData['student_id'],
                            'subject_id' => $subjectId,
                            'academic_year_id' => $request->academic_year_id,
                        ],
                        [
                            'teacher_id' => $teacher->id,
                            'quarter_1' => isset($gradeData['quarter_1']) && $gradeData['quarter_1'] !== '' && $gradeData['quarter_1'] !== null ? (float)$gradeData['quarter_1'] : null,
                            'quarter_2' => isset($gradeData['quarter_2']) && $gradeData['quarter_2'] !== '' && $gradeData['quarter_2'] !== null ? (float)$gradeData['quarter_2'] : null,
                            'quarter_3' => isset($gradeData['quarter_3']) && $gradeData['quarter_3'] !== '' && $gradeData['quarter_3'] !== null ? (float)$gradeData['quarter_3'] : null,
                            'quarter_4' => isset($gradeData['quarter_4']) && $gradeData['quarter_4'] !== '' && $gradeData['quarter_4'] !== null ? (float)$gradeData['quarter_4'] : null,
                            'remarks' => isset($gradeData['remarks']) && $gradeData['remarks'] !== '' ? $gradeData['remarks'] : null,
                        ]
                    );
                    
                    // Recalculate final grade after saving
                    $quarterlyGrade->final_grade = $quarterlyGrade->calculateFinalGrade();
                    
                    // Only set remarks if not provided by teacher
                    if (empty($quarterlyGrade->remarks) && $quarterlyGrade->final_grade !== null) {
                        $quarterlyGrade->remarks = $quarterlyGrade->getRemarks();
                    }
                    
                    $quarterlyGrade->save();
                    $savedQuarterlyGrades[] = $quarterlyGrade->fresh();
                    
                    Log::info('Quarterly grade saved', [
                        'student_id' => $gradeData['student_id'],
                        'subject_id' => $subjectId,
                        'academic_year_id' => $request->academic_year_id,
                        'quarter_1' => $quarterlyGrade->quarter_1,
                        'quarter_2' => $quarterlyGrade->quarter_2,
                        'quarter_3' => $quarterlyGrade->quarter_3,
                        'quarter_4' => $quarterlyGrade->quarter_4,
                        'final_grade' => $quarterlyGrade->final_grade,
                        'remarks' => $quarterlyGrade->remarks,
                    ]);
                    
                    $savedCount++;
                }
            }

            if ($savedCount > 0 && $semesterId) {
                try {
                    $affectedStudentIds = $syncService->syncBatch($savedQuarterlyGrades, $semesterId);
                    $this->calculateGpaForStudents($affectedStudentIds, $request->academic_year_id, $semesterId);
                    $this->checkGradeAlerts($subjectId, $request->academic_year_id, $semesterId);
                    $this->updateRankings($request->academic_year_id, $semesterId);
                } catch (\Exception $syncException) {
                    Log::warning('Quarterly grades saved but post-save sync failed', [
                        'error' => $syncException->getMessage(),
                        'subject_id' => $subjectId,
                        'academic_year_id' => $request->academic_year_id,
                    ]);
                }
            }

            DB::commit();
            
            // Log successful save
            Log::info('Quarterly grades saved successfully', [
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectId,
                'academic_year_id' => $request->academic_year_id,
                'saved_count' => $savedCount,
                'student_ids' => collect($request->grades)->pluck('student_id')->toArray()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully saved {$savedCount} student grade(s)! Students can now view their grades in their portal."
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving quarterly grades', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error saving grades: ' . $e->getMessage()
            ], 500);
        }
    }

    // Store Grades (Legacy - for component-based grading)
    public function storeGrades(Request $request)
    {
        try {
            $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'component_id' => 'required|exists:subject_components,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'semester_id' => 'required|exists:semesters,id',
                'grades' => 'required|array|min:1',
                'grades.*.student_id' => 'required|exists:students,id',
                'grades.*.score' => 'nullable|numeric|min:0',
                'grades.*.max_score' => 'required|numeric|min:1',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $teacher = Auth::user()->teacher;
        $subjectId = $request->subject_id;
        $componentId = $request->component_id;
        
        // Verify that the subject is assigned to this teacher
        $subjectAssigned = Subject::whereHas('classSchedules', function($query) use ($teacher, $subjectId) {
            $query->where('teacher_id', $teacher->id)
                  ->where('subject_id', $subjectId);
        })->exists();
        
        if (!$subjectAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to save grades for this subject.'
            ], 403);
        }
        
        // Verify component belongs to the subject
        $componentValid = SubjectComponent::where('id', $componentId)
            ->where('subject_id', $subjectId)
            ->exists();
        
        if (!$componentValid) {
            return response()->json([
                'success' => false,
                'message' => 'The selected component does not belong to this subject.'
            ], 403);
        }

        $teacherId = $teacher->id;

        DB::beginTransaction();
        try {
            $savedCount = 0;
            foreach ($request->grades as $gradeData) {
                // Ensure score is set and is a valid number
                $score = isset($gradeData['score']) ? $gradeData['score'] : null;
                
                if ($score !== null && $score !== '' && is_numeric($score)) {
                    $score = (float) $score;
                    $maxScore = isset($gradeData['max_score']) ? (float) $gradeData['max_score'] : 100;
                    
                    Grade::updateOrCreate(
                        [
                            'student_id' => $gradeData['student_id'],
                            'subject_id' => $request->subject_id,
                            'component_id' => $request->component_id,
                            'academic_year_id' => $request->academic_year_id,
                            'semester_id' => $request->semester_id,
                        ],
                        [
                            'teacher_id' => $teacherId,
                            'score' => $score,
                            'max_score' => $maxScore,
                            'remarks' => $gradeData['remarks'] ?? null,
                        ]
                    );
                    $savedCount++;
                }
            }

            if ($savedCount > 0) {
                // Calculate GPA for affected students
                $this->calculateGpaForStudents(
                    collect($request->grades)->pluck('student_id')->unique(),
                    $request->academic_year_id,
                    $request->semester_id
                );

                // Check for grade alerts (auto-flagging low grades, performance drops, and at-risk students)
                $this->checkGradeAlerts($request->subject_id, $request->academic_year_id, $request->semester_id);
            }

            DB::commit();
            
            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully saved {$savedCount} grade(s)! Intelligent alerts have been checked for low grades, performance drops, and at-risk students."
                ]);
            }
            
            return redirect()->back()->with('success', "Successfully saved {$savedCount} grade(s)!");
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving grades', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error saving grades: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error saving grades: ' . $e->getMessage());
        }
    }

    // GPA and Ranking View
    public function gpaRanking(Request $request)
    {
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $sections = Section::all();

        $selectedAcademicYear = $request->get('academic_year_id', AcademicYear::latest()->first()?->id);
        $selectedSemester = $request->get('semester_id', Semester::latest()->first()?->id);
        $selectedSection = $request->get('section_id');

        $query = StudentGpa::with(['student', 'academicYear', 'semester'])
            ->where('academic_year_id', $selectedAcademicYear)
            ->where('semester_id', $selectedSemester);

        if ($selectedSection) {
            $query->whereHas('student.sections', function($q) use ($selectedSection) {
                $q->where('section_id', $selectedSection);
            });
        }

        $gpaRecords = $query->orderBy('gpa', 'desc')->get();

        // Update rankings
        $this->updateRankings($selectedAcademicYear, $selectedSemester, $selectedSection);

        return view('grading.gpa-ranking', compact(
            'gpaRecords', 'academicYears', 'semesters', 'sections',
            'selectedAcademicYear', 'selectedSemester', 'selectedSection'
        ));
    }

    // Performance Analytics
    public function performanceAnalytics(Request $request)
    {
        $studentId = $request->get('student_id');
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $students = Student::all();
        $subjects = Subject::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $performanceData = null;
        $trendData = null;
        $alerts = null;

        if ($studentId) {
            $student = Student::find($studentId);
            $performanceData = $this->getStudentPerformanceData($student, $academicYearId, $semesterId);
            $trendData = $this->getPerformanceTrends($student, $academicYearId);
            $alerts = $student->getActiveAlerts();
        }

        return view('grading.performance-analytics', compact(
            'students', 'subjects', 'academicYears', 'semesters', 'performanceData', 'trendData', 'alerts',
            'studentId', 'academicYearId', 'semesterId'
        ));
    }

    // Export Grades
    public function exportGrades(Request $request)
    {
        $format = $request->get('format', 'excel');
        $subjectId = $request->get('subject_id');
        $sectionId = $request->get('section_id');
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $fileName = 'grades_' . date('Y-m-d_H-i-s');

        if ($format === 'pdf') {
            $grades = $this->getGradesForExport($subjectId, $sectionId, $academicYearId, $semesterId);
            $pdf = PDF::loadView('exports.grades-pdf', compact('grades'));
            return $pdf->download($fileName . '.pdf');
        } else {
            return Excel::download(new GradesExport($subjectId, $sectionId, $academicYearId, $semesterId), $fileName . '.xlsx');
        }
    }

    // Export GPA Report
    public function exportGpa(Request $request)
    {
        $format = $request->get('format', 'excel');
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');
        $sectionId = $request->get('section_id');

        $fileName = 'gpa_report_' . date('Y-m-d_H-i-s');

        if ($format === 'pdf') {
            $gpaRecords = $this->getGpaForExport($academicYearId, $semesterId, $sectionId);
            $pdf = PDF::loadView('exports.gpa-pdf', compact('gpaRecords'));
            return $pdf->download($fileName . '.pdf');
        } else {
            return Excel::download(new GpaExport($academicYearId, $semesterId, $sectionId), $fileName . '.xlsx');
        }
    }

    // Weight Settings Management
    public function weightSettings(Request $request)
    {
        $subjects = Subject::all();
        $components = SubjectComponent::where('is_active', true)->get();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $selectedSubject = $request->get('subject_id');
        $selectedAcademicYear = $request->get('academic_year_id');
        $selectedSemester = $request->get('semester_id');

        $weightSettings = collect();
        if ($selectedSubject) {
            $weightSettings = WeightSetting::where('subject_id', $selectedSubject)
                ->where('academic_year_id', $selectedAcademicYear)
                ->where('semester_id', $selectedSemester)
                ->with(['component'])
                ->get();
        }

        return view('grading.weight-settings', compact(
            'subjects', 'components', 'academicYears', 'semesters',
            'weightSettings', 'selectedSubject', 'selectedAcademicYear', 'selectedSemester'
        ));
    }

    // Store Weight Settings
    public function storeWeightSettings(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'weights' => 'required|array',
            'weights.*.component_id' => 'required|exists:subject_components,id',
            'weights.*.weight' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Delete existing weights for this subject and period
            WeightSetting::where('subject_id', $request->subject_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('semester_id', $request->semester_id)
                ->delete();

            // Insert new weights
            foreach ($request->weights as $weightData) {
                WeightSetting::create([
                    'subject_id' => $request->subject_id,
                    'component_id' => $weightData['component_id'],
                    'weight' => $weightData['weight'],
                    'academic_year_id' => $request->academic_year_id,
                    'semester_id' => $request->semester_id,
                    'is_active' => true,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Weight settings saved successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error saving weight settings: ' . $e->getMessage());
        }
    }

    // Grade Alerts Management
    public function gradeAlerts(Request $request)
    {
        $students = Student::all();
        
        $alerts = GradeAlert::with(['student', 'subject', 'academicYear', 'semester'])
            ->where('is_resolved', false)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('grading.grade-alerts', compact('alerts', 'students'));
    }

    // Resolve Alert
    public function resolveAlert(Request $request, $alertId)
    {
        $alert = GradeAlert::findOrFail($alertId);
        $alert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Alert resolved successfully!');
    }

    // Private helper methods

    private function calculateGpaForStudents($studentIds, $academicYearId, $semesterId)
    {
        foreach ($studentIds as $studentId) {
            $this->calculateStudentGpa($studentId, $academicYearId, $semesterId);
        }
    }

    private function calculateStudentGpa($studentId, $academicYearId, $semesterId)
    {
        $grades = Grade::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->with(['subject', 'component'])
            ->get();

        $totalGradePoints = 0;
        $totalUnits = 0;

        foreach ($grades as $grade) {
            if ($grade->percentage !== null) {
                $gradePoints = $this->percentageToGradePoints($grade->percentage);
                $totalGradePoints += $gradePoints;
                $totalUnits += 1; // Assuming 1 unit per subject
            }
        }

        $gpa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

        StudentGpa::updateOrCreate(
            [
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
                'semester_id' => $semesterId,
            ],
            [
                'gpa' => round($gpa, 2),
                'total_units' => $totalUnits,
                'total_grade_points' => $totalGradePoints,
            ]
        );
    }

    private function percentageToGradePoints($percentage)
    {
        if ($percentage >= 90) return 4.0;
        if ($percentage >= 85) return 3.7;
        if ($percentage >= 80) return 3.3;
        if ($percentage >= 75) return 3.0;
        if ($percentage >= 70) return 2.7;
        if ($percentage >= 65) return 2.3;
        if ($percentage >= 60) return 2.0;
        if ($percentage >= 55) return 1.7;
        if ($percentage >= 50) return 1.3;
        if ($percentage >= 45) return 1.0;
        return 0.0;
    }

    private function updateRankings($academicYearId, $semesterId, $sectionId = null)
    {
        $query = StudentGpa::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId);

        if ($sectionId) {
            $query->whereHas('student.sections', function($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            });
        }

        $gpaRecords = $query->orderBy('gpa', 'desc')->get();

        foreach ($gpaRecords as $index => $record) {
            $record->update(['rank' => $index + 1]);
        }
    }

    private function checkGradeAlerts($subjectId, $academicYearId, $semesterId)
    {
        $lowGradeThreshold = 75; // Configurable threshold
        $performanceDropThreshold = 10; // Percentage drop to trigger alert
        $atRiskThreshold = 70; // Multiple low grades = at risk

        // 1. Check for low grades
        $lowGrades = Grade::where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('percentage', '<', $lowGradeThreshold)
            ->with(['student', 'subject'])
            ->get();

        foreach ($lowGrades as $grade) {
            $alert = GradeAlert::firstOrCreate(
                [
                    'student_id' => $grade->student_id,
                    'subject_id' => $subjectId,
                    'alert_type' => GradeAlert::TYPE_LOW_GRADE,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                ],
                [
                    'message' => "Low grade in {$grade->subject->subject_name}: {$grade->percentage}%",
                    'threshold_value' => $lowGradeThreshold,
                    'current_value' => $grade->percentage,
                ]
            );

            if ($alert->wasRecentlyCreated && $grade->student) {
                $student = $grade->student;
                if ($student->user) {
                    $student->user->notify(new \App\Notifications\LowGradeAlertNotification($grade, $student, $grade->subject));
                }
                if ($student->parent_email) {
                    $parent = \App\Models\User::where('email', $student->parent_email)->where('role_name', 'Parent')->first();
                    if ($parent) {
                        $parent->notify(new \App\Notifications\LowGradeAlertNotification($grade, $student, $grade->subject));
                    }
                }
            }
        }

        // 2. Check for performance drops (comparing with previous period)
        $currentGrades = Grade::where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->with(['student', 'subject'])
            ->get()
            ->groupBy('student_id');

        foreach ($currentGrades as $studentId => $currentStudentGrades) {
            $currentAverage = $currentStudentGrades->avg('percentage');
            
            // Get previous period grades (previous semester or previous academic year)
            $previousSemester = Semester::where('id', '<', $semesterId)
                ->orderBy('id', 'desc')
                ->first();
            
            $previousGrades = null;
            if ($previousSemester) {
                $previousGrades = Grade::where('student_id', $studentId)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $previousSemester->id)
                    ->get();
            } else {
                // Try previous academic year
                $previousAcademicYear = AcademicYear::where('id', '<', $academicYearId)
                    ->orderBy('id', 'desc')
                    ->first();
                
                if ($previousAcademicYear) {
                    $previousGrades = Grade::where('student_id', $studentId)
                        ->where('subject_id', $subjectId)
                        ->where('academic_year_id', $previousAcademicYear->id)
                        ->get();
                }
            }

            if ($previousGrades && $previousGrades->count() > 0) {
                $previousAverage = $previousGrades->avg('percentage');
                $dropAmount = $previousAverage - $currentAverage;

                if ($dropAmount >= $performanceDropThreshold && $currentAverage < $lowGradeThreshold) {
                    GradeAlert::firstOrCreate(
                        [
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                            'alert_type' => GradeAlert::TYPE_PERFORMANCE_DROP,
                            'academic_year_id' => $academicYearId,
                            'semester_id' => $semesterId,
                        ],
                        [
                            'message' => "Performance drop in {$currentStudentGrades->first()->subject->subject_name}: {$previousAverage}% → {$currentAverage}% (Drop: {$dropAmount}%)",
                            'threshold_value' => $performanceDropThreshold,
                            'current_value' => $dropAmount,
                        ]
                    );
                }
            }
        }

        // 3. Check for at-risk students (multiple low grades across subjects)
        $allLowGrades = Grade::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('percentage', '<', $atRiskThreshold)
            ->with(['student', 'subject'])
            ->get()
            ->groupBy('student_id');

        foreach ($allLowGrades as $studentId => $studentLowGrades) {
            $lowGradeCount = $studentLowGrades->count();
            $subjectsWithLowGrades = $studentLowGrades->pluck('subject_id')->unique()->count();

            // If student has low grades in 3 or more subjects, mark as at-risk
            if ($subjectsWithLowGrades >= 3) {
                $averageLowGrade = $studentLowGrades->avg('percentage');
                GradeAlert::firstOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => null, // At-risk is across all subjects
                        'alert_type' => GradeAlert::TYPE_AT_RISK,
                        'academic_year_id' => $academicYearId,
                        'semester_id' => $semesterId,
                    ],
                    [
                        'message' => "At-risk student: Low grades in {$subjectsWithLowGrades} subjects (Average: {$averageLowGrade}%)",
                        'threshold_value' => $atRiskThreshold,
                        'current_value' => $averageLowGrade,
                    ]
                );
            }
        }
    }

    private function getStudentPerformanceData($student, $academicYearId, $semesterId)
    {
        return Grade::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->with(['subject', 'component'])
            ->get()
            ->groupBy('subject_id');
    }

    private function getPerformanceTrends($student, $academicYearId)
    {
        return Grade::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->with(['subject', 'semester'])
            ->get()
            ->groupBy(['subject_id', 'semester_id']);
    }

    private function getGradesForExport($subjectId, $sectionId, $academicYearId, $semesterId)
    {
        $query = Grade::with(['student', 'subject', 'component', 'teacher'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($sectionId) {
            $query->whereHas('student.sections', function($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            });
        }

        return $query->get();
    }

    private function getGpaForExport($academicYearId, $semesterId, $sectionId)
    {
        $query = StudentGpa::with(['student', 'academicYear', 'semester'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId);

        if ($sectionId) {
            $query->whereHas('student.sections', function($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            });
        }

        return $query->orderBy('gpa', 'desc')->get();
    }
} 