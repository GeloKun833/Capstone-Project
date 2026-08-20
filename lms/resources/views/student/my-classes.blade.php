@extends('layouts.master')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Modern Header Section -->
            <div class="modern-header">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="header-content">
                                <h1 class="page-title">
                                    <i class="fas fa-graduation-cap"></i>
                                    My Classes
                                </h1>
                                <p class="page-subtitle">Manage your enrolled courses and track your academic progress</p>
                                <div class="header-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-book-open"></i>
                                        <span>{{ $enrollments->count() }} Active Courses</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>{{ $enrollments->unique('academic_year_id')->count() }} Academic Years</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-chart-line"></i>
                                        <span>{{ $performanceStats['average_grade'] ?? 0 }}% Average</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="header-actions">
                                <div class="search-container">
                                    <div class="search-box">
                                        <input type="text" id="classSearch" placeholder="Search courses..." class="form-control">
                                        <i class="fas fa-search"></i>
                                    </div>
                                </div>
                                <div class="filter-container">
                                    <div class="filter-buttons">
                                        <button class="filter-btn active" data-filter="all">
                                            <i class="fas fa-th"></i>
                                            All
                                        </button>
                                        <button class="filter-btn" data-filter="active">
                                            <i class="fas fa-play-circle"></i>
                                            Active
                                        </button>
                                        <button class="filter-btn" data-filter="completed">
                                            <i class="fas fa-check-circle"></i>
                                            Completed
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classes Grid Section -->
            <div class="classes-section">
                <div class="container-fluid">
                    @if($enrollments->count() > 0)
                        <div class="classes-grid" id="classesGrid">
                            @foreach($enrollments as $enrollment)
                                @php
                                    // Calculate progress based on grades
                                    $grades = $enrollment->student->grades()->where('subject_id', $enrollment->subject->id)->get();
                                    $progress = $grades->count() > 0 ? min(100, ($grades->avg('percentage') ?? 0)) : 0;
                                    
                                    // Get assignment counts
                                    $assignmentCount = \App\Models\Assignment::where('subject_id', $enrollment->subject->id)
                                        ->where('status', 'published')
                                        ->where('is_active', true)
                                        ->count();
                                    
                                    $pendingAssignments = \App\Models\Assignment::where('subject_id', $enrollment->subject->id)
                                        ->where('status', 'published')
                                        ->where('is_active', true)
                                        ->where('due_date', '>', now())
                                        ->count();
                                    
                                    $overdueAssignments = \App\Models\Assignment::where('subject_id', $enrollment->subject->id)
                                        ->where('status', 'published')
                                        ->where('is_active', true)
                                        ->where('due_date', '<', now())
                                        ->count();
                                    
                                    // Get next class schedule
                                    $nextClass = \App\Models\ClassSchedule::where('subject_id', $enrollment->subject->id)
                                        ->where('is_active', true)
                                        ->where('day_of_week', strtolower(now()->format('l')))
                                        ->where('start_time', '>', now()->format('H:i:s'))
                                        ->first();
                                @endphp
                                
                                <div class="class-card" 
                                     data-status="{{ $enrollment->status }}" 
                                     data-subject="{{ strtolower($enrollment->subject->subject_name) }}">
                                    <!-- Card Header -->
                                    <div class="card-header">
                                        <div class="subject-info">
                                            <h3 class="subject-title">{{ $enrollment->subject->subject_name }}</h3>
                                            <div class="subject-category">{{ $enrollment->subject->class ?? 'General' }}</div>
                                        </div>
                                        <div class="status-badge {{ $enrollment->status === 'active' ? 'active' : 'inactive' }}">
                                            <span class="status-dot"></span>
                                            {{ ucfirst($enrollment->status) }}
                                        </div>
                                    </div>
                                    
                                    <!-- Card Body -->
                                    <div class="card-body">
                                        <!-- Course Details -->
                                        <div class="course-details">
                                            <div class="detail-grid">
                                                <div class="detail-item">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <div class="detail-content">
                                                        <span class="detail-label">Academic Year</span>
                                                        <span class="detail-value">{{ $enrollment->academicYear->name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                                <div class="detail-item">
                                                    <i class="fas fa-clock"></i>
                                                    <div class="detail-content">
                                                        <span class="detail-label">Semester</span>
                                                        <span class="detail-value">{{ $enrollment->semester->name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                                <div class="detail-item">
                                                    <i class="fas fa-user-graduate"></i>
                                                    <div class="detail-content">
                                                        <span class="detail-label">Enrolled</span>
                                                        <span class="detail-value">{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('M d, Y') : 'N/A' }}</span>
                                                    </div>
                                                </div>
                                                <div class="detail-item">
                                                    <i class="fas fa-users"></i>
                                                    <div class="detail-content">
                                                        <span class="detail-label">Section</span>
                                                        <span class="detail-value">{{ $enrollment->student->sections->first()->name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Progress Section -->
                                        <div class="progress-section">
                                            <div class="progress-header">
                                                <span class="progress-label">Learning Progress</span>
                                                <span class="progress-percentage">{{ number_format($progress, 1) }}%</span>
                                            </div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: {{ $progress }}%"></div>
                                            </div>
                                            <div class="progress-stats">
                                                <div class="stat">
                                                    <span class="stat-number">{{ $grades->count() }}</span>
                                                    <span class="stat-label">Grades</span>
                                                </div>
                                                <div class="stat">
                                                    <span class="stat-number">{{ $assignmentCount }}</span>
                                                    <span class="stat-label">Assignments</span>
                                                </div>
                                                <div class="stat">
                                                    <span class="stat-number">{{ $nextClass ? 'Today' : 'None' }}</span>
                                                    <span class="stat-label">Next Class</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Quick Actions -->
                                        <div class="quick-actions">
                                            <a href="{{ route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'assignments']) }}" class="action-item {{ $overdueAssignments > 0 ? 'urgent' : '' }}" style="text-decoration: none; color: inherit;">
                                                <i class="fas fa-file-alt"></i>
                                                <div class="action-content">
                                                    <span class="action-label">Assignments</span>
                                                    <span class="action-count">{{ $assignmentCount }}</span>
                                                </div>
                                                @if($pendingAssignments > 0)
                                                    <span class="pending-badge">{{ $pendingAssignments }} pending</span>
                                                @endif
                                                @if($overdueAssignments > 0)
                                                    <span class="overdue-badge">{{ $overdueAssignments }} overdue</span>
                                                @endif
                                            </a>
                                            <a href="{{ route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'class-posts']) }}" class="action-item" style="text-decoration: none; color: inherit;">
                                                <i class="fas fa-bullhorn"></i>
                                                <div class="action-content">
                                                    <span class="action-label">Class Posts</span>
                                                    <span class="action-count">{{ \App\Models\ClassPost::where('subject_id', $enrollment->subject->id)->where('is_active', true)->count() }}</span>
                                                </div>
                                            </a>
                                            <a href="{{ route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'grades']) }}" class="action-item" style="text-decoration: none; color: inherit;">
                                                <i class="fas fa-chart-bar"></i>
                                                <div class="action-content">
                                                    <span class="action-label">Grades</span>
                                                    <span class="action-count">{{ $grades->count() }}</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Card Footer -->
                                    <div class="card-footer">
                                        <div class="action-buttons">
                                            <a href="{{ route('student.class.detail', $enrollment->id) }}" class="btn btn-primary btn-view">
                                                <i class="fas fa-eye"></i>
                                                View Details
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-more dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-download"></i> Download Materials</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-calendar"></i> View Schedule</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-chart-line"></i> View Grades</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-bell"></i> Set Reminders</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3>No Classes Enrolled</h3>
                            <p>You haven't enrolled in any classes yet. Contact your academic advisor to start your educational journey.</p>
                            <div class="empty-actions">
                                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Dashboard
                                </a>
                                <a href="#" class="btn btn-secondary">
                                    <i class="fas fa-compass"></i>
                                    Find Your Path
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Performance Summary Section -->
            @if($enrollments->count() > 0)
                <div class="performance-section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="summary-card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-chart-pie"></i> Academic Overview</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="stats-grid">
                                            <div class="stat-item">
                                                <div class="stat-icon">
                                                    <i class="fas fa-graduation-cap"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <span class="stat-value">{{ $enrollments->count() }}</span>
                                                    <span class="stat-label">Total Courses</span>
                                                </div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-icon">
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <span class="stat-value">{{ $performanceStats['average_grade'] ?? 0 }}%</span>
                                                    <span class="stat-label">Average Grade</span>
                                                </div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-icon">
                                                    <i class="fas fa-user-check"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <span class="stat-value">{{ $performanceStats['attendance_rate'] ?? 0 }}%</span>
                                                    <span class="stat-label">Attendance Rate</span>
                                                </div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-icon">
                                                    <i class="fas fa-tasks"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <span class="stat-value">{{ $performanceStats['total_assignments'] ?? 0 }}</span>
                                                    <span class="stat-label">Total Assignments</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <div class="summary-card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-calendar-alt"></i> Upcoming Deadlines</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="deadlines-list">
                                            @foreach($upcomingDeadlines as $deadline)
                                                @php
                                                    $daysLeft = $deadline['due_date']->diffInDays(now(), false);
                                                    $statusClass = $daysLeft < 0 ? 'overdue' : ($daysLeft == 0 ? 'today' : ($daysLeft <= 2 ? 'urgent' : 'normal'));
                                                    $statusText = $daysLeft < 0 ? 'Overdue' : ($daysLeft == 0 ? 'Today' : ($daysLeft == 1 ? '1 day left' : $daysLeft . ' days left'));
                                                @endphp
                                                <div class="deadline-item">
                                                    <div class="deadline-icon">
                                                        <i class="{{ $deadline['icon'] }}"></i>
                                                    </div>
                                                    <div class="deadline-content">
                                                        <span class="deadline-title">{{ $deadline['title'] }}</span>
                                                        <span class="deadline-date">Due: {{ $deadline['due_date']->format('M d, Y') }}</span>
                                                    </div>
                                                    <div class="deadline-status {{ $statusClass }}">{{ $statusText }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@push('styles')
<style>
/* Modern Header Styling */
.modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 50px 0;
    margin-bottom: 40px;
    border-radius: 0 0 30px 30px;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.modern-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 300px;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon fill="rgba(255,255,255,0.1)" points="0,0 100,0 100,100"/></svg>') no-repeat;
    background-size: cover;
}

.page-title {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 20px;
    line-height: 1.2;
}

.page-title i {
    font-size: 2.5rem;
    color: #ffd700;
    text-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
}

.page-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 25px;
    font-weight: 300;
    line-height: 1.5;
}

.header-stats {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1rem;
    opacity: 0.9;
    background: rgba(255, 255, 255, 0.1);
    padding: 12px 20px;
    border-radius: 25px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-item i {
    color: #ffd700;
    font-size: 1.1rem;
}

.header-actions {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.search-container {
    position: relative;
}

.search-box {
    position: relative;
}

.search-box input {
    padding: 15px 50px 15px 20px;
    border-radius: 30px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    color: white;
    backdrop-filter: blur(10px);
    font-size: 1rem;
    width: 100%;
    transition: all 0.3s ease;
}

.search-box input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.search-box input:focus {
    background: rgba(255, 255, 255, 0.2);
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
}

.search-box i {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.7);
    font-size: 1.1rem;
}

.filter-container {
    display: flex;
    justify-content: center;
}

.filter-buttons {
    display: flex;
    gap: 10px;
    background: rgba(255, 255, 255, 0.1);
    padding: 8px;
    border-radius: 25px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.filter-btn {
    background: transparent;
    border: none;
    color: white;
    padding: 10px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.filter-btn:hover,
.filter-btn.active {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.filter-btn i {
    font-size: 0.8rem;
}

/* Classes Section */
.classes-section {
    padding: 0 0 40px 0;
}

.classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

/* Class Card Styling */
.class-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.4s ease;
    border: 1px solid #e9ecef;
    position: relative;
}

.class-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
}

.class-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.class-card:hover::before {
    opacity: 1;
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 30px 30px 25px;
    position: relative;
    border-bottom: 1px solid #e9ecef;
}

.subject-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.subject-code {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    padding: 6px 12px;
    border-radius: 15px;
    align-self: flex-start;
}

.subject-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    line-height: 1.3;
}

.subject-category {
    font-size: 0.85rem;
    color: #667eea;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge {
    position: absolute;
    top: 25px;
    right: 25px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 8px 16px;
    border-radius: 20px;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.status-badge.active {
    color: #28a745;
    border: 1px solid #28a745;
}

.status-badge.inactive {
    color: #6c757d;
    border: 1px solid #6c757d;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-badge.active .status-dot {
    background: #28a745;
}

.status-badge.inactive .status-dot {
    background: #6c757d;
}

.card-body {
    padding: 30px;
}

/* Course Details */
.course-details {
    margin-bottom: 30px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.detail-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.detail-item i {
    color: #667eea;
    font-size: 1.1rem;
    margin-top: 2px;
    width: 20px;
    text-align: center;
}

.detail-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.detail-value {
    font-size: 0.95rem;
    color: #2c3e50;
    font-weight: 500;
}

/* Progress Section */
.progress-section {
    margin-bottom: 30px;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 15px;
    border: 1px solid #e9ecef;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.progress-label {
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.progress-percentage {
    font-size: 1.1rem;
    font-weight: 700;
    color: #667eea;
}

.progress-bar {
    height: 10px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 10px;
    transition: width 0.8s ease;
}

.progress-stats {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.stat {
    text-align: center;
    flex: 1;
}

.stat-number {
    display: block;
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* Quick Actions */
.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.action-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    position: relative;
    cursor: pointer;
}

.action-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.action-item.urgent {
    background: rgba(220, 53, 69, 0.1);
    border-color: rgba(220, 53, 69, 0.3);
    animation: pulse 2s infinite;
}

.action-item i {
    color: #667eea;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

.action-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.action-label {
    font-size: 0.9rem;
    color: #495057;
    font-weight: 500;
}

.action-count {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
}

.pending-badge,
.overdue-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pending-badge {
    background: rgba(255, 193, 7, 0.2);
    color: #856404;
}

.overdue-badge {
    background: rgba(220, 53, 69, 0.2);
    color: #721c24;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* Card Footer */
.card-footer {
    padding: 25px 30px 30px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.action-buttons {
    display: flex;
    gap: 15px;
}

.btn {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    flex: 1;
    justify-content: center;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: white;
    color: #6c757d;
    border: 2px solid #e9ecef;
    padding: 10px 16px;
}

.btn-secondary:hover {
    background: #f8f9fa;
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-2px);
}

/* Performance Section */
.performance-section {
    padding: 40px 0;
    background: #f8f9fa;
    border-radius: 30px 30px 0 0;
    margin-top: 40px;
}

.summary-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
    overflow: hidden;
    height: 100%;
}

.summary-card .card-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 25px 30px;
    border-bottom: none;
}

.summary-card .card-header h4 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
}

.summary-card .card-header h4 i {
    color: #ffd700;
}

.summary-card .card-body {
    padding: 30px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 15px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.stat-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.stat-content {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* Deadlines List */
.deadlines-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.deadline-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.deadline-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.deadline-icon {
    width: 40px;
    height: 40px;
    background: #667eea;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.deadline-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.deadline-title {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.deadline-date {
    font-size: 0.8rem;
    color: #6c757d;
}

.deadline-status {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.deadline-status.urgent {
    background: #ffc107;
    color: #212529;
}

.deadline-status.today {
    background: #28a745;
    color: white;
}

.deadline-status.overdue {
    background: #dc3545;
    color: white;
}

.deadline-status.normal {
    background: #6c757d;
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    max-width: 500px;
    margin: 0 auto;
}

.empty-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    font-size: 3rem;
    color: white;
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
}

.empty-state h3 {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
}

.empty-state p {
    font-size: 1.1rem;
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 30px;
}

.empty-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .classes-grid {
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    }
}

@media (max-width: 768px) {
    .modern-header {
        padding: 40px 0;
        border-radius: 0 0 20px 20px;
    }
    
    .page-title {
        font-size: 2.2rem;
    }
    
    .page-title i {
        font-size: 2rem;
    }
    
    .header-stats {
        justify-content: center;
        gap: 20px;
    }
    
    .stat-item {
        font-size: 0.9rem;
        padding: 10px 16px;
    }
    
    .header-actions {
        margin-top: 30px;
    }
    
    .classes-grid {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 0 15px;
    }
    
    .class-card {
        border-radius: 15px;
    }
    
    .card-header,
    .card-body,
    .card-footer {
        padding: 20px;
    }
    
    .subject-title {
        font-size: 1.4rem;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .empty-actions {
        flex-direction: column;
    }
    
    .empty-actions .btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.8rem;
    }
    
    .page-title i {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 1rem;
    }
    
    .stat-item {
        font-size: 0.8rem;
        padding: 8px 12px;
    }
    
    .classes-grid {
        padding: 0 10px;
    }
    
    .class-card {
        border-radius: 12px;
    }
    
    .card-header,
    .card-body,
    .card-footer {
        padding: 15px;
    }
    
    .subject-title {
        font-size: 1.2rem;
    }
    
    .progress-section {
        padding: 20px;
    }
    
    .detail-grid {
        gap: 12px;
    }
    
    .quick-actions {
        gap: 12px;
    }
    
    .action-item {
        padding: 12px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#classSearch').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.class-card').each(function() {
            const subjectName = $(this).find('.subject-title').text().toLowerCase();
            const category = $(this).find('.subject-category').text().toLowerCase();
            
            if (subjectName.includes(searchTerm) || category.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Filter functionality
    $('.filter-btn').on('click', function() {
        const filter = $(this).data('filter');
        
        // Update active button
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        // Filter classes
        $('.class-card').each(function() {
            const status = $(this).data('status');
            
            if (filter === 'all' || status === filter) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Smooth animations
    $('.class-card').hover(
        function() {
            $(this).find('.progress-fill').css('transition', 'width 0.3s ease');
        },
        function() {
            $(this).find('.progress-fill').css('transition', 'width 0.8s ease');
        }
    );
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush

@endsection 