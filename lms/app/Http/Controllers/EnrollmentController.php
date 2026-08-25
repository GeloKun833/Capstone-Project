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
use Illuminate\Support\Facades\Schema;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $perPage = 15;
        $page = max(1, (int) request('page', 1));

        $hasEnrollmentStatus = Schema::hasColumn('enrollments', 'status');
        $hasEnrollmentDate = Schema::hasColumn('enrollments', 'enrollment_date');
        $hasStudentEnrollmentStatus = Schema::hasColumn('students', 'enrollment_status');

        $statusExpr = $hasEnrollmentStatus ? 'e.status' : "'active'";
        $dateExpr = $hasEnrollmentDate ? 'e.enrollment_date' : 'e.created_at';
        $portalStatusExpr = $hasStudentEnrollmentStatus
            ? "COALESCE(s.enrollment_status, 'active')"
            : "'active'";

        $legacy = DB::table('enrollments as e')
            ->leftJoin('students as s', 's.id', '=', 'e.student_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'e.subject_id')
            ->leftJoin('academic_years as ay', 'ay.id', '=', 'e.academic_year_id')
            ->leftJoin('semesters as sem', 'sem.id', '=', 'e.semester_id')
            ->selectRaw("
                e.id as id,
                'enrollment' as type,
                TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))) as student_name,
                COALESCE(s.email, 'N/A') as student_email,
                COALESCE(sub.subject_name, 'N/A') as subject_name,
                'N/A' as section_name,
                COALESCE(ay.name, 'N/A') as academic_year,
                COALESCE(sem.name, 'N/A') as semester,
                {$statusExpr} as status,
                {$dateExpr} as enrollment_date,
                e.created_at as created_at
            ");

        $portalSelect = "
                s.id as id,
                'portal_student' as type,
                TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))) as student_name,
                COALESCE(s.email, 'N/A') as student_email,
                'Portal Enrollment' as subject_name,
                'Auto-Assigned' as section_name,
                'Current' as academic_year,
                'Current' as semester,
                {$portalStatusExpr} as status,
                s.created_at as enrollment_date,
                s.created_at as created_at
            ";

        $portal = Schema::hasColumn('students', 'enrollment_application_id')
            ? DB::table('students as s')->whereNotNull('s.enrollment_application_id')->selectRaw($portalSelect)
            : DB::table('students as s')->whereRaw('1 = 0')->selectRaw($portalSelect);

        $union = $legacy->unionAll($portal);

        $total = DB::query()->fromSub($union, 'combined_enrollments')->count();
        $items = DB::query()->fromSub($union, 'combined_enrollments')
            ->orderByDesc('created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn ($row) => (array) $row);

        $paginatedEnrollments = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
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
            \Log::error('Failed to create enrollment user: '.$e->getMessage());
            Toastr::error('Unable to complete the operation. Please try again.', 'Error');
            return back()->withInput()->with('error', 'Unable to complete the operation. Please try again.');
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
