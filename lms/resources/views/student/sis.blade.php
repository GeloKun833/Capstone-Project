@extends('layouts.master')
@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><i class="fas fa-user-graduate me-2"></i>Student Information System (SIS)</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('sis.hub') }}">Information Systems</a></li>
                        <li class="breadcrumb-item active">Student SIS</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('sis.hub') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Hub
                    </a>
                    <a href="{{ url('view/user/edit/'.$user->user_id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit User
                    </a>
                </div>
            </div>
        </div>

        <!-- Student Profile Card -->
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="{{ asset('images/'.$user->avatar) }}" 
                                 alt="{{ $student->full_name }}" 
                                 class="rounded-circle" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <h4 class="fw-bold">{{ $student->full_name }}</h4>
                        <p class="text-muted mb-1">
                            <span class="badge bg-primary">{{ $student->year_level }}</span>
                        </p>
                        <p class="text-muted mb-1">
                            <strong>Student ID:</strong> {{ $student->student_id ?? 'N/A' }}
                        </p>
                        <p class="text-muted mb-1">
                            <strong>User ID:</strong> {{ $user->user_id }}
                        </p>
                        <p class="mb-3">
                            @if($student->enrollment_status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($student->enrollment_status === 'graduated')
                                <span class="badge bg-info">Graduated</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($student->enrollment_status) }}</span>
                            @endif
                        </p>

                        <!-- Quick Stats -->
                        <div class="row text-center mt-4">
                            <div class="col-4">
                                <div class="border-end">
                                    <h5 class="fw-bold text-primary">{{ $currentEnrollments->count() }}</h5>
                                    <small class="text-muted">Subjects</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <h5 class="fw-bold text-success">{{ $currentGPA ? number_format($currentGPA->gpa, 2) : 'N/A' }}</h5>
                                    <small class="text-muted">GPA</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <h5 class="fw-bold text-info">{{ $attendancePercentage }}%</h5>
                                <small class="text-muted">Attendance</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Full Name:</td>
                                <td>{{ $student->full_name }}</td>
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

                <!-- Parent/Guardian Information -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Parent/Guardian Info</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Name:</td>
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
                        
                        <hr>
                        
                        <h6 class="fw-bold text-danger mb-2">Emergency Contact</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Name:</td>
                                <td>{{ $student->emergency_contact_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phone:</td>
                                <td>{{ $student->emergency_contact_phone ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Academic Information -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Year Level:</strong> {{ $student->year_level }}</p>
                                <p><strong>Previous School:</strong> {{ $student->previous_school ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                @if($sectionAssignment)
                                    <p><strong>Section:</strong> {{ $sectionAssignment->name }}</p>
                                    <p><strong>Academic Year:</strong> {{ $sectionAssignment->academic_year_name }}</p>
                                    <p><strong>Semester:</strong> {{ $sectionAssignment->semester_name }}</p>
                                @else
                                    <p class="text-muted">No section assigned yet</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Enrollments -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Current Subject Enrollments ({{ $currentEnrollments->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($currentEnrollments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Subject</th>
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
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>No active subject enrollments found.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quarterly Grades -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Quarterly Grades</h5>
                    </div>
                    <div class="card-body">
                        @if($quarterlyGrades->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Academic Year</th>
                                            <th class="text-center">Q1</th>
                                            <th class="text-center">Q2</th>
                                            <th class="text-center">Q3</th>
                                            <th class="text-center">Q4</th>
                                            <th class="text-center">Final</th>
                                            <th class="text-center">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quarterlyGrades as $qg)
                                            <tr>
                                                <td>{{ $qg->subject->subject_name ?? 'N/A' }}</td>
                                                <td>{{ $qg->academicYear->name ?? 'N/A' }}</td>
                                                <td class="text-center">{{ $qg->quarter_1 !== null ? number_format($qg->quarter_1, 2) : '—' }}</td>
                                                <td class="text-center">{{ $qg->quarter_2 !== null ? number_format($qg->quarter_2, 2) : '—' }}</td>
                                                <td class="text-center">{{ $qg->quarter_3 !== null ? number_format($qg->quarter_3, 2) : '—' }}</td>
                                                <td class="text-center">{{ $qg->quarter_4 !== null ? number_format($qg->quarter_4, 2) : '—' }}</td>
                                                <td class="text-center"><strong>{{ $qg->final_grade !== null ? number_format($qg->final_grade, 2) : '—' }}</strong></td>
                                                <td class="text-center">{{ $qg->remarks ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No quarterly grades recorded yet.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Grades -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Grades & Performance</h5>
                    </div>
                    <div class="card-body">
                        @if($grades->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
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
                                                <td>
                                                    <strong>{{ $grade->grade }}</strong>
                                                </td>
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
                            @if($grades->count() > 10)
                                <p class="text-muted text-center mb-0">Showing 10 of {{ $grades->count() }} grades</p>
                            @endif
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No grades recorded yet.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- GPA Records -->
                @if($gpaRecords->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>GPA History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
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

                <!-- Attendance Summary -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Attendance Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4 class="fw-bold text-primary">{{ $totalAttendance }}</h4>
                                <p class="text-muted mb-0">Total Records</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="fw-bold text-success">{{ $presentCount }}</h4>
                                <p class="text-muted mb-0">Present</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="fw-bold text-danger">{{ $absentCount }}</h4>
                                <p class="text-muted mb-0">Absent</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="fw-bold text-info">{{ $attendancePercentage }}%</h4>
                                <p class="text-muted mb-0">Attendance Rate</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promotion History -->
                @if($promotionHistory->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Promotion History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                        <th>GPA</th>
                                        <th>Date</th>
                                        <th>Promoted By</th>
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
                                            <td>{{ $promotion->promoter->name ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Enrollment Documents -->
                @if(count($enrollmentDocuments) > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Enrollment Documents</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
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
    </div>
</div>

@endsection

