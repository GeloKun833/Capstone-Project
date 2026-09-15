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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

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
        $search = trim((string) request('search', ''));

        $statusExpr = 'e.status';
        $dateExpr = 'e.enrollment_date';
        $portalStatusExpr = "COALESCE(s.enrollment_status, 'active')";

        $legacy = DB::table('enrollments as e')
            ->leftJoin('students as s', 's.id', '=', 'e.student_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'e.subject_id')
            ->leftJoin('academic_years as ay', 'ay.id', '=', 'e.academic_year_id')
            ->leftJoin('semesters as sem', 'sem.id', '=', 'e.semester_id')
            ->selectRaw("
                e.id as id,
                e.student_id as student_id,
                s.user_id as student_user_id,
                s.upload as student_upload,
                'enrollment' as type,
                TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))) as student_name,
                COALESCE(s.email, 'N/A') as student_email,
                COALESCE(sub.subject_name, 'N/A') as subject_name,
                COALESCE(NULLIF(s.section, ''), 'N/A') as section_name,
                COALESCE(NULLIF(s.year_level, ''), NULLIF(s.class, ''), '') as grade_level,
                COALESCE(ay.name, 'N/A') as academic_year,
                COALESCE(sem.name, 'N/A') as semester,
                {$statusExpr} as status,
                {$dateExpr} as enrollment_date,
                e.created_at as created_at
            ");

        $portal = DB::table('students as s')
            ->whereNotNull('s.enrollment_application_id')
            ->selectRaw("
                s.id as id,
                s.id as student_id,
                s.user_id as student_user_id,
                s.upload as student_upload,
                'portal_student' as type,
                TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))) as student_name,
                COALESCE(s.email, 'N/A') as student_email,
                'Portal Enrollment' as subject_name,
                COALESCE(NULLIF(s.section, ''), 'N/A') as section_name,
                COALESCE(NULLIF(s.year_level, ''), NULLIF(s.class, ''), '') as grade_level,
                'Current' as academic_year,
                'Current' as semester,
                {$portalStatusExpr} as status,
                s.created_at as enrollment_date,
                s.created_at as created_at
            ");

        if ($search !== '') {
            $like = '%'.$search.'%';
            $legacy->where(function ($query) use ($like) {
                $query->where('s.first_name', 'like', $like)
                    ->orWhere('s.last_name', 'like', $like)
                    ->orWhere('s.email', 'like', $like)
                    ->orWhere('sub.subject_name', 'like', $like);
            });
            $portal->where(function ($query) use ($like) {
                $query->where('s.first_name', 'like', $like)
                    ->orWhere('s.last_name', 'like', $like)
                    ->orWhere('s.email', 'like', $like);
            });
        }

        $rows = DB::query()
            ->fromSub($legacy->unionAll($portal), 'combined_enrollments')
            ->orderByDesc('created_at')
            ->get();

        $grouped = $rows->groupBy(function ($row) {
            return $row->student_id ?: strtolower(($row->student_email ?? '').'|'.($row->student_name ?? ''));
        })->map(function ($studentRows) {
            $first = $studentRows->first();
            $classRows = $studentRows->where('type', 'enrollment');
            $portalRows = $studentRows->where('type', 'portal_student');

            $subjects = $classRows->pluck('subject_name')
                ->filter(fn ($name) => $name && $name !== 'N/A' && $name !== 'Portal Enrollment')
                ->unique()
                ->values();

            $sections = $studentRows->pluck('section_name')
                ->filter(fn ($name) => $name && $name !== 'N/A')
                ->unique()
                ->values();

            $years = $classRows->pluck('academic_year')
                ->filter(fn ($name) => $name && $name !== 'N/A')
                ->unique()
                ->values();

            $semesters = $classRows->pluck('semester')
                ->filter(fn ($name) => $name && $name !== 'N/A')
                ->unique()
                ->values();

            $grades = $studentRows->pluck('grade_level')->filter()->unique()->values();
            $statuses = $studentRows->pluck('status')->filter()->unique()->values();

            $status = 'active';
            if ($statuses->contains('pending') && !$statuses->contains('active')) {
                $status = 'pending';
            } elseif ($statuses->contains('active')) {
                $status = 'active';
            } elseif ($statuses->isNotEmpty()) {
                $status = $statuses->first();
            }

            $latest = $studentRows->sortByDesc(function ($row) {
                return $row->enrollment_date ?: $row->created_at;
            })->first();

            return [
                'student_id' => $first->student_id,
                'student_user_id' => $first->student_user_id,
                'student_upload' => $first->student_upload,
                'student_name' => $first->student_name ?: 'Unnamed student',
                'student_email' => $first->student_email,
                'grade_level' => $grades->implode(', '),
                'sections' => $sections,
                'subjects' => $subjects,
                'academic_year' => $years->implode(', ') ?: '—',
                'semester' => $semesters->implode(', ') ?: '—',
                'status' => $status,
                'statuses' => $statuses,
                'enrollment_date' => $latest->enrollment_date ?? $latest->created_at ?? null,
                'has_portal' => $portalRows->isNotEmpty(),
                'portal_id' => optional($portalRows->first())->id,
                'primary_enrollment_id' => optional($classRows->first())->id,
                'enrollment_ids' => $classRows->pluck('id')->values(),
                'subject_count' => $subjects->count(),
            ];
        })->sortByDesc('enrollment_date')->values();

        $paginatedEnrollments = new LengthAwarePaginator(
            $grouped->forPage($page, $perPage)->values(),
            $grouped->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('enrollments.index', compact('paginatedEnrollments', 'search'));
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
            'date_of_birth' => \App\Support\FormRules::DOB,
            
            
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
            Log::error('Failed to create enrollment user: '.$e->getMessage());
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
        $enrollment->load(['student', 'subject', 'academicYear', 'semester']);
        $students = Student::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $subjects = Subject::orderBy('subject_name')->get();
        $sections = Section::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $semesters = Semester::orderByDesc('id')->get();

        return view('enrollments.edit', compact('enrollment', 'students', 'subjects', 'sections', 'academicYears', 'semesters'));
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

        $enrollment->update($request->only([
            'subject_id',
            'academic_year_id',
            'semester_id',
            'status',
        ]));
        
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
