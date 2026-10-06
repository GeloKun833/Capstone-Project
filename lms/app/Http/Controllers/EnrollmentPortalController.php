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
use App\Support\TemporaryPassword;

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
        $enrollmentGroupToken = $this->activeEnrollmentGroupToken($request);
        $drafts = EnrollmentApplication::query()
            ->where('enrollment_group_token', $enrollmentGroupToken)
            ->where('status', 'draft')
            ->orderBy('id')
            ->get();
        $openDraftId = (int) session('enrollment_portal_current_draft_id');
        $openDraft = $openDraftId ? $drafts->firstWhere('id', $openDraftId) : null;
        $sourceDraft = $openDraft ?: $drafts->last();
        $enrollmentGroupStarted = $drafts->isNotEmpty();
        $currentDraftId = $openDraft?->id ?? '';
        $enrollmentServerRestore = [
            'draft_id' => $currentDraftId,
            'group_started' => $enrollmentGroupStarted,
            'fields' => $sourceDraft
                ? $this->draftFormValues($sourceDraft, $openDraft !== null)
                : [],
        ];

        return view('enrollment.portal.create', compact(
            'type',
            'enrollmentGroupToken',
            'enrollmentGroupStarted',
            'currentDraftId',
            'enrollmentServerRestore'
        ));
    }

    public function saveChildDraft(Request $request)
    {
        $validated = $request->validate([
            'enrollment_group_token' => 'required|uuid',
            'current_child_draft_id' => 'nullable|integer',
            'student_category' => 'required|in:new_student,old_student,transferee',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female',
            'grade_level_applying_for' => 'required|string|max:255',
            'address_city_municipality' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $groupToken = $validated['enrollment_group_token'];
        session(['enrollment_portal_group_token' => $groupToken]);
        $draftId = $validated['current_child_draft_id'] ?? null;
        $draft = null;

        if ($draftId) {
            $draft = EnrollmentApplication::query()
                ->whereKey($draftId)
                ->where('enrollment_group_token', $groupToken)
                ->where('status', 'draft')
                ->first();

            if (! $draft) {
                return response()->json([
                    'message' => 'The saved child draft could not be found. Please try again.',
                ], 422);
            }
        }

        $studentFields = [
            'student_category', 'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
            'email', 'phone_number', 'address', 'address_lot_block_village', 'address_barangay_district',
            'address_city_municipality', 'age_years', 'age_months', 'date_enrolled', 'time_enrolled', 'lrn', 'esc_no',
            'religion', 'citizenship', 'birthplace', 'previous_school', 'previous_school_id',
            'previous_school_location', 'previous_school_type', 'psa_birth_cert_no', 'grade_level_applying_for',
        ];
        $draftData = $request->only($studentFields);
        $draftData['phone_number'] = $this->normalizePhoneNumber($draftData['phone_number'] ?? null) ?: null;
        $draftData['address'] = ($draftData['address'] ?? null) ?: null;
        if ($this->parentAccountRequiredForGrade((string) ($draftData['grade_level_applying_for'] ?? ''))) {
            $draftData['email'] = null;
            $draftData['phone_number'] = null;
        }
        $draftData['enrollment_group_token'] = $groupToken;
        $draftData['status'] = 'draft';

        if (! $draft) {
            $draft = $this->findMatchingChildDraft($groupToken, $draftData);
        }

        DB::beginTransaction();
        try {
            if ($draft) {
                $draft->fill($draftData)->save();
            } else {
                $draft = EnrollmentApplication::createUnique($draftData);
            }

            $this->mergeParentIdentity($request);
            if ($this->parentIdentityIsComplete($request)) {
                $studentEmail = trim((string) $request->input('email', ''));
                if ($studentEmail !== '' && strcasecmp($studentEmail, (string) $request->input('parent_email')) === 0) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'The parent email must be different from the student email.',
                    ], 422);
                }

                $parentPhone = (string) $request->input('parent_phone', '');
                if ($parentPhone !== '' && ! preg_match('/^09\d{9}$/', $parentPhone)) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Parent/Guardian phone number must contain exactly 11 digits and start with 09.',
                    ], 422);
                }

                $phoneConflict = $request->input('parent_phone')
                    ? $this->findParentPhoneConflict($request, (string) $request->input('parent_phone'))
                    : null;
                if ($phoneConflict) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'This phone number is already registered. Please use a different phone number.',
                    ], 422);
                }

                [$parentUser, $plainPassword] = $this->resolveEnrollmentParentUser($request, $groupToken);
                $this->applyParentProfileToDrafts($request, $groupToken, $parentUser);
                if ($plainPassword) {
                    session()->put('enrollment_parent_password_'.$groupToken, $plainPassword);
                }
            } else {
                $this->linkDraftToExistingGroupParent($draft, $groupToken);
            }

            DB::commit();
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Unable to save enrollment child draft.', ['exception' => $e]);

            return response()->json(['message' => 'Unable to save this child as a draft. Please try again.'], 500);
        }

        $draft->refresh();
        session(['enrollment_portal_current_draft_id' => $draft->id]);

        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'application_number' => $draft->application_number,
            'saved_child' => trim($draft->full_name.' – '.$draft->grade_level_applying_for),
            'parent_ready' => (bool) $draft->parent_user_id,
            'children' => $this->draftChildrenPayload($groupToken),
            'parent' => $this->draftParentPayload($groupToken),
        ]);
    }

    public function startNextChild(Request $request)
    {
        $groupToken = $request->input('enrollment_group_token');
        if (! is_string($groupToken) || ! Str::isUuid($groupToken)) {
            return response()->json(['message' => 'The enrollment session is invalid. Please restart the application.'], 422);
        }

        if (session('enrollment_portal_group_token') === $groupToken) {
            session()->forget('enrollment_portal_current_draft_id');
        }

        return response()->json(['success' => true]);
    }

    public function editChildDraft(Request $request)
    {
        $groupToken = $request->input('enrollment_group_token');
        $draftId = $request->input('draft_id');
        if (! is_string($groupToken) || ! Str::isUuid($groupToken)) {
            return response()->json(['message' => 'The enrollment session is invalid. Please restart the application.'], 422);
        }

        if (session('enrollment_portal_group_token') !== $groupToken) {
            return response()->json(['message' => 'This draft does not belong to the current enrollment.'], 422);
        }

        $draft = EnrollmentApplication::query()
            ->whereKey($draftId)
            ->where('enrollment_group_token', $groupToken)
            ->where('status', 'draft')
            ->first();

        if (! $draft) {
            return response()->json(['message' => 'The saved child draft could not be found.'], 422);
        }

        session(['enrollment_portal_current_draft_id' => $draft->id]);

        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'fields' => $this->draftFormValues($draft, true),
        ]);
    }

    public function saveChildSection(Request $request)
    {
        $groupToken = $request->input('enrollment_group_token');
        $draftId = $request->input('draft_id');
        $sectionId = $request->input('section_id');
        if (! is_string($groupToken) || ! Str::isUuid($groupToken) || session('enrollment_portal_group_token') !== $groupToken) {
            return response()->json(['message' => 'This draft does not belong to the current enrollment.'], 422);
        }

        $draft = EnrollmentApplication::query()
            ->whereKey($draftId)
            ->where('enrollment_group_token', $groupToken)
            ->where('status', 'draft')
            ->first();
        if (! $draft) {
            return response()->json(['message' => 'The saved child draft could not be found.'], 422);
        }

        if (! $this->sectionMatchesGrade((int) $sectionId, (string) $draft->grade_level_applying_for)) {
            return response()->json([
                'message' => 'Choose a section for '.$draft->grade_level_applying_for.'.',
            ], 422);
        }

        $draft->preferred_section_id = (int) $sectionId;
        $draft->save();

        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'section_id' => $draft->preferred_section_id,
        ]);
    }

    public function childDrafts(Request $request)
    {
        $groupToken = $request->query('enrollment_group_token');
        if (! is_string($groupToken) || ! Str::isUuid($groupToken)) {
            return response()->json(['message' => 'The enrollment session is invalid. Please restart the application.'], 422);
        }

        return response()->json([
            'children' => $this->draftChildrenPayload($groupToken),
            'parent' => $this->draftParentPayload($groupToken),
        ]);
    }

    public function saveEnrollmentParent(Request $request)
    {
        $groupToken = $request->input('enrollment_group_token');
        if (! is_string($groupToken) || ! Str::isUuid($groupToken)) {
            return response()->json(['message' => 'The enrollment session is invalid. Please restart the application.'], 422);
        }

        $this->mergeParentIdentity($request);

        $validated = $request->validate([
            'parent_name' => 'required|string|max:255',
            'parent_email' => 'required|email|max:255|different:email',
            'parent_phone' => 'nullable|regex:/^09\d{9}$/',
        ], [
            'parent_phone.regex' => 'Parent/Guardian phone number must contain exactly 11 digits and start with 09.',
        ]);

        $drafts = EnrollmentApplication::query()
            ->where('enrollment_group_token', $groupToken)
            ->where('status', 'draft')
            ->get();
        if ($drafts->isEmpty()) {
            return response()->json(['message' => 'Save the child information before continuing.'], 422);
        }

        $phoneConflict = $validated['parent_phone']
            ? $this->findParentPhoneConflict($request, $validated['parent_phone'])
            : null;
        if ($phoneConflict) {
            return response()->json([
                'message' => 'This phone number is already registered. Please use a different phone number.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            [$parentUser, $plainPassword] = $this->resolveEnrollmentParentUser($request, $groupToken);
            $this->applyParentProfileToDrafts($request, $groupToken, $parentUser);

            if ($plainPassword) {
                session()->put('enrollment_parent_password_'.$groupToken, $plainPassword);
            }

            DB::commit();
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Unable to save enrollment parent account.', ['exception' => $e]);

            return response()->json(['message' => 'Unable to save the parent account. Please try again.'], 500);
        }

        return response()->json([
            'success' => true,
            'parent_ready' => true,
            'children' => $this->draftChildrenPayload($groupToken),
            'parent' => $this->draftParentPayload($groupToken),
        ]);
    }

    /**
     * Store the enrollment application
     */
    public function store(Request $request)
    {
        foreach (['phone_number', 'parent_phone', 'father_contact_no', 'mother_contact_no', 'guardian_contact_no'] as $phoneField) {
            $rawValue = $request->input($phoneField);
            if ($rawValue !== null && $rawValue !== '') {
                $request->merge([$phoneField => $this->normalizePhoneNumber((string) $rawValue)]);
            }
        }

        // Get student category
        $studentCategory = $request->input('student_category', 'new_student');
        $gradeLevel = (string) $request->input('grade_level_applying_for', '');
        $parentAccountRequested = $request->input('create_parent_account') === '1';
        $parentAccountWillBeCreated = $this->shouldCreateParentAccount($gradeLevel, $parentAccountRequested);
        $emailRules = ['nullable', 'email', Rule::unique('users', 'email')];
        if ($request->filled('enrollment_group_token')) {
            $emailRules[] = Rule::unique('enrollment_applications', 'email')
                ->ignore($request->input('current_child_draft_id'));
        } else {
            $emailRules[] = Rule::unique('enrollment_applications', 'email');
        }

        $fatherName = trim(implode(' ', array_filter([
            $request->input('father_first_name'),
            $request->input('father_middle_name'),
            $request->input('father_last_name'),
        ])));
        $motherName = trim(implode(' ', array_filter([
            $request->input('mother_first_name'),
            $request->input('mother_middle_name'),
            $request->input('mother_last_name'),
        ])));
        $request->merge([
            'parent_name' => $request->input('parent_name') ?: ($fatherName ?: ($motherName ?: $request->input('guardian_name'))),
            'parent_email' => $request->input('parent_email') ?: ($request->input('father_email') ?: ($request->input('mother_email') ?: $request->input('guardian_email'))),
        ]);
        
        // Base validation rules
        $rules = [
            'student_category' => 'required|in:new_student,old_student,transferee',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female',
            'email' => $emailRules,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'address_city_municipality' => 'required|string|max:255',
            'address_lot_block_village' => 'nullable|string|max:255',
            'address_barangay_district' => 'nullable|string|max:255',
            'age_years' => 'nullable|integer|min:0',
            'age_months' => 'nullable|integer|min:0|max:11',
            'date_enrolled' => 'nullable|date',
            'time_enrolled' => 'nullable|date_format:H:i',
            'lrn' => 'nullable|string|max:255',
            'esc_no' => 'nullable|string|max:255',
            'covid_vaccinated' => 'nullable|in:Yes,No',
            'covid_first_shot_date' => 'nullable|date',
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
            'selected_section_id' => 'nullable|exists:sections,id',
            'agree_terms' => 'required|accepted',
            'existing_student_id' => 'nullable|exists:students,id',
            'enrollment_group_token' => 'nullable|uuid',
            'current_child_draft_id' => 'nullable|integer',
            // Document validation (varies by category)
            'birth_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'sf10' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'good_moral' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'parent_guardian_id' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        if ($parentAccountWillBeCreated) {
            $rules['parent_name'] = 'required|string|max:255';
            $rules['parent_email'] = 'required|email|different:email';
        }
        
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

        $validator->after(function ($validator) use ($request, $parentAccountWillBeCreated) {
            $phoneFields = [
                'parent_phone',
                'father_contact_no',
                'mother_contact_no',
                'guardian_contact_no',
            ];

            foreach ($phoneFields as $field => $label) {
                $value = trim((string) $request->input($field, ''));

                if ($value === '') {
                    continue;
                }

                if (! preg_match('/^\d+$/', $value)) {
                    $validator->errors()->add($field, 'Phone number must contain numbers only.');
                    continue;
                }

                if (! preg_match('/^09\d{9}$/', $value)) {
                    $validator->errors()->add($field, 'Phone number must contain exactly 11 digits.');
                    continue;
                }

            }

            $parentPhone = trim((string) $request->input('parent_phone', ''));
            if ($parentAccountWillBeCreated && preg_match('/^09\d{9}$/', $parentPhone)) {
                $conflictingParent = $this->findParentPhoneConflict($request, $parentPhone);
                if ($conflictingParent) {
                    $validator->errors()->add('parent_phone', 'This phone number is already registered. Please use a different phone number.');
                }
            }
        });

        // Note: Documents are now OPTIONAL - users can submit applications without documents
        // The registrar can still create student accounts even if documents are incomplete

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->filled('enrollment_group_token') && EnrollmentApplication::query()
            ->where('enrollment_group_token', $request->input('enrollment_group_token'))
            ->where('status', 'draft')
            ->exists()) {
            return $this->finalizeSiblingEnrollment($request, $request->boolean('save_as_draft'));
        }

        try {
            DB::beginTransaction();

            $saveAsDraft = (bool) $request->boolean('save_as_draft');

            // Prepare application data with all fields
            $applicationData = $request->only([
                'student_category', 'existing_student_id', 
                'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
                'email', 'phone_number', 'address', 
                'address_lot_block_village', 'address_barangay_district', 'address_city_municipality',
                'age_years', 'age_months',
                'date_enrolled', 'time_enrolled', 'lrn', 'esc_no',
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

            $applicationData['status'] = $saveAsDraft ? 'draft' : 'pending';
            if ($this->parentAccountRequiredForGrade((string) ($applicationData['grade_level_applying_for'] ?? ''))) {
                $applicationData['email'] = null;
                $applicationData['phone_number'] = null;
            }
            
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

            // Persist Block Section choice from enrollment form (admin-managed sections)
            $applicationData['preferred_section_id'] = $request->filled('selected_section_id')
                ? (int) $request->input('selected_section_id')
                : null;
            if (($applicationData['time_enrolled'] ?? '') === '') {
                $applicationData['time_enrolled'] = null;
            }
            if (! empty($applicationData['date_enrolled'])) {
                $applicationData['date_of_first_attendance'] = $applicationData['date_enrolled'];
            }
            
            // Create the enrollment application (unique APP-YYYY-######, incl. soft-deleted)
            $application = EnrollmentApplication::createUnique($applicationData);

            // Process required documents
            $uploadedDocuments = [];
            $missingDocuments = [];
            
            foreach (EnrollmentDocument::REQUIRED_DOCUMENT_TYPES as $documentType) {
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
            
            if ($saveAsDraft) {
                $application->status = 'draft';
                $application->save();

                DB::commit();

                return redirect()->route('enrollment.portal.create', ['type' => 'new'])
                    ->with('draft_saved', true)
                    ->with('success', 'Child enrollment saved as draft. Add another child or continue later using the same parent email.');
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

            $this->grantPortalApplicationAccess($application->id);
            $this->rememberSuccessAccountDetails($application->id, $accountDetails);

            // Prepare user-friendly success message
            $successMessage = '🎉 Welcome to Panorama Montessori School! Your enrollment application has been submitted successfully.';
            
            // Add section assignment info
            $selectedSectionId = $request->input('selected_section_id');
            if ($selectedSectionId && isset($accountDetails['assigned_section']) && $accountDetails['assigned_section'] !== 'To be assigned after approval') {
                $successMessage .= "\n\n🎯 Section Assignment:";
                $successMessage .= "\n✅ You have been automatically assigned to: " . $accountDetails['assigned_section'];
            }
            
            // Add account creation info
            $createParentAccount = $this->shouldCreateParentAccount(
                $application->grade_level_applying_for,
                $request->input('create_parent_account')
            );
            
            if ($createParentAccount) {
                $successMessage .= "\n\n✅ Accounts Created:";
                if ($accountDetails['student_account'] ?? false) {
                    $successMessage .= "\n\n👨‍🎓 Student Account:";
                    $successMessage .= "\n📧 Email: " . $application->email;
                    $successMessage .= "\n🔑 Password: " . ($accountDetails['password'] ?? 'Shown on the next page');
                } else {
                    $successMessage .= "\n\n👨‍🎓 Student record created without a separate student login.";
                }
                $successMessage .= "\n\n👨‍👩‍👧 Parent Account:";
                $successMessage .= "\n📧 Email: " . $application->parent_email;
                $parentPassword = $accountDetails['parent_account']['password'] ?? null;
                $successMessage .= $parentPassword
                    ? "\n🔑 Password: " . $parentPassword
                    : "\n🔑 Password: Use the existing parent account password";
            } else {
                $successMessage .= "\n\n✅ Student account has been created! You can now login using:";
                $successMessage .= "\n📧 Email: " . $application->email;
                $successMessage .= "\n🔑 Password: " . ($accountDetails['password'] ?? 'Shown on the next page');
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
            
            $this->forgetFinishedEnrollmentForm();

            return redirect()->route('enrollment.portal.success', $application->id)
                ->with('account_details', $accountDetails)
                ->with('missing_documents', $missingDocuments)
                ->with('uploaded_documents', $uploadedDocuments)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to submit enrollment application.', ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Failed to submit your application. Please try again or contact the school for assistance.')
                ->withInput();
        }
    }

    private function finalizeSiblingEnrollment(Request $request, bool $saveAsDraft = false)
    {
        $groupToken = (string) $request->input('enrollment_group_token');
        $drafts = EnrollmentApplication::query()
            ->where('enrollment_group_token', $groupToken)
            ->where('status', 'draft')
            ->orderBy('id')
            ->get();
        $currentDraft = $drafts->firstWhere('id', (int) $request->input('current_child_draft_id'));

        if ($drafts->isEmpty() || ! $currentDraft) {
            return redirect()->back()
                ->with('error', 'The saved child drafts could not be found. Please restart the enrollment process.')
                ->withInput();
        }

        $studentFields = [
            'student_category', 'existing_student_id', 'first_name', 'last_name', 'middle_name',
            'date_of_birth', 'gender', 'email', 'phone_number', 'address', 'address_lot_block_village',
            'address_barangay_district', 'address_city_municipality', 'age_years', 'age_months',
            'date_enrolled', 'time_enrolled', 'lrn', 'esc_no', 'religion', 'citizenship', 'birthplace',
            'previous_school', 'previous_school_id', 'previous_school_location', 'previous_school_type',
            'psa_birth_cert_no', 'grade_level_applying_for',
        ];
        $currentStudentData = $request->only($studentFields);
        $currentStudentData['phone_number'] = $this->normalizePhoneNumber($currentStudentData['phone_number'] ?? null) ?: null;
        $currentStudentData['address'] = $currentStudentData['address'] ?: null;

        $sharedFields = $request->only([
            'parent_name', 'parent_phone', 'parent_email', 'parent_relationship',
            'father_last_name', 'father_first_name', 'father_middle_name', 'father_education',
            'father_employment', 'father_company_name', 'father_work_address', 'father_contact_no', 'father_email',
            'mother_last_name', 'mother_first_name', 'mother_middle_name', 'mother_education',
            'mother_employment', 'mother_company_name', 'mother_work_address', 'mother_contact_no', 'mother_email',
            'family_income_bracket', 'no_of_siblings', 'no_of_siblings_studying', 'siblings_schools',
            'emergency_contact_name', 'emergency_contact_phone', 'guardian_name', 'guardian_relation',
            'guardian_contact_no', 'guardian_email', 'authorized_fetcher', 'authorized_fetcher_relation',
            'parent_signature_name', 'date_of_first_attendance',
        ]);

        $documentCheckboxes = [
            'doc_submitted_form138', 'doc_submitted_psa_birth', 'doc_submitted_form137',
            'doc_submitted_baptismal', 'doc_submitted_pic_1x1', 'doc_submitted_pic_2x2',
            'doc_submitted_itr', 'doc_submitted_unemployment',
        ];
        foreach ($documentCheckboxes as $checkbox) {
            $sharedFields[$checkbox] = $request->boolean($checkbox);
        }

        $sectionError = $this->assignRequestedSections($request, $drafts, $currentDraft);
        if ($sectionError) {
            return redirect()->back()
                ->with('error', $sectionError)
                ->withInput();
        }

        $stagedDocuments = $this->stagedEnrollmentDocuments($request);
        $childAccountDetails = [];
        $missingByChild = [];
        DB::beginTransaction();
        try {
            foreach ($drafts as $draft) {
                if ($draft->id === $currentDraft->id) {
                    $draft->fill($currentStudentData);
                }

                $draft->fill($sharedFields);
                if ($draft->date_enrolled) {
                    $draft->date_of_first_attendance = $draft->date_enrolled;
                }
                if (blank($draft->time_enrolled)) {
                    $draft->time_enrolled = null;
                }
                if ($this->parentAccountRequiredForGrade((string) $draft->grade_level_applying_for)) {
                    $draft->email = null;
                    $draft->phone_number = null;
                }
                [$uploadedDocuments, $missingDocuments] = $this->attachStagedDocuments($draft, $stagedDocuments);

                $draft->status = $saveAsDraft ? 'draft' : ($missingDocuments ? 'needs_documents' : 'under_review');
                $draft->save();
                $missingByChild[$draft->id] = $missingDocuments;

                if ($saveAsDraft) {
                    continue;
                }

                $childAccountDetails[] = $this->createStudentAccount($draft, $draft->preferred_section_id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Unable to finalize sibling enrollment drafts.', ['group' => $groupToken, 'exception' => $e]);

            return redirect()->back()
                ->with('error', 'Failed to submit the saved child enrollments. Please try again.')
                ->withInput();
        }

            if ($saveAsDraft) {
                return redirect()->back()
                ->withInput()
                ->with('draft_saved', true)
                ->with('success', 'All child enrollments remain saved as drafts. Submit the application when you are ready.');
            }

        foreach ($drafts as $draft) {
            $this->grantPortalApplicationAccess($draft->id);
        }

        $parentUser = User::query()
            ->where('email', $request->input('parent_email'))
            ->where('role_name', User::ROLE_PARENT)
            ->first();
        $parentPassword = session()->pull('enrollment_parent_password_'.$groupToken);
        $parentAccount = $parentUser ? [
            'user_id' => $parentUser->user_id,
            'email' => $parentUser->email,
            'name' => $parentUser->name,
            'password' => $parentPassword,
        ] : null;
        $children = [];
        foreach ($drafts->values() as $index => $draft) {
            $details = $childAccountDetails[$index];
            $children[] = [
                'student_name' => $draft->full_name,
                'grade_level' => $draft->grade_level_applying_for,
                'application_number' => $draft->application_number,
                'assigned_section' => $details['assigned_section'],
                'student_account' => $details['student_account'],
                'email' => $details['email'],
                'password' => $details['password'],
            ];
        }

        $accountDetails = [
            'student_account' => false,
            'student_name' => $children[0]['student_name'],
            'grade_level' => $children[0]['grade_level'],
            'application_number' => $children[0]['application_number'],
            'parent_account' => $parentAccount,
            'children' => $children,
        ];
        $allMissingDocuments = collect($missingByChild)->flatten()->unique()->values()->all();
        foreach ($drafts as $draft) {
            $this->rememberSuccessAccountDetails($draft->id, $accountDetails);
        }
        $this->forgetFinishedEnrollmentForm();

        return redirect()->route('enrollment.portal.success', $drafts->first()->id)
            ->with('account_details', $accountDetails)
            ->with('missing_documents', $allMissingDocuments)
            ->with('success', 'The child enrollment applications have been submitted successfully.');
    }

    /**
     * Show the application details
     */
    public function show($id)
    {
        $application = EnrollmentApplication::with(['documents', 'reviewer'])->findOrFail($id);
        $this->assertPortalApplicationAccess($application);

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
            ->where('status', '!=', 'draft')
            ->first();

        if (!$application) {
            return redirect()->back()
                ->with('error', 'Application not found. Please check your application number and email.');
        }

        $this->grantPortalApplicationAccess($application->id);

        return view('enrollment.portal.show', compact('application'));
    }

    /**
     * Download a document
     */
    public function downloadDocument($id)
    {
        $document = EnrollmentDocument::findOrFail($id);
        $application = $document->enrollmentApplication;
        if (! $application) {
            abort(404, 'File not found');
        }
        $this->assertPortalApplicationAccess($application);

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
                'password' => Hash::make(TemporaryPassword::make()),
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

        // Verify section is for the correct grade level (allow aliases e.g. Kinder/Kindergarten)
        $aliases = \App\Services\GradeSubjectCatalogService::gradeAliases($gradeLevel);
        if (!in_array($section->grade_level, $aliases, true)) {
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
     * Create student account immediately after enrollment application
     */
    private function createStudentAccount($application, $selectedSectionId = null)
    {
        $createStudentUser = ! $this->parentAccountRequiredForGrade($application->grade_level_applying_for);
        $studentPassword = $createStudentUser ? TemporaryPassword::make() : null;
        $parentPassword = null;
        $parentUser = null;

        $shouldCreateParent = $this->shouldCreateParentAccount(
            $application->grade_level_applying_for,
            request()->input('create_parent_account')
        );

        if ($application->parent_email && $application->parent_email !== $application->email) {
            $parentUser = User::where('email', $application->parent_email)
                ->where('role_name', 'Parent')
                ->first();
            if (! $parentUser && $shouldCreateParent) {
                $parentPassword = TemporaryPassword::make();
                $parentUser = User::create([
                    'name' => $application->parent_name,
                    'email' => $application->parent_email,
                    'password' => Hash::make($parentPassword),
                    'role_name' => 'Parent',
                    'status' => 'active',
                    'join_date' => now()->format('Y-m-d'),
                    'phone_number' => $application->parent_phone,
                    'position' => 'Parent/Guardian',
                    'department' => 'Parent Relations',
                    'avatar' => 'default-avatar.png',
                ]);
                Log::info("✅ Created parent account: {$parentUser->email}");
            }
        }

        if ($parentUser && $application->parent_user_id !== $parentUser->id) {
            $application->parent_user_id = $parentUser->id;
            $application->save();
        }

        $user = null;
        if ($createStudentUser) {
            $user = User::create([
                'name' => $application->full_name,
                'email' => $application->email,
                'password' => Hash::make($studentPassword),
                'role_name' => 'Student',
                'status' => 'active',
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
            'user_id' => $user?->user_id,
            'first_name' => $application->first_name,
            'last_name' => $application->last_name,
            'middle_name' => $application->middle_name,
            'gender' => $application->gender,
            'date_of_birth' => $application->date_of_birth,
            'email' => $application->email,
            'phone_number' => $application->phone_number,
            'address' => $application->address,
            'parent_email' => $application->parent_email,
            'parent_user_id' => $parentUser?->id,
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
            $sectionChoice = $selectedSectionId ?: $application->preferred_section_id;
            if ($sectionChoice) {
                // User selected a specific section - assign to that section
                $assignedSection = $this->assignStudentToSelectedSection($student, $sectionChoice, $application->grade_level_applying_for);
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
            'student_account' => $user !== null,
            'user_id' => $user?->user_id,
            'email' => $user?->email,
            'password' => $studentPassword,
            'student_name' => $application->full_name,
            'grade_level' => $application->grade_level_applying_for,
            'assigned_section' => $assignedSection ? $assignedSection->name : 'To be assigned after approval',
            'application_number' => $application->application_number,
            'parent_account' => $parentUser ? [
                'user_id' => $parentUser->user_id,
                'email' => $parentUser->email,
                'name' => $parentUser->name,
                'password' => $parentPassword,
            ] : null,
        ];
    }

    private function activeEnrollmentGroupToken(Request $request): string
    {
        $old = old('enrollment_group_token');
        if (is_string($old) && Str::isUuid($old)) {
            session(['enrollment_portal_group_token' => $old]);

            return $old;
        }

        $requested = $request->query('enrollment_group_token');
        if ($this->tokenHasOpenDrafts($requested)) {
            session(['enrollment_portal_group_token' => $requested]);

            return $requested;
        }

        $sessionToken = session('enrollment_portal_group_token');
        if (is_string($sessionToken) && Str::isUuid($sessionToken)) {
            $hasAnyApplication = EnrollmentApplication::query()
                ->where('enrollment_group_token', $sessionToken)
                ->exists();
            if ($this->tokenHasOpenDrafts($sessionToken) || ! $hasAnyApplication) {
                return $sessionToken;
            }
        }

        $token = (string) Str::uuid();
        session(['enrollment_portal_group_token' => $token]);
        session()->forget('enrollment_portal_current_draft_id');

        return $token;
    }

    private function tokenHasOpenDrafts(mixed $token): bool
    {
        return is_string($token)
            && Str::isUuid($token)
            && EnrollmentApplication::query()
                ->where('enrollment_group_token', $token)
                ->where('status', 'draft')
                ->exists();
    }

    private function childRestoreFieldNames(): array
    {
        return [
            'student_category', 'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
            'email', 'phone_number', 'age_years', 'age_months', 'date_enrolled', 'time_enrolled', 'lrn', 'esc_no',
            'covid_vaccinated', 'covid_first_shot_date', 'covid_full_vaccination_date',
            'birthplace', 'previous_school', 'previous_school_id', 'previous_school_location',
            'previous_school_type', 'psa_birth_cert_no', 'grade_level_applying_for', 'preferred_section_id',
        ];
    }

    private function householdRestoreFieldNames(): array
    {
        return [
            'address', 'address_lot_block_village', 'address_barangay_district', 'address_city_municipality',
            'religion', 'citizenship',
        ];
    }

    private function draftFormValues(EnrollmentApplication $draft, bool $includeChild): array
    {
        $names = array_merge($this->householdRestoreFieldNames(), $this->sharedParentFieldNames(), [
            'emergency_contact_name', 'emergency_contact_phone', 'parent_signature_name', 'date_of_first_attendance',
        ]);
        if ($includeChild) {
            $names = array_merge($names, $this->childRestoreFieldNames(), [
                'doc_submitted_form138', 'doc_submitted_psa_birth', 'doc_submitted_form137',
                'doc_submitted_baptismal', 'doc_submitted_pic_1x1', 'doc_submitted_pic_2x2',
                'doc_submitted_itr', 'doc_submitted_unemployment',
            ]);
        }

        $values = [];
        foreach ($names as $name) {
            $value = $draft->{$name};
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d');
            }
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            if ($value === null || $value === '') {
                continue;
            }
            if ($name === 'time_enrolled') {
                $value = substr((string) $value, 0, 5);
            }
            $values[$name] = is_scalar($value) ? (string) $value : $value;
        }

        return $values;
    }

    /**
     * @return array<string, array{contents: string, file_name: string, extension: string, mime_type: ?string, file_size: int}>
     */
    private function stagedEnrollmentDocuments(Request $request): array
    {
        $staged = [];
        foreach (EnrollmentDocument::REQUIRED_DOCUMENT_TYPES as $documentType) {
            $document = $request->file($documentType);
            if (! $document || ! $document->isValid()) {
                continue;
            }
            $path = $document->getRealPath();
            if (! is_string($path) || ! is_file($path)) {
                continue;
            }
            $contents = file_get_contents($path);
            if ($contents === false) {
                continue;
            }
            $staged[$documentType] = [
                'contents' => $contents,
                'file_name' => $document->getClientOriginalName(),
                'extension' => $document->getClientOriginalExtension() ?: 'bin',
                'mime_type' => $document->getMimeType(),
                'file_size' => $document->getSize(),
            ];
        }

        return $staged;
    }

    /**
     * @param  array<string, array{contents: string, file_name: string, extension: string, mime_type: ?string, file_size: int}>  $staged
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function attachStagedDocuments(EnrollmentApplication $application, array $staged): array
    {
        $uploaded = [];
        $missing = [];
        foreach (EnrollmentDocument::REQUIRED_DOCUMENT_TYPES as $documentType) {
            if (! isset($staged[$documentType])) {
                $missing[] = $documentType;
                continue;
            }
            $file = $staged[$documentType];
            $fileName = time().'_'.Str::random(10).'.'.$file['extension'];
            $filePath = 'enrollment_documents/'.$application->id.'/'.$fileName;
            Storage::disk('public')->put($filePath, $file['contents']);
            EnrollmentDocument::create([
                'enrollment_application_id' => $application->id,
                'document_type' => $documentType,
                'file_name' => $file['file_name'],
                'file_path' => $filePath,
                'file_size' => $file['file_size'],
                'mime_type' => $file['mime_type'],
                'status' => 'pending',
            ]);
            $uploaded[] = $documentType;
        }

        return [$uploaded, $missing];
    }

    private function assignRequestedSections(Request $request, $drafts, EnrollmentApplication $currentDraft): ?string
    {
        $choices = $request->input('child_sections', []);
        if (! is_array($choices)) {
            $choices = [];
        }

        foreach ($drafts as $draft) {
            $chosen = $choices[$draft->id] ?? $choices[(string) $draft->id] ?? null;
            if (($chosen === null || $chosen === '') && $draft->id === $currentDraft->id && $request->filled('selected_section_id')) {
                $chosen = $request->input('selected_section_id');
            }
            if ($chosen === null || $chosen === '') {
                continue;
            }
            if (! $this->sectionMatchesGrade((int) $chosen, (string) $draft->grade_level_applying_for)) {
                return 'The section chosen for '.$draft->full_name.' does not match '.$draft->grade_level_applying_for.'.';
            }
            $draft->preferred_section_id = (int) $chosen;
        }

        return null;
    }

    private function sectionMatchesGrade(int $sectionId, string $gradeLevel): bool
    {
        $section = \App\Models\Section::query()->find($sectionId);

        return $section
            && in_array($section->grade_level, \App\Services\GradeSubjectCatalogService::gradeAliases($gradeLevel), true);
    }

    private function findMatchingChildDraft(string $groupToken, array $draftData): ?EnrollmentApplication
    {
        return EnrollmentApplication::query()
            ->where('enrollment_group_token', $groupToken)
            ->where('status', 'draft')
            ->where('first_name', $draftData['first_name'])
            ->where('last_name', $draftData['last_name'])
            ->whereDate('date_of_birth', $draftData['date_of_birth'])
            ->where('grade_level_applying_for', $draftData['grade_level_applying_for'])
            ->first();
    }

    private function mergeParentIdentity(Request $request): void
    {
        $fatherName = trim(implode(' ', array_filter([
            $request->input('father_first_name'),
            $request->input('father_middle_name'),
            $request->input('father_last_name'),
        ])));
        $motherName = trim(implode(' ', array_filter([
            $request->input('mother_first_name'),
            $request->input('mother_middle_name'),
            $request->input('mother_last_name'),
        ])));
        $phone = $request->input('parent_phone')
            ?: ($request->input('father_contact_no') ?: $request->input('mother_contact_no'));

        $request->merge([
            'parent_name' => $request->input('parent_name') ?: ($fatherName ?: ($motherName ?: $request->input('guardian_name'))),
            'parent_email' => $request->input('parent_email') ?: ($request->input('father_email') ?: ($request->input('mother_email') ?: $request->input('guardian_email'))),
            'parent_phone' => $this->normalizePhoneNumber(is_string($phone) ? $phone : null) ?: null,
        ]);
    }

    private function parentIdentityIsComplete(Request $request): bool
    {
        $name = trim((string) $request->input('parent_name', ''));
        $email = trim((string) $request->input('parent_email', ''));

        return $name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @return array{0: User, 1: ?string}
     */
    private function resolveEnrollmentParentUser(Request $request, string $groupToken): array
    {
        $name = trim((string) $request->input('parent_name'));
        $email = trim((string) $request->input('parent_email'));
        $phone = $this->normalizePhoneNumber($request->input('parent_phone')) ?: null;

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Enter a parent or guardian name and email before continuing.');
        }

        $linkedId = EnrollmentApplication::query()
            ->where('enrollment_group_token', $groupToken)
            ->whereNotNull('parent_user_id')
            ->value('parent_user_id');

        $parentUser = $linkedId
            ? User::query()->whereKey($linkedId)->where('role_name', User::ROLE_PARENT)->first()
            : null;

        if (! $parentUser) {
            $parentUser = User::query()
                ->where('email', $email)
                ->where('role_name', User::ROLE_PARENT)
                ->first();
        }

        $plainPassword = null;
        if ($parentUser) {
            $emailTaken = User::query()
                ->where('email', $email)
                ->where('id', '!=', $parentUser->id)
                ->exists();
            if ($emailTaken) {
                throw new \InvalidArgumentException('That email address is already used by another account.');
            }

            $parentUser->update([
                'name' => $name,
                'email' => $email,
                'phone_number' => $phone,
            ]);
        } else {
            if (User::query()->where('email', $email)->exists()) {
                throw new \InvalidArgumentException('That email address is already used by a non-parent account.');
            }

            $plainPassword = TemporaryPassword::make();
            $parentUser = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($plainPassword),
                'role_name' => User::ROLE_PARENT,
                'status' => 'active',
                'join_date' => now()->format('Y-m-d'),
                'phone_number' => $phone,
                'position' => 'Parent/Guardian',
                'department' => 'Parent Relations',
                'avatar' => 'default-avatar.png',
            ]);
        }

        return [$parentUser, $plainPassword];
    }

    private function applyParentProfileToDrafts(Request $request, string $groupToken, User $parentUser): void
    {
        $parentFields = $request->only($this->sharedParentFieldNames());
        $parentFields['parent_phone'] = $this->normalizePhoneNumber($parentFields['parent_phone'] ?? null) ?: null;
        foreach ($parentFields as $field => $value) {
            if ($value === '') {
                $parentFields[$field] = null;
            }
        }

        EnrollmentApplication::query()
            ->where('enrollment_group_token', $groupToken)
            ->where('status', 'draft')
            ->update($parentFields + ['parent_user_id' => $parentUser->id]);
    }

    private function linkDraftToExistingGroupParent(EnrollmentApplication $draft, string $groupToken): void
    {
        if ($draft->parent_user_id) {
            return;
        }

        $linked = EnrollmentApplication::query()
            ->where('enrollment_group_token', $groupToken)
            ->where('status', 'draft')
            ->whereNotNull('parent_user_id')
            ->where('id', '!=', $draft->id)
            ->first();

        if (! $linked) {
            return;
        }

        $draft->parent_user_id = $linked->parent_user_id;
        foreach ($this->sharedParentFieldNames() as $field) {
            if (blank($draft->{$field}) && filled($linked->{$field})) {
                $draft->{$field} = $linked->{$field};
            }
        }
        $draft->save();
    }

    private function sharedParentFieldNames(): array
    {
        return [
            'parent_name', 'parent_phone', 'parent_email', 'parent_relationship',
            'father_last_name', 'father_first_name', 'father_middle_name', 'father_education',
            'father_employment', 'father_company_name', 'father_work_address', 'father_contact_no', 'father_email',
            'mother_last_name', 'mother_first_name', 'mother_middle_name', 'mother_education',
            'mother_employment', 'mother_company_name', 'mother_work_address', 'mother_contact_no', 'mother_email',
            'family_income_bracket', 'no_of_siblings', 'no_of_siblings_studying', 'siblings_schools',
            'guardian_name', 'guardian_relation', 'guardian_contact_no', 'guardian_email',
            'authorized_fetcher', 'authorized_fetcher_relation',
        ];
    }

    private function draftChildrenPayload(string $groupToken): array
    {
        return EnrollmentApplication::query()
            ->where('enrollment_group_token', $groupToken)
            ->where('status', 'draft')
            ->orderBy('id')
            ->get()
            ->map(function (EnrollmentApplication $draft) {
                return [
                    'id' => $draft->id,
                    'name' => $draft->full_name,
                    'grade_level' => $draft->grade_level_applying_for,
                    'date_of_birth' => optional($draft->date_of_birth)->format('M d, Y'),
                    'application_number' => $draft->application_number,
                    'preferred_section_id' => $draft->preferred_section_id,
                    'date_enrolled' => optional($draft->date_enrolled)->format('Y-m-d'),
                    'time_enrolled' => $draft->time_enrolled ? substr((string) $draft->time_enrolled, 0, 5) : null,
                    'status' => 'Draft',
                ];
            })
            ->all();
    }

    private function draftParentPayload(string $groupToken): ?array
    {
        $draft = EnrollmentApplication::query()
            ->where('enrollment_group_token', $groupToken)
            ->where(function ($query) {
                $query->whereNotNull('parent_user_id')->orWhereNotNull('parent_name');
            })
            ->orderByDesc('parent_user_id')
            ->orderBy('id')
            ->first();

        if (! $draft || blank($draft->parent_name)) {
            return null;
        }

        return [
            'name' => $draft->parent_name,
            'email' => $draft->parent_email,
            'account_count' => $draft->parent_user_id ? 1 : 0,
        ];
    }

    private function parentAccountRequiredForGrade(string $gradeLevel): bool
    {
        return in_array($gradeLevel, [
            'Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3',
            'Grade 4', 'Grade 5', 'Grade 6',
        ], true);
    }

    private function shouldCreateParentAccount(string $gradeLevel, $requested): bool
    {
        if ($this->parentAccountRequiredForGrade($gradeLevel)) {
            return true;
        }

        return in_array($gradeLevel, ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'], true)
            && in_array($requested, [true, 1, '1', 'on'], true);
    }

    private function normalizePhoneNumber(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return preg_replace('/\D+/', '', (string) $value);
    }

    private function findParentPhoneConflict(Request $request, string $phoneNumber): ?User
    {
        $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);

        if ($normalizedPhone === '') {
            return null;
        }

        $existingParent = User::query()
            ->where('role_name', User::ROLE_PARENT)
            ->whereNotNull('phone_number')
            ->get()
            ->first(function ($user) use ($normalizedPhone) {
                return $this->normalizePhoneNumber((string) $user->phone_number) === $normalizedPhone;
            });

        if (! $existingParent) {
            return null;
        }

        return $this->isSameParentAccount($request, $existingParent) ? null : $existingParent;
    }

    private function isSameParentAccount(Request $request, User $existingParent): bool
    {
        $groupToken = $request->input('enrollment_group_token');
        if (is_string($groupToken) && Str::isUuid($groupToken)) {
            $linkedToGroup = EnrollmentApplication::query()
                ->where('enrollment_group_token', $groupToken)
                ->where('parent_user_id', $existingParent->id)
                ->exists();
            if ($linkedToGroup) {
                return true;
            }
        }

        $candidateEmails = array_filter(array_map('trim', [
            (string) $request->input('parent_email', ''),
            (string) $request->input('father_email', ''),
            (string) $request->input('mother_email', ''),
            (string) $request->input('guardian_email', ''),
        ]), fn ($email) => $email !== '');

        foreach ($candidateEmails as $email) {
            if (strtolower($email) === strtolower((string) $existingParent->email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show success page with account details
     */
    public function success($id)
    {
        $application = EnrollmentApplication::findOrFail($id);
        $this->assertPortalApplicationAccess($application);
        $accountDetails = $this->successAccountDetails($application);
        $sessionToken = session('enrollment_portal_group_token');
        if (is_string($sessionToken) && $sessionToken === $application->enrollment_group_token) {
            $stillHasDrafts = EnrollmentApplication::query()
                ->where('enrollment_group_token', $sessionToken)
                ->where('status', 'draft')
                ->exists();
            if (! $stillHasDrafts) {
                session()->forget(['enrollment_portal_group_token', 'enrollment_portal_current_draft_id']);
            }
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
            $application = EnrollmentApplication::createUnique([
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
            $this->grantPortalApplicationAccess($application->id);

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

            // Get sections for the specified grade level (canonical + aliases)
            $sections = app(\App\Services\GradeSubjectCatalogService::class)
                ->sectionsForGrade(urldecode($gradeLevel));

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
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');

        } catch (\Exception $e) {
            Log::error('Error fetching sections: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading sections. Please try again.',
                'sections' => []
            ], 500)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }
    }

    /**
     * Get subjects by grade level for enrollment form preview.
     * Driven by admin/registrar-managed subjects (subjects.class = grade).
     */
    public function getSubjectsByGradeLevel($gradeLevel)
    {
        try {
            $subjects = app(\App\Services\GradeSubjectCatalogService::class)
                ->subjectsForGrade(urldecode($gradeLevel));

            if ($subjects->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No subjects have been set up for this grade yet. Please contact the school registrar.',
                    'subjects' => [],
                    'count' => 0,
                ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
            }

            return response()->json([
                'success' => true,
                'subjects' => $subjects->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->subject_name,
                    'class' => $s->class,
                ])->values(),
                'count' => $subjects->count(),
                'grade_level' => urldecode($gradeLevel),
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        } catch (\Exception $e) {
            Log::error('Error fetching subjects by grade: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading subjects. Please try again.',
                'subjects' => [],
                'count' => 0,
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

    protected function grantPortalApplicationAccess(int $applicationId): void
    {
        session()->put('enrollment_access.'.$applicationId, true);
    }

    protected function forgetFinishedEnrollmentForm(): void
    {
        session()->forget(['enrollment_portal_group_token', 'enrollment_portal_current_draft_id']);
    }

    protected function rememberSuccessAccountDetails(int $applicationId, array $accountDetails): void
    {
        session()->put('enrollment_account_details.'.$applicationId, $accountDetails);
    }

    protected function successAccountDetails(EnrollmentApplication $application): array
    {
        $flashed = session('account_details');
        if (is_array($flashed) && $flashed !== []) {
            $this->rememberSuccessAccountDetails($application->id, $flashed);

            return $flashed;
        }

        $stored = session('enrollment_account_details.'.$application->id);
        if (is_array($stored) && $stored !== []) {
            return $stored;
        }

        return $this->accountDetailsFromApplication($application);
    }

    protected function accountDetailsFromApplication(EnrollmentApplication $application): array
    {
        $applications = collect([$application]);
        if ($application->enrollment_group_token) {
            $group = EnrollmentApplication::query()
                ->with(['student', 'preferredSection', 'parentUser'])
                ->where('enrollment_group_token', $application->enrollment_group_token)
                ->where('status', '!=', 'draft')
                ->orderBy('id')
                ->get();
            if ($group->isNotEmpty()) {
                $applications = $group;
            }
        } else {
            $application->loadMissing(['student', 'preferredSection', 'parentUser']);
        }

        $children = $applications->map(function (EnrollmentApplication $row) {
            $student = $row->student;
            $section = $student?->sectionLabel() ?: $row->preferredSection?->name;
            $hasStudentLogin = $student && filled($student->user_id);

            return [
                'student_name' => $row->full_name,
                'grade_level' => $row->grade_level_applying_for,
                'application_number' => $row->application_number,
                'assigned_section' => $section ?: 'To be assigned after approval',
                'student_account' => $hasStudentLogin,
                'email' => $hasStudentLogin ? $student->email : null,
                'password' => null,
            ];
        })->values()->all();

        $parent = $application->parentUser ?: $applications->first()?->parentUser;
        $first = $children[0] ?? [
            'student_name' => $application->full_name,
            'grade_level' => $application->grade_level_applying_for,
            'application_number' => $application->application_number,
            'student_account' => false,
        ];

        $parentAccount = $parent ? [
            'user_id' => $parent->user_id,
            'email' => $parent->email,
            'name' => $parent->name,
            'password' => null,
        ] : null;

        if (count($children) > 1 || ! ($first['student_account'] ?? false)) {
            return [
                'student_account' => false,
                'student_name' => $first['student_name'],
                'grade_level' => $first['grade_level'],
                'application_number' => $first['application_number'],
                'parent_account' => $parentAccount,
                'children' => $children,
            ];
        }

        return [
            'student_account' => true,
            'student_name' => $first['student_name'],
            'grade_level' => $first['grade_level'],
            'application_number' => $first['application_number'],
            'assigned_section' => $first['assigned_section'] ?? 'To be assigned after approval',
            'email' => $first['email'] ?? null,
            'password' => null,
            'parent_account' => $parentAccount,
        ];
    }

    protected function assertPortalApplicationAccess(EnrollmentApplication $application): void
    {
        $user = auth()->user();
        if ($user && in_array($user->role_name, [User::ROLE_ADMIN, User::ROLE_REGISTRAR], true)) {
            return;
        }

        if (session('enrollment_access.'.$application->id)) {
            return;
        }

        abort(404);
    }
}
