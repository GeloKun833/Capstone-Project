@extends('layouts.enrollment-portal')

@section('title', 'Enrollment Application Form')

@section('content')

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="text-center mb-4">
            <h1 class="ep-page-title"><i class="fas fa-graduation-cap text-primary me-2"></i>Enrollment Application</h1>
            <p class="ep-page-subtitle">Complete each step to submit your application.</p>
            <div class="alert {{ empty($enrollmentClosedMessage) ? 'alert-info' : 'alert-warning' }} text-start mx-auto mt-3" style="max-width: 640px;">
                <div class="fw-bold mb-1">New Enrollment</div>
                <div>Academic Year: {{ $activeAcademicYear?->displayName() ?? 'None' }}</div>
                <div>Enrollment Status: {{ empty($enrollmentClosedMessage) ? 'OPEN' : 'CLOSED' }}</div>
            </div>
        </div>

        @if(!empty($enrollmentClosedMessage))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ $enrollmentClosedMessage }}
            </div>
        </div>
    </div>
        @else
        
        <!-- Progress Steps -->
        <div class="wizard-steps mb-5">
            <div class="wizard-step @if(!isset($type) || $type === null) active @else completed @endif" data-step="1">
                <div class="wizard-step-circle">1</div>
                <div class="wizard-step-title">Student Category</div>
            </div>
            <div class="wizard-step @if(isset($type) && $type !== null) active @endif" data-step="2">
                <div class="wizard-step-circle">2</div>
                <div class="wizard-step-title">Personal Information</div>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="wizard-step-circle">3</div>
                <div class="wizard-step-title">Parent/Guardian</div>
            </div>
            <div class="wizard-step" data-step="4">
                <div class="wizard-step-circle">4</div>
                <div class="wizard-step-title">Emergency Contact</div>
            </div>
            <div class="wizard-step" data-step="5">
                <div class="wizard-step-circle">5</div>
                <div class="wizard-step-title">Academic Information</div>
            </div>
            <div class="wizard-step" data-step="6">
                <div class="wizard-step-circle">6</div>
                <div class="wizard-step-title">Block Section</div>
            </div>
            <div class="wizard-step" data-step="7">
                <div class="wizard-step-circle">7</div>
                <div class="wizard-step-title">Required Documents</div>
            </div>
            <div class="wizard-step" data-step="8">
                <div class="wizard-step-circle">8</div>
                <div class="wizard-step-title">Enrollment Guidelines</div>
            </div>
        </div>

        <form action="{{ route('enrollment.portal.store') }}" method="POST" enctype="multipart/form-data" id="enrollmentForm">
            @csrf
            
            @if(isset($type) && $type !== null)
                <!-- Store type in hidden field for use throughout the form -->
                <input type="hidden" name="enrollment_type" id="enrollmentType" value="{{ $type }}">
            @endif

            @if(isset($type) && $type !== null)
                <!-- Auto-set category based on type parameter - Hidden input -->
                <input type="hidden" name="student_category" id="autoStudentCategory" value="{{ $type === 'new' ? 'new_student' : ($type === 'transferee' ? 'transferee' : 'new_student') }}">
            @endif
            
            <!-- Step 1: Student Category Selection -->
            <div class="form-step @if(!isset($type) || $type === null) active @endif" id="step1" @if(isset($type) && $type !== null) style="display: none;" @endif>
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Student Category</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Please select the student category that applies to you</p>
                        
                        <div class="row g-3">
                            <!-- New Student -->
                            <div class="col-md-4">
                                <div class="form-check p-4 border rounded h-100" style="cursor: pointer;" onclick="selectCategory('new_student')">
                                    <input class="form-check-input" type="radio" name="student_category" id="new_student" value="new_student" {{ old('student_category', 'new_student') == 'new_student' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="new_student" style="cursor: pointer;">
                                        <div class="text-center">
                                            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                                            <h6 class="fw-bold">New Student</h6>
                                            <small class="text-muted">First time enrolling at this school</small>
                                            <div class="mt-3">
                                                <span class="badge bg-info">All Documents Required</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Old Student (Returning) -->
                            <div class="col-md-4">
                                <div class="form-check p-4 border rounded h-100" style="cursor: pointer;" onclick="selectCategory('old_student')">
                                    <input class="form-check-input" type="radio" name="student_category" id="old_student" value="old_student" {{ old('student_category') == 'old_student' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="old_student" style="cursor: pointer;">
                                        <div class="text-center">
                                            <i class="fas fa-user-check fa-3x text-success mb-3"></i>
                                            <h6 class="fw-bold">Old Student</h6>
                                            <small class="text-muted">Previously enrolled, re-enrolling for new year</small>
                                            <div class="mt-3">
                                                <span class="badge bg-success">Login Required</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Transferee -->
                            <div class="col-md-4">
                                <div class="form-check p-4 border rounded h-100" style="cursor: pointer;" onclick="selectCategory('transferee')">
                                    <input class="form-check-input" type="radio" name="student_category" id="transferee" value="transferee" {{ old('student_category') == 'transferee' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="transferee" style="cursor: pointer;">
                                        <div class="text-center">
                                            <i class="fas fa-exchange-alt fa-3x text-warning mb-3"></i>
                                            <h6 class="fw-bold">Transferee</h6>
                                            <small class="text-muted">Transferring from another school</small>
                                            <div class="mt-3">
                                                <span class="badge bg-warning">SF9 Required</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        @error('student_category')
                            <div class="alert alert-danger mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                            </div>
                        @enderror

                        <!-- Old Student Login Section (Hidden by default) -->
                        <div id="oldStudentLoginSection" class="mt-4" style="display: none;">
                            <div class="alert alert-warning border-warning">
                                <h6 class="fw-bold"><i class="fas fa-sign-in-alt me-2"></i>Old Student Login Required <span class="text-danger">*</span></h6>
                                <p class="mb-3"><strong>You must login to proceed.</strong> Please verify your existing student credentials to continue with re-enrollment.</p>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="old_student_email" name="old_student_email" placeholder="Enter your student email">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="old_student_password" name="old_student_password" placeholder="Enter your password">
                                            <button class="btn btn-outline-secondary" type="button" id="toggleOldStudentPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary" onclick="verifyOldStudent()">
                                        <i class="fas fa-sign-in-alt me-2"></i>Verify & Continue
                                    </button>
                                    <small class="d-block text-muted mt-2">
                                        <i class="fas fa-info-circle me-1"></i>After verification, your information will be pre-filled for re-enrollment.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Student's Information -->
            <div class="form-step @if(isset($type) && $type !== null) active @endif" id="step2">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>STUDENT'S INFORMATION</h5>
                    </div>
                    <div class="card-body p-4">
                        <div id="viewChildDraftsWrap" class="justify-content-between align-items-center mb-4" style="display: none;">
                            <p class="text-muted mb-0">Saved children stay on this application while you enroll the next student.</p>
                            <button type="button" class="btn btn-outline-primary" id="viewChildDraftsBtn" onclick="openSavedChildDrafts()">
                                <i class="fas fa-folder-open me-2"></i>View Child Draft Saved
                            </button>
                        </div>
                        <p class="text-muted mb-4" id="studentInfoIntro">Enter the student's complete information</p>
                        
                        <h6 class="fw-bold mb-3 text-primary">Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Level Applied for <span class="text-danger">*</span></label>
                                <select class="form-select @error('grade_level_applying_for') is-invalid @enderror" 
                                        name="grade_level_applying_for" id="grade_level_applying_for" required
                                        onchange="updateStudentContactFields(this.value)">
                                    <option value="">Select Grade Level</option>
                                    <option value="Nursery" {{ old('grade_level_applying_for') == 'Nursery' ? 'selected' : '' }}>Nursery</option>
                                    <option value="Kindergarten" {{ old('grade_level_applying_for') == 'Kindergarten' ? 'selected' : '' }}>Kindergarten</option>
                                    <option value="Grade 1" {{ old('grade_level_applying_for') == 'Grade 1' ? 'selected' : '' }}>Grade 1</option>
                                    <option value="Grade 2" {{ old('grade_level_applying_for') == 'Grade 2' ? 'selected' : '' }}>Grade 2</option>
                                    <option value="Grade 3" {{ old('grade_level_applying_for') == 'Grade 3' ? 'selected' : '' }}>Grade 3</option>
                                    <option value="Grade 4" {{ old('grade_level_applying_for') == 'Grade 4' ? 'selected' : '' }}>Grade 4</option>
                                    <option value="Grade 5" {{ old('grade_level_applying_for') == 'Grade 5' ? 'selected' : '' }}>Grade 5</option>
                                    <option value="Grade 6" {{ old('grade_level_applying_for') == 'Grade 6' ? 'selected' : '' }}>Grade 6</option>
                                    <option value="Grade 7" {{ old('grade_level_applying_for') == 'Grade 7' ? 'selected' : '' }}>Grade 7</option>
                                    <option value="Grade 8" {{ old('grade_level_applying_for') == 'Grade 8' ? 'selected' : '' }}>Grade 8</option>
                                    <option value="Grade 9" {{ old('grade_level_applying_for') == 'Grade 9' ? 'selected' : '' }}>Grade 9</option>
                                    <option value="Grade 10" {{ old('grade_level_applying_for') == 'Grade 10' ? 'selected' : '' }}>Grade 10</option>
                                </select>
                                @error('grade_level_applying_for')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select @error('gender') is-invalid @enderror" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="date_enrolled">Date Enrolled</label>
                                <input type="date" class="form-control @error('date_enrolled') is-invalid @enderror"
                                       name="date_enrolled" id="date_enrolled" value="{{ old('date_enrolled') }}">
                                @error('date_enrolled')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <label class="form-label mt-2" for="time_enrolled">Time Enrolled</label>
                                <input type="time" class="form-control @error('time_enrolled') is-invalid @enderror"
                                       name="time_enrolled" id="time_enrolled" value="{{ old('time_enrolled') }}">
                                @error('time_enrolled')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                       name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                       name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control @error('middle_name') is-invalid @enderror" 
                                       name="middle_name" value="{{ old('middle_name') }}">
                                @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label">LRN (Learner Reference Number)</label>
                                <input type="text" class="form-control @error('lrn') is-invalid @enderror" 
                                       name="lrn" value="{{ old('lrn') }}">
                                @error('lrn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ESC NO. (Educational Service Contract Number)</label>
                                <input type="text" class="form-control @error('esc_no') is-invalid @enderror" 
                                       name="esc_no" value="{{ old('esc_no') }}">
                                @error('esc_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @php
                            $upperGradeLevels = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
                            $showStudentContact = in_array(old('grade_level_applying_for'), $upperGradeLevels, true);
                        @endphp
                        <div class="row g-3 mt-1 {{ $showStudentContact ? '' : 'd-none' }}" id="student-contact-fields">
                            <div class="col-md-6" id="student-email-field">
                                <label class="form-label" for="student_email">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       name="email" id="student_email" value="{{ old('email') }}" {{ $showStudentContact ? '' : 'disabled' }}>
                                <div class="form-text">Used for the Grade 7–10 student login.</div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6" id="student-phone-field">
                                <label class="form-label" for="student_phone_number">Phone Number (Optional)</label>
                                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror"
                                       name="phone_number" id="student_phone_number" value="{{ old('phone_number') }}"
                                       maxlength="11" inputmode="numeric" pattern="[0-9]*" {{ $showStudentContact ? '' : 'disabled' }}>
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <script>
                        function updateStudentContactFields(gradeLevel) {
                            var upperGrades = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
                            var showContact = upperGrades.indexOf(gradeLevel) !== -1;
                            var wrap = document.getElementById('student-contact-fields');
                            if (!wrap) return;
                            wrap.classList.toggle('d-none', !showContact);
                            wrap.style.display = showContact ? 'flex' : 'none';
                            wrap.querySelectorAll('input').forEach(function (input) {
                                input.disabled = !showContact;
                                if (!showContact) input.value = '';
                            });
                        }
                        updateStudentContactFields(document.getElementById('grade_level_applying_for').value);
                        </script>
                        
                        <h6 class="fw-bold mb-3 mt-4 text-primary">Address</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Lot # Block # Village / Subd</label>
                                <input type="text" class="form-control @error('address_lot_block_village') is-invalid @enderror" 
                                       name="address_lot_block_village" value="{{ old('address_lot_block_village') }}" 
                                       placeholder="e.g., LOT 35 BLOCK B STA. ROSA HOMES, DITA">
                                @error('address_lot_block_village')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Barangay / District</label>
                                <input type="text" class="form-control @error('address_barangay_district') is-invalid @enderror" 
                                       name="address_barangay_district" value="{{ old('address_barangay_district') }}">
                                @error('address_barangay_district')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">City / Municipality <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('address_city_municipality') is-invalid @enderror" 
                                       name="address_city_municipality" value="{{ old('address_city_municipality') }}" required>
                                @error('address_city_municipality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label class="form-label">Complete Address (for reference)</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          name="address" rows="2">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <h6 class="fw-bold mb-3 mt-4 text-primary">Birth Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                       name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}"
                                       max="{{ now()->toDateString() }}" required>
                                @error('date_of_birth')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Birthplace</label>
                                <input type="text" class="form-control @error('birthplace') is-invalid @enderror" 
                                       name="birthplace" value="{{ old('birthplace') }}" placeholder="e.g., MANILA">
                                @error('birthplace')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">PSA Birth Cert. No.</label>
                                <input type="text" class="form-control @error('psa_birth_cert_no') is-invalid @enderror" 
                                       name="psa_birth_cert_no" value="{{ old('psa_birth_cert_no') }}" placeholder="e.g., 2013-53679">
                                @error('psa_birth_cert_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-3">
                                <label class="form-label">Age (Years)</label>
                                <input type="number" class="form-control @error('age_years') is-invalid @enderror" 
                                       name="age_years" id="age_years" value="{{ old('age_years') }}" readonly>
                                @error('age_years')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Age (Months)</label>
                                <input type="number" class="form-control @error('age_months') is-invalid @enderror" 
                                       name="age_months" id="age_months" value="{{ old('age_months') }}" readonly>
                                @error('age_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <h6 class="fw-bold mb-3 mt-4 text-primary">Additional Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Religion</label>
                                <input type="text" class="form-control @error('religion') is-invalid @enderror" 
                                       name="religion" value="{{ old('religion') }}" placeholder="e.g., IGLESIA NI CRISTO">
                                @error('religion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Citizenship</label>
                                <input type="text" class="form-control @error('citizenship') is-invalid @enderror" 
                                       name="citizenship" value="{{ old('citizenship') }}" placeholder="e.g., FILIPINO">
                                @error('citizenship')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <h6 class="fw-bold mb-3 mt-4 text-primary">Last School Attended</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">School Name</label>
                                <input type="text" class="form-control @error('previous_school') is-invalid @enderror" 
                                       name="previous_school" value="{{ old('previous_school') }}" placeholder="e.g., CABUYAO CENTRAL ELEM. SCHOOL">
                                @error('previous_school')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">School ID</label>
                                <input type="text" class="form-control @error('previous_school_id') is-invalid @enderror" 
                                       name="previous_school_id" value="{{ old('previous_school_id') }}">
                                @error('previous_school_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">School's Location</label>
                                <input type="text" class="form-control @error('previous_school_location') is-invalid @enderror" 
                                       name="previous_school_location" value="{{ old('previous_school_location') }}" placeholder="e.g., CABUYAO CITY, LAGUNA">
                                @error('previous_school_location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">School Type</label>
                                <select class="form-select @error('previous_school_type') is-invalid @enderror" name="previous_school_type">
                                    <option value="">Select Type</option>
                                    <option value="Public" {{ old('previous_school_type') == 'Public' ? 'selected' : '' }}>Public</option>
                                    <option value="Private" {{ old('previous_school_type') == 'Private' ? 'selected' : '' }}>Private</option>
                                </select>
                                @error('previous_school_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: OTHER INFORMATION (for parents) -->
            <div class="form-step" id="step3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-friends me-2"></i>OTHER INFORMATION (for parents)</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Fields marked with <span class="text-danger">*</span> are used to create the parent account. The account uses the <strong>Father's email address</strong> first. If that is blank, it uses the <strong>Mother's email address</strong>. If both are blank, it uses the <strong>Guardian's email address</strong>. Fill the name and email of that person. Do not leave them blank.</p>
                        
                        <!-- Father's Information -->
                        <h6 class="fw-bold mb-3 text-primary">Father's Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('father_last_name') is-invalid @enderror" 
                                       name="father_last_name" value="{{ old('father_last_name') }}">
                                @error('father_last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('father_first_name') is-invalid @enderror" 
                                       name="father_first_name" value="{{ old('father_first_name') }}">
                                @error('father_first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control @error('father_middle_name') is-invalid @enderror" 
                                       name="father_middle_name" value="{{ old('father_middle_name') }}">
                                @error('father_middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Highest Educational Attainment</label>
                                <select class="form-select @error('father_education') is-invalid @enderror" name="father_education">
                                    <option value="">Select</option>
                                    <option value="Elementary graduate" {{ old('father_education') == 'Elementary graduate' ? 'selected' : '' }}>Elementary graduate</option>
                                    <option value="High School graduate" {{ old('father_education') == 'High School graduate' ? 'selected' : '' }}>High School graduate</option>
                                    <option value="College graduate" {{ old('father_education') == 'College graduate' ? 'selected' : '' }}>College graduate</option>
                                    <option value="Vocational" {{ old('father_education') == 'Vocational' ? 'selected' : '' }}>Vocational</option>
                                    <option value="Masters Doctorate degree" {{ old('father_education') == 'Masters Doctorate degree' ? 'selected' : '' }}>Master's Doctorate degree</option>
                                    <option value="Did not attend school" {{ old('father_education') == 'Did not attend school' ? 'selected' : '' }}>Did not attend school</option>
                                    <option value="Others" {{ old('father_education') == 'Others' ? 'selected' : '' }}>Others</option>
                                </select>
                                @error('father_education')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employment Status</label>
                                <select class="form-select @error('father_employment') is-invalid @enderror" name="father_employment">
                                    <option value="">Select</option>
                                    <option value="Full time" {{ old('father_employment') == 'Full time' ? 'selected' : '' }}>Full time</option>
                                    <option value="Part time" {{ old('father_employment') == 'Part time' ? 'selected' : '' }}>Part time</option>
                                    <option value="Self-employed" {{ old('father_employment') == 'Self-employed' ? 'selected' : '' }}>Self-employed</option>
                                    <option value="Unemployed" {{ old('father_employment') == 'Unemployed' ? 'selected' : '' }}>Unemployed</option>
                                </select>
                                @error('father_employment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control @error('father_company_name') is-invalid @enderror" 
                                       name="father_company_name" value="{{ old('father_company_name') }}">
                                @error('father_company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Work Address</label>
                                <input type="text" class="form-control @error('father_work_address') is-invalid @enderror" 
                                       name="father_work_address" value="{{ old('father_work_address') }}">
                                @error('father_work_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Contact no.</label>
                                    <input type="tel" class="form-control @error('father_contact_no') is-invalid @enderror" 
                                        name="father_contact_no" value="{{ old('father_contact_no') }}" maxlength="11" inputmode="numeric" pattern="[0-9]*">
                                @error('father_contact_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('father_email') is-invalid @enderror" 
                                       name="father_email" value="{{ old('father_email') }}">
                                @error('father_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Mother's Information -->
                        <h6 class="fw-bold mb-3 mt-4 text-primary">Mother's Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Last Name (Maiden Name) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('mother_last_name') is-invalid @enderror" 
                                       name="mother_last_name" value="{{ old('mother_last_name') }}">
                                @error('mother_last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('mother_first_name') is-invalid @enderror" 
                                       name="mother_first_name" value="{{ old('mother_first_name') }}">
                                @error('mother_first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control @error('mother_middle_name') is-invalid @enderror" 
                                       name="mother_middle_name" value="{{ old('mother_middle_name') }}">
                                @error('mother_middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Highest Educational Attainment</label>
                                <select class="form-select @error('mother_education') is-invalid @enderror" name="mother_education">
                                    <option value="">Select</option>
                                    <option value="Elementary graduate" {{ old('mother_education') == 'Elementary graduate' ? 'selected' : '' }}>Elementary graduate</option>
                                    <option value="High School graduate" {{ old('mother_education') == 'High School graduate' ? 'selected' : '' }}>High School graduate</option>
                                    <option value="College graduate" {{ old('mother_education') == 'College graduate' ? 'selected' : '' }}>College graduate</option>
                                    <option value="Vocational" {{ old('mother_education') == 'Vocational' ? 'selected' : '' }}>Vocational</option>
                                    <option value="Masters Doctorate degree" {{ old('mother_education') == 'Masters Doctorate degree' ? 'selected' : '' }}>Master's Doctorate degree</option>
                                    <option value="Did not attend school" {{ old('mother_education') == 'Did not attend school' ? 'selected' : '' }}>Did not attend school</option>
                                    <option value="Others" {{ old('mother_education') == 'Others' ? 'selected' : '' }}>Others</option>
                                </select>
                                @error('mother_education')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employment Status</label>
                                <select class="form-select @error('mother_employment') is-invalid @enderror" name="mother_employment">
                                    <option value="">Select</option>
                                    <option value="Full time" {{ old('mother_employment') == 'Full time' ? 'selected' : '' }}>Full time</option>
                                    <option value="Part time" {{ old('mother_employment') == 'Part time' ? 'selected' : '' }}>Part time</option>
                                    <option value="Self-employed" {{ old('mother_employment') == 'Self-employed' ? 'selected' : '' }}>Self-employed</option>
                                    <option value="Unemployed" {{ old('mother_employment') == 'Unemployed' ? 'selected' : '' }}>Unemployed</option>
                                </select>
                                @error('mother_employment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control @error('mother_company_name') is-invalid @enderror" 
                                       name="mother_company_name" value="{{ old('mother_company_name') }}">
                                @error('mother_company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Work Address</label>
                                <input type="text" class="form-control @error('mother_work_address') is-invalid @enderror" 
                                       name="mother_work_address" value="{{ old('mother_work_address') }}">
                                @error('mother_work_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Contact no.</label>
                                    <input type="tel" class="form-control @error('mother_contact_no') is-invalid @enderror" 
                                        name="mother_contact_no" value="{{ old('mother_contact_no') }}" maxlength="11" inputmode="numeric" pattern="[0-9]*">
                                @error('mother_contact_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('mother_email') is-invalid @enderror" 
                                       name="mother_email" value="{{ old('mother_email') }}">
                                @error('mother_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Family Income Information -->
                        <h6 class="fw-bold mb-3 mt-4 text-primary">FAMILY'S MONTHLY INCOME BRACKET</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Income Bracket</label>
                                <select class="form-select @error('family_income_bracket') is-invalid @enderror" name="family_income_bracket">
                                    <option value="">Select</option>
                                    <option value="Below 10,000.00" {{ old('family_income_bracket') == 'Below 10,000.00' ? 'selected' : '' }}>Below 10,000.00</option>
                                    <option value="10,001-30,000" {{ old('family_income_bracket') == '10,001-30,000' ? 'selected' : '' }}>10,001-30,000</option>
                                    <option value="Above 30,000.00" {{ old('family_income_bracket') == 'Above 30,000.00' ? 'selected' : '' }}>Above 30,000.00</option>
                                </select>
                                @error('family_income_bracket')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. of Siblings</label>
                                <input type="number" class="form-control @error('no_of_siblings') is-invalid @enderror" 
                                       name="no_of_siblings" value="{{ old('no_of_siblings') }}" min="0">
                                @error('no_of_siblings')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. of Siblings who are studying</label>
                                <input type="number" class="form-control @error('no_of_siblings_studying') is-invalid @enderror" 
                                       name="no_of_siblings_studying" value="{{ old('no_of_siblings_studying') }}" min="0">
                                @error('no_of_siblings_studying')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label class="form-label">Schools they are attending (separate with commas)</label>
                                <input type="text" class="form-control @error('siblings_schools') is-invalid @enderror" 
                                       name="siblings_schools" value="{{ old('siblings_schools') }}" placeholder="e.g., PMSI, PMSI">
                                @error('siblings_schools')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Guardian/Authorized Fetcher Information -->
                        <h6 class="fw-bold mb-3 mt-4 text-primary">Guardian/Authorized Fetcher Information (if student is not living with parents)</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Guardian's Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('guardian_name') is-invalid @enderror" 
                                       name="guardian_name" value="{{ old('guardian_name') }}">
                                @error('guardian_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Relation to student</label>
                                <input type="text" class="form-control @error('guardian_relation') is-invalid @enderror" 
                                       name="guardian_relation" value="{{ old('guardian_relation') }}" placeholder="e.g., Mother">
                                @error('guardian_relation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contact nos.</label>
                                    <input type="tel" class="form-control @error('guardian_contact_no') is-invalid @enderror" 
                                        name="guardian_contact_no" value="{{ old('guardian_contact_no') }}" placeholder="e.g., 0929 6736008" maxlength="11" inputmode="numeric" pattern="[0-9]*">
                                @error('guardian_contact_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('guardian_email') is-invalid @enderror" 
                                       name="guardian_email" value="{{ old('guardian_email') }}">
                                @error('guardian_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Authorized Fetcher</label>
                                <input type="text" class="form-control @error('authorized_fetcher') is-invalid @enderror" 
                                       name="authorized_fetcher" value="{{ old('authorized_fetcher') }}" placeholder="e.g., Butch Xavier / Margot Alvarez">
                                @error('authorized_fetcher')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label class="form-label">Relation to student</label>
                                <input type="text" class="form-control @error('authorized_fetcher_relation') is-invalid @enderror" 
                                       name="authorized_fetcher_relation" value="{{ old('authorized_fetcher_relation') }}" placeholder="e.g., Parents">
                                @error('authorized_fetcher_relation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Legacy fields for backward compatibility -->
                        <input type="hidden" name="parent_name" id="parent_name_auto" value="">
                        <input type="hidden" name="parent_email" id="parent_email_auto" value="">
                        <input type="hidden" name="parent_phone" id="parent_phone_auto" value="">
                        <input type="hidden" name="parent_relationship" id="parent_relationship_auto" value="">
                        
                        @php
                            $parentPortalRequiredGrades = ['Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
                            $parentPortalOptionalGrades = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
                            $initialGradeLevel = old('grade_level_applying_for', '');
                            $parentPortalIsRequired = in_array($initialGradeLevel, $parentPortalRequiredGrades, true);
                            $showParentPortalAccess = $parentPortalIsRequired || in_array($initialGradeLevel, $parentPortalOptionalGrades, true);
                        @endphp
                        <div class="row g-3 mt-4" id="parent-portal-access" style="{{ $showParentPortalAccess ? '' : 'display: none;' }}">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong id="parent-portal-heading">{{ $parentPortalIsRequired ? 'Parent Portal Access Required' : 'Parent Portal Access (Optional)' }}</strong>
                                </div>
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="create_parent_account" id="create_parent_account" value="1"
                                                   {{ $parentPortalIsRequired || ($showParentPortalAccess && old('create_parent_account') == '1') ? 'checked' : '' }}
                                                   {{ $parentPortalIsRequired ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="create_parent_account">
                                                <strong class="text-primary" id="parent-portal-checkbox-label">{{ $parentPortalIsRequired ? 'Create a parent/guardian account (required)' : 'Create a parent/guardian account (optional)' }}</strong>
                                            </label>
                                        </div>
                                        <div class="mt-2 ms-4">
                                            <p class="mb-2 text-muted">
                                                <small>
                                                    <i class="fas fa-check-circle text-success me-1"></i>
                                                    <span id="parent-portal-description">{{ $parentPortalIsRequired ? 'A parent portal account will be created where the parent/guardian can:' : 'Choose whether to create a parent portal account. When enabled, the parent/guardian can:' }}</span>
                                                </small>
                                            </p>
                                            <p class="mb-2 text-muted"><small id="parent-portal-contact-note">{{ $parentPortalIsRequired ? 'The account email is the Father\'s email. If that is blank, the Mother\'s email is used. If both are blank, the Guardian\'s email is used. Do not leave that name and email blank.' : 'If you create an account, its email is the Father\'s email, then the Mother\'s email, then the Guardian\'s email. Do not leave that name and email blank.' }}</small></p>
                                            <p class="mb-2 text-muted" id="parent-portal-student-note" style="{{ $parentPortalIsRequired ? '' : 'display: none;' }}"><small>Nursery through Grade 6 does not create a separate student login. This one parent account can manage every child enrolled in the application.</small></p>
                                            <ul class="mb-2" style="font-size: 0.875rem;">
                                                <li class="text-muted">Monitor their child's academic progress and grades</li>
                                                <li class="text-muted">View attendance records and schedules</li>
                                                <li class="text-muted">Receive school announcements and updates</li>
                                                <li class="text-muted">Communicate with teachers and school staff</li>
                                            </ul>
                                            <p class="mb-0">
                                                <small class="text-muted">
                                                    <i class="fas fa-key text-warning me-1"></i>
                                                    <strong>Login Credentials:</strong> After you submit, a temporary password will be shown once on the success page. Save it and change it after first login.
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('parent_name')
                            <div class="text-danger small mt-2" role="alert">{{ $message }}</div>
                        @enderror
                        @error('parent_email')
                            <div class="text-danger small mt-2" role="alert">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Step 4: Emergency Contact -->
            <div class="form-step" id="step4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Emergency Contact Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Provide an emergency contact person (can be different from parent/guardian)</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Emergency Contact Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                       name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required>
                                @error('emergency_contact_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Emergency Contact Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                        name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" required maxlength="11" inputmode="numeric" pattern="[0-9]*">
                                @error('emergency_contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This person will be contacted in case of emergencies when parent/guardian cannot be reached.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Academic Information -->
            <div class="form-step" id="step5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-school me-2"></i>Academic Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Review the subjects for every child in this application. Each child is shown with the subjects for that child's own grade.</p>
                        
                        <!-- Subjects Preview -->
                        <div class="mt-4" id="subjectsPreview" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3" id="subjectsPreviewHeading">
                                        <i class="fas fa-book text-success me-2"></i>Subjects for each child
                                    </h6>
                                    <div id="subjectsList"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Block Section Selection -->
            <div class="form-step" id="step6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Choose Your Block Section</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Block Section Assignment:</strong> Choose a section for each child. A section can be selected after its class schedule has been plotted for the current academic year.
                        </div>

                        <div id="sectionSelectionStatus" class="section-selection-status is-empty mb-4" role="status" aria-live="polite">
                            <div class="section-selection-status__icon">
                                <i class="fas fa-hand-pointer"></i>
                            </div>
                            <div>
                                <strong id="sectionSelectionStatusTitle">No section selected yet</strong>
                                <div class="small mb-0" id="sectionSelectionStatusText">Click a section card below to choose your preferred block section.</div>
                            </div>
                        </div>
                        
                        <div id="sectionsLoading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading sections...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading available sections for your grade level...</p>
                        </div>
                        
                        <div id="sectionsContainer" style="display: none;">
                            <div class="row g-3" id="sectionsList">
                                <!-- Sections will be loaded dynamically via JavaScript -->
                            </div>
                            <input type="hidden" name="selected_section_id" id="selected_section_id" value="">
                        </div>
                        
                        <div id="noSectionsMessage" style="display: none;">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>No sections available yet.</strong> Please select your grade level in the previous step first, or contact the registrar if you continue to see this message.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 7: Required Documents -->
            <div class="form-step" id="step7">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-upload me-2"></i>Supporting Documents <span class="badge bg-success ms-2">Optional</span></h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Document Requirements Info (Changes based on student category) -->
                        <div class="alert alert-info mb-4" id="documentInfoNew">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>New Student Documents:</strong> These uploads are optional. You can submit now and provide the documents later. The registrar can still approve the enrollment.
                        </div>

                        <div class="alert alert-success mb-4" id="documentInfoOld" style="display: none;">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Old Student Documents:</strong> Since you're a returning student, <strong>SF9 is NOT required</strong>. Only update documents if needed.
                        </div>

                        <div class="alert alert-warning mb-4" id="documentInfoTransferee" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Transferee Documents:</strong> <strong>SF9 is REQUIRED</strong> for transfer students. Please upload your SF9 from your previous school.
                        </div>
                        
                        <div class="alert alert-secondary mb-4">
                            <i class="fas fa-copy me-2"></i>
                            <strong>Optional for every child.</strong> Upload these files once for this application. If more than one child is enrolled, the same files are saved on each child. You can leave every file blank and still submit. The documents can be given to the registrar later.
                        </div>
                        <p class="text-muted mb-4">Accepted formats: PDF, JPG, JPEG, PNG. Maximum size: 5MB per file.</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-certificate text-primary me-2"></i>Birth Certificate
                                </label>
                                <input type="file" class="form-control @error('birth_certificate') is-invalid @enderror" 
                                       name="birth_certificate" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">NSO/PSA Birth Certificate</small>
                                @error('birth_certificate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- SF9 (Hidden for New Students, Required for Transferees) -->
                            @php
                                // Determine initial visibility based on type parameter
                                $initiallyHideSF9 = isset($type) && $type === 'new';
                                $sf9Required = isset($type) && $type === 'transferee';
                            @endphp
                            <div class="col-md-6" id="sf9Field" style="{{ $initiallyHideSF9 ? 'display: none;' : '' }}">
                                <label class="form-label">
                                    <i class="fas fa-file-alt text-primary me-2"></i>SF9 (Learner's Permanent Record)
                                    <span class="badge bg-danger" id="sf9RequiredBadge" style="display: {{ $sf9Required ? 'inline-block' : 'none' }};">Required</span>
                                    <span class="badge bg-secondary" id="sf9NotRequiredBadge" style="display: none;">Not Required for Old Students</span>
                                </label>
                                <input type="file" class="form-control @error('sf9') is-invalid @enderror" 
                                       name="sf9" id="sf9Input" accept=".pdf,.jpg,.jpeg,.png" {{ $sf9Required ? 'required' : '' }}>
                                <small class="text-muted">School Form 9 - Learner's Permanent Record from previous school</small>
                                @error('sf9')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-file-alt text-primary me-2"></i>SF10 (Report Card)
                                </label>
                                <input type="file" class="form-control @error('sf10') is-invalid @enderror" 
                                       name="sf10" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">School Form 10</small>
                                @error('sf10')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-award text-primary me-2"></i>Certificate of Good Moral Character
                                </label>
                                <input type="file" class="form-control @error('good_moral') is-invalid @enderror" 
                                       name="good_moral" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">From previous school</small>
                                @error('good_moral')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-image text-primary me-2"></i>ID Photo (2x2)
                                </label>
                                <input type="file" class="form-control @error('id_photo') is-invalid @enderror" 
                                       name="id_photo" accept=".jpg,.jpeg,.png">
                                <small class="text-muted">Recent 2x2 ID picture</small>
                                @error('id_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-id-card text-primary me-2"></i>Parent/Guardian ID
                                </label>
                                <input type="file" class="form-control @error('parent_guardian_id') is-invalid @enderror" 
                                       name="parent_guardian_id" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Valid government ID</small>
                                @error('parent_guardian_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 8: Enrollment Guidelines & Review -->
            <div class="form-step" id="step8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>STUDENT ADMISSION ENROLLMENT GUIDELINES</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="card bg-light mb-4" style="max-height: 500px; overflow-y: auto;">
                            <div class="card-body">
                                <h5 class="fw-bold mb-4 text-primary">I. STUDENT ADMISSION ENROLLMENT GUIDELINES</h5>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold">1. Enrollment Duration</h6>
                                    <p class="mb-2">Registration implies enrollment for the entire school year for elementary and secondary courses.</p>
                                    <small class="text-muted">(Section 62, sub-paragraph a - Manual of Regulations for Private Schools, 8th edition 1992)</small>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold">2. Official Enrollment Requirement</h6>
                                    <p class="mb-2">A student is not officially enrolled without presenting proper school credentials by the enrollment period's end.</p>
                                    <small class="text-muted">(Section 62, sub-paragraph d - Manual of Regulations for Private Schools, 8th edition 1992)</small>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold">3. Conditions for Official Enrollment</h6>
                                    <p class="mb-2">A student is officially enrolled after submitting credentials, making an initial payment, and being authorized to attend classes.</p>
                                    <small class="text-muted">(Section 62, sub-paragraph e - Manual of Regulations for Private Schools, 8th edition 1992)</small>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold">4. Withdrawal Policy</h6>
                                    <p class="mb-2">If a student withdraws within 2 weeks after classes start, and has paid for more than 1 month:</p>
                                    <ul class="mb-2">
                                        <li>They are charged <strong>10%</strong> of the total amount for the term if withdrawing in the <strong>1st week</strong></li>
                                        <li>They are charged <strong>20%</strong> if withdrawing in the <strong>2nd week</strong></li>
                                        <li>Charges apply regardless of attendance</li>
                                    </ul>
                                    <p class="mb-2">If withdrawal is due to a "justifiable reason," fees are charged only up to the last month of attendance.</p>
                                    <small class="text-muted">(Section 66. Tuition Charges - Manual of Regulations for Private Schools, 8th edition 1992)</small>
                                    
                                    <div class="mt-3 p-3 bg-white rounded border">
                                        <strong>"Justifiable reasons" acceptable to PMS are:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li><strong>CHANGE OF RESIDENCE:</strong> If the student and family relocate to another province or country (requires proof).</li>
                                            <li><strong>ILLNESS:</strong> If the student contracts an illness preventing studies or posing a threat to others (requires a medical certificate).</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold">5. Transfer of Student and Transfer Credentials</h6>
                                    <p class="mb-2">An enrolled student can transfer if they have no unsettled obligations with the school.</p>
                                    <small class="text-muted">(Section 71 - Manual of Regulations for Private Schools, 8th edition 1992)</small>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold">6. Withholding of Credentials</h6>
                                    <p class="mb-2">Transfer credentials may be withheld due to suspension, expulsion, non-payment of financial obligations, or property responsibility. They are released once obligations are settled or penalties lifted.</p>
                                    <small class="text-muted">(Section 72 - Manual of Regulations for Private Schools, 8th edition 1992)</small>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5 class="fw-bold mb-4 text-primary">II. PARENT - SCHOOL AGREEMENT</h5>
                                <div class="mb-4 p-3 bg-white rounded border">
                                    <p class="mb-3">
                                        <strong>As parent of / guardian of:</strong> 
                                        <span id="studentNameForAgreement" class="text-primary fw-bold"></span>
                                    </p>
                                    <p class="mb-0">
                                        <strong>I hereby agree to abide by the rules and regulations set by Panorama Montessori School, Inc.</strong>
                                    </p>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Name and Signature of parent/guardian <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('parent_signature_name') is-invalid @enderror" 
                                           name="parent_signature_name" id="parent_signature_name" 
                                           value="{{ old('parent_signature_name') }}" required
                                           placeholder="Enter your full name as signature">
                                    @error('parent_signature_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">By typing your name, you are providing your digital signature agreeing to the terms above.</small>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5 class="fw-bold mb-4 text-primary">IV. DATE OF FIRST ATTENDANCE</h5>
                                <div class="mb-4">
                                    <label class="form-label" for="date_of_first_attendance_display">Date of First Attendance</label>
                                    <input type="text" class="form-control" id="date_of_first_attendance_display" value="" readonly>
                                    <input type="hidden" name="date_of_first_attendance" id="date_of_first_attendance" value="{{ old('date_of_first_attendance') }}">
                                    <small class="text-muted">Filled automatically from the date and time enrolled.</small>
                                    <div id="firstAttendanceByChild" class="mt-2"></div>
                                    @error('date_of_first_attendance')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5 class="fw-bold mb-3 text-primary">V. DOCUMENTS SUBMITTED</h5>
                                <p class="text-muted small">These are checked automatically from the documents uploaded for the child.</p>
                                <p id="uploadedDocumentSummary" class="small mb-3"></p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="doc_submitted_form138" id="doc_submitted_form138" value="1" {{ old('doc_submitted_form138') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="doc_submitted_form138">
                                                Form138/SF9/Report Card
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="doc_submitted_psa_birth" id="doc_submitted_psa_birth" value="1" {{ old('doc_submitted_psa_birth') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="doc_submitted_psa_birth">
                                                PSA Birth Cert (Orig)
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="doc_submitted_form137" id="doc_submitted_form137" value="1" {{ old('doc_submitted_form137') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="doc_submitted_form137">
                                                Form137/SF10
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="doc_submitted_baptismal" id="doc_submitted_baptismal" value="1" {{ old('doc_submitted_baptismal') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="doc_submitted_baptismal">
                                                Baptismal Cert (Gr.3 only)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="doc_submitted_pic_1x1" id="doc_submitted_pic_1x1" value="1" {{ old('doc_submitted_pic_1x1') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="doc_submitted_pic_1x1">
                                                Recent Pic (1x1) 3 copies each
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="doc_submitted_pic_2x2" id="doc_submitted_pic_2x2" value="1" {{ old('doc_submitted_pic_2x2') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="doc_submitted_pic_2x2">
                                                Recent Pic (2x2) 2 copies each (For Grade 7 Only)
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="doc_submitted_itr" id="doc_submitted_itr" value="1" {{ old('doc_submitted_itr') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="doc_submitted_itr">
                                                ITR of Parents (If employed)
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="doc_submitted_unemployment" id="doc_submitted_unemployment" value="1" {{ old('doc_submitted_unemployment') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="doc_submitted_unemployment">
                                                Certification of Unemployment (if not employed)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" name="agree_terms" required>
                            <label class="form-check-label" for="agreeTerms">
                                <strong>I have read and agree to the Student Admission Enrollment Guidelines and Parent-School Agreement <span class="text-danger">*</span></strong>
                            </label>
                        </div>
                        
                        <div class="card border-primary mb-4" id="enrollmentFamilySummary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fas fa-users me-2"></i>Enrollment Summary</h6>
                                <p class="mb-1"><strong>Parent/Guardian:</strong> <span id="summaryParentName">Not saved yet</span></p>
                                <p class="mb-2"><strong>Children to be Enrolled:</strong></p>
                                <ol id="summaryChildrenList" class="mb-3"></ol>
                                <p class="mb-1"><strong>Parent Account:</strong> <span id="summaryParentCount">0</span> account</p>
                                <p class="mb-0"><strong>Student Records:</strong> <span id="summaryStudentCount">0</span> records</p>
                                <p class="small text-muted mb-0 mt-2">Draft records stay saved until you submit this application.</p>
                            </div>
                        </div>

                        <div class="alert alert-success">
                            <h6 class="fw-bold mb-3"><i class="fas fa-check-circle me-2"></i>Review Your Application</h6>
                            <p class="mb-2">Please review all information before submitting. Once submitted, you will receive a confirmation email with your application number and temporary login credentials.</p>
                            <p class="mb-0 small"><i class="fas fa-info-circle me-1"></i>You can check your application status and upload missing documents after submission by logging in to your account.</p>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="save_as_draft" id="saveAsDraft" value="0">
            <input type="hidden" name="enrollment_group_token" id="enrollmentGroupToken" value="{{ $enrollmentGroupToken }}">
            <input type="hidden" name="current_child_draft_id" id="currentChildDraftId" value="{{ old('current_child_draft_id', $currentDraftId ?? '') }}">
            <input type="hidden" name="enrollment_group_started" id="enrollmentGroupStarted" value="{{ old('enrollment_group_started', !empty($enrollmentGroupStarted) ? '1' : '0') }}">
            <input type="hidden" name="enrollment_parent_ready" id="enrollmentParentReady" value="{{ old('enrollment_parent_ready', '0') }}">

            <!-- Navigation Buttons -->
            <div class="step-navigation">
                <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                    <i class="fas fa-arrow-left me-2"></i>Previous
                </button>
                <div></div>
                <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                    Next<i class="fas fa-arrow-right ms-2"></i>
                </button>
                <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                </button>
            </div>
        </form>
    </div>
</div>

    @php
        $existingParentPhones = \App\Models\User::query()
            ->where('role_name', 'Parent')
            ->whereNotNull('phone_number')
            ->pluck('phone_number')
            ->map(fn ($phone) => preg_replace('/\D+/', '', (string) $phone))
            ->filter()->values()->all();

        $existingParentEmails = \App\Models\User::query()
            ->where('role_name', 'Parent')
            ->whereNotNull('email')
            ->pluck('email')
            ->map(fn ($email) => strtolower((string) $email))
            ->filter()->values()->all();
    @endphp

    <!-- Lightweight Custom Modals (No Bootstrap Modal JS) -->
<div id="siblingEnrollmentModal" class="custom-overlay" style="display: none;">
    <div class="custom-modal" style="max-width: 560px;">
        <div class="custom-modal-header custom-success">
            <h5><i class="fas fa-user-friends me-2"></i>Child Draft Saved</h5>
        </div>
        <div class="custom-modal-body" style="text-align: left;">
            <p id="siblingDraftConfirmation" class="mb-2 fw-semibold"></p>
            <ol id="siblingSavedChildren" class="mb-3"></ol>
            <p class="mb-0">Do you have another child to enroll?</p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="custom-btn custom-btn-primary" onclick="enrollAnotherChild()">
                <i class="fas fa-user-plus"></i>Yes, Enroll Another Child
            </button>
            <button type="button" class="custom-btn custom-btn-success" onclick="finishSiblingEnrollment()">
                <i class="fas fa-arrow-right"></i>No, Proceed to Step 3
            </button>
        </div>
    </div>
</div>

<div id="savedChildDraftsModal" class="custom-overlay" style="display: none;">
    <div class="custom-modal" style="max-width: 720px;">
        <div class="custom-modal-header custom-success">
            <h5><i class="fas fa-folder-open me-2"></i>Child Draft Saved</h5>
            <button type="button" class="custom-close-btn" onclick="closeModal('savedChildDraftsModal')">&times;</button>
        </div>
        <div class="custom-modal-body" style="text-align: left;">
            <p id="savedChildDraftsParent" class="mb-3"></p>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Grade</th>
                            <th>Birthdate</th>
                            <th>Application No.</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="savedChildDraftsBody"></tbody>
                </table>
            </div>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="custom-btn custom-btn-primary" onclick="closeModal('savedChildDraftsModal')">
                <i class="fas fa-check"></i>Close
            </button>
        </div>
    </div>
</div>

<div id="customAlertModal" class="custom-overlay" style="display: none;">
    <div class="custom-modal" style="max-width: 500px;">
        <div class="custom-modal-header" id="alertModalHeader">
            <h5 id="alertModalTitle"><i class="fas fa-info-circle me-2"></i>Notice</h5>
            <button type="button" class="custom-close-btn" onclick="closeModal('customAlertModal')">&times;</button>
        </div>
        <div class="custom-modal-body" style="white-space: pre-line; text-align: left;">
            <p id="alertModalMessage" style="font-size: 1rem; line-height: 1.6;"></p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="custom-btn custom-btn-primary" onclick="closeModal('customAlertModal')">
                <i class="fas fa-check me-2"></i>Got it!
            </button>
        </div>
    </div>
</div>

<div id="successModal" class="custom-overlay" style="display: none;">
    <div class="custom-modal" style="max-width: 600px;">
        <div class="custom-modal-header custom-success">
            <h5><i class="fas fa-check-circle me-2"></i>Success!</h5>
            <button type="button" class="custom-close-btn" onclick="closeModal('successModal')">&times;</button>
        </div>
        <div class="custom-modal-body" style="white-space: pre-line; text-align: left;">
            <p id="successModalMessage" style="font-size: 1rem; line-height: 1.6;"></p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="custom-btn custom-btn-success" id="successModalBtn" onclick="closeModal('successModal')">
                <i class="fas fa-check me-2"></i>Got it!
            </button>
        </div>
    </div>
</div>

<div id="errorModal" class="custom-overlay" style="display: none;">
    <div class="custom-modal" style="max-width: 500px;">
        <div class="custom-modal-header custom-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Attention Required</h5>
            <button type="button" class="custom-close-btn" onclick="closeModal('errorModal')">&times;</button>
        </div>
        <div class="custom-modal-body" style="white-space: pre-line; text-align: left;">
            <p id="errorModalMessage" style="font-size: 1rem; line-height: 1.6;"></p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="custom-btn custom-btn-danger" onclick="closeModal('errorModal')">
                <i class="fas fa-check me-2"></i>I understand
            </button>
        </div>
    </div>
</div>

<style>
.custom-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease;
}

.custom-modal {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

.custom-modal-header {
    padding: 20px 24px;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px 16px 0 0;
}

.custom-modal-header.custom-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-bottom-color: #059669;
}

.custom-modal-header.custom-error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border-bottom-color: #dc2626;
}

.custom-modal-header h5 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
}

.custom-close-btn {
    background: none;
    border: none;
    font-size: 2rem;
    line-height: 1;
    color: inherit;
    opacity: 0.7;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-close-btn:hover {
    opacity: 1;
    transform: scale(1.2);
}

.custom-modal-body {
    padding: 30px 24px;
    font-size: 1rem;
    line-height: 1.6;
}

.custom-modal-body p {
    margin: 0;
}

.custom-modal-footer {
    padding: 20px 24px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    border-radius: 0 0 16px 16px;
}

.custom-btn {
    padding: 12px 28px;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.custom-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.custom-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.custom-btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.custom-btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.custom-btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

.custom-btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<script>
// Initialize currentStep based on whether type is set
@if(isset($type) && $type !== null)
    let currentStep = 2; // Start at step 2 (Personal Information) when type is set
@else
    let currentStep = 1; // Start at step 1 (Student Category) normally
@endif
const totalSteps = 8; // Updated to 8 steps (added Block Section)
let studentCategory = '{{ isset($type) && $type !== null ? ($type === "new" ? "new_student" : ($type === "transferee" ? "transferee" : "new_student")) : "new_student" }}'; // Set category based on type
const siblingGradeLevels = ['Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
const enrollmentForm = document.getElementById('enrollmentForm');
const enrollmentServerRestore = @json($enrollmentServerRestore ?? ['draft_id' => '', 'group_started' => false, 'fields' => []]);
const enrollmentFormStorageKey = 'pmsEnrollmentForm';
let continueAfterParentStep = false;
let enrollmentPersistTimer = null;

function readStoredEnrollmentForm() {
    try {
        return JSON.parse(localStorage.getItem(enrollmentFormStorageKey) || 'null');
    } catch (error) {
        return null;
    }
}

function collectEnrollmentFields() {
    const data = {};
    const formData = new FormData(enrollmentForm);
    formData.forEach(function (value, key) {
        if (typeof value !== 'string' || key === '_token') return;
        data[key] = value;
    });
    enrollmentForm.querySelectorAll('input[type="checkbox"][name]').forEach(function (field) {
        data[field.name] = field.checked ? '1' : '0';
    });
    return data;
}

function persistEnrollmentForm() {
    if (!enrollmentForm) return;
    const payload = {
        token: document.getElementById('enrollmentGroupToken')?.value || '',
        draftId: document.getElementById('currentChildDraftId')?.value || '',
        groupStarted: document.getElementById('enrollmentGroupStarted')?.value || '0',
        step: currentStep,
        fields: collectEnrollmentFields(),
    };
    try {
        localStorage.setItem(enrollmentFormStorageKey, JSON.stringify(payload));
    } catch (error) {}
}

function schedulePersistEnrollmentForm() {
    clearTimeout(enrollmentPersistTimer);
    enrollmentPersistTimer = setTimeout(persistEnrollmentForm, 200);
}

function applyEnrollmentFields(fields, overwrite) {
    if (!fields) return;
    Object.keys(fields).forEach(function (name) {
        if (name === '_token' || name === 'enrollment_group_token') return;
        const nodes = enrollmentForm.querySelectorAll('[name="' + CSS.escape(name) + '"]');
        nodes.forEach(function (field) {
            if (field.type === 'file') return;
            const value = fields[name];
            if (value === null || value === undefined) return;
            if (field.type === 'radio') {
                field.checked = String(field.value) === String(value);
                return;
            }
            if (field.type === 'checkbox') {
                field.checked = value === true || value === 1 || value === '1' || value === 'on' || value === 'true';
                return;
            }
            if (!overwrite && field.value) return;
            field.value = value;
        });
    });
}

function applySavedEnrollmentState(stored) {
    if (!stored || !stored.fields) return;
    applyEnrollmentFields(stored.fields, true);
    if (stored.draftId !== undefined && stored.draftId !== null) {
        document.getElementById('currentChildDraftId').value = stored.draftId;
    }
    if (stored.groupStarted === '1') {
        document.getElementById('enrollmentGroupStarted').value = '1';
    }
    if (stored.step && Number(stored.step) !== currentStep && typeof goToWizardStep === 'function') {
        goToWizardStep(Number(stored.step));
    }
}

function showSiblingEnrollmentModal() {
    document.getElementById('siblingEnrollmentModal').style.display = 'flex';
}

function goToWizardStep(step) {
    document.querySelectorAll('.form-step').forEach(element => element.classList.remove('active'));
    document.querySelectorAll('.wizard-step').forEach(element => element.classList.remove('active'));
    currentStep = step;
    document.getElementById('step' + step)?.classList.add('active');
    if (step === 2 && typeof refreshSavedChildDraftButton === 'function') {
        refreshSavedChildDraftButton();
    }
    document.querySelector(`.wizard-step[data-step="${step}"]`)?.classList.add('active');
    updateButtons();
    document.querySelector('.wizard-steps')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function postEnrollmentWizardData(url) {
    const formData = new FormData(enrollmentForm);
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': enrollmentForm.querySelector('input[name="_token"]').value,
        },
        body: formData,
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        const validationMessage = payload.errors ? Object.values(payload.errors).flat()[0] : null;
        throw new Error(validationMessage || payload.message || 'Unable to save enrollment information. Please try again.');
    }

    return payload;
}

function renderSiblingSavedList(children) {
    const list = document.getElementById('siblingSavedChildren');
    if (!list) return;
    list.innerHTML = '';
    (children || []).forEach(function (child) {
        const item = document.createElement('li');
        item.textContent = child.name + ' – ' + child.grade_level + ' (Draft)';
        list.appendChild(item);
    });
}

async function saveChildDraftBeforeNext() {
    const nextButton = document.getElementById('nextBtn');
    nextButton.disabled = true;
    try {
        const result = await postEnrollmentWizardData('{{ route('enrollment.portal.drafts.child') }}');
        document.getElementById('currentChildDraftId').value = result.draft_id;
        document.getElementById('enrollmentGroupStarted').value = '1';
        document.getElementById('enrollmentParentReady').value = result.parent_ready ? '1' : '0';
        const confirmation = document.getElementById('siblingDraftConfirmation');
        if (confirmation) {
            confirmation.textContent = (result.saved_child || 'This child') + ' was saved as a draft.';
        }
        renderSiblingSavedList(result.children || []);
        persistEnrollmentForm();
        showSiblingEnrollmentModal();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        nextButton.disabled = false;
    }
}

async function saveParentAndContinue() {
    const nextButton = document.getElementById('nextBtn');
    if (typeof updateParentFields === 'function') updateParentFields();
    const parentName = document.getElementById('parent_name_auto')?.value.trim() || '';
    const parentEmail = document.getElementById('parent_email_auto')?.value.trim() || '';
    if (!parentName || !parentEmail) {
        showAlert('Enter the name and email for the parent account. Use the Father\'s name and email. If the father\'s email is blank, use the Mother\'s. If the student is not living with the parents, use the Guardian\'s name and email.', 'error');
        return;
    }
    nextButton.disabled = true;
    try {
        await postEnrollmentWizardData('{{ route('enrollment.portal.drafts.parent') }}');
        document.getElementById('enrollmentParentReady').value = '1';
        persistEnrollmentForm();
        continueAfterParentStep = false;
        changeStep(1, true);
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        nextButton.disabled = false;
    }
}

function enrollAnotherChild() {
    closeModal('siblingEnrollmentModal');
    const keptForNextChild = [
        'address',
        'address_lot_block_village',
        'address_barangay_district',
        'address_city_municipality',
        'religion',
        'citizenship',
    ];
    document.querySelectorAll('#step2 input:not([type="hidden"]), #step2 select, #step2 textarea').forEach(field => {
        if (keptForNextChild.includes(field.name)) {
            field.classList.remove('is-invalid');
            return;
        }
        if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = false;
        } else {
            field.value = '';
        }
        field.classList.remove('is-invalid');
    });
    document.getElementById('currentChildDraftId').value = '';
    updateStudentContactFields('');
    refreshSavedChildDraftButton();
    persistEnrollmentForm();
    fetch('{{ route('enrollment.portal.drafts.next-child') }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': enrollmentForm.querySelector('input[name="_token"]').value,
        },
        body: JSON.stringify({
            enrollment_group_token: document.getElementById('enrollmentGroupToken').value,
        }),
    }).catch(function () {});
    goToWizardStep(2);
}

function refreshSavedChildDraftButton() {
    const wrap = document.getElementById('viewChildDraftsWrap');
    const intro = document.getElementById('studentInfoIntro');
    if (!wrap) return;
    const hasDrafts = document.getElementById('enrollmentGroupStarted')?.value === '1';
    wrap.style.display = hasDrafts ? 'flex' : 'none';
    if (intro) intro.style.display = hasDrafts ? 'none' : '';
}

(function restoreEnrollmentFormNow() {
    const server = enrollmentServerRestore || {};
    if (server.group_started) {
        document.getElementById('enrollmentGroupStarted').value = '1';
    }
    if (server.draft_id) {
        document.getElementById('currentChildDraftId').value = server.draft_id;
    }
    applyEnrollmentFields(server.fields || {}, false);
    const pageToken = document.getElementById('enrollmentGroupToken')?.value || '';
    const stored = readStoredEnrollmentForm();
    if (stored && stored.token === pageToken) {
        applySavedEnrollmentState(stored);
    }
    updateStudentContactFields(document.getElementById('grade_level_applying_for')?.value || '');
    if (document.getElementById('date_of_birth')?.value && typeof calculateAge === 'function') {
        calculateAge();
    }
    refreshSavedChildDraftButton();
    syncFirstAttendanceFromEnrollment();
})();

async function resumeEnrollmentFormAfterRefresh() {
    const pageToken = document.getElementById('enrollmentGroupToken')?.value || '';
    const stored = readStoredEnrollmentForm();
    if (!stored || !stored.token || stored.token === pageToken) {
        return;
    }

    let hasDrafts = false;
    let checked = false;
    try {
        const response = await fetch('{{ route('enrollment.portal.drafts.children') }}?enrollment_group_token=' + encodeURIComponent(stored.token), {
            headers: { 'Accept': 'application/json' },
        });
        const payload = await response.json();
        checked = response.ok;
        hasDrafts = response.ok && (payload.children || []).length > 0;
    } catch (error) {
        checked = false;
    }

    if (!checked || !hasDrafts) {
        if (checked) {
            try { localStorage.removeItem(enrollmentFormStorageKey); } catch (error) {}
        }
        return;
    }

    if (sessionStorage.getItem('enrollmentResumeToken') !== stored.token) {
        sessionStorage.setItem('enrollmentResumeToken', stored.token);
        const url = new URL(window.location.href);
        url.searchParams.set('enrollment_group_token', stored.token);
        window.location.replace(url.toString());
    }
}

async function openSavedChildDrafts() {
    const token = document.getElementById('enrollmentGroupToken')?.value || '';
    const body = document.getElementById('savedChildDraftsBody');
    const parentLine = document.getElementById('savedChildDraftsParent');
    body.innerHTML = '<tr><td colspan="6">Loading saved children...</td></tr>';
    document.getElementById('savedChildDraftsModal').style.display = 'flex';
    try {
        const response = await fetch('{{ route('enrollment.portal.drafts.children') }}?enrollment_group_token=' + encodeURIComponent(token), {
            headers: { 'Accept': 'application/json' },
        });
        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'Unable to load the saved children.');
        }
        const children = payload.children || [];
        parentLine.textContent = payload.parent && payload.parent.name
            ? 'Parent/Guardian: ' + payload.parent.name
            : 'Parent account is saved with these children when parent information is completed.';
        body.innerHTML = '';
        if (children.length === 0) {
            body.innerHTML = '<tr><td colspan="6">No child drafts have been saved yet.</td></tr>';
            return;
        }
        children.forEach(function (child) {
            const row = document.createElement('tr');
            [child.name, child.grade_level, child.date_of_birth || '—', child.application_number || '—', child.status || 'Draft'].forEach(function (value) {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.appendChild(cell);
            });
            const action = document.createElement('td');
            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'btn btn-sm btn-outline-primary';
            editButton.innerHTML = '<i class="fas fa-pen me-1"></i>Edit';
            editButton.addEventListener('click', function () {
                editSavedChildDraft(child.id, editButton);
            });
            action.appendChild(editButton);
            row.appendChild(action);
            body.appendChild(row);
        });
    } catch (error) {
        body.innerHTML = '';
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 6;
        cell.textContent = error.message;
        row.appendChild(cell);
        body.appendChild(row);
    }
}

async function editSavedChildDraft(draftId, button) {
    const token = document.getElementById('enrollmentGroupToken')?.value || '';
    if (button) button.disabled = true;
    try {
        const response = await fetch('{{ route('enrollment.portal.drafts.edit') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': enrollmentForm.querySelector('input[name="_token"]').value,
            },
            body: JSON.stringify({
                enrollment_group_token: token,
                draft_id: draftId,
            }),
        });
        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'Unable to open this child for editing.');
        }

        document.querySelectorAll('#step2 input:not([type="hidden"]), #step2 select, #step2 textarea').forEach(function (field) {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = false;
            } else {
                field.value = '';
            }
            field.classList.remove('is-invalid');
        });
        applyEnrollmentFields(payload.fields || {}, true);
        document.getElementById('currentChildDraftId').value = payload.draft_id;
        document.getElementById('enrollmentGroupStarted').value = '1';
        const gradeLevel = document.getElementById('grade_level_applying_for')?.value || '';
        updateStudentContactFields(gradeLevel);
        if (document.getElementById('date_of_birth')?.value && typeof calculateAge === 'function') {
            calculateAge();
        }
        if (typeof updateParentPortalAccess === 'function') {
            updateParentPortalAccess(gradeLevel);
        }
        syncFirstAttendanceFromEnrollment();
        persistEnrollmentForm();
        closeModal('savedChildDraftsModal');
        goToWizardStep(2);
    } catch (error) {
        if (button) button.disabled = false;
        showAlert(error.message, 'error');
    }
}

function finishSiblingEnrollment() {
    closeModal('siblingEnrollmentModal');
    continueAfterParentStep = true;
    goToWizardStep(3);
}

function renderEnrollmentSummary(parent, children) {
    const parentName = document.getElementById('summaryParentName');
    const list = document.getElementById('summaryChildrenList');
    const parentCount = document.getElementById('summaryParentCount');
    const studentCount = document.getElementById('summaryStudentCount');
    if (!list || !parentName || !parentCount || !studentCount) return;

    parentName.textContent = parent && parent.name ? parent.name : 'Not saved yet';
    if (parent && typeof parent.account_count !== 'undefined') {
        parentCount.textContent = String(parent.account_count);
    } else {
        parentCount.textContent = parent && parent.name ? '1' : '0';
    }
    list.innerHTML = '';
    (children || []).forEach(function (child) {
        const item = document.createElement('li');
        item.textContent = child.name + ' – ' + child.grade_level;
        list.appendChild(item);
    });
    if (!children || children.length === 0) {
        const item = document.createElement('li');
        const first = document.querySelector('input[name="first_name"]')?.value || '';
        const last = document.querySelector('input[name="last_name"]')?.value || '';
        const grade = document.getElementById('grade_level_applying_for')?.value || '';
        item.textContent = ((first + ' ' + last).trim() || 'Current student') + (grade ? ' – ' + grade : '');
        list.appendChild(item);
        studentCount.textContent = '1';
    } else {
        studentCount.textContent = String(children.length);
    }

    const agreementName = document.getElementById('studentNameForAgreement');
    if (agreementName && children && children.length) {
        agreementName.textContent = children.map(function (child) {
            return child.name + ' (' + child.grade_level + ')';
        }).join(', ');
    }
}

async function loadEnrollmentSummary() {
    const grade = document.getElementById('grade_level_applying_for')?.value || '';
    const groupStarted = document.getElementById('enrollmentGroupStarted')?.value === '1';
    if (!groupStarted && !siblingGradeLevels.includes(grade)) {
        const checkbox = document.getElementById('create_parent_account');
        const parentName = document.getElementById('parent_name_auto')?.value.trim() || '';
        renderEnrollmentSummary(
            checkbox && checkbox.checked ? { name: parentName || 'Parent/Guardian', account_count: 1 } : null,
            []
        );
        if (checkbox && !checkbox.checked) {
            document.getElementById('summaryParentCount').textContent = '0';
        }
        return;
    }

    const token = document.getElementById('enrollmentGroupToken')?.value || '';
    try {
        const response = await fetch('{{ route('enrollment.portal.drafts.children') }}?enrollment_group_token=' + encodeURIComponent(token), {
            headers: { 'Accept': 'application/json' },
        });
        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'Unable to load the saved children.');
        }
        renderEnrollmentSummary(payload.parent, payload.children || []);
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// Simple, reliable modal functions (NO Bootstrap dependency!)
function showAlert(message, type = 'info') {
    console.log(`📢 ${type.toUpperCase()}: ${message}`);
    
    // Close any existing modals first
    closeAllModals();
    
    let modalId;
    
    if (type === 'error') {
        document.getElementById('errorModalMessage').textContent = message;
        modalId = 'errorModal';
    } else if (type === 'success') {
        document.getElementById('successModalMessage').textContent = message;
        modalId = 'successModal';
    } else {
        document.getElementById('alertModalMessage').textContent = message;
        modalId = 'customAlertModal';
    }
    
    // Simple display - just change display property
    const modalEl = document.getElementById(modalId);
    modalEl.style.display = 'flex';
    
    console.log('✅ Modal shown:', modalId);
}

function closeModal(modalId) {
    console.log('🔴 Closing modal:', modalId);
    const modalEl = document.getElementById(modalId);
    
    if (!modalEl) {
        console.error('Modal not found:', modalId);
        return;
    }
    
    // Super simple close - just hide it
    modalEl.style.display = 'none';
    
    console.log('✅ Modal closed:', modalId);
}

function closeAllModals() {
    console.log('🚨 Closing all modals');
    
    // Hide all custom overlays
    const overlays = document.querySelectorAll('.custom-overlay');
    overlays.forEach(overlay => {
        overlay.style.display = 'none';
    });
    
    console.log('✅ All modals closed');
}

function showSuccessWithAction(message, action) {
    document.getElementById('successModalMessage').textContent = message;
    
    // Set action for Continue button
    const continueBtn = document.getElementById('successModalBtn');
    continueBtn.onclick = function() {
        closeModal('successModal');
        if (action && typeof action === 'function') {
            action();
        }
    };
    
    // Simple display
    const modalEl = document.getElementById('successModal');
    modalEl.style.display = 'flex';
    
    console.log('✅ Success modal shown with action');
}

// Handle student category selection
function selectCategory(category) {
    studentCategory = category;
    console.log('📋 Selected category:', category);
    
    // Update radio button
    document.getElementById(category).checked = true;
    
    // Reset old student verification if switching away from old_student
    if (category !== 'old_student') {
        oldStudentVerified = false;
        // Remove existing_student_id if exists
        const existingInput = document.querySelector('input[name="existing_student_id"]');
        if (existingInput) {
            existingInput.remove();
        }
    }
    
    // Show/hide old student login section
    const loginSection = document.getElementById('oldStudentLoginSection');
    if (category === 'old_student') {
        loginSection.style.display = 'block';
    } else {
        loginSection.style.display = 'none';
    }
    
    // Update wizard step title to show selected category
    const stepTitle = document.querySelector('.wizard-step[data-step="1"] .wizard-step-title');
    let categoryText = '';
    if (category === 'new_student') {
        categoryText = 'Student Category (New)';
    } else if (category === 'old_student') {
        categoryText = 'Student Category (Old)';
    } else if (category === 'transferee') {
        categoryText = 'Student Category (Transferee)';
    }
    stepTitle.textContent = categoryText;
    
    // Update document requirements display
    updateDocumentRequirements(category);
}

// Update document requirements based on category
function updateDocumentRequirements(category) {
    // Hide all info alerts
    document.getElementById('documentInfoNew').style.display = 'none';
    document.getElementById('documentInfoOld').style.display = 'none';
    document.getElementById('documentInfoTransferee').style.display = 'none';
    
    const sf9Field = document.getElementById('sf9Field');
    const sf9Input = document.getElementById('sf9Input');
    const sf9RequiredBadge = document.getElementById('sf9RequiredBadge');
    const sf9NotRequiredBadge = document.getElementById('sf9NotRequiredBadge');
    
    // Show relevant alert and handle SF9 field
    if (category === 'new_student') {
        document.getElementById('documentInfoNew').style.display = 'block';
        // HIDE SF9 field entirely for new students and disable it
        sf9Field.style.display = 'none';
        sf9RequiredBadge.style.display = 'none';
        sf9NotRequiredBadge.style.display = 'none';
        sf9Input.removeAttribute('required');
        sf9Input.setAttribute('disabled', 'disabled'); // Disable so it won't be submitted
        sf9Input.value = ''; // Clear any value
    } else if (category === 'old_student') {
        document.getElementById('documentInfoOld').style.display = 'block';
        // SHOW SF9 field but disabled for old students
        sf9Field.style.display = 'block';
        sf9RequiredBadge.style.display = 'none';
        sf9NotRequiredBadge.style.display = 'inline-block';
        sf9Input.removeAttribute('required');
        sf9Input.setAttribute('disabled', 'disabled');
    } else if (category === 'transferee') {
        document.getElementById('documentInfoTransferee').style.display = 'block';
        // SHOW SF9 field and make it required for transferees
        sf9Field.style.display = 'block';
        sf9RequiredBadge.style.display = 'inline-block';
        sf9NotRequiredBadge.style.display = 'none';
        sf9Input.removeAttribute('disabled');
        sf9Input.setAttribute('required', 'required');
    }
}

// Verify old student credentials
let oldStudentVerified = false;

function verifyOldStudent() {
    const email = document.getElementById('old_student_email').value;
    const password = document.getElementById('old_student_password').value;
    
    if (!email || !password) {
        showAlert('Please enter both email and password to verify your account.', 'error');
        return;
    }
    
    console.log('🔍 Verifying old student:', email);
    
    // Show loading
    const btn = event.target;
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
    
    // AJAX call to verify student
    fetch('{{ route("enrollment.verify-old-student") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Verification successful:', data.student);
            
            // Mark as verified
            oldStudentVerified = true;
            
            // Pre-fill form with student data
            document.querySelector('input[name="first_name"]').value = data.student.first_name || '';
            document.querySelector('input[name="last_name"]').value = data.student.last_name || '';
            document.querySelector('input[name="middle_name"]').value = data.student.middle_name || '';
            document.querySelector('input[name="date_of_birth"]').value = data.student.date_of_birth || '';
            document.querySelector('select[name="gender"]').value = data.student.gender || '';
            document.querySelector('input[name="email"]').value = data.student.email || '';
            document.querySelector('input[name="phone_number"]').value = data.student.phone_number || '';
            document.querySelector('textarea[name="address"]').value = data.student.address || '';
            document.querySelector('input[name="parent_name"]').value = data.student.parent_name || '';
            document.querySelector('input[name="parent_email"]').value = data.student.parent_email || '';
            document.querySelector('input[name="parent_phone"]').value = data.student.parent_phone || '';
            document.querySelector('input[name="parent_relationship"]').value = data.student.parent_relationship || '';
            document.querySelector('input[name="emergency_contact_name"]').value = data.student.emergency_contact_name || '';
            document.querySelector('input[name="emergency_contact_phone"]').value = data.student.emergency_contact_phone || '';
            document.querySelector('input[name="previous_school"]').value = data.student.previous_school || '';
            document.querySelector('select[name="grade_level_applying_for"]').value = data.student.year_level || '';
            updateStudentContactFields(data.student.year_level || '');
            updateParentPortalAccess(data.student.year_level || '');
            
            // Store student ID for re-enrollment
            let hiddenInput = document.querySelector('input[name="existing_student_id"]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'existing_student_id';
                document.getElementById('enrollmentForm').appendChild(hiddenInput);
            }
            hiddenInput.value = data.student.id;
            
            // Update button to show success
            btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Verified Successfully!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            btn.disabled = true;
            
            // Show success message in login section
            const loginSection = document.getElementById('oldStudentLoginSection');
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success mt-3';
            successAlert.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                <strong>✅ Verification Successful!</strong>
                <p class="mb-0 mt-2">Welcome back, ${data.student.first_name}! Your information has been pre-filled. Click "Next" to review and proceed with re-enrollment.</p>
            `;
            loginSection.appendChild(successAlert);
            
            // Disable email and password fields
            document.getElementById('old_student_email').disabled = true;
            document.getElementById('old_student_password').disabled = true;
            document.getElementById('toggleOldStudentPassword').disabled = true;
            
            // Show success modal with auto-proceed option
            showSuccessWithAction(
                `Welcome back, ${data.student.first_name}! Your information has been pre-filled. Click "Continue" to review and proceed with re-enrollment.`,
                function() {
                    changeStep(1); // Auto-proceed to next step
                }
            );
            
        } else {
            // Show error
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            
            // Show error modal
            showAlert(data.message || 'Invalid credentials. Please check your email and password.', 'error');
            
            // Also show inline error alert
            const loginSection = document.getElementById('oldStudentLoginSection');
            const existingError = loginSection.querySelector('.alert-danger');
            if (existingError) {
                existingError.remove();
            }
            
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger mt-3';
            errorAlert.innerHTML = `
                <i class="fas fa-times-circle me-2"></i>
                <strong>Verification Failed</strong>
                <p class="mb-0">${data.message || 'Invalid credentials. Please check your email and password.'}</p>
            `;
            loginSection.appendChild(errorAlert);
            
            // Remove error after 5 seconds
            setTimeout(() => {
                errorAlert.remove();
            }, 5000);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        console.error('❌ Error:', error);
        showAlert('Error verifying student. Please try again.', 'error');
    });
}

// Toggle old student password visibility
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleOldStudentPassword');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const passwordInput = document.getElementById('old_student_password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
    
    // Initialize category on page load
    const selectedCategory = document.querySelector('input[name="student_category"]:checked');
    const enrollmentType = document.getElementById('enrollmentType');
    
    if (enrollmentType) {
        // If enrollment_type is set from URL parameter, use it
        const type = enrollmentType.value; // 'new' or 'transferee'
        let category = type === 'new' ? 'new_student' : (type === 'transferee' ? 'transferee' : 'new_student');
        studentCategory = category;
        
        console.log('📋 Initialized with enrollment type:', type, '-> category:', category);
        console.log('📋 Current step:', currentStep);
        
        // Update document requirements for the selected category
        updateDocumentRequirements(category);
        
        // Update step 1 title in wizard to show the category
        const step1Title = document.querySelector('.wizard-step[data-step="1"] .wizard-step-title');
        if (step1Title) {
            step1Title.textContent = type === 'new' ? 'Student Category (New)' : 'Student Category (Transferee)';
        }
        
        // Mark step 1 as completed in the wizard
        const step1Wizard = document.querySelector('.wizard-step[data-step="1"]');
        if (step1Wizard) {
            step1Wizard.classList.add('completed');
            step1Wizard.classList.remove('active');
        }
        
        // Activate step 2 in the wizard (Personal Information)
        const step2Wizard = document.querySelector('.wizard-step[data-step="2"]');
        if (step2Wizard) {
            step2Wizard.classList.add('active');
            step2Wizard.classList.remove('completed');
        }
        
        // Update navigation buttons
        updateButtons();
    } else if (selectedCategory) {
        // Otherwise, use the selected radio button
        selectCategory(selectedCategory.value);
    }
    
    // Add ESC key handler for modals (Enhanced)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            console.log('🔑 ESC key pressed - closing all modals');
            // Close any open modal
            closeModal('errorModal');
            closeModal('successModal');
            closeModal('customAlertModal');
        }
    });
    
    // Add ENTER key handler for modal buttons
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            // Check if a modal is open
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const modalId = openModal.getAttribute('id');
                console.log('⏎ ENTER pressed in modal:', modalId);
                closeModal(modalId);
                e.preventDefault();
            }
        }
    });
    
    // Add click-outside-modal handler
    ['customAlertModal', 'successModal', 'errorModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal(modalId);
                }
            });
        }
    });
});

// Live catalog loaders (admin subjects/sections) — always cache-bust
const enrollmentGradeOrder = ['Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];

async function enrollmentChildrenForSubjects(fallbackGrade) {
    const token = document.getElementById('enrollmentGroupToken')?.value || '';
    const groupStarted = document.getElementById('enrollmentGroupStarted')?.value === '1';
    let children = [];

    if (groupStarted && token) {
        try {
            const response = await fetch('{{ route('enrollment.portal.drafts.children') }}?enrollment_group_token=' + encodeURIComponent(token), {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
            });
            const payload = await response.json();
            if (response.ok) {
                children = (payload.children || [])
                    .filter(function (child) { return child.grade_level; })
                    .map(function (child) {
                        return {
                            id: child.id,
                            name: child.name || 'Saved child',
                            grade_level: child.grade_level,
                            preferred_section_id: child.preferred_section_id || '',
                            date_enrolled: child.date_enrolled || '',
                            time_enrolled: child.time_enrolled || '',
                        };
                    });
            }
        } catch (error) {
            children = [];
        }
    }

    const openDraftId = document.getElementById('currentChildDraftId')?.value || '';
    const formFirst = document.querySelector('#step2 input[name="first_name"]')?.value.trim() || '';
    const formLast = document.querySelector('#step2 input[name="last_name"]')?.value.trim() || '';
    const formName = [formFirst, formLast].filter(Boolean).join(' ');
    if (openDraftId) {
        const openChild = children.find(function (child) { return String(child.id) === String(openDraftId); });
        if (openChild && fallbackGrade) {
            openChild.grade_level = fallbackGrade;
            if (formName) openChild.name = formName;
        } else if (!openChild && fallbackGrade) {
            children.push({
                id: openDraftId,
                name: formName || 'This student',
                grade_level: fallbackGrade,
                preferred_section_id: '',
            });
        }
    } else if (fallbackGrade && children.length === 0) {
        children.push({
            id: '',
            name: formName || 'This student',
            grade_level: fallbackGrade,
            preferred_section_id: '',
        });
    }

    children.sort(function (left, right) {
        const gradeCompare = enrollmentGradeOrder.indexOf(left.grade_level) - enrollmentGradeOrder.indexOf(right.grade_level);
        if (gradeCompare !== 0) return gradeCompare;
        return String(left.name).localeCompare(String(right.name));
    });

    return children;
}

function renderSubjectBadges(subjects) {
    let html = '<div class="row">';
    subjects.forEach(function (subject) {
        const name = escapeHtml(subject.name || subject);
        html += `
            <div class="col-md-4 col-sm-6 mb-2">
                <div class="badge bg-success fs-6 p-2 w-100 text-start">
                    <i class="fas fa-book me-2"></i>${name}
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

async function loadEnrollmentSubjects(gradeLevel) {
    const subjectsPreview = document.getElementById('subjectsPreview');
    const subjectsList = document.getElementById('subjectsList');
    const heading = document.getElementById('subjectsPreviewHeading');
    if (!subjectsPreview || !subjectsList) return;

    const children = await enrollmentChildrenForSubjects(gradeLevel);
    if (children.length === 0) {
        subjectsPreview.style.display = 'none';
        subjectsList.innerHTML = '';
        return;
    }

    if (heading) {
        heading.innerHTML = children.length > 1
            ? '<i class="fas fa-book text-success me-2"></i>Subjects for each enrolled child'
            : '<i class="fas fa-book text-success me-2"></i>Subjects for ' + escapeHtml(children[0].grade_level);
    }

    subjectsPreview.style.display = 'block';
    subjectsList.innerHTML = `
        <div class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin me-2"></i>Loading subjects for each child...
        </div>
    `;

    const grades = [...new Set(children.map(function (child) { return child.grade_level; }))];
    const catalogs = {};
    await Promise.all(grades.map(async function (grade) {
        try {
            const response = await fetch('/enrollment-portal/get-subjects/' + encodeURIComponent(grade) + '?_=' + Date.now(), {
                cache: 'no-store',
                headers: { 'Accept': 'application/json' },
            });
            catalogs[grade] = await response.json();
        } catch (error) {
            catalogs[grade] = { success: false, message: 'Unable to load subjects. Please try again.', subjects: [] };
        }
    }));

    subjectsList.innerHTML = children.map(function (child) {
        const catalog = catalogs[child.grade_level] || {};
        const subjects = catalog.subjects || [];
        let body;
        if (!catalog.success || subjects.length === 0) {
            body = `
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${escapeHtml(catalog.message || 'No subjects are available for this grade yet. Please contact the school.')}
                </div>
            `;
        } else {
            body = renderSubjectBadges(subjects) + `
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Total: ${subjects.length} subjects for ${escapeHtml(child.grade_level)}
                    </small>
                </div>
            `;
        }

        return `
            <div class="border rounded p-3 mb-3 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h6 class="fw-bold mb-0">${escapeHtml(child.name)}</h6>
                    <span class="badge bg-primary">${escapeHtml(child.grade_level)}</span>
                </div>
                ${body}
            </div>
        `;
    }).join('');
}

document.getElementById('grade_level_applying_for').addEventListener('change', function() {
    const selectedGrade = this.value;
    updateStudentContactFields(selectedGrade);
    loadEnrollmentSubjects(selectedGrade);
    if (typeof loadSections === 'function') {
        loadSections(selectedGrade);
    }
    updateParentPortalAccess(selectedGrade);
});

function updateParentPortalAccess(gradeLevel) {
    const requiredGrades = ['Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
    const optionalGrades = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
    const panel = document.getElementById('parent-portal-access');
    const checkbox = document.getElementById('create_parent_account');
    const heading = document.getElementById('parent-portal-heading');
    const checkboxLabel = document.getElementById('parent-portal-checkbox-label');
    const description = document.getElementById('parent-portal-description');
    const contactNote = document.getElementById('parent-portal-contact-note');
    const studentNote = document.getElementById('parent-portal-student-note');
    const accessRequired = requiredGrades.includes(gradeLevel);
    const accessOptional = optionalGrades.includes(gradeLevel);

    if (!panel || !checkbox || !heading || !checkboxLabel || !description || !contactNote) {
        return;
    }
    panel.style.display = accessRequired || accessOptional ? '' : 'none';
    if (studentNote) {
        studentNote.style.display = accessRequired ? '' : 'none';
    }
    checkbox.disabled = !accessOptional;
    if (accessRequired) {
        checkbox.checked = true;
    } else if (!accessOptional) {
        checkbox.checked = false;
    }

    heading.textContent = accessRequired ? 'Parent Portal Access Required' : 'Parent Portal Access (Optional)';
    checkboxLabel.textContent = accessRequired
        ? 'Create a parent/guardian account (required)'
        : 'Create a parent/guardian account (optional)';
    description.textContent = accessRequired
        ? 'A parent portal account will be created where the parent/guardian can:'
        : 'Choose whether to create a parent portal account. When enabled, the parent/guardian can:';
    contactNote.textContent = accessRequired
        ? 'The account email is the Father\'s email. If that is blank, the Mother\'s email is used. If both are blank, the Guardian\'s email is used. Do not leave that name and email blank.'
        : 'If you create an account, its email is the Father\'s email, then the Mother\'s email, then the Guardian\'s email. Do not leave that name and email blank.';
}

const gradeLevelSelect = document.getElementById('grade_level_applying_for');
const optionalParentPortalGrades = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
let previousGradeAllowsOptionalParentPortal = optionalParentPortalGrades.includes(gradeLevelSelect.value);
updateParentPortalAccess(gradeLevelSelect.value);
updateStudentContactFields(gradeLevelSelect.value);
gradeLevelSelect.addEventListener('change', function() {
    const nextGradeAllowsOptionalParentPortal = optionalParentPortalGrades.includes(this.value);
    if (nextGradeAllowsOptionalParentPortal && !previousGradeAllowsOptionalParentPortal) {
        document.getElementById('create_parent_account').checked = false;
    }
    updateStudentContactFields(this.value);
    updateParentPortalAccess(this.value);
    previousGradeAllowsOptionalParentPortal = nextGradeAllowsOptionalParentPortal;
});

// Calculate age from date of birth
function calculateAge() {
    const dateOfBirth = document.getElementById('date_of_birth').value;
    const ageYears = document.getElementById('age_years');
    const ageMonths = document.getElementById('age_months');
    
    if (dateOfBirth) {
        const birthDate = new Date(dateOfBirth);
        const today = new Date();
        
        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();
        
        if (months < 0) {
            years--;
            months += 12;
        }
        
        if (today.getDate() < birthDate.getDate()) {
            months--;
            if (months < 0) {
                years--;
                months += 12;
            }
        }
        
        ageYears.value = years;
        ageMonths.value = months;
    }
}

// Auto-fill parent fields from father/mother
function updateParentFields() {
    const fatherFirst = document.querySelector('input[name="father_first_name"]')?.value.trim() || '';
    const fatherLast = document.querySelector('input[name="father_last_name"]')?.value.trim() || '';
    const fatherEmail = document.querySelector('input[name="father_email"]')?.value.trim() || '';
    const fatherContact = document.querySelector('input[name="father_contact_no"]')?.value.trim() || '';
    
    const motherFirst = document.querySelector('input[name="mother_first_name"]')?.value.trim() || '';
    const motherLast = document.querySelector('input[name="mother_last_name"]')?.value.trim() || '';
    const motherEmail = document.querySelector('input[name="mother_email"]')?.value.trim() || '';
    const motherContact = document.querySelector('input[name="mother_contact_no"]')?.value.trim() || '';

    const guardianName = document.querySelector('input[name="guardian_name"]')?.value.trim() || '';
    const guardianEmail = document.querySelector('input[name="guardian_email"]')?.value.trim() || '';
    const guardianContact = document.querySelector('input[name="guardian_contact_no"]')?.value.trim() || '';
    
    let parentName = '';
    let parentRelationship = '';
    if (fatherFirst || fatherLast) {
        parentName = [fatherLast, fatherFirst].filter(Boolean).join(', ');
        parentRelationship = 'Father';
    } else if (motherFirst || motherLast) {
        parentName = [motherLast, motherFirst].filter(Boolean).join(', ');
        parentRelationship = 'Mother';
    } else if (guardianName) {
        parentName = guardianName;
        parentRelationship = 'Guardian';
    }
    document.getElementById('parent_name_auto').value = parentName;
    document.getElementById('parent_email_auto').value = fatherEmail || motherEmail || guardianEmail;
    document.getElementById('parent_phone_auto').value = fatherContact || motherContact || guardianContact;
    document.getElementById('parent_relationship_auto').value = parentRelationship;
}

// Event listeners for birthdate calculation
document.addEventListener('DOMContentLoaded', function() {
    const birthMonth = document.getElementById('birth_month');
    const birthDate = document.getElementById('birth_date');
    const birthYear = document.getElementById('birth_year');
    const dateOfBirth = document.getElementById('date_of_birth');
    
    if (birthMonth) birthMonth.addEventListener('change', updateBirthdate);
    if (birthDate) birthDate.addEventListener('change', updateBirthdate);
    if (birthYear) birthYear.addEventListener('change', updateBirthdate);
    if (dateOfBirth) dateOfBirth.addEventListener('change', calculateAge);
    const dateEnrolled = document.getElementById('date_enrolled');
    const timeEnrolled = document.getElementById('time_enrolled');
    if (dateEnrolled) dateEnrolled.addEventListener('change', syncFirstAttendanceFromEnrollment);
    document.querySelectorAll('#step7 input[type="file"]').forEach(function (input) {
        input.addEventListener('change', syncSubmittedDocumentChecks);
    });
    if (timeEnrolled) {
        timeEnrolled.addEventListener('change', syncFirstAttendanceFromEnrollment);
        timeEnrolled.addEventListener('input', syncFirstAttendanceFromEnrollment);
    }
    enrollmentForm.addEventListener('input', schedulePersistEnrollmentForm);
    enrollmentForm.addEventListener('change', schedulePersistEnrollmentForm);
    enrollmentForm.addEventListener('submit', function (event) {
        if (event.defaultPrevented || document.getElementById('saveAsDraft')?.value === '1') return;
        try { localStorage.removeItem(enrollmentFormStorageKey); } catch (error) {}
    });
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    resumeEnrollmentFormAfterRefresh();
    
    // Auto-fill parent fields when father/mother fields change
    const parentFields = ['father_first_name', 'father_last_name', 'father_email', 'father_contact_no', 
                          'mother_first_name', 'mother_last_name', 'mother_email', 'mother_contact_no',
                          'guardian_name', 'guardian_email', 'guardian_contact_no'];
    parentFields.forEach(fieldName => {
        const field = document.querySelector(`input[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('blur', updateParentFields);
        }
    });
    
    // Initial calculations
    if (dateOfBirth && dateOfBirth.value) {
        calculateAge();
    }
    if (covidVaccinated) {
        toggleCovidFields();
    }
    
    // Auto-fill student name in agreement section
    function updateStudentNameInAgreement() {
        const firstName = document.querySelector('input[name="first_name"]')?.value || '';
        const middleName = document.querySelector('input[name="middle_name"]')?.value || '';
        const lastName = document.querySelector('input[name="last_name"]')?.value || '';
        const studentNameElement = document.getElementById('studentNameForAgreement');
        
        if (studentNameElement) {
            let fullName = lastName;
            if (firstName) {
                fullName += ', ' + firstName;
            }
            if (middleName) {
                fullName += ' ' + middleName.charAt(0) + '.';
            }
            studentNameElement.textContent = fullName || 'Student Name';
        }
        
        // Auto-fill parent signature name if not already filled
        const parentSignatureName = document.getElementById('parent_signature_name');
        if (parentSignatureName && !parentSignatureName.value) {
            const fatherFirst = document.querySelector('input[name="father_first_name"]')?.value || '';
            const fatherLast = document.querySelector('input[name="father_last_name"]')?.value || '';
            const motherFirst = document.querySelector('input[name="mother_first_name"]')?.value || '';
            const motherLast = document.querySelector('input[name="mother_last_name"]')?.value || '';
            
            if (fatherFirst || fatherLast) {
                parentSignatureName.value = (fatherLast + ', ' + fatherFirst).trim().replace(/^,\s*|,\s*$/g, '');
            } else if (motherFirst || motherLast) {
                parentSignatureName.value = (motherLast + ', ' + motherFirst).trim().replace(/^,\s*|,\s*$/g, '');
            }
        }
    }
    
    // Update student name when name fields change
    ['first_name', 'middle_name', 'last_name'].forEach(fieldName => {
        const field = document.querySelector(`input[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('blur', updateStudentNameInAgreement);
            field.addEventListener('change', updateStudentNameInAgreement);
        }
    });
    
    // Initial update
    updateStudentNameInAgreement();
});

function formatEnrolledAttendance(dateValue, timeValue) {
    if (!dateValue) return '';
    const parts = String(dateValue).split('-');
    if (parts.length < 3) return String(dateValue);
    let text = parts[1] + '/' + parts[2] + '/' + parts[0];
    if (!timeValue) return text;
    const timeParts = String(timeValue).split(':');
    let hour = parseInt(timeParts[0], 10);
    if (Number.isNaN(hour)) return text;
    const minute = timeParts[1] || '00';
    const suffix = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12 || 12;
    return text + ' ' + hour + ':' + minute + ' ' + suffix;
}

const submittedDocumentMap = [
    { file: 'birth_certificate', checkbox: 'doc_submitted_psa_birth', label: 'PSA Birth Certificate' },
    { file: 'sf9', checkbox: 'doc_submitted_form138', label: 'SF9 / Report Card' },
    { file: 'sf10', checkbox: 'doc_submitted_form137', label: 'SF10' },
    { file: 'id_photo', checkbox: 'doc_submitted_pic_2x2', label: 'ID Photo (2x2)' },
    { file: 'good_moral', checkbox: '', label: 'Certificate of Good Moral Character' },
    { file: 'parent_guardian_id', checkbox: '', label: 'Parent/Guardian ID' },
];

function syncSubmittedDocumentChecks() {
    const uploaded = [];
    submittedDocumentMap.forEach(function (item) {
        const input = document.querySelector('#step7 input[name="' + item.file + '"]');
        const hasFile = Boolean(input && !input.disabled && input.files && input.files.length > 0);
        if (item.checkbox && input && !input.disabled) {
            const box = document.getElementById(item.checkbox);
            if (box) box.checked = hasFile;
        }
        if (hasFile) {
            uploaded.push(item.label + ': ' + input.files[0].name);
        }
    });
    const summary = document.getElementById('uploadedDocumentSummary');
    if (!summary) return;
    if (uploaded.length === 0) {
        summary.textContent = 'No documents uploaded yet.';
        return;
    }
    summary.textContent = 'Uploaded for each child: ' + uploaded.join('; ');
}

function syncFirstAttendanceFromEnrollment() {
    const dateValue = document.getElementById('date_enrolled')?.value || '';
    const timeValue = document.getElementById('time_enrolled')?.value || '';
    const attendance = document.getElementById('date_of_first_attendance');
    const display = document.getElementById('date_of_first_attendance_display');
    if (attendance) attendance.value = dateValue;
    if (display) display.value = formatEnrolledAttendance(dateValue, timeValue);
}

async function showFirstAttendanceForChildren() {
    syncFirstAttendanceFromEnrollment();
    const box = document.getElementById('firstAttendanceByChild');
    if (!box) return;
    const children = await enrollmentChildrenForSubjects(document.getElementById('grade_level_applying_for')?.value || '');
    const openId = document.getElementById('currentChildDraftId')?.value || '';
    const dateValue = document.getElementById('date_enrolled')?.value || '';
    const timeValue = document.getElementById('time_enrolled')?.value || '';
    children.forEach(function (child) {
        if (!child.id) {
            child.date_enrolled = dateValue;
            child.time_enrolled = timeValue;
        } else if (String(child.id) === String(openId)) {
            child.date_enrolled = dateValue;
            child.time_enrolled = timeValue;
        }
    });
    const listed = children.filter(function (child) { return child.name && (child.date_enrolled || child.time_enrolled); });
    if (listed.length < 2) {
        box.innerHTML = '';
        return;
    }
    box.innerHTML = '<ul class="mb-0 ps-3">' + listed.map(function (child) {
        const when = formatEnrolledAttendance(child.date_enrolled, child.time_enrolled) || 'No enrollment date entered';
        return '<li><strong>' + escapeHtml(child.name) + ':</strong> ' + escapeHtml(when) + '</li>';
    }).join('') + '</ul>';
}

function changeStep(direction, skipDraftSave = false) {
    // Validate current step before moving forward
    if (direction === 1 && !validateStep(currentStep)) {
        return false;
    }

    if (!skipDraftSave && direction === 1 && currentStep === 2) {
        const gradeLevel = document.getElementById('grade_level_applying_for').value;
        const siblingGroupStarted = document.getElementById('enrollmentGroupStarted').value === '1';
        if (siblingGradeLevels.includes(gradeLevel) || siblingGroupStarted) {
            saveChildDraftBeforeNext();
            return false;
        }
    }

    if (!skipDraftSave && direction === 1 && currentStep === 3
        && document.getElementById('enrollmentGroupStarted').value === '1') {
        saveParentAndContinue();
        return false;
    }
    
    // Hide current step
    document.getElementById('step' + currentStep).classList.remove('active');
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.remove('active');
    
    // Mark completed steps
    if (direction === 1) {
        document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.add('completed');
    }
    
    // Update step number
    currentStep += direction;
    
    // Show new step
    document.getElementById('step' + currentStep).classList.add('active');
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.add('active');

    // Always refresh live catalog when entering subjects / sections steps
    const gradeNow = document.getElementById('grade_level_applying_for')?.value || '';
    if (currentStep === 8 && typeof loadEnrollmentSummary === 'function') {
        loadEnrollmentSummary();
    }
    if (currentStep === 5 && typeof loadEnrollmentSubjects === 'function') {
        loadEnrollmentSubjects(gradeNow);
    }
    if (currentStep === 6 && typeof loadSections === 'function') {
        loadSections(gradeNow);
    }
    
    // Update student name in agreement if step 8
    if (currentStep === 8) {
        showFirstAttendanceForChildren();
        syncSubmittedDocumentChecks();
        const firstName = document.querySelector('input[name="first_name"]')?.value || '';
        const middleName = document.querySelector('input[name="middle_name"]')?.value || '';
        const lastName = document.querySelector('input[name="last_name"]')?.value || '';
        const studentNameElement = document.getElementById('studentNameForAgreement');
        
        if (studentNameElement) {
            let fullName = lastName;
            if (firstName) {
                fullName += ', ' + firstName;
            }
            if (middleName) {
                fullName += ' ' + middleName.charAt(0) + '.';
            }
            studentNameElement.textContent = fullName || 'Student Name';
        }
        
        // Auto-fill parent signature name if not already filled
        const parentSignatureName = document.getElementById('parent_signature_name');
        if (parentSignatureName && !parentSignatureName.value) {
            const fatherFirst = document.querySelector('input[name="father_first_name"]')?.value || '';
            const fatherLast = document.querySelector('input[name="father_last_name"]')?.value || '';
            const motherFirst = document.querySelector('input[name="mother_first_name"]')?.value || '';
            const motherLast = document.querySelector('input[name="mother_last_name"]')?.value || '';
            
            if (fatherFirst || fatherLast) {
                parentSignatureName.value = (fatherLast + ', ' + fatherFirst).trim().replace(/^,\s*|,\s*$/g, '');
            } else if (motherFirst || motherLast) {
                parentSignatureName.value = (motherLast + ', ' + motherFirst).trim().replace(/^,\s*|,\s*$/g, '');
            }
        }
    }
    
    // Update buttons
    updateButtons();
    persistEnrollmentForm();
    
    // Scroll to wizard steps (not to the very top)
    const wizardSteps = document.querySelector('.wizard-steps');
    if (wizardSteps) {
        wizardSteps.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        // Fallback: scroll to portal content with offset
        window.scrollTo({ 
            top: 200, 
            behavior: 'smooth' 
        });
    }
}

function updateButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Show/hide previous button
    prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-block';
    
    // Show/hide next and submit buttons
    if (currentStep === totalSteps) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'inline-block';
    } else {
        nextBtn.style.display = 'inline-block';
        submitBtn.style.display = 'none';
    }
}

function validateStep(step) {
    // Special validation for Step 1 (Student Category)
    if (step === 1) {
        const selectedCategory = document.querySelector('input[name="student_category"]:checked');
        
        if (!selectedCategory) {
            showAlert('Please select a student category to continue.', 'error');
            return false;
        }
        
        // If old student is selected, check if they've verified
        if (selectedCategory.value === 'old_student') {
            const existingStudentId = document.querySelector('input[name="existing_student_id"]');
            if (!existingStudentId || !existingStudentId.value) {
                showAlert('Please verify your credentials by clicking "Verify & Continue" button before proceeding.', 'error');
                return false;
            }
            console.log('✅ Old student verified, proceeding...');
        }
        
        return true;
    }

    // Step 6: require a block section when options are shown
    if (step === 6) {
        const childGroups = document.querySelectorAll('[data-child-section]');
        if (childGroups.length > 1) {
            const missing = [];
            const unscheduled = [];
            childGroups.forEach(function (group) {
                const input = group.querySelector('input[name^="child_sections["]');
                const name = group.getAttribute('data-child-name') || 'a child';
                const selectable = group.querySelector('.section-choice-card:not(.is-full):not(.is-unscheduled)');
                const anyCard = group.querySelector('.section-choice-card');
                if (anyCard && !selectable) {
                    unscheduled.push(name);
                } else if (selectable && (!input || !input.value)) {
                    missing.push(name);
                }
            });
            if (unscheduled.length) {
                showAlert('Enrollment unavailable for ' + unscheduled.join(', ') + '. The schedule for the selected section has not been configured yet. Please wait until the class schedule has been finalized before proceeding with enrollment.', 'error');
                return false;
            }
            if (missing.length) {
                showAlert('Choose a block section for ' + missing.join(', ') + '. Each child uses the sections for their own grade.', 'error');
                return false;
            }
            return true;
        }

        const sectionsVisible = document.getElementById('sectionsContainer')
            && document.getElementById('sectionsContainer').style.display !== 'none';
        const noSections = document.getElementById('noSectionsMessage')
            && document.getElementById('noSectionsMessage').style.display !== 'none';
        if (sectionsVisible && !noSections) {
            const selectable = document.querySelector('#sectionsList .section-choice-card:not(.is-full):not(.is-unscheduled)');
            const anyCard = document.querySelector('#sectionsList .section-choice-card');
            if (anyCard && !selectable) {
                showAlert('Enrollment unavailable. The schedule for this section has not been configured yet. Please wait until the class schedule has been finalized before proceeding with enrollment.', 'error');
                updateSectionSelectionStatus(false);
                return false;
            }
            const chosen = document.getElementById('selected_section_id');
            if (!chosen || !chosen.value) {
                showAlert('Please select a block section before continuing. Look for the blue “Your Choice” badge on the card you pick.', 'error');
                updateSectionSelectionStatus(false);
                return false;
            }
        }
        return true;
    }
    
    const currentStepElement = document.getElementById('step' + step);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (field.type === 'radio') {
            const radioGroup = currentStepElement.querySelectorAll(`[name="${field.name}"]`);
            const isChecked = Array.from(radioGroup).some(radio => radio.checked);
            if (!isChecked) {
                isValid = false;
                // Highlight the radio group container
                radioGroup[0].closest('.card-body').classList.add('border-danger');
                setTimeout(() => {
                    radioGroup[0].closest('.card-body').classList.remove('border-danger');
                }, 3000);
            }
        } else if (field.type === 'checkbox') {
            if (!field.checked) {
                isValid = false;
                field.classList.add('is-invalid');
                setTimeout(() => {
                    field.classList.remove('is-invalid');
                }, 3000);
            }
        } else if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            setTimeout(() => {
                field.classList.remove('is-invalid');
            }, 3000);
        }
    });
    
    if (isValid && step === 2) {
        const dob = document.getElementById('date_of_birth');
        if (dob && dob.value) {
            const selected = new Date(dob.value + 'T00:00:00');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (Number.isNaN(selected.getTime()) || selected >= today) {
                dob.classList.add('is-invalid');
                showAlert('Date of birth must be a valid date before today.', 'error');
                return false;
            }
        }
    }

    if (isValid && step === 3 && parentAccountNeeded()) {
        if (typeof updateParentFields === 'function') updateParentFields();
        const parentName = document.getElementById('parent_name_auto')?.value.trim() || '';
        const parentEmail = document.getElementById('parent_email_auto')?.value.trim() || '';
        if (!parentName || !parentEmail) {
            showAlert('Enter the name and email for the parent account. Use the Father\'s name and email. If the father\'s email is blank, use the Mother\'s. If the student is not living with the parents, use the Guardian\'s name and email.', 'error');
            return false;
        }
    }

    if (!isValid) {
        showAlert('⚠️ Please complete all required fields\n\nWe noticed some required information is missing. Please fill in all fields marked with (*) before continuing to the next step.', 'error');
    }
    
    return isValid;
}

function parentAccountNeeded() {
    const gradeLevel = document.getElementById('grade_level_applying_for')?.value || '';
    const checkbox = document.getElementById('create_parent_account');
    return siblingGradeLevels.includes(gradeLevel) || Boolean(checkbox && checkbox.checked);
}

// Form submission validation
document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
    syncFirstAttendanceFromEnrollment();
    syncSubmittedDocumentChecks();
    console.log('📝 Form submission started');
    console.log('📝 Student category:', studentCategory);
    
    // Check if terms are agreed
    const agreeTerms = document.getElementById('agreeTerms');
    if (!agreeTerms.checked) {
        e.preventDefault();
        showAlert('📋 Terms and Conditions Required\n\nPlease read and accept the Terms and Conditions to complete your enrollment application.\n\nThis is required to protect both you and the school.', 'error');
        return false;
    }
    
    // Documents are optional - the registrar can approve applications even without documents
    console.log('✅ Form validation passed, submitting...');
    
    // Let the form submit naturally
    return true;
});

// Load sections based on grade level
let selectedSectionId = null;
let selectedSectionName = null;
let sectionsLoadedForGrade = null;

function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function updateSectionSelectionStatus(isSelected, sectionName) {
    const box = document.getElementById('sectionSelectionStatus');
    const title = document.getElementById('sectionSelectionStatusTitle');
    const text = document.getElementById('sectionSelectionStatusText');
    const icon = box ? box.querySelector('.section-selection-status__icon i') : null;
    if (!box || !title || !text) return;

    if (isSelected) {
        box.classList.remove('is-empty');
        box.classList.add('is-selected');
        title.textContent = 'Selected: ' + sectionName;
        text.textContent = 'This section will be used when you submit your application. You can change it anytime before submitting.';
        if (icon) icon.className = 'fas fa-check-circle';
    } else {
        box.classList.add('is-empty');
        box.classList.remove('is-selected');
        title.textContent = 'No section selected yet';
        text.textContent = 'Click a section card below to choose your preferred block section.';
        if (icon) icon.className = 'fas fa-hand-pointer';
    }
}

function applySectionSelectionUI() {
    document.querySelectorAll('.section-choice-card').forEach(function (card) {
        const id = String(card.getAttribute('data-section-id') || '');
        const btn = card.querySelector('.section-select-btn');
        const badge = card.querySelector('.section-select-badge');
        const isSelected = selectedSectionId != null && String(selectedSectionId) === id;

        card.classList.toggle('is-selected', isSelected);
        card.setAttribute('aria-pressed', isSelected ? 'true' : 'false');

        if (btn && !btn.disabled) {
            if (isSelected) {
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-primary');
                btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Selected';
            } else {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-success');
                btn.innerHTML = '<i class="fas fa-hand-pointer me-2"></i>Select This Section';
            }
        }

        if (badge) {
            badge.style.display = isSelected ? 'inline-flex' : 'none';
        }
    });

    updateSectionSelectionStatus(!!selectedSectionId, selectedSectionName || '');
}

async function loadSections(gradeLevel) {
    const children = await enrollmentChildrenForSubjects(gradeLevel);
    const savedChildren = children.filter(function (child) { return child.id; });
    if (savedChildren.length > 1) {
        await loadFamilySections(savedChildren);
        return;
    }
    loadSingleGradeSections(children[0]?.grade_level || gradeLevel);
}

function loadSingleGradeSections(gradeLevel) {
    if (!gradeLevel) {
        document.getElementById('sectionsLoading').style.display = 'none';
        document.getElementById('sectionsContainer').style.display = 'none';
        document.getElementById('noSectionsMessage').style.display = 'block';
        return;
    }
    
    console.log('🔍 Loading sections for grade level:', gradeLevel);

    // Only clear selection when the grade actually changes
    if (sectionsLoadedForGrade && sectionsLoadedForGrade !== gradeLevel) {
        selectedSectionId = null;
        selectedSectionName = null;
        const hidden = document.getElementById('selected_section_id');
        if (hidden) hidden.value = '';
        updateSectionSelectionStatus(false);
    }
    
    // Show loading
    document.getElementById('sectionsLoading').style.display = 'block';
    document.getElementById('sectionsContainer').style.display = 'none';
    document.getElementById('noSectionsMessage').style.display = 'none';
    
    // Fetch sections via AJAX
    fetch(`/enrollment-portal/get-sections/${encodeURIComponent(gradeLevel)}?_=${Date.now()}`, {
        cache: 'no-store',
        headers: { 'Accept': 'application/json' }
    })
        .then(response => response.json())
        .then(data => {
            console.log('📦 Sections received:', data);
            sectionsLoadedForGrade = gradeLevel;
            
            if (data.success && data.sections && data.sections.length > 0) {
                // Drop stale selection if that section is no longer in the list
                if (selectedSectionId != null) {
                    const stillThere = data.sections.some(function (s) {
                        return String(s.id) === String(selectedSectionId) && s.available_spots !== 0 && s.enrollment_allowed !== false;
                    });
                    if (!stillThere) {
                        selectedSectionId = null;
                        selectedSectionName = null;
                        const hidden = document.getElementById('selected_section_id');
                        if (hidden) hidden.value = '';
                    }
                }
                renderSections(data.sections);
                document.getElementById('sectionsLoading').style.display = 'none';
                document.getElementById('sectionsContainer').style.display = 'block';
            } else {
                selectedSectionId = null;
                selectedSectionName = null;
                const hidden = document.getElementById('selected_section_id');
                if (hidden) hidden.value = '';
                updateSectionSelectionStatus(false);
                document.getElementById('sectionsLoading').style.display = 'none';
                document.getElementById('noSectionsMessage').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('❌ Error loading sections:', error);
            document.getElementById('sectionsLoading').style.display = 'none';
            document.getElementById('noSectionsMessage').style.display = 'block';
        });
}

function sectionIsScheduleLocked(section) {
    return section.enrollment_allowed === false;
}

function sectionScheduleDetails(section) {
    const year = escapeHtml(section.academic_year || 'Current year');
    const label = escapeHtml(section.schedule_label || 'Not Plotted');
    const locked = sectionIsScheduleLocked(section);
    const reasons = {
        incomplete: 'The schedule for this section is incomplete.',
        conflict: 'This section has unresolved schedule conflicts.',
        not_finalized: 'The schedule for this section has not been finalized.',
        subjects_missing: 'This section does not have its subjects assigned yet.'
    };
    const badgeClass = locked ? 'bg-warning text-dark' : 'bg-success';
    const reason = reasons[section.schedule_status]
        || 'The schedule for this section has not been configured or finalized yet. Please wait until the class schedule has been finalized before proceeding with enrollment.';
    return `
        <p class="mb-2">
            <i class="fas fa-calendar-alt me-1 text-primary"></i>
            <strong>Academic Year:</strong> ${year}
        </p>
        <p class="mb-2">
            <strong>Schedule Status:</strong>
            <span class="badge ${badgeClass}">${label}</span>
        </p>
        ${locked ? `<div class="alert alert-warning py-2 px-3 small mb-3"><strong>Enrollment unavailable.</strong> ${reason}</div>` : ''}
    `;
}

function renderSections(sections) {
    const sectionsList = document.getElementById('sectionsList');
    sectionsList.innerHTML = '';
    
    sections.forEach(section => {
        const isFull = section.available_spots === 0;
        const scheduleLocked = sectionIsScheduleLocked(section);
        const locked = isFull || scheduleLocked;
        const safeName = escapeHtml(section.name);
        const safeGrade = escapeHtml(section.grade_level);
        const safeAdviser = escapeHtml(section.adviser || '');
        const safeDesc = escapeHtml(section.description || '');
        const sectionCard = `
            <div class="col-md-6">
                <div class="card section-choice-card h-100 ${isFull ? 'is-full' : ''} ${scheduleLocked ? 'is-unscheduled' : ''}"
                     data-section-id="${section.id}"
                     data-section-name="${safeName}"
                     role="button"
                     tabindex="${locked ? '-1' : '0'}"
                     aria-pressed="false"
                     style="cursor: ${locked ? 'not-allowed' : 'pointer'}; opacity: ${locked ? '0.65' : '1'};">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">
                                    <i class="fas fa-chalkboard me-2 text-primary"></i>${safeName}
                                </h5>
                                <p class="text-muted mb-0"><small>Grade Level: ${safeGrade}</small></p>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1">
                                <span class="badge section-select-badge" style="display:none;">
                                    <i class="fas fa-check me-1"></i>Your Choice
                                </span>
                                ${isFull ? '<span class="badge bg-danger">Full</span>' : (scheduleLocked ? '' : '<span class="badge bg-success">Available</span>')}
                            </div>
                        </div>
                        
                        ${section.adviser ? `
                            <p class="mb-2">
                                <i class="fas fa-user-tie me-1 text-primary"></i>
                                <strong>Adviser:</strong> ${safeAdviser}
                            </p>
                        ` : `
                            <p class="mb-2 text-muted">
                                <i class="fas fa-user-tie me-1"></i>
                                <strong>Adviser:</strong> To be assigned
                            </p>
                        `}

                        ${sectionScheduleDetails(section)}
                        
                        <p class="mb-3">
                            <i class="fas fa-users me-1 text-info"></i>
                            <strong>Capacity:</strong> ${section.available_spots} / ${section.capacity} spots available
                        </p>
                        
                        ${section.description ? `<p class="text-muted mb-3"><small>${safeDesc}</small></p>` : ''}
                        
                        ${!locked ? `
                            <button type="button" class="btn btn-outline-success w-100 section-select-btn">
                                <i class="fas fa-hand-pointer me-2"></i>Select This Section
                            </button>
                        ` : `
                            <button type="button" class="btn btn-secondary w-100 section-select-btn" disabled>
                                <i class="fas fa-times-circle me-2"></i>${scheduleLocked ? 'Enrollment Unavailable' : 'Section is Full'}
                            </button>
                        `}
                    </div>
                </div>
            </div>
        `;
        sectionsList.innerHTML += sectionCard;
    });

    sectionsList.querySelectorAll('.section-choice-card:not(.is-full):not(.is-unscheduled)').forEach(function (card) {
        const pick = function () {
            selectSection(
                parseInt(card.getAttribute('data-section-id'), 10),
                card.getAttribute('data-section-name') || 'Section'
            );
        };
        card.addEventListener('click', function (e) {
            if (e.target.closest('button')) return;
            pick();
        });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                pick();
            }
        });
        const btn = card.querySelector('.section-select-btn');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                pick();
            });
        }
    });

    applySectionSelectionUI();
}

function selectSection(sectionId, sectionName) {
    selectedSectionId = sectionId;
    selectedSectionName = sectionName;
    document.getElementById('selected_section_id').value = sectionId;

    applySectionSelectionUI();

    console.log('✅ Selected section:', sectionId, sectionName);
    showAlert(
        'Section selected: ' + sectionName + '\n\nLook for the blue “Your Choice” badge and the status bar above the cards. You can change this anytime before submitting.',
        'success'
    );
}

async function loadFamilySections(children) {
    const loading = document.getElementById('sectionsLoading');
    const container = document.getElementById('sectionsContainer');
    const empty = document.getElementById('noSectionsMessage');
    const sectionsList = document.getElementById('sectionsList');
    loading.style.display = 'block';
    container.style.display = 'none';
    empty.style.display = 'none';
    sectionsList.innerHTML = '';

    const grades = [...new Set(children.map(function (child) { return child.grade_level; }))];
    const catalogs = {};
    await Promise.all(grades.map(async function (grade) {
        try {
            const response = await fetch('/enrollment-portal/get-sections/' + encodeURIComponent(grade) + '?_=' + Date.now(), {
                cache: 'no-store',
                headers: { 'Accept': 'application/json' },
            });
            catalogs[grade] = await response.json();
        } catch (error) {
            catalogs[grade] = { success: false, sections: [] };
        }
    }));

    const openDraftId = document.getElementById('currentChildDraftId')?.value || '';
    sectionsList.innerHTML = children.map(function (child) {
        const sections = (catalogs[child.grade_level] && catalogs[child.grade_level].sections) || [];
        const savedSection = child.preferred_section_id ? String(child.preferred_section_id) : '';
        return `
            <div class="col-12" data-child-section="${escapeHtml(child.id)}" data-child-name="${escapeHtml(child.name)}">
                <div class="border rounded p-3 mb-3 bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h6 class="fw-bold mb-0">${escapeHtml(child.name)}</h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary">${escapeHtml(child.grade_level)}</span>
                            <span class="small text-muted child-section-status">${savedSection ? 'Section saved' : 'No section selected yet'}</span>
                        </div>
                    </div>
                    ${sections.length ? '<div class="row g-3 child-section-cards"></div>' : `
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No sections are available for ${escapeHtml(child.grade_level)} yet.
                        </div>
                    `}
                    <input type="hidden" name="child_sections[${escapeHtml(child.id)}]" value="${escapeHtml(savedSection)}">
                </div>
            </div>
        `;
    }).join('');

    children.forEach(function (child) {
        const group = sectionsList.querySelector('[data-child-section="' + child.id + '"]');
        const cardRow = group ? group.querySelector('.child-section-cards') : null;
        const sections = (catalogs[child.grade_level] && catalogs[child.grade_level].sections) || [];
        if (!group || !cardRow) return;
        sections.forEach(function (section) {
            cardRow.insertAdjacentHTML('beforeend', familySectionCard(section));
        });
        cardRow.querySelectorAll('.section-choice-card:not(.is-full):not(.is-unscheduled)').forEach(function (card) {
            const pick = function () {
                selectChildSection(
                    child.id,
                    card.getAttribute('data-section-id'),
                    card.getAttribute('data-section-name') || 'Section',
                    String(child.id) === String(openDraftId)
                );
            };
            card.addEventListener('click', function (event) {
                if (event.target.closest('button')) return;
                pick();
            });
            card.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    pick();
                }
            });
            const button = card.querySelector('.section-select-btn');
            if (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    pick();
                });
            }
        });
        if (child.preferred_section_id && group.querySelector('.section-choice-card[data-section-id="' + child.preferred_section_id + '"]:not(.is-full):not(.is-unscheduled)')) {
            const input = group.querySelector('input[name^="child_sections["]');
            if (input) input.value = child.preferred_section_id;
            markChildSectionChoice(group, child.preferred_section_id);
            if (String(child.id) === String(openDraftId)) {
                document.getElementById('selected_section_id').value = child.preferred_section_id;
            }
        } else {
            const input = group.querySelector('input[name^="child_sections["]');
            if (input) input.value = '';
        }
    });

    loading.style.display = 'none';
    const anySections = sectionsList.querySelector('.section-choice-card');
    container.style.display = anySections ? 'block' : 'none';
    empty.style.display = anySections ? 'none' : 'block';
    updateFamilySectionStatus();
}

function familySectionCard(section) {
    const isFull = section.available_spots === 0;
    const scheduleLocked = sectionIsScheduleLocked(section);
    const locked = isFull || scheduleLocked;
    const safeName = escapeHtml(section.name);
    const safeGrade = escapeHtml(section.grade_level);
    const safeAdviser = escapeHtml(section.adviser || '');
    const safeDesc = escapeHtml(section.description || '');
    return `
        <div class="col-md-6">
            <div class="card section-choice-card h-100 ${isFull ? 'is-full' : ''} ${scheduleLocked ? 'is-unscheduled' : ''}"
                 data-section-id="${section.id}"
                 data-section-name="${safeName}"
                 role="button"
                 tabindex="${locked ? '-1' : '0'}"
                 aria-pressed="false"
                 style="cursor: ${locked ? 'not-allowed' : 'pointer'}; opacity: ${locked ? '0.65' : '1'};">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="fas fa-chalkboard me-2 text-primary"></i>${safeName}
                            </h5>
                            <p class="text-muted mb-0"><small>Grade Level: ${safeGrade}</small></p>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1">
                            <span class="badge section-select-badge" style="display:none;">
                                <i class="fas fa-check me-1"></i>Your Choice
                            </span>
                            ${isFull ? '<span class="badge bg-danger">Full</span>' : (scheduleLocked ? '' : '<span class="badge bg-success">Available</span>')}
                        </div>
                    </div>
                    <p class="mb-2">
                        <i class="fas fa-user-tie me-1 text-primary"></i>
                        <strong>Adviser:</strong> ${section.adviser ? safeAdviser : 'To be assigned'}
                    </p>
                    ${sectionScheduleDetails(section)}
                    <p class="mb-3">
                        <i class="fas fa-users me-1 text-info"></i>
                        <strong>Capacity:</strong> ${section.available_spots} / ${section.capacity} spots available
                    </p>
                    ${section.description ? `<p class="text-muted mb-3"><small>${safeDesc}</small></p>` : ''}
                    ${!locked ? `
                        <button type="button" class="btn btn-outline-success w-100 section-select-btn">
                            <i class="fas fa-hand-pointer me-2"></i>Select This Section
                        </button>
                    ` : `
                        <button type="button" class="btn btn-secondary w-100 section-select-btn" disabled>
                            <i class="fas fa-times-circle me-2"></i>${scheduleLocked ? 'Enrollment Unavailable' : 'Section is Full'}
                        </button>
                    `}
                </div>
            </div>
        </div>
    `;
}

function selectChildSection(childId, sectionId, sectionName, isOpenChild) {
    const group = document.querySelector('[data-child-section="' + childId + '"]');
    if (!group) return;
    const input = group.querySelector('input[name^="child_sections["]');
    if (input) input.value = sectionId;
    markChildSectionChoice(group, sectionId);
    if (isOpenChild) {
        selectedSectionId = sectionId;
        selectedSectionName = sectionName;
        document.getElementById('selected_section_id').value = sectionId;
    }
    updateFamilySectionStatus();
    if (typeof persistEnrollmentForm === 'function') persistEnrollmentForm();
    saveChildSectionChoice(childId, sectionId);
}

function markChildSectionChoice(group, sectionId) {
    group.querySelectorAll('.section-choice-card').forEach(function (card) {
        const selected = String(card.getAttribute('data-section-id')) === String(sectionId);
        card.classList.toggle('is-selected', selected);
        card.setAttribute('aria-pressed', selected ? 'true' : 'false');
        const badge = card.querySelector('.section-select-badge');
        if (badge) badge.style.display = selected ? 'inline-flex' : 'none';
        const button = card.querySelector('.section-select-btn');
        if (button && !button.disabled) {
            button.classList.toggle('btn-primary', selected);
            button.classList.toggle('btn-outline-success', !selected);
            button.innerHTML = selected
                ? '<i class="fas fa-check-circle me-2"></i>Selected'
                : '<i class="fas fa-hand-pointer me-2"></i>Select This Section';
        }
    });
    const status = group.querySelector('.child-section-status');
    const chosen = group.querySelector('.section-choice-card.is-selected');
    if (status) {
        status.textContent = chosen
            ? 'Selected: ' + (chosen.getAttribute('data-section-name') || 'Section')
            : 'No section selected yet';
    }
}

function updateFamilySectionStatus() {
    const groups = document.querySelectorAll('[data-child-section]');
    const title = document.getElementById('sectionSelectionStatusTitle');
    const text = document.getElementById('sectionSelectionStatusText');
    const box = document.getElementById('sectionSelectionStatus');
    if (!groups.length || !title || !text || !box) return;

    const chosen = [];
    const missing = [];
    groups.forEach(function (group) {
        const name = group.getAttribute('data-child-name') || 'Child';
        const input = group.querySelector('input[name^="child_sections["]');
        const card = group.querySelector('.section-choice-card.is-selected');
        if (input && input.value && card) {
            chosen.push(name + ': ' + (card.getAttribute('data-section-name') || 'Section'));
        } else if (group.querySelector('.section-choice-card:not(.is-full)')) {
            missing.push(name);
        }
    });

    if (missing.length === 0 && chosen.length > 0) {
        box.classList.remove('is-empty');
        box.classList.add('is-selected');
        title.textContent = 'Sections selected for every child';
        text.textContent = chosen.join(' · ');
        return;
    }

    box.classList.add('is-empty');
    box.classList.remove('is-selected');
    title.textContent = missing.length ? 'Choose a section for ' + missing.join(', ') : 'No section selected yet';
    text.textContent = chosen.length ? chosen.join(' · ') : 'Each child has a section list for their own grade.';
}

async function saveChildSectionChoice(childId, sectionId) {
    const token = document.getElementById('enrollmentGroupToken')?.value || '';
    if (!childId || !token) return;
    try {
        const response = await fetch('{{ route('enrollment.portal.drafts.section') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': enrollmentForm.querySelector('input[name="_token"]').value,
            },
            body: JSON.stringify({
                enrollment_group_token: token,
                draft_id: childId,
                section_id: sectionId,
            }),
        });
        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'Unable to save this section.');
        }
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// Add event listener to grade level select
document.addEventListener('DOMContentLoaded', function() {
    const gradeLevelSelect = document.getElementById('grade_level_applying_for');
    
    if (gradeLevelSelect) {
        gradeLevelSelect.addEventListener('change', function() {
            const gradeLevel = this.value;
            console.log('📚 Grade level changed to:', gradeLevel);
            
            // Load sections for the selected grade level
            if (gradeLevel) {
                loadSections(gradeLevel);
            }
        });
        
        // Trigger initial load if there's already a value
        if (gradeLevelSelect.value) {
            loadSections(gradeLevelSelect.value);
        }
    }
});

        const existingParentPhoneNumbers = @json($existingParentPhones);
        const existingParentEmails = @json($existingParentEmails);

        function normalizePhoneDigits(value) {
            return String(value || '').replace(/\D+/g, '').slice(0, 11);
        }

        function isSameParentPhoneAllowed(phoneValue) {
            const normalizedPhone = normalizePhoneDigits(phoneValue);
            if (!normalizedPhone || !existingParentPhoneNumbers.includes(normalizedPhone)) {
                return true;
            }

            const candidateEmails = [
                document.querySelector('input[name="parent_email"]')?.value || '',
                document.querySelector('input[name="father_email"]')?.value || '',
                document.querySelector('input[name="mother_email"]')?.value || '',
                document.querySelector('input[name="guardian_email"]')?.value || '',
            ].map(value => String(value).trim().toLowerCase()).filter(Boolean);

            return candidateEmails.some(email => existingParentEmails.includes(email));
        }

        function validateParentPhoneField(input, checkDuplicate = false) {
            const rawValue = String(input.value || '').trim();

            if (!rawValue) {
                input.classList.remove('is-invalid');
                input.setCustomValidity('');
                return true;
            }

            if (!/^[\d\s()+-]+$/.test(rawValue)) {
                input.classList.add('is-invalid');
                input.setCustomValidity('Phone number must contain numbers only.');
                showAlert('Phone number must contain numbers only.', 'error');
                return false;
            }

            const normalized = normalizePhoneDigits(rawValue);
            input.value = normalized;

            if (!/^09\d{9}$/.test(normalized)) {
                input.classList.add('is-invalid');
                input.setCustomValidity('Phone number must contain exactly 11 digits.');
                showAlert('Phone number must contain exactly 11 digits.', 'error');
                return false;
            }

            if (checkDuplicate && existingParentPhoneNumbers.includes(normalized) && !isSameParentPhoneAllowed(normalized)) {
                input.classList.add('is-invalid');
                input.setCustomValidity('This phone number is already registered. Please use a different phone number.');
                showAlert('This phone number is already registered. Please use a different phone number.', 'error');
                return false;
            }

            input.classList.remove('is-invalid');
            input.setCustomValidity('');
            return true;
        }

        document.querySelectorAll('input[name="father_contact_no"], input[name="mother_contact_no"], input[name="guardian_contact_no"], input[name="parent_phone"]').forEach(input => {
            input.addEventListener('input', function () {
                this.value = normalizePhoneDigits(this.value);
            });

            input.addEventListener('blur', function () {
                validateParentPhoneField(this);
            });
        });

        document.getElementById('enrollmentForm')?.addEventListener('submit', function (event) {
            const criticalPhoneFields = ['father_contact_no', 'mother_contact_no', 'guardian_contact_no'];
            let isValid = true;

            criticalPhoneFields.forEach(fieldName => {
                const field = this.querySelector(`input[name="${fieldName}"]`);
                if (field && field.value && !validateParentPhoneField(field)) {
                    isValid = false;
                }
            });

            if (typeof updateParentFields === 'function') {
                updateParentFields();
            }

            const gradeLevel = document.getElementById('grade_level_applying_for')?.value || '';
            const parentAccountRequired = ['Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'].includes(gradeLevel);
            const parentAccountRequested = document.getElementById('create_parent_account')?.checked;
            const parentPhone = this.querySelector('input[name="parent_phone"]');
            if (parentPhone && (parentAccountRequired || parentAccountRequested) && parentPhone.value && !validateParentPhoneField(parentPhone, true)) {
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
</script>
        @endif

<style>
.section-selection-status {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    border: 2px solid #cbd5e1;
    background: #f8fafc;
    color: #334155;
}
.section-selection-status__icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e2e8f0;
    color: #475569;
    flex-shrink: 0;
}
.section-selection-status.is-selected {
    border-color: #2563eb;
    background: #eff6ff;
    color: #1e3a8a;
}
.section-selection-status.is-selected .section-selection-status__icon {
    background: #2563eb;
    color: #fff;
}
.section-choice-card {
    border: 2px solid #e2e8f0 !important;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease, background .2s ease;
    background: #fff;
}
.section-choice-card:hover:not(.is-full):not(.is-selected) {
    border-color: #93c5fd !important;
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
}
.section-choice-card.is-selected {
    border-color: #2563eb !important;
    border-width: 3px !important;
    background: linear-gradient(180deg, #eff6ff 0%, #fff 55%);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.18), 0 12px 24px rgba(37, 99, 235, 0.18);
    transform: translateY(-2px);
}
.section-choice-card.is-full {
    border-color: #fecaca !important;
}
.section-select-badge {
    background: #1d4ed8 !important;
    color: #fff !important;
    font-weight: 700;
    align-items: center;
}
</style>
@endsection
    