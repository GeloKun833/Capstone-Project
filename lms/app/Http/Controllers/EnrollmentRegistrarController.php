<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\EnrollmentApplication;
use App\Models\EnrollmentDocument;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentRegistrarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin|Registrar']);
    }

    /**
     * Display all enrollment applications
     */
    public function index(Request $request)
    {
        $query = EnrollmentApplication::with(['documents', 'reviewer']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('application_number', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $applications = $query->paginate(15);

        return view('enrollment.registrar.index', compact('applications'));
    }

    /**
     * Show application details for review
     */
    public function show($id)
    {
        $application = EnrollmentApplication::with(['documents', 'reviewer'])->findOrFail($id);
        return view('enrollment.registrar.show', compact('application'));
    }

    /**
     * Mark application as under review
     */
    public function markUnderReview($id)
    {
        $application = EnrollmentApplication::findOrFail($id);
        $application->markAsUnderReview();

        return back()->with('success', 'Application marked as under review.');
    }

    /**
     * Approve application and create student account with automatic subject enrollment
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $application = EnrollmentApplication::findOrFail($id);
        
        // Check if student already exists
        $existingStudent = Student::where('email', $application->email)->first();
        if ($existingStudent) {
            // Just approve the application if student already exists
            $application->approve();
            if ($request->filled('notes')) {
                $application->update(['notes' => $request->notes]);
            }
            return back()->with('success', 'Application approved successfully. Student account already exists.');
        }

        try {
            DB::beginTransaction();

            // Check if parent account should be created (if checkbox was checked during enrollment)
            // Since we don't store this in the application, we'll check if parent account already exists
            $parentUser = null;
            $existingParentUser = User::where('email', $application->parent_email)
                ->where('role_name', 'Parent')
                ->first();
            
            if ($existingParentUser) {
                // Parent account already exists (created during application submission)
                $parentUser = $existingParentUser;
                Log::info("✅ Parent account already exists: {$parentUser->email}");
            } else {
                // Parent account doesn't exist - create it now if parent email is different from student email
                if ($application->parent_email && $application->parent_email !== $application->email) {
                    $parentUser = User::create([
                        'name' => $application->parent_name,
                        'email' => $application->parent_email,
                        'password' => Hash::make('password123'),
                        'role_name' => 'Parent',
                        'status' => 'active',
                        'join_date' => now()->format('Y-m-d'),
                        'phone_number' => $application->parent_phone,
                        'position' => 'Parent/Guardian',
                        'department' => 'Parent Relations',
                        'avatar' => 'default-avatar.png',
                    ]);
                    Log::info("✅ Created parent account during approval: {$parentUser->email}");
                }
            }

            // Create user account for student
            $user = User::create([
                'name' => $application->full_name,
                'email' => $application->email,
                'password' => Hash::make('password123'), // Default password
                'role_name' => 'Student',
                'status' => 'active',
                'join_date' => now()->format('Y-m-d'),
                'phone_number' => $application->phone_number,
                'position' => 'Student',
                'department' => 'Student Affairs',
                'avatar' => 'default-avatar.png',
            ]);

            Log::info("✅ Created student user account: {$user->name} (ID: {$user->user_id})");

            // Create student record
            $student = Student::create([
                'user_id' => $user->user_id,
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'middle_name' => $application->middle_name,
                'gender' => $application->gender,
                'date_of_birth' => $application->date_of_birth,
                'email' => $application->email,
                'phone_number' => $application->phone_number,
                'address' => $application->address,
                'parent_email' => $application->parent_email,
                'parent_name' => $application->parent_name,
                'parent_phone' => $application->parent_phone,
                'parent_relationship' => $application->parent_relationship,
                'emergency_contact_name' => $application->emergency_contact_name,
                'emergency_contact_phone' => $application->emergency_contact_phone,
                'previous_school' => $application->previous_school,
                'enrollment_application_id' => $application->id,
                'enrollment_status' => 'active',
                'year_level' => $application->grade_level_applying_for,
            ]);

            Log::info("✅ Created student profile: {$student->first_name} {$student->last_name} (ID: {$student->id})");

            // Verify the user-student relationship
            $verifyStudent = User::find($user->user_id)->student;
            if (!$verifyStudent) {
                throw new \Exception("Student profile was not properly linked to user account");
            }

            // Auto-assign student to preferred section (from enrollment form) or first available
            try {
                if ($application->preferred_section_id) {
                    $assignedSection = $this->assignStudentToPreferredSection(
                        $student,
                        $application->preferred_section_id,
                        $application->grade_level_applying_for
                    );
                } else {
                    $assignedSection = $this->autoAssignStudentToSection($student, $application->grade_level_applying_for);
                }
                Log::info("✅ Assigned student to section: {$assignedSection->name}");
            } catch (\Exception $e) {
                Log::warning("⚠️  Section assignment failed: " . $e->getMessage());
                $assignedSection = null;
            }

            // Auto-enroll student in subjects for their grade level
            try {
                $enrolledSubjects = $this->autoEnrollStudentInSubjects($student, $application->grade_level_applying_for);
                $subjectCount = count($enrolledSubjects);
                Log::info("✅ Enrolled student in {$subjectCount} subjects: " . implode(', ', $enrolledSubjects));
            } catch (\Exception $e) {
                Log::error("❌ Subject enrollment failed: " . $e->getMessage());
                throw $e; // Re-throw to rollback transaction
            }

            // Approve the application
            $application->approve();

            if ($request->filled('notes')) {
                $application->update(['notes' => $request->notes]);
            }

            DB::commit();

            // Store credentials in session for display
            $credentials = [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'password' => 'password123',
                'student_name' => $application->full_name,
                'parent_user_id' => $parentUser ? $parentUser->user_id : null,
                'parent_email' => $parentUser ? $parentUser->email : null,
            ];
            
            session()->flash('student_credentials', $credentials);
            
            $sectionInfo = $assignedSection ? "assigned to section: {$assignedSection->name}, " : "";
            $successMessage = "✅ <strong>Application Approved Successfully!</strong><br><br>";
            $successMessage .= "📋 Student account created, {$sectionInfo}and enrolled in {$subjectCount} subjects.<br><br>";
            
            $successMessage .= "<div class='alert alert-success'>";
            $successMessage .= "<strong>👨‍🎓 STUDENT LOGIN CREDENTIALS:</strong><br>";
            $successMessage .= "📧 <strong>Email:</strong> {$user->email}<br>";
            $successMessage .= "🔑 <strong>Password:</strong> password123<br>";
            $successMessage .= "🆔 <strong>User ID:</strong> {$user->user_id}";
            $successMessage .= "</div>";
            
            if ($parentUser) {
                $successMessage .= "<div class='alert alert-info'>";
                $successMessage .= "<strong>👨‍👩‍👧 PARENT LOGIN CREDENTIALS:</strong><br>";
                $successMessage .= "📧 <strong>Email:</strong> {$parentUser->email}<br>";
                $successMessage .= "🔑 <strong>Password:</strong> password123<br>";
                $successMessage .= "🆔 <strong>User ID:</strong> {$parentUser->user_id}<br>";
                $successMessage .= "<small class='text-muted'>Parent can access the Parent Portal to monitor their child's progress.</small>";
                $successMessage .= "</div>";
            }
            
            $successMessage .= "<div class='alert alert-warning'>";
            $successMessage .= "<strong>⚠️ IMPORTANT:</strong> Save these credentials and share them with the student" . ($parentUser ? " and parent" : "") . "!";
            $successMessage .= "</div>";
            
            return back()->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("❌ Failed to approve application: " . $e->getMessage());
            return back()->with('error', 'Failed to approve application: ' . $e->getMessage());
        }
    }

    /**
     * Reject application
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $application = EnrollmentApplication::findOrFail($id);
        $application->reject($request->rejection_reason);

        return back()->with('success', 'Application rejected.');
    }

    /**
     * Mark application as needing documents
     */
    public function needsDocuments(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $application = EnrollmentApplication::findOrFail($id);
        $application->needsDocuments($request->notes);

        return back()->with('success', 'Application marked as needing additional documents.');
    }

    /**
     * Display archived enrollment applications
     */
    public function archive(Request $request)
    {
        $query = EnrollmentApplication::onlyTrashed()->with(['documents', 'reviewer']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('application_number', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'deleted_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $applications = $query->paginate(15);

        // Get status counts for archive categories
        $statusCounts = [
            'approved' => EnrollmentApplication::onlyTrashed()->where('status', 'approved')->count(),
            'rejected' => EnrollmentApplication::onlyTrashed()->where('status', 'rejected')->count(),
            'deleted' => EnrollmentApplication::onlyTrashed()->count(),
        ];

        return view('enrollment.registrar.archive', compact('applications', 'statusCounts'));
    }

    /**
     * Restore a deleted enrollment application
     */
    public function restore($id)
    {
        $application = EnrollmentApplication::onlyTrashed()->findOrFail($id);
        
        try {
            DB::beginTransaction();
            
            // Restore the application
            $application->restore();
            
            // Note: We don't restore associated student accounts and documents
            // as they were hard deleted. The registrar can recreate them if needed.
            
            DB::commit();
            
            return redirect()->route('enrollment.registrar.index')
                ->with('success', 'Enrollment application has been restored successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('enrollment.registrar.archive')
                ->with('error', 'Failed to restore enrollment application: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete an enrollment application
     */
    public function forceDelete($id)
    {
        $application = EnrollmentApplication::onlyTrashed()->findOrFail($id);
        
        try {
            DB::beginTransaction();
            
            // Permanently delete associated documents
            $application->documents()->forceDelete();
            
            // Permanently delete the application
            $application->forceDelete();
            
            DB::commit();
            
            return redirect()->route('enrollment.registrar.archive')
                ->with('success', 'Enrollment application has been permanently deleted.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('enrollment.registrar.archive')
                ->with('error', 'Failed to permanently delete enrollment application: ' . $e->getMessage());
        }
    }

    /**
     * Delete an enrollment application
     */
    public function destroy($id)
    {
        $application = EnrollmentApplication::findOrFail($id);
        
        try {
            DB::beginTransaction();
            
            // Delete associated documents
            $application->documents()->delete();
            
            // Delete associated student record if exists
            $student = Student::where('enrollment_application_id', $application->id)->first();
            if ($student) {
                // Delete the user account associated with this student
                $user = User::find($student->user_id);
                if ($user) {
                    $user->delete();
                }
                $student->delete();
            }
            
            // Soft delete the application
            $application->delete();
            
            DB::commit();
            
            return redirect()->route('enrollment.registrar.index')
                ->with('success', 'Enrollment application has been deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('enrollment.registrar.index')
                ->with('error', 'Failed to delete enrollment application: ' . $e->getMessage());
        }
    }

    /**
     * Verify a document
     */
    public function verifyDocument(Request $request, $id)
    {
        try {
            Log::info('Verify document called', ['document_id' => $id, 'request_data' => $request->all()]);
            
            $request->validate([
                'verification_notes' => 'nullable|string|max:500',
            ]);

            $document = EnrollmentDocument::findOrFail($id);
            Log::info('Document found', ['document' => $document->toArray()]);
            
            $document->verify(null, $request->verification_notes);
            Log::info('Document verified successfully');

            return back()->with('success', 'Document verified successfully.');
        } catch (\Exception $e) {
            Log::error('Error verifying document', ['error' => $e->getMessage(), 'document_id' => $id]);
            return back()->with('error', 'Failed to verify document: ' . $e->getMessage());
        }
    }

    /**
     * Reject a document
     */
    public function rejectDocument(Request $request, $id)
    {
        try {
            Log::info('Reject document called', ['document_id' => $id, 'request_data' => $request->all()]);
            
            $request->validate([
                'verification_notes' => 'required|string|max:500',
            ]);

            $document = EnrollmentDocument::findOrFail($id);
            Log::info('Document found', ['document' => $document->toArray()]);
            
            $document->reject($request->verification_notes);
            Log::info('Document rejected successfully');

            return back()->with('success', 'Document rejected.');
        } catch (\Exception $e) {
            Log::error('Error rejecting document', ['error' => $e->getMessage(), 'document_id' => $id]);
            return back()->with('error', 'Failed to reject document: ' . $e->getMessage());
        }
    }

    /**
     * Process approved application and create student account with automatic section assignment
     */
    public function processApplication($id)
    {
        $application = EnrollmentApplication::findOrFail($id);
        
        if ($application->status !== 'approved') {
            return back()->with('error', 'Only approved applications can be processed.');
        }

        // Check if student already exists
        $existingStudent = Student::where('email', $application->email)->first();
        if ($existingStudent) {
            return back()->with('error', 'A student with this email already exists.');
        }

        try {
            DB::beginTransaction();

            // Create user account
            $user = User::create([
                'name' => $application->full_name,
                'email' => $application->email,
                'password' => Hash::make('password123'), // Default password
                'role_name' => 'Student',
                'status' => 'active',
                'join_date' => now()->format('Y-m-d'),
                'phone_number' => $application->phone_number,
                'position' => 'Student',
                'department' => 'Student Affairs',
                'avatar' => 'default-avatar.png',
            ]);

            // Create student record
            $student = Student::create([
                'user_id' => $user->user_id,
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'middle_name' => $application->middle_name,
                'gender' => $application->gender,
                'date_of_birth' => $application->date_of_birth,
                'email' => $application->email,
                'phone_number' => $application->phone_number,
                'address' => $application->address,
                'parent_email' => $application->parent_email,
                'parent_name' => $application->parent_name,
                'parent_phone' => $application->parent_phone,
                'parent_relationship' => $application->parent_relationship,
                'emergency_contact_name' => $application->emergency_contact_name,
                'emergency_contact_phone' => $application->emergency_contact_phone,
                'previous_school' => $application->previous_school,
                'enrollment_application_id' => $application->id,
                'enrollment_status' => 'active',
                'year_level' => $application->grade_level_applying_for,
            ]);

            // Auto-assign student to preferred section (from enrollment form) or first available
            if ($application->preferred_section_id) {
                $assignedSection = $this->assignStudentToPreferredSection(
                    $student,
                    $application->preferred_section_id,
                    $application->grade_level_applying_for
                );
            } else {
                $assignedSection = $this->autoAssignStudentToSection($student, $application->grade_level_applying_for);
            }

            // Auto-enroll student in subjects for their grade level
            $this->autoEnrollStudentInSubjects($student, $application->grade_level_applying_for);

            DB::commit();

            return back()->with('success', "Student account created, assigned to section: {$assignedSection->name}, and enrolled in subjects. User ID: {$user->user_id}");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to create student account: ' . $e->getMessage());
        }
    }

    /**
     * Assign student to the section they chose on the enrollment form.
     */
    private function assignStudentToPreferredSection($student, $sectionId, $gradeLevel)
    {
        $academicYear = \App\Models\AcademicYear::latest()->first();
        $semester = \App\Models\Semester::latest()->first();

        if (!$academicYear || !$semester) {
            throw new \Exception('No academic year or semester found. Please set up academic periods first.');
        }

        $section = \App\Models\Section::find($sectionId);
        if (!$section) {
            return $this->autoAssignStudentToSection($student, $gradeLevel);
        }

        $aliases = \App\Services\GradeSubjectCatalogService::gradeAliases($gradeLevel);
        if (!in_array($section->grade_level, $aliases, true)) {
            return $this->autoAssignStudentToSection($student, $gradeLevel);
        }

        $currentCount = DB::table('student_section_assignments')
            ->where('section_id', $section->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->count();

        if ($currentCount >= ($section->capacity ?? 25)) {
            return $this->autoAssignStudentToSection($student, $gradeLevel);
        }

        $existingAssignment = DB::table('student_section_assignments')
            ->where([
                'student_id' => $student->id,
                'section_id' => $section->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ])->first();

        if (!$existingAssignment) {
            DB::table('student_section_assignments')->insert([
                'student_id' => $student->id,
                'section_id' => $section->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'assigned_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $section;
    }

    /**
     * Automatically assign student to appropriate section based on grade level
     */
    private function autoAssignStudentToSection($student, $gradeLevel)
    {
        // Get the latest academic year and semester (since is_active column doesn't exist)
        $academicYear = \App\Models\AcademicYear::latest()->first();
        $semester = \App\Models\Semester::latest()->first();

        if (!$academicYear || !$semester) {
            throw new \Exception('No academic year or semester found. Please set up academic periods first.');
        }

        // Find sections for the student's grade level (canonical + aliases)
        $sections = app(\App\Services\GradeSubjectCatalogService::class)->sectionsForGrade($gradeLevel);

        if ($sections->isEmpty()) {
            throw new \Exception("No sections found for grade level: {$gradeLevel}. Please create sections first.");
        }

        // Find section with available capacity
        $assignedSection = null;
        foreach ($sections as $section) {
            $currentCount = DB::table('student_section_assignments')
                ->where('section_id', $section->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('semester_id', $semester->id)
                ->count();

            $capacity = $section->capacity ?? 25; // Default capacity if not set

            if ($currentCount < $capacity) {
                $assignedSection = $section;
                break;
            }
        }

        if (!$assignedSection) {
            // If no section has capacity, assign to the first available section
            $assignedSection = $sections->first();
        }

        // Check if student is already assigned to this section
        $existingAssignment = DB::table('student_section_assignments')
            ->where([
                'student_id' => $student->id,
                'section_id' => $assignedSection->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ])->first();

        if (!$existingAssignment) {
            // Create section assignment
            DB::table('student_section_assignments')->insert([
                'student_id' => $student->id,
                'section_id' => $assignedSection->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'assigned_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $assignedSection;
    }

    /**
     * Automatically enroll student in subjects for their grade level
     * Source of truth: admin-managed subjects table (subjects.class = grade label)
     */
    private function autoEnrollStudentInSubjects($student, $gradeLevel)
    {
        $academicYear = \App\Models\AcademicYear::latest()->first();
        $semester = \App\Models\Semester::latest()->first();

        if (!$academicYear || !$semester) {
            throw new \Exception('No academic year or semester found. Please set up academic periods first.');
        }

        $subjects = app(\App\Services\GradeSubjectCatalogService::class)->subjectsForGrade($gradeLevel);

        if ($subjects->isEmpty()) {
            throw new \Exception(
                "No subjects found for grade level: {$gradeLevel}. " .
                'Please add subjects under Academic Management → Classes & Subjects first.'
            );
        }

        $enrolledSubjects = [];

        foreach ($subjects as $subject) {
            $existingEnrollment = \App\Models\Enrollment::where([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ])->first();

            if (!$existingEnrollment) {
                \App\Models\Enrollment::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);

                $enrolledSubjects[] = $subject->subject_name;
            }
        }

        return $enrolledSubjects;
    }

    /**
     * Get enrollment statistics
     */
    public function statistics()
    {
        $stats = [
            'total_applications' => EnrollmentApplication::count(),
            'pending' => EnrollmentApplication::where('status', 'pending')->count(),
            'under_review' => EnrollmentApplication::where('status', 'under_review')->count(),
            'approved' => EnrollmentApplication::where('status', 'approved')->count(),
            'rejected' => EnrollmentApplication::where('status', 'rejected')->count(),
            'needs_documents' => EnrollmentApplication::where('status', 'needs_documents')->count(),
        ];

        return view('enrollment.registrar.statistics', compact('stats'));
    }

    /**
     * Manually create student account (works even if documents are incomplete)
     */
    public function createManualAccount(Request $request, $id)
    {
        $application = EnrollmentApplication::findOrFail($id);
        
        // Prevent creating accounts for rejected applications
        if ($application->status === 'rejected') {
            return back()->with('error', 'Cannot create account for rejected applications.');
        }

        // Validate form inputs
        $request->validate([
            'username' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'student_id_number' => 'nullable|string|unique:students,student_id',
        ]);

        // Check if student already exists
        $existingStudent = Student::where('email', $request->username)->first();
        if ($existingStudent) {
            return back()->with('warning', 'A student account with this email already exists! User ID: ' . $existingStudent->user_id);
        }

        try {
            DB::beginTransaction();

            Log::info("🔧 Manual student account creation started for: {$application->full_name}");
            Log::info("Username: {$request->username}, Password: " . ($request->password ? 'provided' : 'not provided'));

            // Create user account with custom credentials
            $user = User::create([
                'name' => $application->full_name,
                'email' => $request->username, // Use custom username from form
                'password' => Hash::make($request->password), // Use custom password from form
                'role_name' => 'Student',
                'status' => 'active',
                'join_date' => now()->format('Y-m-d'),
                'phone_number' => $application->phone_number,
                'position' => 'Student',
                'department' => 'Student Affairs',
                'avatar' => 'default-avatar.png',
            ]);

            Log::info("✅ Created user account: {$user->name} (ID: {$user->user_id})");

            // Create student record with optional custom student ID
            $studentData = [
                'user_id' => $user->user_id,
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'middle_name' => $application->middle_name,
                'gender' => $application->gender,
                'date_of_birth' => $application->date_of_birth,
                'email' => $application->email,
                'phone_number' => $application->phone_number,
                'address' => $application->address,
                'parent_email' => $application->parent_email,
                'parent_name' => $application->parent_name,
                'parent_phone' => $application->parent_phone,
                'parent_relationship' => $application->parent_relationship,
                'emergency_contact_name' => $application->emergency_contact_name,
                'emergency_contact_phone' => $application->emergency_contact_phone,
                'previous_school' => $application->previous_school,
                'enrollment_application_id' => $application->id,
                'enrollment_status' => 'active',
                'year_level' => $application->grade_level_applying_for,
            ];

            // Add custom student ID if provided
            if ($request->filled('student_id_number')) {
                $studentData['student_id'] = $request->student_id_number;
                Log::info("Using custom student ID: {$request->student_id_number}");
            }

            $student = Student::create($studentData);

            Log::info("✅ Created student profile: {$student->first_name} {$student->last_name} (ID: {$student->id})");

            // Verify the user-student relationship
            $verifyStudent = User::find($user->user_id)->student;
            if (!$verifyStudent) {
                throw new \Exception("Student profile was not properly linked to user account");
            }

            // Auto-assign student to preferred section (from enrollment form) or first available
            try {
                if ($application->preferred_section_id) {
                    $assignedSection = $this->assignStudentToPreferredSection(
                        $student,
                        $application->preferred_section_id,
                        $application->grade_level_applying_for
                    );
                } else {
                    $assignedSection = $this->autoAssignStudentToSection($student, $application->grade_level_applying_for);
                }
                Log::info("✅ Assigned student to section: {$assignedSection->name}");
            } catch (\Exception $e) {
                Log::warning("⚠️ Section assignment failed: " . $e->getMessage());
                $assignedSection = null;
            }

            // Auto-enroll student in subjects for their grade level
            try {
                $enrolledSubjects = $this->autoEnrollStudentInSubjects($student, $application->grade_level_applying_for);
                $subjectCount = count($enrolledSubjects);
                Log::info("✅ Enrolled student in {$subjectCount} subjects: " . implode(', ', $enrolledSubjects));
            } catch (\Exception $e) {
                Log::error("❌ Subject enrollment failed: " . $e->getMessage());
                throw $e; // Re-throw to rollback transaction
            }

            // Automatically mark application as approved if it wasn't already
            if ($application->status !== 'approved') {
                $application->approve();
                Log::info("✅ Application automatically approved during manual account creation");
            }

            DB::commit();

            $sectionInfo = $assignedSection ? "assigned to section: {$assignedSection->name}, " : "";
            $documentStatus = $application->documents->count() > 0 
                ? "with " . $application->documents->count() . " documents uploaded" 
                : "without documents (can be uploaded later)";
            $studentIdInfo = $request->filled('student_id_number') ? ", Student ID: {$request->student_id_number}" : "";
            
            return back()->with('success', "✅ Student account created successfully ({$documentStatus})! <br><strong>Login Details:</strong> Username: {$request->username} | Password: {$request->password}<br>User ID: {$user->user_id}{$studentIdInfo}, {$sectionInfo}enrolled in {$subjectCount} subjects for {$application->grade_level_applying_for}.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("❌ Failed to manually create student account: " . $e->getMessage());
            return back()->with('error', 'Failed to create student account: ' . $e->getMessage());
        }
    }
}
