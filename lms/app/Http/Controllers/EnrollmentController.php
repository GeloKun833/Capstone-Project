<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

class EnrollmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get enrollments from the old system
        $enrollments = Enrollment::with(['student', 'subject', 'academicYear', 'semester'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get students from the new enrollment portal system
        $portalStudents = Student::whereNotNull('enrollment_application_id')
            ->with(['user', 'enrollmentApplication'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Combine both types of enrollments
        $allEnrollments = collect();
        
        // Add old system enrollments
        foreach ($enrollments as $enrollment) {
            $allEnrollments->push([
                'id' => $enrollment->id,
                'type' => 'enrollment',
                'student_name' => $enrollment->student->first_name . ' ' . $enrollment->student->last_name ?? 'N/A',
                'student_email' => $enrollment->student->email ?? 'N/A',
                'subject_name' => $enrollment->subject->subject_name ?? 'N/A',
                'section_name' => $enrollment->section->name ?? 'N/A',
                'academic_year' => $enrollment->academicYear->name ?? 'N/A',
                'semester' => $enrollment->semester->name ?? 'N/A',
                'status' => $enrollment->status,
                'enrollment_date' => $enrollment->enrollment_date,
                'created_at' => $enrollment->created_at,
            ]);
        }
        
        // Add portal students
        foreach ($portalStudents as $student) {
            $allEnrollments->push([
                'id' => $student->id,
                'type' => 'portal_student',
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'student_email' => $student->email,
                'subject_name' => 'Portal Enrollment',
                'section_name' => 'Auto-Assigned',
                'academic_year' => 'Current',
                'semester' => 'Current',
                'status' => $student->enrollment_status ?? 'active',
                'enrollment_date' => $student->created_at,
                'created_at' => $student->created_at,
            ]);
        }
        
        // Sort by creation date and paginate
        $allEnrollments = $allEnrollments->sortByDesc('created_at')->values();
        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $allEnrollments->slice($offset, $perPage)->values();
        
        $paginatedEnrollments = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allEnrollments->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        
        return view('enrollments.index', compact('paginatedEnrollments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('enrollments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role_name' => 'required|string|in:Teacher,Registrar',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            
            
            // Teacher-specific fields
            'teacher_id' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:255',
            
        ]);

        DB::beginTransaction();
        
        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role_name' => $request->role_name,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'department' => $request->department,
                'position' => $request->position,
                'date_of_birth' => $request->date_of_birth,
                'join_date' => Carbon::now()->toDayDateTimeString(),
                'status' => 'active',
            ]);

            // Create role-specific profile
            if ($request->role_name === 'Teacher') {
                $this->createTeacherProfile($user, $request);
            }

            DB::commit();
            
            Toastr::success('User created successfully!', 'Success');
            return redirect()->route('enrollments.index');
            
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Failed to create user: ' . $e->getMessage(), 'Error');
            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student', 'subject', 'academicYear', 'semester']);
        return view('enrollments.show', compact('enrollment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enrollment $enrollment)
    {
        $subjects = Subject::all();
        $sections = Section::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        
        return view('enrollments.edit', compact('enrollment', 'subjects', 'sections', 'academicYears', 'semesters'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'nullable|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'status' => 'required|string|in:active,inactive,completed,dropped',
        ]);

        $enrollment->update($request->all());
        
        Toastr::success('Enrollment updated successfully!', 'Success');
        return redirect()->route('enrollments.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        
        Toastr::success('Enrollment deleted successfully!', 'Success');
        return redirect()->route('enrollments.index');
    }


    /**
     * Create teacher profile
     */
    private function createTeacherProfile(User $user, Request $request)
    {
        Teacher::create([
            'user_id' => $user->user_id,
            'first_name' => explode(' ', $user->name)[0] ?? $user->name,
            'last_name' => count(explode(' ', $user->name)) > 1 ? implode(' ', array_slice(explode(' ', $user->name), 1)) : '',
            'email' => $user->email,
            'teacher_id' => $request->teacher_id,
            'specialization' => $request->specialization,
            'status' => 'active',
        ]);
    }

}
