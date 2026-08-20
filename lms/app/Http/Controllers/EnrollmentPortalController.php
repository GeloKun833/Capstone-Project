<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\EnrollmentApplication;
use App\Models\EnrollmentDocument;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EnrollmentPortalController extends Controller
{
    /**
     * Show the enrollment portal landing page
     */
    public function index()
    {
        return view('enrollment.portal.index');
    }

    /**
     * Show the application form
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'new'); // new, transferee, or old_student
        return view('enrollment.portal.create', compact('type'));
    }

    /**
     * Store the enrollment application
     */
    public function store(Request $request)
    {
        // Get student category
        $studentCategory = $request->input('student_category', 'new_student');
        
        // Base validation rules
        $rules = [
            'student_category' => 'required|in:new_student,old_student,transferee',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female',
            'email' => 'nullable|email|unique:enrollment_applications,email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'address_city_municipality' => 'required|string|max:255',
            'address_lot_block_village' => 'nullable|string|max:255',
            'address_barangay_district' => 'nullable|string|max:255',
            'age_years' => 'nullable|integer|min:0',
            'age_months' => 'nullable|integer|min:0|max:11',
            'date_enrolled' => 'nullable|date',
            'lrn' => 'nullable|string|max:255',
            'esc_no' => 'nullable|string|max:255',
            'covid_vaccinated' => 'required|in:Yes,No',
            'covid_first_shot_date' => 'nullable|date|required_if:covid_vaccinated,Yes',
            'covid_full_vaccination_date' => 'nullable|date',
            'religion' => 'nullable|string|max:255',
            'citizenship' => 'nullable|string|max:255',
            'birthplace' => 'nullable|string|max:255',
            'psa_birth_cert_no' => 'nullable|string|max:255',
            'previous_school' => 'nullable|string|max:255',
            'previous_school_id' => 'nullable|string|max:255',
            'previous_school_location' => 'nullable|string|max:255',
            'previous_school_type' => 'nullable|in:Public,Private',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email',
            'parent_relationship' => 'nullable|string|max:255',
            'father_last_name' => 'nullable|string|max:255',
            'father_first_name' => 'nullable|string|max:255',
            'father_middle_name' => 'nullable|string|max:255',
            'father_education' => 'nullable|string|max:255',
            'father_employment' => 'nullable|in:Full time,Part time,Self-employed,Unemployed',
            'father_company_name' => 'nullable|string|max:255',
            'father_work_address' => 'nullable|string|max:500',
            'father_contact_no' => 'nullable|string|max:20',
            'father_email' => 'nullable|email',
            'mother_last_name' => 'nullable|string|max:255',
            'mother_first_name' => 'nullable|string|max:255',
            'mother_middle_name' => 'nullable|string|max:255',
            'mother_education' => 'nullable|string|max:255',
            'mother_employment' => 'nullable|in:Full time,Part time,Self-employed,Unemployed',
            'mother_company_name' => 'nullable|string|max:255',
            'mother_work_address' => 'nullable|string|max:500',
            'mother_contact_no' => 'nullable|string|max:20',
            'mother_email' => 'nullable|email',
            'family_income_bracket' => ['nullable', Rule::in(['Below 10,000.00', '10,001-30,000', 'Above 30,000.00'])],
            'no_of_siblings' => 'nullable|integer|min:0',
            'no_of_siblings_studying' => 'nullable|integer|min:0',
            'siblings_schools' => 'nullable|string|max:500',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_relation' => 'nullable|string|max:255',
            'guardian_contact_no' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email',
            'authorized_fetcher' => 'nullable|string|max:255',
            'authorized_fetcher_relation' => 'nullable|string|max:255',
            'parent_signature_name' => 'required|string|max:255',
            'date_of_first_attendance' => 'nullable|date',
            'doc_submitted_form138' => 'nullable|boolean',
            'doc_submitted_psa_birth' => 'nullable|boolean',
            'doc_submitted_form137' => 'nullable|boolean',
            'doc_submitted_baptismal' => 'nullable|boolean',
            'doc_submitted_pic_1x1' => 'nullable|boolean',
            'doc_submitted_pic_2x2' => 'nullable|boolean',
            'doc_submitted_itr' => 'nullable|boolean',
            'doc_submitted_unemployment' => 'nullable|boolean',
            'grade_level_applying_for' => 'required|string|max:255',
            'agree_terms' => 'required|accepted',
            'existing_student_id' => 'nullable|exists:students,id',
            // Document validation (varies by category)
            'birth_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'sf10' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'good_moral' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'parent_guardian_id' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
        
        // SF9 validation based on category
        if ($studentCategory === 'transferee') {
            // SF9 is required for transferees
            $rules['sf9'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
        } else if ($studentCategory === 'old_student') {
            // SF9 is not required for old students (disabled in form)
            $rules['sf9'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        } else if ($studentCategory === 'new_student') {
            // SF9 is NOT required and should not be in the form for new students
            $rules['sf9'] = 'nullable'; // Allow null but don't validate if present
        } else {
            // Default: SF9 is optional (for backward compatibility)
            $rules['sf9'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }
        
        $validator = Validator::make($request->all(), $rules, [
            'agree_terms.required' => 'You must agree to the Terms and Conditions.',
            'agree_terms.accepted' => 'You must agree to the Terms and Conditions.',
            'sf9.required' => 'SF9 (Learner\'s Permanent Record) is required for transferee students.',
        ]);

        // Note: Documents are now OPTIONAL - users can submit applications without documents
        // The registrar can still create student accounts even if documents are incomplete

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Prepare application data with all fields
            $applicationData = $request->only([
                'student_category', 'existing_student_id', 
                'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
                'email', 'phone_number', 'address', 
                'address_lot_block_village', 'address_barangay_district', 'address_city_municipality',
                'age_years', 'age_months',
                'date_enrolled', 'lrn', 'esc_no',
                'covid_vaccinated', 'covid_first_shot_date', 'covid_full_vaccination_date',
                'religion', 'citizenship', 'birthplace',
                'previous_school', 'previous_school_id', 'previous_school_location', 'previous_school_type',
                'psa_birth_cert_no',
                'parent_name', 'parent_phone', 'parent_email', 'parent_relationship',
                'father_last_name', 'father_first_name', 'father_middle_name',
                'father_education', 'father_employment', 'father_company_name',
                'father_work_address', 'father_contact_no', 'father_email',
                'mother_last_name', 'mother_first_name', 'mother_middle_name',
                'mother_education', 'mother_employment', 'mother_company_name',
                'mother_work_address', 'mother_contact_no', 'mother_email',
                'family_income_bracket', 'no_of_siblings', 'no_of_siblings_studying', 'siblings_schools',
                'emergency_contact_name', 'emergency_contact_phone',
                'guardian_name', 'guardian_relation', 'guardian_contact_no', 'guardian_email',
                'authorized_fetcher', 'authorized_fetcher_relation',
                'parent_signature_name', 'date_of_first_attendance',
                'doc_submitted_form138', 'doc_submitted_psa_birth', 'doc_submitted_form137',
                'doc_submitted_baptismal', 'doc_submitted_pic_1x1', 'doc_submitted_pic_2x2',
                'doc_submitted_itr', 'doc_submitted_unemployment',
                'grade_level_applying_for'
            ]);
            
            // Auto-fill parent fields if not provided but father/mother fields are
            if (empty($applicationData['parent_name'])) {
                if (!empty($applicationData['father_first_name']) || !empty($applicationData['father_last_name'])) {
                    $applicationData['parent_name'] = trim(($applicationData['father_last_name'] ?? '') . ', ' . ($applicationData['father_first_name'] ?? ''));
                    $applicationData['parent_email'] = $applicationData['parent_email'] ?? $applicationData['father_email'] ?? '';
                    $applicationData['parent_phone'] = $applicationData['parent_phone'] ?? $applicationData['father_contact_no'] ?? '';
                    $applicationData['parent_relationship'] = $applicationData['parent_relationship'] ?? 'Father';
                } elseif (!empty($applicationData['mother_first_name']) || !empty($applicationData['mother_last_name'])) {
                    $applicationData['parent_name'] = trim(($applicationData['mother_last_name'] ?? '') . ', ' . ($applicationData['mother_first_name'] ?? ''));
                    $applicationData['parent_email'] = $applicationData['parent_email'] ?? $applicationData['mother_email'] ?? '';
                    $applicationData['parent_phone'] = $applicationData['parent_phone'] ?? $applicationData['mother_contact_no'] ?? '';
                    $applicationData['parent_relationship'] = $applicationData['parent_relationship'] ?? 'Mother';
                }
            }
            
            // Convert checkbox values to boolean
            $documentCheckboxes = [
                'doc_submitted_form138',
                'doc_submitted_psa_birth',
                'doc_submitted_form137',
                'doc_submitted_baptismal',
                'doc_submitted_pic_1x1',
                'doc_submitted_pic_2x2',
                'doc_submitted_itr',
                'doc_submitted_unemployment',
            ];
            
            foreach ($documentCheckboxes as $checkbox) {
                $applicationData[$checkbox] = isset($applicationData[$checkbox]) && $applicationData[$checkbox] == '1' ? true : false;
            }
            
            // Create the enrollment application
            $application = EnrollmentApplication::create($applicationData);

            // Process required documents
            $requiredDocuments = [
                'birth_certificate',
                'sf9',
                'sf10', 
                'good_moral',
                'id_photo',
                'parent_guardian_id'
            ];
            
            $uploadedDocuments = [];
            $missingDocuments = [];
            
            foreach ($requiredDocuments as $documentType) {
                if ($request->hasFile($documentType)) {
                    $document = $request->file($documentType);
                    
                    if ($document && $document->isValid()) {
                        $originalName = $document->getClientOriginalName();
                        $fileName = time() . '_' . Str::random(10) . '.' . $document->getClientOriginalExtension();
                        $filePath = $document->storeAs('enrollment_documents/' . $application->id, $fileName, 'public');

                        EnrollmentDocument::create([
                            'enrollment_application_id' => $application->id,
                            'document_type' => $documentType,
                            'file_name' => $originalName,
                            'file_path' => $filePath,
                            'file_size' => $document->getSize(),
                            'mime_type' => $document->getMimeType(),
                            'status' => 'pending',
                        ]);
                        
                        $uploadedDocuments[] = $documentType;
                    }
                } else {
                    $missingDocuments[] = $documentType;
                }
            }
            
            // Update application status based on document completeness
            if (empty($missingDocuments)) {
                $application->status = 'under_review';
                $application->save();
            } else {
                $application->status = 'needs_documents';
                $application->save();
            }

            // Automatically create student account
            $accountDetails = $this->createStudentAccount($application, $request->input('selected_section_id'));

            DB::commit();

            // Prepare user-friendly success message
            $successMessage = '🎉 Welcome to Panorama Montessori School! Your enrollment application has been submitted successfully.';
            
            // Add section assignment info
            $selectedSectionId = $request->input('selected_section_id');
            if ($selectedSectionId && isset($accountDetails['assigned_section']) && $accountDetails['assigned_section'] !== 'To be assigned after approval') {
                $successMessage .= "\n\n🎯 Section Assignment:";
                $successMessage .= "\n✅ You have been automatically assigned to: " . $accountDetails['assigned_section'];
            }
            
            // Add account creation info
            $createParentAccount = $request->input('create_parent_account', '1') === '1';
            
            if ($createParentAccount) {
                $successMessage .= "\n\n✅ Accounts Created:";
                $successMessage .= "\n\n👨‍🎓 Student Account:";
                $successMessage .= "\n📧 Email: " . $application->email;
                $successMessage .= "\n🔑 Password: password123";
                $successMessage .= "\n\n👨‍👩‍👧 Parent Account:";
                $successMessage .= "\n📧 Email: " . $application->parent_email;
                $successMessage .= "\n🔑 Password: password123";
            } else {
                $successMessage .= "\n\n✅ Student account has been created! You can now login using:";
                $successMessage .= "\n📧 Email: " . $application->email;
                $successMessage .= "\n🔑 Password: password123";
                $successMessage .= "\n\n💡 Note: Parent account was not created as per your selection.";
            }
            
            // Document status
            if (empty($uploadedDocuments)) {
                $successMessage .= "\n\n📄 Documents: You can upload your documents anytime from your student dashboard.";
            } elseif (!empty($missingDocuments)) {
                $missingCount = count($missingDocuments);
                $uploadedCount = count($uploadedDocuments);
                $successMessage .= "\n\n📄 Documents: You've uploaded {$uploadedCount} of 6 documents. The remaining {$missingCount} can be uploaded later.";
            } else {
                $successMessage .= "\n\n📄 Documents: All 6 documents uploaded successfully! ✓";
            }
            
            // Next steps
            $successMessage .= "\n\n⏳ Next Steps:";
            $successMessage .= "\n1. Wait for the registrar to review and approve your application";
            $successMessage .= "\n2. You can login now to view your application status";
            $successMessage .= "\n3. Once approved, you'll have full access to the student portal";
            
            $successMessage .= "\n\n💡 Tip: Save your login credentials in a safe place!";
            
            return redirect()->route('enrollment.portal.success', $application->id)
                ->with('account_details', $accountDetails)
                ->with('missing_documents', $missingDocuments)
                ->with('uploaded_documents', $uploadedDocuments)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to submit application: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the application details
     */
    public function show($id)
    {
        $application = EnrollmentApplication::with(['documents', 'reviewer'])->findOrFail($id);
        return view('enrollment.portal.show', compact('application'));
    }

    /**
     * Show application status by application number
     */
    public function status()
    {
        return view('enrollment.portal.status');
    }

    /**
     * Check application status
     */
    public function checkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_number' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $application = EnrollmentApplication::with(['documents', 'reviewer'])
            ->where('application_number', $request->application_number)
            ->where('email', $request->email)
            ->first();

        if (!$application) {
            return redirect()->back()
                ->with('error', 'Application not found. Please check your application number and email.');
        }

        return view('enrollment.portal.show', compact('application'));
    }

    /**
     * Download a document
     */
    public function downloadDocument($id)
    {
        $document = EnrollmentDocument::findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Approve application and create student account with automatic section assignment
     */
    public function approveApplication(Request $request, $id)
    {
        $application = EnrollmentApplication::findOrFail($id);
        
        if ($application->status !== 'approved') {
            return back()->with('error', 'Only approved applications can be processed.');
        }

        try {
            DB::beginTransaction();

            // Create user account
            $user = User::create([
                'name' => $application->full_name,
                'email' => $application->email,
                'password' => Hash::make('password123'), // Default password, should be changed
                'role_name' => 'Student',
                'status' => 'active',
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

            // Auto-assign student to section based on grade level
            $this->autoAssignStudentToSection($student, $application->grade_level_applying_for);

            // Auto-enroll student in subjects for their grade level
            $this->autoEnrollStudentInSubjects($student, $application->grade_level_applying_for);

            DB::commit();

            return back()->with('success', "Student account created, assigned to section, and enrolled in subjects. User ID: {$user->user_id}");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to create student account: ' . $e->getMessage());
        }
    }

    /**
     * Assign student to their selected section
     */
    private function assignStudentToSelectedSection($student, $sectionId, $gradeLevel)
    {
        // Get the latest academic year and semester
        $academicYear = \App\Models\AcademicYear::latest()->first();
        $semester = \App\Models\Semester::latest()->first();

        if (!$academicYear || !$semester) {
            throw new \Exception('No academic year or semester found. Please set up academic periods first.');
        }

        // Get the selected section
        $section = \App\Models\Section::find($sectionId);
        
        if (!$section) {
            throw new \Exception("Selected section not found.");
        }

        // Verify section is for the correct grade level
        if ($section->grade_level !== $gradeLevel) {
            throw new \Exception("Selected section does not match the student's grade level.");
        }

        // Check if section is full
        $currentCount = DB::table('student_section_assignments')
            ->where('section_id', $section->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->count();

        $capacity = $section->capacity ?? 25;

        if ($currentCount >= $capacity) {
            // Section is full, auto-assign to another section
            Log::warning("Selected section {$section->name} is full, auto-assigning to another section");
            return $this->autoAssignStudentToSection($student, $gradeLevel);
        }

        // Check if student is already assigned to this section
        $existingAssignment = DB::table('student_section_assignments')
            ->where([
                'student_id' => $student->id,
                'section_id' => $section->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ])->first();

        if (!$existingAssignment) {
            // Create section assignment
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

        // Find sections for the student's grade level
        $sections = \App\Models\Section::where('grade_level', $gradeLevel)->get();

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
     */
    private function autoEnrollStudentInSubjects($student, $gradeLevel)
    {
        // Get the latest academic year and semester (since is_active column doesn't exist)
        $academicYear = \App\Models\AcademicYear::latest()->first();
        $semester = \App\Models\Semester::latest()->first();

        if (!$academicYear || !$semester) {
            throw new \Exception('No academic year or semester found. Please set up academic periods first.');
        }

        // Get subjects for the grade level from config
        $gradeSubjects = config('grade_subjects.' . $gradeLevel, []);
        
        if (empty($gradeSubjects)) {
            throw new \Exception("No subjects configured for grade level: {$gradeLevel}");
        }

        $enrolledSubjects = [];

        foreach ($gradeSubjects as $subjectName) {
            // Find or create subject
            $subject = \App\Models\Subject::where('subject_name', $subjectName)
                ->where('class', $gradeLevel)
                ->first();

            if (!$subject) {
                // Create subject if it doesn't exist
                $subject = \App\Models\Subject::create([
                    'subject_name' => $subjectName,
                    'class' => $gradeLevel,
                ]);
            }

            // Check if student is already enrolled in this subject
            $existingEnrollment = \App\Models\Enrollment::where([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ])->first();

            if (!$existingEnrollment) {
                // Create enrollment record
                \App\Models\Enrollment::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);

                $enrolledSubjects[] = $subjectName;
            }
        }

        return $enrolledSubjects;
    }

    /**
     * Create student account immediately after enrollment application
     */
    private function createStudentAccount($application, $selectedSectionId = null)
    {
        // Use standard password for all students
        $tempPassword = 'password123'; // Standard password - easy to remember
        
        // Check if we should create parent account (from form checkbox or default behavior)
        // Default to '1' (checked) for backward compatibility
        $shouldCreateParent = request()->input('create_parent_account', '1') === '1';
        
        if (!$shouldCreateParent) {
            // Parent account NOT requested - Create ONLY student account
            $user = User::create([
                'name' => $application->full_name,
                'email' => $application->email, // Use exact email from application
                'password' => Hash::make($tempPassword),
                'role_name' => 'Student',
                'status' => 'active',
                'join_date' => now()->format('Y-m-d'),
                'phone_number' => $application->phone_number,
                'position' => 'Student',
                'department' => 'Student Affairs',
                'avatar' => 'default-avatar.png',
            ]);
            
            Log::info("✅ Created student account (self-enrollment): {$user->email}");
            
            // No parent account created for student enrollment
            $parentUser = null;
            
        } else {
            // Parent is enrolling their child - Create BOTH parent and student accounts
            
            // Create parent account with parent email
            $parentUser = User::create([
                'name' => $application->parent_name,
                'email' => $application->parent_email,
                'password' => Hash::make($tempPassword),
                'role_name' => 'Parent',
                'status' => 'active',
                'join_date' => now()->format('Y-m-d'),
                'phone_number' => $application->parent_phone,
                'position' => 'Parent/Guardian',
                'department' => 'Parent Relations',
                'avatar' => 'default-avatar.png',
            ]);
            
            Log::info("✅ Created parent account: {$parentUser->email}");
            
            // Create student account with student email (from application form)
            $user = User::create([
                'name' => $application->full_name,
                'email' => $application->email, // Use exact email from application (student's email)
                'password' => Hash::make($tempPassword), // Same password as parent
                'role_name' => 'Student',
                'status' => 'active', // Active so student can login
                'join_date' => now()->format('Y-m-d'),
                'phone_number' => $application->phone_number,
                'position' => 'Student',
                'department' => 'Student Affairs',
                'avatar' => 'default-avatar.png',
            ]);
            
            Log::info("✅ Created student account: {$user->email}");
        }

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
            'enrollment_status' => 'pending', // Will be active after approval
            'year_level' => $application->grade_level_applying_for,
        ]);

        // Assign student to selected section or auto-assign
        try {
            if ($selectedSectionId) {
                // User selected a specific section - assign to that section
                $assignedSection = $this->assignStudentToSelectedSection($student, $selectedSectionId, $application->grade_level_applying_for);
                Log::info("✅ Assigned student to selected section: {$assignedSection->name}");
            } else {
                // Auto-assign to available section
                $assignedSection = $this->autoAssignStudentToSection($student, $application->grade_level_applying_for);
                Log::info("✅ Auto-assigned student to section: {$assignedSection->name}");
            }
        } catch (\Exception $e) {
            // If section assignment fails, continue without error (will be assigned later)
            Log::warning("⚠️ Section assignment failed: " . $e->getMessage());
            $assignedSection = null;
        }

        // Auto-enroll student in subjects for their grade level
        try {
            $this->autoEnrollStudentInSubjects($student, $application->grade_level_applying_for);
        } catch (\Exception $e) {
            // If subject enrollment fails, continue without error (will be enrolled later)
        }

        return [
            'user_id' => $user->user_id,
            'email' => $user->email,
            'password' => $tempPassword,
            'student_name' => $application->full_name,
            'grade_level' => $application->grade_level_applying_for,
            'assigned_section' => $assignedSection ? $assignedSection->name : 'To be assigned after approval',
            'application_number' => $application->application_number,
            'parent_account' => $parentUser ? [
                'user_id' => $parentUser->user_id,
                'email' => $parentUser->email,
                'name' => $parentUser->name,
                'password' => $tempPassword, // Will be included if new account was created
            ] : null,
        ];
    }

    /**
     * Show success page with account details
     */
    public function success($id)
    {
        $application = EnrollmentApplication::findOrFail($id);
        $accountDetails = session('account_details');
        
        if (!$accountDetails) {
            return redirect()->route('enrollment.portal.show', $id);
        }

        return view('enrollment.portal.success', compact('application', 'accountDetails'));
    }

    /**
     * Show old student login page
     */
    public function oldStudentLogin()
    {
        return view('enrollment.portal.old-student.login');
    }

    /**
     * Authenticate old student and redirect to dashboard
     */
    public function oldStudentAuthenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            // Try to authenticate the student
            $user = User::where('email', $request->email)
                ->where('role_name', 'Student')
                ->first();

            if (!$user) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Student account not found with this email address.');
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Invalid password. Please try again.');
            }

            // Get student profile
            $student = Student::where('user_id', $user->user_id)->first();

            if (!$student) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Student profile not found. Please contact the registrar.');
            }

            // Store student info in session (not full auth, just for enrollment process)
            session([
                'old_student_enrollment' => [
                    'user_id' => $user->user_id,
                    'student_id' => $student->id,
                    'email' => $user->email,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'grade_level' => $student->year_level,
                ]
            ]);

            return redirect()->route('enrollment.old-student.dashboard');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Show old student dashboard with subjects and sections
     */
    public function oldStudentDashboard()
    {
        // Check if student is in session
        if (!session('old_student_enrollment')) {
            return redirect()->route('enrollment.old-student.login')
                ->with('error', 'Please login first.');
        }

        $enrollmentData = session('old_student_enrollment');
        $student = Student::findOrFail($enrollmentData['student_id']);
        $gradeLevel = $student->year_level;

        // Get subjects for the student's grade level
        $subjects = \App\Models\Subject::where('class', $gradeLevel)->get();
        
        // Get class schedules for these subjects (to show schedule info)
        $schedules = \App\Models\ClassSchedule::with(['subject', 'teacher', 'room', 'section'])
            ->whereHas('subject', function($query) use ($gradeLevel) {
                $query->where('class', $gradeLevel);
            })
            ->where('is_active', true)
            ->get()
            ->groupBy('section_id');

        // Get available sections for the student's grade level (block sections)
        $availableSections = \App\Models\Section::where('grade_level', $gradeLevel)
            ->with('adviser')
            ->orderBy('name')
            ->get();

        // Get the latest academic year and semester
        $academicYear = \App\Models\AcademicYear::latest()->first();
        $semester = \App\Models\Semester::latest()->first();

        // Check which sections have available capacity
        foreach ($availableSections as $section) {
            $currentCount = DB::table('student_section_assignments')
                ->where('section_id', $section->id)
                ->where('academic_year_id', $academicYear ? $academicYear->id : null)
                ->where('semester_id', $semester ? $semester->id : null)
                ->count();

            $capacity = $section->capacity ?? 25;
            $section->available_spots = max(0, $capacity - $currentCount);
            $section->is_full = $section->available_spots === 0;
        }

        return view('enrollment.portal.old-student.dashboard', compact('student', 'subjects', 'schedules', 'availableSections', 'academicYear', 'semester'));
    }

    /**
     * Handle old student section selection
     */
    public function oldStudentSelectSection(Request $request)
    {
        // Check if student is in session
        if (!session('old_student_enrollment')) {
            return redirect()->route('enrollment.old-student.login')
                ->with('error', 'Please login first.');
        }

        $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $enrollmentData = session('old_student_enrollment');
        $sectionId = $request->section_id;

        // Store selected section in session
        session([
            'old_student_enrollment' => array_merge($enrollmentData, ['selected_section_id' => $sectionId])
        ]);

        return redirect()->route('enrollment.old-student.generate-form', $sectionId);
    }

    /**
     * Generate enrollment form for old student after section selection
     */
    public function oldStudentGenerateForm($sectionId)
    {
        // Check if student is in session
        if (!session('old_student_enrollment')) {
            return redirect()->route('enrollment.old-student.login')
                ->with('error', 'Please login first.');
        }

        $enrollmentData = session('old_student_enrollment');
        $student = Student::findOrFail($enrollmentData['student_id']);
        $section = \App\Models\Section::findOrFail($sectionId);

        // Verify section is for the student's grade level
        if ($section->grade_level !== $student->year_level) {
            return redirect()->route('enrollment.old-student.dashboard')
                ->with('error', 'Selected section does not match your grade level.');
        }

        try {
            DB::beginTransaction();

            // Get the latest academic year and semester
            $academicYear = \App\Models\AcademicYear::latest()->first();
            $semester = \App\Models\Semester::latest()->first();

            if (!$academicYear || !$semester) {
                throw new \Exception('No academic year or semester found. Please contact the registrar.');
            }

            // Check if student is already assigned to this section
            $existingAssignment = DB::table('student_section_assignments')
                ->where([
                    'student_id' => $student->id,
                    'section_id' => $sectionId,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                ])->first();

            if (!$existingAssignment) {
                // Assign student to selected section
                DB::table('student_section_assignments')->insert([
                    'student_id' => $student->id,
                    'section_id' => $sectionId,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'assigned_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create enrollment application for old student
            $application = EnrollmentApplication::create([
                'student_category' => 'old_student',
                'existing_student_id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'date_of_birth' => $student->date_of_birth,
                'gender' => $student->gender,
                'email' => $student->email,
                'phone_number' => $student->phone_number,
                'address' => $student->address,
                'parent_name' => $student->parent_name,
                'parent_phone' => $student->parent_phone,
                'parent_email' => $student->parent_email,
                'parent_relationship' => $student->parent_relationship,
                'emergency_contact_name' => $student->emergency_contact_name,
                'emergency_contact_phone' => $student->emergency_contact_phone,
                'previous_school' => $student->previous_school,
                'grade_level_applying_for' => $student->year_level,
                'status' => 'approved', // Auto-approve old students
            ]);

            // Auto-enroll student in subjects for their grade level
            try {
                $this->autoEnrollStudentInSubjects($student, $student->year_level);
            } catch (\Exception $e) {
                // Log error but continue
            }

            // Update student enrollment status
            $student->update([
                'enrollment_status' => 'active',
            ]);

            DB::commit();

            // Clear session
            session()->forget('old_student_enrollment');

            return redirect()->route('enrollment.portal.success', $application->id)
                ->with('success', 'Enrollment completed successfully! You have been assigned to section: ' . $section->name);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('enrollment.old-student.dashboard')
                ->with('error', 'Failed to complete enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Get sections by grade level for enrollment form
     */
    public function getSectionsByGradeLevel($gradeLevel)
    {
        try {
            // Get the latest academic year and semester
            $academicYear = \App\Models\AcademicYear::latest()->first();
            $semester = \App\Models\Semester::latest()->first();

            // Get sections for the specified grade level
            $sections = \App\Models\Section::where('grade_level', $gradeLevel)
                ->with('adviser')
                ->orderBy('name')
                ->get();

            if ($sections->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sections found for this grade level.',
                    'sections' => []
                ]);
            }

            // Calculate available spots for each section
            $sectionsData = $sections->map(function($section) use ($academicYear, $semester) {
                $currentCount = DB::table('student_section_assignments')
                    ->where('section_id', $section->id)
                    ->where('academic_year_id', $academicYear ? $academicYear->id : null)
                    ->where('semester_id', $semester ? $semester->id : null)
                    ->count();

                $capacity = $section->capacity ?? 25;
                $availableSpots = max(0, $capacity - $currentCount);

                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'grade_level' => $section->grade_level,
                    'adviser' => $section->adviser ? $section->adviser->full_name : null,
                    'capacity' => $capacity,
                    'current_count' => $currentCount,
                    'available_spots' => $availableSpots,
                    'is_full' => $availableSpots === 0,
                    'description' => $section->description,
                ];
            });

            return response()->json([
                'success' => true,
                'sections' => $sectionsData,
                'academic_year' => $academicYear ? $academicYear->name : null,
                'semester' => $semester ? $semester->name : null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching sections: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading sections. Please try again.',
                'sections' => []
            ], 500);
        }
    }

    /**
     * Verify old student credentials for re-enrollment
     */
    public function verifyOldStudent(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            // Try to authenticate the student
            $user = User::where('email', $request->email)
                ->where('role_name', 'Student')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student account not found with this email address.'
                ]);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password. Please try again.'
                ]);
            }

            // Get student profile
            $student = Student::where('user_id', $user->user_id)->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student profile not found. Please contact the registrar.'
                ]);
            }

            // Return student data for pre-filling
            return response()->json([
                'success' => true,
                'message' => 'Verification successful!',
                'student' => [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'middle_name' => $student->middle_name,
                    'date_of_birth' => $student->date_of_birth,
                    'gender' => $student->gender,
                    'email' => $student->email,
                    'phone_number' => $student->phone_number,
                    'address' => $student->address,
                    'parent_name' => $student->parent_name,
                    'parent_email' => $student->parent_email,
                    'parent_phone' => $student->parent_phone,
                    'parent_relationship' => $student->parent_relationship,
                    'emergency_contact_name' => $student->emergency_contact_name,
                    'emergency_contact_phone' => $student->emergency_contact_phone,
                    'previous_school' => $student->previous_school,
                    'year_level' => $student->year_level,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during verification: ' . $e->getMessage()
            ]);
        }
    }
}
