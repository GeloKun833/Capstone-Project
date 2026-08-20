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
        $subjects = Subject::orderBy('subject_name')->get();
        $students = Student::with('user')->orderBy('first_name')->get();
        
        // Only get teachers who have valid user accounts with role "Teacher"
        // First, create Teacher records for Users with role "Teacher" if they don't exist
        $this->syncTeacherUsers();
        
        // Then get only teachers who have valid user relationships
        $teachers = Teacher::with('user')
            ->whereHas('user', function($query) {
                $query->where('role_name', 'Teacher');
            })
            ->get()
            ->sortBy(function($teacher) {
                return $teacher->full_name ?: ($teacher->user ? $teacher->user->name : '');
            });
            
        $academicYears = AcademicYear::orderBy('name')->get();
        $semesters = Semester::orderBy('name')->get();
        $sections = Section::orderBy('name')->get();
        
        return view('class-subject.unified-management', compact(
            'subjects', 
            'students', 
            'teachers', 
            'academicYears', 
            'semesters', 
            'sections'
        ));
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
            $academicYear = AcademicYear::findOrFail($request->academic_year_id);
            $semester = Semester::findOrFail($request->semester_id);
            
            $assignedCount = 0;
            $alreadyAssignedCount = 0;

            foreach ($request->student_ids as $studentId) {
                // Check if student is already assigned to this section for this academic period
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

                // Create new assignment
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

    /**
     * Get students for AJAX request
     */
    public function getStudents(Request $request)
    {
        try {
            $query = Student::with('user');
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('admission_id', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('email', 'like', "%{$search}%");
                      });
                });
            }
            
            $students = $query->limit(50)->get();
            
            return response()->json($students);
            
        } catch (\Exception $e) {
            Log::error('ClassSubjectController: Error in getStudents: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subjects for AJAX request
     */
    public function getSubjects(Request $request)
    {
        try {
            $query = Subject::query();
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('subject_name', 'like', "%{$search}%")
                      ->orWhere('class', 'like', "%{$search}%");
                });
            }
            
            $subjects = $query->limit(50)->get();
            
            return response()->json($subjects);
            
        } catch (\Exception $e) {
            Log::error('ClassSubjectController: Error in getSubjects: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync Teacher records with User records that have role "Teacher"
     */
    private function syncTeacherUsers()
    {
        try {
            // Get all users with role "Teacher" who don't have a corresponding Teacher record
            $teacherUsers = User::where('role_name', 'Teacher')
                ->whereDoesntHave('teacher')
                ->get();

            foreach ($teacherUsers as $user) {
                Teacher::create([
                    'user_id' => $user->user_id,
                    'full_name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'address' => '', // Default empty
                    'gender' => '', // Default empty
                    'date_of_birth' => null,
                    'qualification' => '', // Default empty
                    'experience' => '', // Default empty
                    'upload' => 'photo_defaults.jpg', // Default avatar
                ]);
            }

            Log::info('ClassSubjectController: Synced ' . $teacherUsers->count() . ' teacher users');

        } catch (\Exception $e) {
            Log::error('ClassSubjectController: Error syncing teacher users: ' . $e->getMessage());
        }
    }

    /**
     * Handle teacher-subject assignment from unified management form
     */
    public function handleAssignment(Request $request)
    {
        $operationType = $request->input('operation_type');

        if ($operationType === 'teacher_subject') {
            return $this->assignTeachersToSubject($request);
        } elseif ($operationType === 'student_section') {
            return $this->assignStudentsToSection($request);
        }

        Toastr::error('Invalid operation type', 'Error');
        return back();
    }

    /**
     * Assign teachers to a subject
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
            $academicYear = AcademicYear::findOrFail($request->academic_year_id);
            $semester = Semester::findOrFail($request->semester_id);
            $section = Section::findOrFail($request->section_id);
            
            $assignedCount = 0;

            foreach ($request->teacher_ids as $teacherId) {
                $teacher = Teacher::findOrFail($teacherId);
                
                // Check if teacher is already assigned to this subject
                if (!$subject->teachers()->where('teacher_id', $teacherId)->exists()) {
                    // Assign teacher to subject
                    $subject->teachers()->attach($teacherId);
                    $assignedCount++;
                }
                
                // Also assign teacher to the section if not already assigned
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
