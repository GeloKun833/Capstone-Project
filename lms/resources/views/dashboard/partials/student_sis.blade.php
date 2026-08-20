{{-- Student Information System (SIS) for Profile Page --}}
<div class="row">
    {{-- Left Column: Personal & Family Info --}}
    <div class="col-lg-4">
        {{-- Personal Information --}}
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="fw-bold" style="width: 45%;">Full Name:</td>
                        <td>{{ $student->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Student ID:</td>
                        <td>{{ $student->student_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Date of Birth:</td>
                        <td>{{ \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Age:</td>
                        <td>{{ \Carbon\Carbon::parse($student->date_of_birth)->age }} years</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Gender:</td>
                        <td>{{ $student->gender }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Email:</td>
                        <td>{{ $student->email }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Phone:</td>
                        <td>{{ $student->phone_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Address:</td>
                        <td>{{ $student->address }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Parent/Guardian Information --}}
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent/Guardian Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="fw-bold" style="width: 45%;">Name:</td>
                        <td>{{ $student->parent_name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Relationship:</td>
                        <td>{{ $student->parent_relationship ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Email:</td>
                        <td>{{ $student->parent_email }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Phone:</td>
                        <td>{{ $student->parent_phone }}</td>
                    </tr>
                </table>
                
                @if($student->emergency_contact_name)
                    <hr>
                    <h6 class="fw-bold text-danger mb-2"><i class="fas fa-phone-alt me-1"></i>Emergency Contact</h6>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="fw-bold" style="width: 45%;">Name:</td>
                            <td>{{ $student->emergency_contact_name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Phone:</td>
                            <td>{{ $student->emergency_contact_phone }}</td>
                        </tr>
                    </table>
                @endif
            </div>
        </div>

        {{-- Account Status --}}
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Account Status</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Status:</strong> 
                    @if($student->enrollment_status === 'active')
                        <span class="badge bg-success">Active</span>
                    @elseif($student->enrollment_status === 'graduated')
                        <span class="badge bg-info">Graduated</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($student->enrollment_status) }}</span>
                    @endif
                </p>
                <p class="mb-0">
                    <strong>Year Level:</strong> 
                    <span class="badge bg-primary">{{ $student->year_level }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Right Column: Academic Information --}}
    <div class="col-lg-8">
        {{-- Academic Performance Summary --}}
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Academic Performance Overview</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="fw-bold text-primary">{{ $currentEnrollments->count() }}</h4>
                            <p class="text-muted mb-0 small">Current Subjects</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="fw-bold text-success">{{ $currentGPA ? number_format($currentGPA->gpa, 2) : 'N/A' }}</h4>
                            <p class="text-muted mb-0 small">Current GPA</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="fw-bold text-info">{{ $attendancePercentage }}%</h4>
                            <p class="text-muted mb-0 small">Attendance Rate</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="fw-bold text-warning">{{ $student->year_level }}</h4>
                            <p class="text-muted mb-0 small">Year Level</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Academic Information --}}
        @if($sectionAssignment)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-school me-2"></i>Academic Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Section:</strong> {{ $sectionAssignment->name }}</p>
                        <p><strong>Grade Level:</strong> {{ $sectionAssignment->grade_level }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Academic Year:</strong> {{ $sectionAssignment->academic_year_name }}</p>
                        <p><strong>Semester:</strong> {{ $sectionAssignment->semester_name }}</p>
                    </div>
                </div>
                @if($student->previous_school)
                    <p class="mb-0"><strong>Previous School:</strong> {{ $student->previous_school }}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Current Subject Enrollments --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-book me-2"></i>My Current Subjects ({{ $currentEnrollments->count() }})</h6>
            </div>
            <div class="card-body">
                @if($currentEnrollments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Subject Name</th>
                                    <th>Code</th>
                                    <th>Academic Year</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentEnrollments as $index => $enrollment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $enrollment->subject->subject_name }}</td>
                                        <td>{{ $enrollment->subject->subject_code ?? 'N/A' }}</td>
                                        <td>{{ $enrollment->academicYear->name ?? 'N/A' }}</td>
                                        <td>{{ $enrollment->semester->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success">{{ ucfirst($enrollment->status) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No active subject enrollments found.
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Grades --}}
        @if($grades->count() > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-star me-2"></i>Recent Grades (Last 10)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Quarter</th>
                                <th>Grade</th>
                                <th>Percentage</th>
                                <th>Academic Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grades->take(10) as $grade)
                                <tr>
                                    <td>{{ $grade->subject->subject_name }}</td>
                                    <td>{{ $grade->quarter }}</td>
                                    <td><strong>{{ $grade->grade }}</strong></td>
                                    <td>
                                        <span class="badge {{ $grade->percentage >= 75 ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($grade->percentage, 1) }}%
                                        </span>
                                    </td>
                                    <td>{{ $grade->academicYear->name ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-2">
                    <a href="{{ route('student.grades') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-chart-bar me-1"></i>View All Grades
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- GPA History --}}
        @if($gpaRecords->count() > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>GPA History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>GPA</th>
                                <th>Ranking</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gpaRecords as $gpa)
                                <tr>
                                    <td>{{ $gpa->academicYear->name ?? 'N/A' }}</td>
                                    <td>{{ $gpa->semester->name ?? 'N/A' }}</td>
                                    <td>
                                        <strong class="text-success">{{ number_format($gpa->gpa, 2) }}</strong>
                                    </td>
                                    <td>{{ $gpa->ranking ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Attendance Summary --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Attendance Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h5 class="fw-bold text-primary">{{ $totalAttendance }}</h5>
                        <p class="text-muted mb-0 small">Total Records</p>
                    </div>
                    <div class="col-md-3">
                        <h5 class="fw-bold text-success">{{ $presentCount }}</h5>
                        <p class="text-muted mb-0 small">Present</p>
                    </div>
                    <div class="col-md-3">
                        <h5 class="fw-bold text-danger">{{ $absentCount }}</h5>
                        <p class="text-muted mb-0 small">Absent</p>
                    </div>
                    <div class="col-md-3">
                        <h5 class="fw-bold text-info">{{ $attendancePercentage }}%</h5>
                        <p class="text-muted mb-0 small">Attendance Rate</p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('student.attendance') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-calendar-check me-1"></i>View Full Attendance
                    </a>
                </div>
            </div>
        </div>

        {{-- Promotion History --}}
        @if($promotionHistory->count() > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Promotion History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                                <th>GPA</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promotionHistory as $promotion)
                                <tr>
                                    <td>{{ $promotion->from_year_level }}</td>
                                    <td>{{ $promotion->to_year_level }}</td>
                                    <td>
                                        <span class="badge bg-{{ $promotion->status_badge }}">
                                            {{ ucfirst($promotion->promotion_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $promotion->final_gpa ? number_format($promotion->final_gpa, 2) : 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($promotion->promotion_date)->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Enrollment Documents --}}
        @if(count($enrollmentDocuments) > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Enrollment Documents</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Document Type</th>
                                <th>File Name</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrollmentDocuments as $doc)
                                <tr>
                                    <td>{{ \App\Models\EnrollmentDocument::DOCUMENT_TYPES[$doc->document_type] ?? $doc->document_type }}</td>
                                    <td>{{ $doc->file_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $doc->status_badge }}">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

