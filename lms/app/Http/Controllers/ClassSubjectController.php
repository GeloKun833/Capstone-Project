<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Enrollment;
use App\Services\GradeSubjectCatalogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;

class ClassSubjectController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    /**
     * Show unified Class & Subject Management form
     */
    public function unifiedManagementForm()
    {
        $catalogService = app(GradeSubjectCatalogService::class);
        $subjectsByGrade = $catalogService->subjectsGroupedByGrade();
        $gradeLevels = GradeSubjectCatalogService::gradeLevels();

        $this->syncTeacherUsers();

        $teachers = Teacher::with(['user', 'subjects'])
            ->whereHas('user', function ($query) {
                $query->where('role_name', 'Teacher');
            })
            ->get()
            ->sortBy(function ($teacher) {
                return $teacher->full_name ?: ($teacher->user ? $teacher->user->name : '');
            })
            ->values();

        $academicYears = AcademicYear::orderBy('name')->get();
        $semesters = Semester::orderBy('name')->get();
        $sections = Section::orderBy('grade_level')->orderBy('name')->get();
        $sectionsByGrade = $catalogService->sectionsGroupedByGrade();
        $catalogService->normalizeSectionGradeLabels();
        $sectionsByGrade = $catalogService->sectionsGroupedByGrade();

        // Teachers currently linked to each grade (via subject_teacher)
        $teachersByGrade = [];
        foreach ($subjectsByGrade as $grade => $gradeSubjects) {
            $subjectIds = $gradeSubjects->pluck('id')->all();
            if (empty($subjectIds)) {
                $teachersByGrade[$grade] = collect();
                continue;
            }
            $teacherIds = DB::table('subject_teacher')
                ->whereIn('subject_id', $subjectIds)
                ->pluck('teacher_id')
                ->unique()
                ->all();
            $teachersByGrade[$grade] = $teachers->whereIn('id', $teacherIds)->values();
        }

        // Flat JSON for catalog modal JS (avoid complex @json closures in Blade)
        $subjectsByGradeJson = [];
        foreach ($subjectsByGrade as $grade => $gradeSubjects) {
            $subjectsByGradeJson[$grade] = $gradeSubjects->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->subject_name,
                    'subject_id' => $s->subject_id,
                ];
            })->values()->all();
        }

        $sectionsByGradeJson = [];
        foreach ($sectionsByGrade as $grade => $gradeSections) {
            $sectionsByGradeJson[$grade] = $gradeSections->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'capacity' => $s->capacity ?? 25,
                    'adviser' => $s->adviser->full_name ?? null,
                ];
            })->values()->all();
        }

        return view('class-subject.unified-management', compact(
            'subjectsByGrade',
            'subjectsByGradeJson',
            'sectionsByGrade',
            'sectionsByGradeJson',
            'gradeLevels',
            'teachers',
            'teachersByGrade',
            'academicYears',
            'semesters',
            'sections'
        ));
    }

    /**
     * Import missing default subjects from config into the admin catalog.
     */
    public function importDefaultSubjects(Request $request)
    {
        $grade = $request->filled('grade_level') ? $request->grade_level : null;
        $created = app(GradeSubjectCatalogService::class)->importMissingFromConfig($grade);

        if ($created > 0) {
            Toastr::success("Imported {$created} subject(s) into the catalog.", 'Success');
        } else {
            Toastr::info('All default subjects for the selected grade(s) already exist.', 'Info');
        }

        return redirect()->route('class-subject.unified-management');
    }

    /**
     * Quick-add a subject for a grade (from catalog modal).
     */
    public function quickAddSubject(Request $request)
    {
        $grade = $request->input('grade_level', $request->input('class'));

        $request->merge(['grade_level' => $grade]);

        $request->validate([
            'subject_name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M}\p{N}\s\'\-\.\,\&\(\)]+$/u'],
            'grade_level' => 'required|string|in:' . implode(',', GradeSubjectCatalogService::gradeLevels()),
        ], [
            'subject_name.regex' => 'Subject name cannot contain emojis.',
        ]);

        $exists = Subject::where('subject_name', $request->subject_name)
            ->where('class', $request->grade_level)
            ->exists();

        if ($exists) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'That subject already exists for ' . $request->grade_level . '.',
                ], 422);
            }
            Toastr::error('That subject already exists for ' . $request->grade_level . '.', 'Error');
            return redirect()->route('class-subject.unified-management');
        }

        $subject = Subject::create([
            'subject_name' => trim($request->subject_name),
            'class' => $request->grade_level,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $subject->subject_name . ' added to ' . $subject->class . '.',
                'subject' => [
                    'id' => $subject->id,
                    'name' => $subject->subject_name,
                    'subject_id' => $subject->subject_id,
                    'class' => $subject->class,
                ],
                'grade_level' => $subject->class,
            ]);
        }

        Toastr::success($subject->subject_name . ' added to ' . $subject->class . '.', 'Success');
        return redirect()->route('class-subject.unified-management');
    }

    /**
     * Quick-add a block section for a grade (from sections modal).
     */
    public function quickAddSection(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|string|in:' . implode(',', GradeSubjectCatalogService::gradeLevels()),
            'capacity' => 'nullable|integer|min:1',
        ]);

        $section = Section::create([
            'name' => trim($request->name),
            'grade_level' => $request->grade_level,
            'capacity' => $request->capacity ?: 25,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $section->name . ' added for ' . $section->grade_level . '.',
                'section' => [
                    'id' => $section->id,
                    'name' => $section->name,
                    'capacity' => $section->capacity ?? 25,
                    'adviser' => null,
                    'grade_level' => $section->grade_level,
                ],
                'grade_level' => $section->grade_level,
            ]);
        }

        Toastr::success(
            $section->name . ' added for ' . $section->grade_level . '. It will show on enrollment Block Section.',
            'Success'
        );
        return redirect()->route('class-subject.unified-management');
    }

    /**
     * Handle assigning students to sections
     */
    public function assignStudentsToSection(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        DB::beginTransaction();

        try {
            $section = Section::findOrFail($request->section_id);
            $assignedCount = 0;
            $alreadyAssignedCount = 0;

            foreach ($request->student_ids as $studentId) {
                $existingAssignment = DB::table('student_section_assignments')
                    ->where([
                        'student_id' => $studentId,
                        'section_id' => $request->section_id,
                        'academic_year_id' => $request->academic_year_id,
                        'semester_id' => $request->semester_id,
                    ])->first();

                if ($existingAssignment) {
                    $alreadyAssignedCount++;
                    continue;
                }

                DB::table('student_section_assignments')->insert([
                    'student_id' => $studentId,
                    'section_id' => $request->section_id,
                    'academic_year_id' => $request->academic_year_id,
                    'semester_id' => $request->semester_id,
                    'assigned_date' => now(),
                ]);

                $assignedCount++;
            }

            DB::commit();

            $message = "Successfully assigned {$assignedCount} students to {$section->name}";
            if ($alreadyAssignedCount > 0) {
                $message .= " ({$alreadyAssignedCount} students were already assigned)";
            }

            Toastr::success($message, 'Success');
            return redirect()->route('class-subject.unified-management');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Failed to assign students to section: ' . $e->getMessage(), 'Error');
            return back()->withInput();
        }
    }

    public function getStudents(Request $request)
    {
        try {
            $query = Student::with('user');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'like', "%{$search}%");
                        });
                });
            }

            return response()->json($query->limit(50)->get());
        } catch (\Exception $e) {
            Log::error('ClassSubjectController: Error in getStudents: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch students: ' . $e->getMessage()], 500);
        }
    }

    public function getSubjects(Request $request)
    {
        try {
            $query = Subject::query();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('subject_name', 'like', "%{$search}%")
                        ->orWhere('class', 'like', "%{$search}%");
                });
            }

            return response()->json($query->limit(50)->get());
        } catch (\Exception $e) {
            Log::error('ClassSubjectController: Error in getSubjects: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch subjects: ' . $e->getMessage()], 500);
        }
    }

    private function syncTeacherUsers()
    {
        try {
            $teacherUsers = User::where('role_name', 'Teacher')
                ->whereDoesntHave('teacher')
                ->get();

            foreach ($teacherUsers as $user) {
                Teacher::create([
                    'user_id' => $user->user_id,
                    'full_name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'address' => '',
                    'gender' => '',
                    'date_of_birth' => null,
                    'qualification' => '',
                    'experience' => '',
                    'upload' => 'photo_defaults.jpg',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('ClassSubjectController: Error syncing teacher users: ' . $e->getMessage());
        }
    }

    public function handleAssignment(Request $request)
    {
        $operationType = $request->input('operation_type');

        if ($operationType === 'teacher_grade') {
            return $this->assignTeachersToGrade($request);
        }

        if ($operationType === 'teacher_grade_unassign') {
            return $this->unassignTeachersFromGrade($request);
        }

        if ($operationType === 'teacher_subject') {
            // Legacy single-subject path kept for compatibility
            return $this->assignTeachersToSubject($request);
        }

        if ($operationType === 'student_section') {
            return $this->assignStudentsToSection($request);
        }

        Toastr::error('Invalid operation type', 'Error');
        return back();
    }

    /**
     * Assign teacher(s) to ALL subjects under a grade level.
     */
    private function assignTeachersToGrade(Request $request)
    {
        $request->validate([
            'grade_level' => 'required|string|in:' . implode(',', GradeSubjectCatalogService::gradeLevels()),
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'nullable|exists:sections,id',
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'exists:teachers,id',
        ]);

        $subjects = app(GradeSubjectCatalogService::class)->subjectsForGrade($request->grade_level);

        if ($subjects->isEmpty()) {
            Toastr::error(
                'No subjects found for ' . $request->grade_level . '. Add subjects in the catalog first.',
                'Error'
            );
            return back()->withInput();
        }

        DB::beginTransaction();

        try {
            $section = $request->filled('section_id')
                ? Section::findOrFail($request->section_id)
                : null;

            $linkCount = 0;
            $teacherNames = [];

            foreach ($request->teacher_ids as $teacherId) {
                $teacher = Teacher::findOrFail($teacherId);
                $teacherNames[] = $teacher->full_name ?: ($teacher->user->name ?? 'Teacher');

                foreach ($subjects as $subject) {
                    if (!$subject->teachers()->where('teacher_id', $teacherId)->exists()) {
                        $subject->teachers()->attach($teacherId);
                        $linkCount++;
                    }
                }

                if ($section && !$teacher->sections()->where('section_id', $section->id)->exists()) {
                    $teacher->sections()->attach($section->id);
                }
            }

            DB::commit();

            $subjectCount = $subjects->count();
            $teacherCount = count($request->teacher_ids);

            if ($linkCount > 0) {
                Toastr::success(
                    "Assigned {$teacherCount} teacher(s) to all {$subjectCount} subject(s) in {$request->grade_level}.",
                    'Success'
                );
            } else {
                Toastr::info(
                    'Selected teacher(s) were already assigned to all subjects in ' . $request->grade_level . '.',
                    'Info'
                );
            }

            return redirect()->route('class-subject.unified-management');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to assign teachers to grade: ' . $e->getMessage());
            Toastr::error('Failed to assign teachers: ' . $e->getMessage(), 'Error');
            return back()->withInput();
        }
    }

    /**
     * Unassign teacher(s) from ALL subjects under a grade level.
     */
    private function unassignTeachersFromGrade(Request $request)
    {
        $request->validate([
            'grade_level' => 'required|string|in:' . implode(',', GradeSubjectCatalogService::gradeLevels()),
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'nullable|exists:sections,id',
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'exists:teachers,id',
        ]);

        $subjects = app(GradeSubjectCatalogService::class)->subjectsForGrade($request->grade_level);

        if ($subjects->isEmpty()) {
            Toastr::error(
                'No subjects found for ' . $request->grade_level . '.',
                'Error'
            );
            return back()->withInput();
        }

        DB::beginTransaction();

        try {
            $unlinkCount = 0;
            $subjectIds = $subjects->pluck('id')->all();

            foreach ($request->teacher_ids as $teacherId) {
                $teacher = Teacher::findOrFail($teacherId);

                foreach ($subjects as $subject) {
                    if ($subject->teachers()->where('teacher_id', $teacherId)->exists()) {
                        $subject->teachers()->detach($teacherId);
                        $unlinkCount++;
                    }
                }

                // Optional: unlink from selected section only when requested
                if ($request->filled('section_id')) {
                    $teacher->sections()->detach((int) $request->section_id);
                }
            }

            DB::commit();

            $teacherCount = count($request->teacher_ids);

            if ($unlinkCount > 0) {
                Toastr::success(
                    "Unassigned {$teacherCount} teacher(s) from subjects in {$request->grade_level} ({$unlinkCount} link(s) removed).",
                    'Success'
                );
            } else {
                Toastr::info(
                    'Selected teacher(s) were not assigned to subjects in ' . $request->grade_level . '.',
                    'Info'
                );
            }

            return redirect()->route('class-subject.unified-management');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to unassign teachers from grade: ' . $e->getMessage());
            Toastr::error('Failed to unassign teachers: ' . $e->getMessage(), 'Error');
            return back()->withInput();
        }
    }

    /**
     * Remove a subject from the grade catalog and clean student enrollments.
     */
    public function quickDeleteSubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        DB::beginTransaction();
        try {
            $subject = Subject::findOrFail($request->subject_id);
            $grade = $subject->class;
            $name = $subject->subject_name;

            $this->purgeSubjectAndRelated($subject);

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$name} removed from {$grade}. Student class lists updated.",
                    'grade_level' => $grade,
                ]);
            }

            Toastr::success("{$name} removed from {$grade}.", 'Success');
            return redirect()->route('class-subject.unified-management');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete subject: '.$e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete subject: '.$e->getMessage(),
                ], 500);
            }

            Toastr::error('Failed to delete subject.', 'Error');
            return back();
        }
    }

    /**
     * Delete subject + related links/enrollments and bust student caches.
     */
    private function purgeSubjectAndRelated(Subject $subject): void
    {
        $subjectId = $subject->id;
        $grade = (string) ($subject->class ?? '');

        $studentIds = Enrollment::where('subject_id', $subjectId)->pluck('student_id')->unique()->filter();

        $subject->teachers()->detach();
        $subject->sections()->detach();
        if (method_exists($subject, 'curricula')) {
            $subject->curricula()->detach();
        }

        if (DB::getSchemaBuilder()->hasTable('class_schedules')) {
            DB::table('class_schedules')->where('subject_id', $subjectId)->delete();
        }
        if (DB::getSchemaBuilder()->hasTable('curriculum_subject')) {
            DB::table('curriculum_subject')->where('subject_id', $subjectId)->delete();
        }

        Enrollment::where('subject_id', $subjectId)->delete();

        foreach ($studentIds as $studentId) {
            \Illuminate\Support\Facades\Cache::forget('student.dashboard.v2.'.$studentId);
            $student = Student::with('user')->find($studentId);
            if ($student && $student->user) {
                \App\Support\SidebarMenu::forgetForUser($student->user);
            }
        }

        if ($grade !== '') {
            \Illuminate\Support\Facades\Cache::forget('catalog.subjects.'.md5($grade));
        }

        $subject->delete();
    }

    /**
     * Legacy: assign teachers to a single subject
     */
    private function assignTeachersToSubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'exists:teachers,id',
        ]);

        DB::beginTransaction();

        try {
            $subject = Subject::findOrFail($request->subject_id);
            $section = Section::findOrFail($request->section_id);
            $assignedCount = 0;

            foreach ($request->teacher_ids as $teacherId) {
                $teacher = Teacher::findOrFail($teacherId);

                if (!$subject->teachers()->where('teacher_id', $teacherId)->exists()) {
                    $subject->teachers()->attach($teacherId);
                    $assignedCount++;
                }

                if (!$teacher->sections()->where('section_id', $section->id)->exists()) {
                    $teacher->sections()->attach($section->id);
                }
            }

            DB::commit();

            if ($assignedCount > 0) {
                Toastr::success("Successfully assigned {$assignedCount} teacher(s) to {$subject->subject_name} ({$subject->class})", 'Success');
            } else {
                Toastr::info('All selected teachers were already assigned to this subject', 'Info');
            }

            return redirect()->route('class-subject.unified-management');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to assign teachers to subject: ' . $e->getMessage());
            Toastr::error('Failed to assign teachers: ' . $e->getMessage(), 'Error');
            return back()->withInput();
        }
    }
}
