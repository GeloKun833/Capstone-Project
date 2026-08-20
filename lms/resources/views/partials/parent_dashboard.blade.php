<div class="page-header">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-sub-header">
                <h3 class="page-title">Parent Dashboard</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Parent Dashboard</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@if(isset($parent['error']))
    <!-- Error State -->
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem; opacity: 0.6;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">Dashboard Error</h4>
                    <p class="text-muted mb-4">{{ $parent['error'] }}</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Return to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
@elseif(isset($parent['noChildren']) && $parent['noChildren'])
    <!-- No Children State -->
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-users text-primary" style="font-size: 4rem; opacity: 0.6;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">No Children Linked</h4>
                    <p class="text-muted mb-4">No students are currently linked to your parent account. Please contact the school administration to link your children to your account.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Return to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
@elseif(isset($parent['children']) && $parent['children']->count() > 0)
    
    <!-- Welcome Message for Parents -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex align-items-center text-white">
                    <i class="fas fa-heart fa-2x me-3" style="opacity: 0.9;"></i>
                    <div>
                        <h5 class="mb-1 text-white fw-bold">Welcome, {{ auth()->user()->name }}!</h5>
                        <p class="mb-0" style="opacity: 0.95;">Monitor your {{ $parent['children']->count() > 1 ? 'children\'s' : 'child\'s' }} academic journey and stay connected with their progress.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Child Selector (if multiple children) -->
    @if($parent['children']->count() > 1)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card modern-card border-0 shadow-sm">
                    <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border: none;">
                        <h5 class="mb-0 text-white fw-bold">
                            <i class="fas fa-users me-2"></i>Select Your Child
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                                @foreach($parent['children'] as $child)
                                <div class="col-md-6 col-lg-4">
                                    <div class="child-selector-card {{ $parent['selectedChild']->id == $child->id ? 'active' : '' }}" 
                                         onclick="switchChild({{ $child->id }})"
                                         style="cursor: pointer; transition: all 0.3s ease;">
                                        <div class="d-flex align-items-center p-3">
                                            <div class="me-3">
                                                <img src="{{ $child->upload ? asset('storage/' . $child->upload) : URL::to('assets/img/profiles/avatar-01.jpg') }}" 
                                                     alt="{{ $child->first_name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 60px; height: 60px; object-fit: cover; border: 3px solid {{ $parent['selectedChild']->id == $child->id ? '#667eea' : '#e0e0e0' }};">
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">{{ $child->first_name }} {{ $child->last_name }}</h6>
                                                <p class="mb-0 text-muted small">
                                                    <i class="fas fa-graduation-cap me-1"></i>{{ $child->year_level }}
                                                </p>
                                                <p class="mb-0 text-muted small">
                                                    <i class="fas fa-chalkboard me-1"></i>{{ $child->sections->first() ? $child->sections->first()->name : 'No Section' }}
                                                </p>
                                            </div>
                                            @if($parent['selectedChild']->id == $child->id)
                                                <div class="ms-2">
                                                    <i class="fas fa-check-circle text-success fa-2x"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modern Child Information Card (Similar to Student Dashboard) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card child-hero-card text-white border-0 shadow-lg">
                <div class="card-body p-4 p-md-5" style="position: relative; z-index: 1;">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-4 mb-md-0">
                            <div class="child-photo-wrapper">
                                @if(!empty($parent['selectedChild']->upload))
                                    <img src="{{ asset('storage/' . $parent['selectedChild']->upload) }}" alt="Child Photo" class="child-photo">
                                @else
                                    <img src="{{ URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="Child Photo" class="child-photo">
                                @endif
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h2 class="text-white mb-1 fw-bold" style="font-size: 2rem; text-shadow: 2px 2px 8px rgba(0,0,0,0.3);">
                                {{ $parent['selectedChild']->first_name }} {{ $parent['selectedChild']->middle_name }} {{ $parent['selectedChild']->last_name }}
                            </h2>
                            <p class="mb-4 opacity-75" style="font-size: 1.1rem;">Your Child's Academic Overview</p>
                            <div class="row g-3">
                                <div class="col-md-3 col-6">
                                    <div class="info-pill text-center">
                                        <i class="fas fa-id-card"></i>
                                        <small>Student ID</small>
                                        <div class="value">{{ $parent['selectedChild']->user_id ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="info-pill text-center">
                                        <i class="fas fa-graduation-cap"></i>
                                        <small>Grade Level</small>
                                        <div class="value">{{ $parent['selectedChild']->year_level ?? 'Not Set' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="info-pill text-center">
                                        <i class="fas fa-users"></i>
                                        <small>Section</small>
                                        @php
                                            $childSection = $parent['selectedChild']->sections->first();
                                        @endphp
                                        @if($childSection)
                                            <div class="value">{{ $childSection->name }}</div>
                                            @if($childSection->adviser)
                                                <small class="opacity-75" style="font-size: 0.7rem; text-transform: none;">Adviser: {{ $childSection->adviser->full_name ?? 'TBA' }}</small>
                                            @endif
                                        @else
                                            <div class="value"><span class="badge bg-warning text-dark" style="font-size: 0.85rem;">Not Assigned</span></div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="info-pill text-center">
                                        <i class="fas fa-chart-line"></i>
                                        <small>Overall Progress</small>
                                        @php
                                            $avgGrade = $parent['grades']->avg('percentage') ?? 0;
                                        @endphp
                                        <div class="value">{{ number_format($avgGrade, 1) }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
            <div class="stat-card stat-card-attendance">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $parent['attendanceStats']->present_count ?? 0 }}<span class="stat-total">/{{ $parent['attendanceStats']->total_records ?? 0 }}</span></h3>
                    <p>Attendance Record</p>
                    <div class="stat-percentage">
                        @php
                            $attendancePercent = ($parent['attendanceStats']->total_records ?? 0) > 0 
                                ? round((($parent['attendanceStats']->present_count ?? 0) / ($parent['attendanceStats']->total_records ?? 1)) * 100, 1) 
                                : 0;
                        @endphp
                        <span class="{{ $attendancePercent >= 90 ? 'text-success' : ($attendancePercent >= 75 ? 'text-warning' : 'text-danger') }}">
                            {{ $attendancePercent }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
            <div class="stat-card stat-card-subjects">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $parent['enrollments'] ? $parent['enrollments']->count() : 0 }}</h3>
                    <p>Enrolled Subjects</p>
                    <div class="stat-percentage">
                        <span class="text-info">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4 mb-md-0">
            <div class="stat-card stat-card-grades">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ number_format($avgGrade ?? 0, 1) }}%</h3>
                    <p>Average Grade</p>
                    <div class="stat-percentage">
                        <span class="{{ $avgGrade >= 90 ? 'text-success' : ($avgGrade >= 75 ? 'text-info' : 'text-warning') }}">
                            {{ $avgGrade >= 90 ? 'Excellent' : ($avgGrade >= 75 ? 'Good' : 'Needs Support') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-activities">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $parent['activities'] ? $parent['activities']->count() : 0 }}</h3>
                    <p>Pending Activities</p>
                    <div class="stat-percentage">
                        <span class="text-warning">To Do</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrolled Subjects (Modern Design) -->
    @if($parent['enrollments'] && $parent['enrollments']->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-book-open me-2"></i>{{ $parent['selectedChild']->first_name }}'s Subjects
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($parent['enrollments']->take(6) as $enrollment)
                            @php
                                // Get average grade for this subject
                                $subjectGrades = $parent['grades']->where('subject_id', $enrollment->subject_id);
                                $subjectAvg = $subjectGrades->avg('percentage') ?? 0;
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <div class="subject-card">
                                    <div class="subject-header">
                                        <h6 class="mb-0">{{ $enrollment->subject->subject_name }}</h6>
                                        <span class="subject-code">{{ $enrollment->subject->subject_id }}</span>
                                    </div>
                                    <div class="subject-body">
                                        <div class="subject-stat">
                                            <i class="fas fa-chart-line text-primary"></i>
                                            <span>Average: <strong>{{ number_format($subjectAvg, 1) }}%</strong></span>
                                        </div>
                                        <div class="subject-stat">
                                            <i class="fas fa-tasks text-success"></i>
                                            <span>Status: <strong class="text-success">{{ ucfirst($enrollment->status) }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($parent['enrollments']->count() > 6)
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing 6 of {{ $parent['enrollments']->count() }} subjects
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Academic Performance & Attendance Overview -->
    <div class="row mb-4">
        <!-- Recent Grades -->
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-star me-2 text-warning"></i>Recent Grades
                    </h5>
                </div>
                <div class="card-body">
                    @if($parent['grades'] && $parent['grades']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Subject</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($parent['grades']->take(5) as $grade)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">{{ $grade->subject->subject_name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge grade-badge grade-{{ $grade->percentage >= 90 ? 'excellent' : ($grade->percentage >= 75 ? 'good' : 'needs-improvement') }}">
                                                    {{ number_format($grade->percentage, 1) }}%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $grade->percentage >= 75 ? 'success' : 'warning' }}">
                                                    {{ $grade->percentage >= 75 ? 'Passing' : 'Needs Attention' }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                {{ \Carbon\Carbon::parse($grade->created_at)->format('M d, Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($parent['grades']->count() > 5)
                            <div class="text-center mt-3">
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>View All Grades
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3 mb-0">No grades recorded yet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-check me-2 text-success"></i>Attendance Summary
                    </h5>
                </div>
                <div class="card-body">
                    @if($parent['attendanceStats'] && ($parent['attendanceStats']->total_records ?? 0) > 0)
                        <div class="attendance-overview">
                            <div class="attendance-circle mb-4">
                                <svg width="200" height="200" viewBox="0 0 200 200" class="mx-auto d-block">
                                    <circle cx="100" cy="100" r="80" fill="none" stroke="#e0e0e0" stroke-width="20"/>
                                    <circle cx="100" cy="100" r="80" fill="none" stroke="#10b981" stroke-width="20"
                                            stroke-dasharray="{{ 2 * 3.14159 * 80 }}"
                                            stroke-dashoffset="{{ 2 * 3.14159 * 80 * (1 - $attendancePercent / 100) }}"
                                            transform="rotate(-90 100 100)"
                                            style="transition: stroke-dashoffset 1s ease;"/>
                                    <text x="100" y="95" text-anchor="middle" font-size="36" font-weight="bold" fill="#333">{{ $attendancePercent }}%</text>
                                    <text x="100" y="120" text-anchor="middle" font-size="14" fill="#666">Present</text>
                                </svg>
                            </div>
                            
                            <div class="row g-3 text-center">
                                <div class="col-4">
                                    <div class="attendance-stat">
                                        <h4 class="text-success mb-0">{{ $parent['attendanceStats']->present_count ?? 0 }}</h4>
                                        <small class="text-muted">Present</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="attendance-stat">
                                        <h4 class="text-danger mb-0">{{ ($parent['attendanceStats']->total_records ?? 0) - ($parent['attendanceStats']->present_count ?? 0) }}</h4>
                                        <small class="text-muted">Absent</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="attendance-stat">
                                        <h4 class="text-info mb-0">{{ $parent['attendanceStats']->total_records ?? 0 }}</h4>
                                        <small class="text-muted">Total Days</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-alt text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3 mb-0">No attendance records yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Activities & Lessons -->
    <div class="row mb-4">
        <!-- Upcoming Activities -->
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-tasks me-2 text-warning"></i>Upcoming Activities
                    </h5>
                </div>
                <div class="card-body">
                    @if($parent['activities'] && $parent['activities']->count() > 0)
                        <div class="activity-list">
                            @foreach($parent['activities']->take(5) as $activity)
                                @php
                                    $dueDate = \Carbon\Carbon::parse($activity->due_date);
                                    $isOverdue = $dueDate->isPast();
                                    $daysUntilDue = $dueDate->diffInDays(now(), false);
                                @endphp
                                <div class="activity-item {{ $isOverdue ? 'overdue' : '' }}">
                                    <div class="activity-icon">
                                        <i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-clipboard-list' }}"></i>
                                    </div>
                                    <div class="activity-details">
                                        <h6 class="mb-1">{{ $activity->title }}</h6>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-book me-1"></i>{{ $activity->lesson->subject->subject_name ?? 'N/A' }}
                                        </p>
                                        <p class="mb-0 small">
                                            <i class="fas fa-clock me-1 {{ $isOverdue ? 'text-danger' : 'text-warning' }}"></i>
                                            {{ $isOverdue ? 'Overdue by ' . abs($daysUntilDue) . ' days' : 'Due in ' . $daysUntilDue . ' days' }}
                                        </p>
                                    </div>
                                    <div class="activity-status">
                                        @php
                                            $submission = $parent['submissions']->where('activity_id', $activity->id)->first();
                                        @endphp
                                        @if($submission)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Submitted
                                            </span>
                                        @else
                                            <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-warning' }}">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($parent['activities']->count() > 5)
                            <div class="text-center mt-3">
                                <p class="text-muted mb-0 small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Showing 5 of {{ $parent['activities']->count() }} activities
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3 mb-0">No upcoming activities</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Lessons -->
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chalkboard-teacher me-2 text-info"></i>Recent Lessons
                    </h5>
                </div>
                <div class="card-body">
                    @if($parent['lessons'] && $parent['lessons']->count() > 0)
                        <div class="lesson-list">
                            @foreach($parent['lessons'] as $lesson)
                                <div class="lesson-item">
                                    <div class="lesson-icon">
                                        <i class="fas fa-book-reader"></i>
                                    </div>
                                    <div class="lesson-details">
                                        <h6 class="mb-1">{{ $lesson->title }}</h6>
                                        <p class="mb-0 text-muted small">
                                            <i class="fas fa-book me-1"></i>{{ $lesson->subject->subject_name ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="lesson-date">
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($lesson->created_at)->format('M d') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chalkboard text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3 mb-0">No lessons posted yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Application Status (if exists) -->
    @php
        $enrollmentApplication = $parent['selectedChild']->enrollmentApplication;
    @endphp

    @if($enrollmentApplication)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-file-alt me-2"></i>Enrollment Application Status
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $requiredDocuments = [
                            'birth_certificate' => 'Birth Certificate',
                            'sf9' => 'SF9 (Learner\'s Permanent Record)',
                            'sf10' => 'SF10 (Report Card)',
                            'good_moral' => 'Certificate of Good Moral Character',
                            'id_photo' => 'ID Photo (2x2)',
                            'parent_guardian_id' => 'Parent/Guardian ID'
                        ];
                        
                        $uploadedTypes = $enrollmentApplication->documents->pluck('document_type')->toArray();
                        $missingDocuments = array_diff(array_keys($requiredDocuments), $uploadedTypes);
                    @endphp
                    
                    <div class="row mb-4 g-3">
                        <div class="col-md-4">
                            <div class="info-pill text-center" style="background: rgba(59, 130, 246, 0.1);">
                                <i class="fas fa-file-alt text-primary"></i>
                                <small>Application #</small>
                                <div class="value text-primary">{{ $enrollmentApplication->application_number }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-pill text-center" style="background: rgba(16, 185, 129, 0.1);">
                                <i class="fas fa-info-circle text-success"></i>
                                <small>Status</small>
                                <div class="value">
                                    <span class="badge bg-{{ $enrollmentApplication->status === 'approved' ? 'success' : ($enrollmentApplication->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $enrollmentApplication->status)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-pill text-center" style="background: rgba(245, 158, 11, 0.1);">
                                <i class="fas fa-file-upload text-warning"></i>
                                <small>Documents</small>
                                <div class="value text-warning">{{ $enrollmentApplication->documents->count() }}/6</div>
                            </div>
                        </div>
                    </div>
                    
                    @if(count($missingDocuments) > 0)
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Missing Documents
                            </h6>
                            <ul class="mb-2">
                                @foreach($missingDocuments as $missingType)
                                    <li>{{ $requiredDocuments[$missingType] }}</li>
                                @endforeach
                            </ul>
                            <small class="d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Please upload these documents to complete the enrollment process.
                            </small>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>All Required Documents Uploaded!</strong>
                            <p class="mb-0 mt-1 small">The enrollment application has all necessary documents for review.</p>
                        </div>
                    @endif
                    
                    <!-- View Full Details Button -->
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#enrollmentModal{{ $enrollmentApplication->id }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fas fa-eye me-2"></i>View Full Application Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Application Details Modal -->
    <div class="modal fade" id="enrollmentModal{{ $enrollmentApplication->id }}" tabindex="-1" aria-labelledby="enrollmentModalLabel{{ $enrollmentApplication->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <h5 class="modal-title fw-bold" id="enrollmentModalLabel{{ $enrollmentApplication->id }}">
                        <i class="fas fa-file-alt me-2"></i>Enrollment Application Details - {{ $parent['selectedChild']->first_name }} {{ $parent['selectedChild']->last_name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Application Status --}}
                    <div class="alert alert-{{ $enrollmentApplication->status === 'approved' ? 'success' : ($enrollmentApplication->status === 'rejected' ? 'danger' : 'info') }} border-0 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-2 fw-bold"><i class="fas fa-info-circle me-2"></i>Application Status</h6>
                                <p class="mb-1">
                                    <strong>Status:</strong> 
                                    <span class="badge bg-{{ $enrollmentApplication->status === 'approved' ? 'success' : ($enrollmentApplication->status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                                        {{ ucfirst(str_replace('_', ' ', $enrollmentApplication->status)) }}
                                    </span>
                                </p>
                                <p class="mb-1"><strong>Application Number:</strong> {{ $enrollmentApplication->application_number }}</p>
                                <p class="mb-0"><strong>Submitted:</strong> {{ $enrollmentApplication->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Personal Information --}}
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-user me-2"></i>Personal Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="fw-bold text-muted" style="width: 40%;">Full Name:</td>
                                            <td class="fw-semibold">{{ $enrollmentApplication->full_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Date of Birth:</td>
                                            <td>{{ \Carbon\Carbon::parse($enrollmentApplication->date_of_birth)->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Gender:</td>
                                            <td>{{ $enrollmentApplication->gender }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Email:</td>
                                            <td>{{ $enrollmentApplication->email }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="fw-bold text-muted" style="width: 40%;">Phone:</td>
                                            <td>{{ $enrollmentApplication->phone_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Address:</td>
                                            <td>{{ $enrollmentApplication->address }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Grade Level:</td>
                                            <td><span class="badge bg-primary">{{ $enrollmentApplication->grade_level_applying_for }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Previous School:</td>
                                            <td>{{ $enrollmentApplication->previous_school ?: 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Parent/Guardian Information --}}
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none;">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i>Parent/Guardian Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="fw-bold text-muted" style="width: 40%;">Name:</td>
                                            <td class="fw-semibold">{{ $enrollmentApplication->parent_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Email:</td>
                                            <td>{{ $enrollmentApplication->parent_email }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="fw-bold text-muted" style="width: 40%;">Phone:</td>
                                            <td>{{ $enrollmentApplication->parent_phone }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Relationship:</td>
                                            <td>{{ $enrollmentApplication->parent_relationship ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Emergency Contact --}}
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header text-dark" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border: none;">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-phone-alt me-2"></i>Emergency Contact</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><span class="fw-bold text-muted">Name:</span> {{ $enrollmentApplication->emergency_contact_name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><span class="fw-bold text-muted">Phone:</span> {{ $enrollmentApplication->emergency_contact_phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Uploaded Documents --}}
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-file-upload me-2"></i>Uploaded Documents ({{ $enrollmentApplication->documents->count() }}/6)</h6>
                        </div>
                        <div class="card-body">
                            @if($enrollmentApplication->documents->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th><i class="fas fa-file me-1"></i>Document Type</th>
                                                <th><i class="fas fa-file-alt me-1"></i>File Name</th>
                                                <th><i class="fas fa-info-circle me-1"></i>Status</th>
                                                <th><i class="fas fa-calendar me-1"></i>Uploaded</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($enrollmentApplication->documents as $doc)
                                                <tr>
                                                    <td class="fw-semibold">{{ \App\Models\EnrollmentDocument::DOCUMENT_TYPES[$doc->document_type] ?? ucfirst(str_replace('_', ' ', $doc->document_type)) }}</td>
                                                    <td>{{ $doc->file_name }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $doc->status === 'verified' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'warning') }}">
                                                            <i class="fas fa-{{ $doc->status === 'verified' ? 'check' : ($doc->status === 'rejected' ? 'times' : 'clock') }} me-1"></i>
                                                            {{ ucfirst($doc->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-muted">{{ $doc->created_at->format('M d, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-file-upload text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="text-muted mt-3 mb-0">No documents uploaded yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Subjects for Grade Level --}}
                    @if($enrollmentApplication->grade_level_applying_for)
                        @php
                            $subjects = \App\Helpers\GradeSubjectsHelper::getSubjectsForGrade($enrollmentApplication->grade_level_applying_for);
                        @endphp
                        @if($subjects)
                            <div class="card border-0 shadow-sm">
                                <div class="card-header text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border: none;">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-book me-2"></i>Subjects for {{ $enrollmentApplication->grade_level_applying_for }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2">
                                        @foreach($subjects as $subject)
                                            <div class="col-md-4 col-sm-6">
                                                <div class="badge bg-success fs-6 p-2 w-100 text-start">
                                                    <i class="fas fa-book me-2"></i>{{ $subject }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-3 text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Total: {{ count($subjects) }} subjects
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

@endif

<style>
/* Modern Child Hero Card */
.child-hero-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    animation: fadeInUp 0.6s ease;
}

.child-hero-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="rgba(255,255,255,0.1)"></path></svg>') no-repeat bottom;
    background-size: cover;
    opacity: 0.3;
    z-index: 0;
}

.child-photo-wrapper {
    position: relative;
    display: inline-block;
    animation: pulse 2s ease-in-out infinite;
}

.child-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease;
}

.child-photo:hover {
    transform: scale(1.05);
}

/* Info Pills */
.info-pill {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    padding: 15px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.info-pill:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-3px);
}

.info-pill i {
    font-size: 1.2rem;
    margin-bottom: 5px;
    opacity: 0.9;
}

.info-pill small {
    display: block;
    font-size: 0.75rem;
    opacity: 0.85;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-pill .value {
    font-size: 1.1rem;
    font-weight: 700;
    margin-top: 5px;
}

/* Modern Stats Cards */
.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: none;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.stat-card-attendance .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card-subjects .stat-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card-grades .stat-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card-activities .stat-icon {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.stat-content {
    flex: 1;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: #333;
}

.stat-content h3 .stat-total {
    font-size: 1.2rem;
    color: #999;
    font-weight: 400;
}

.stat-content p {
    color: #666;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.stat-percentage {
    font-size: 0.85rem;
    font-weight: 600;
}

/* Modern Card */
.modern-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.modern-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 20px;
}

.modern-card .card-header .card-title {
    color: white;
    margin-bottom: 0;
    font-weight: 600;
}

/* Child Selector Cards */
.child-selector-card {
    border: 2px solid #e0e0e0;
    border-radius: 15px;
    background: white;
    transition: all 0.3s ease;
}

.child-selector-card:hover {
    border-color: #667eea;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
    transform: translateY(-3px);
}

.child-selector-card.active {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
}

/* Subject Cards */
.subject-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
    padding: 20px;
    transition: all 0.3s ease;
    height: 100%;
}

.subject-card:hover {
    border-color: #667eea;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.15);
    transform: translateY(-3px);
}

.subject-header {
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 12px;
    margin-bottom: 15px;
}

.subject-header h6 {
    color: #333;
    font-weight: 600;
    margin-bottom: 5px;
}

.subject-code {
    font-size: 0.75rem;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.subject-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.subject-stat {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.subject-stat i {
    width: 20px;
}

/* Grade Badges */
.grade-badge {
    padding: 6px 12px;
    font-size: 0.9rem;
    font-weight: 600;
}

.grade-excellent {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.grade-good {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.grade-needs-improvement {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    background: #f9fafb;
    border-radius: 12px;
    border-left: 4px solid #667eea;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #f3f4f6;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

.activity-item.overdue {
    border-left-color: #ef4444;
    background: #fef2f2;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.activity-item.overdue .activity-icon {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.activity-details {
    flex: 1;
}

.activity-details h6 {
    color: #333;
    font-weight: 600;
    margin-bottom: 5px;
}

.activity-status {
    flex-shrink: 0;
}

/* Lesson List */
.lesson-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.lesson-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f9fafb;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.lesson-item:hover {
    background: #f3f4f6;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

.lesson-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.lesson-details {
    flex: 1;
}

.lesson-details h6 {
    color: #333;
    font-weight: 600;
    margin-bottom: 3px;
}

.lesson-date {
    text-align: right;
    flex-shrink: 0;
}

/* Attendance Circle */
.attendance-overview {
    padding: 20px;
}

.attendance-stat h4 {
    font-size: 2rem;
    font-weight: 700;
}

.attendance-stat small {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.02);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .child-hero-card .row {
        text-align: center;
    }
    
    .child-photo {
        width: 120px;
        height: 120px;
    }
    
    .stat-card {
        padding: 20px;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .stat-content h3 {
        font-size: 1.5rem;
    }
}

/* Print Styles */
@media print {
    .child-selector-card {
        page-break-inside: avoid;
    }
}
</style>

<script>
function switchChild(childId) {
    if (childId) {
        window.location.href = '{{ route("dashboard") }}?child_id=' + childId;
    }
}
</script>
