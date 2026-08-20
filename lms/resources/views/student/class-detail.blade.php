@extends('layouts.master')
@section('content')


<style>
/* ========================================
   PROFESSIONAL CLASS DETAIL PAGE STYLING - MINIMALIST
   ======================================== */

/* Page Background - Minimalist */
.page-wrapper {
    background: #f8f9fa !important;
}

.content.container-fluid {
    padding: 20px !important;
}

/* Minimalist Hero Section */
.hero-section {
    background: white;
    color: #2c323f;
    padding: 20px 25px;
    margin: -20px -20px 20px -20px;
    border-bottom: 2px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.hero-background {
    display: none;
}

.hero-content {
    position: relative;
}

.hero-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: #2c323f;
}

.hero-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 15px;
    font-weight: 500;
}

.hero-meta {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8f9fa;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #495057;
    border: 1px solid #e9ecef;
}

.meta-item i {
    color: #667eea;
    font-size: 0.9rem;
}

.hero-sidebar {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.status-card,
.schedule-card {
    background: white;
    border-radius: 8px;
    padding: 16px;
    border: 1px solid #e9ecef;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.status-header,
.schedule-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}

.status-header i,
.schedule-header i {
    color: #667eea;
    font-size: 1rem;
}

.status-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #28a745;
    margin-bottom: 12px;
    text-align: center;
}

.status-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.85rem;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row .label {
    color: #6c757d;
    font-weight: 500;
}

.detail-row .value {
    font-weight: 600;
    color: #2c323f;
}

.schedule-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.schedule-item {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 10px;
    border: 1px solid #e9ecef;
}

.schedule-item .day {
    font-weight: 700;
    font-size: 0.9rem;
    color: #667eea;
    margin-bottom: 4px;
}

.schedule-item .time {
    font-size: 0.8rem;
    margin-bottom: 2px;
    font-weight: 600;
    color: #495057;
}

.schedule-item .room {
    font-size: 0.75rem;
    color: #6c757d;
}

/* Minimalist Tab Navigation */
.modern-tabs {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
}

.tabs-wrapper {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.tab-button {
    flex: 1;
    min-width: 140px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer !important;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.9rem;
    color: #495057;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    position: relative;
    z-index: 1;
    pointer-events: auto;
}

.tab-button:hover {
    background: #f8f9fa;
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
}

.tab-button:active {
    transform: scale(0.98);
}

.tab-button.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.tab-button.active:hover {
    background: #5568d3;
    transform: translateY(-2px);
}

.tab-icon {
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.tab-button.active .tab-icon {
    color: white;
}

.tab-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    pointer-events: none;
}

.tab-title {
    font-weight: 600;
    font-size: 0.9rem;
    pointer-events: none;
}

.tab-subtitle {
    font-size: 0.7rem;
    opacity: 0.7;
    pointer-events: none;
}

.tab-button.active .tab-subtitle {
    opacity: 0.9;
}

/* Minimalist Tab Content */
.tab-content-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
}

.tab-panel {
    display: none;
    padding: 25px;
}

.tab-panel.active {
    display: block;
}

/* Minimalist Assignment Cards */
.assignments-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.assignment-card {
    background: white;
    border: 1px solid #e9ecef;
    border-left: 3px solid #667eea;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.assignment-card:hover {
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.assignment-card.overdue {
    border-left-color: #dc3545;
}

.assignment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f0f0f0;
}

.assignment-title h5 {
    margin: 0 0 5px 0;
    color: #2c323f;
    font-weight: 700;
    font-size: 1.1rem;
}

.assignment-code {
    color: #6c757d;
    font-size: 0.75rem;
    font-weight: 600;
}

.assignment-status .badge {
    font-size: 0.7rem;
    padding: 4px 10px;
    border-radius: 4px;
    font-weight: 700;
    text-transform: uppercase;
}

.assignment-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 15px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #495057;
    font-size: 0.85rem;
    font-weight: 500;
}

.detail-item i {
    width: 16px;
    text-align: center;
    font-size: 0.9rem;
    color: #667eea;
}

.assignment-description {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 6px;
    border-left: 3px solid #667eea;
    margin-bottom: 15px;
}

.assignment-description p {
    margin: 0;
    color: #495057;
    font-size: 0.9rem;
    line-height: 1.5;
}

.assignment-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.assignment-actions .btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
    border: 1px solid transparent;
}

.assignment-actions .btn-primary {
    background: #667eea;
    border-color: #667eea;
}

.assignment-actions .btn-primary:hover {
    background: #5568d3;
}

.assignment-actions .btn-success {
    background: #28a745;
    border-color: #28a745;
}

.assignment-actions .btn-success:hover {
    background: #218838;
}

.assignment-actions .btn-info {
    background: #17a2b8;
    border-color: #17a2b8;
}

.assignment-actions .btn-info:hover {
    background: #138496;
}

/* Minimalist Empty State - Extra Compact */
.empty-state {
    text-align: center;
    padding: 20px 15px;
}

.empty-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    border: 1px solid #e9ecef;
}

.empty-icon i {
    font-size: 16px;
    color: #6c757d;
}

.empty-state h4 {
    color: #2c323f;
    margin-bottom: 4px;
    font-weight: 600;
    font-size: 0.9rem;
}

.empty-state p {
    color: #6c757d;
    font-size: 0.8rem;
    margin: 0;
}

/* Minimalist Class Posts */
.class-posts-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.post-card {
    background: white;
    border: 1px solid #e9ecef;
    border-left: 3px solid #ffc107;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.post-card:hover {
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.post-actions .btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
    background: #667eea;
    border: 1px solid #667eea;
}

.post-actions .btn:hover {
    background: #5568d3;
}

/* Responsive - Minimalist */
@media (max-width: 768px) {
    .hero-section { padding: 15px 20px; }
    .hero-title { font-size: 1.5rem; }
    .hero-subtitle { font-size: 0.8rem; }
    .meta-item { font-size: 0.8rem; padding: 5px 10px; }
    .tabs-wrapper { gap: 8px; }
    .tab-button { min-width: 120px; padding: 10px 12px; font-size: 0.8rem; }
    .tab-panel { padding: 20px 15px; }
    .assignment-card { padding: 15px; }
    .status-card, .schedule-card { padding: 12px; }
}
</style>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Modern Hero Section -->
            <div class="hero-section">
                <div class="hero-background"></div>
                <div class="hero-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="hero-main">
                                <h1 class="hero-title">{{ $enrollment->subject->subject_name }}</h1>
                                <div class="hero-subtitle">{{ $enrollment->subject->class ?? 'General Course' }}</div>
                                <div class="hero-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-user-graduate"></i>
                                        <span>{{ $student->first_name }} {{ $student->last_name }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-id-card"></i>
                                        <span>Student #{{ $student->admission_id ?? 'N/A' }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-users"></i>
                                        <span>{{ $student->section ?? '4IT-B' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="hero-sidebar">
                                <div class="status-card">
                                    <div class="status-header">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Enrollment Status</span>
                                    </div>
                                    <div class="status-value">ENROLLED</div>
                                    <div class="status-details">
                                        <div class="detail-row">
                                            <span class="label">Academic Year:</span>
                                            <span class="value">{{ $enrollment->academicYear->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Semester:</span>
                                            <span class="value">{{ $enrollment->semester->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Class ID:</span>
                                            <span class="value">#{{ $enrollment->subject->id }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="schedule-card">
                                    <div class="schedule-header">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Class Schedule</span>
                                    </div>
                                    <div class="schedule-list">
                                        <div class="schedule-item">
                                            <div class="day">Tuesday</div>
                                            <div class="time">11:30 AM - 1:00 PM</div>
                                            <div class="room">Network Room</div>
                                        </div>
                                        <div class="schedule-item">
                                            <div class="day">Friday</div>
                                            <div class="time">11:30 AM - 1:00 PM</div>
                                            <div class="room">Network Room</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Tab Navigation -->
            <div class="modern-tabs">
                <div class="tabs-container">
                    <div class="tabs-wrapper">
                        <button class="tab-button {{ $activeTab === 'assignments' ? 'active' : '' }}" 
                                data-tab="assignments"
                                onclick="switchTab('assignments')">
                            <div class="tab-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="tab-text">
                                <span class="tab-title">Assignments</span>
                                <span class="tab-subtitle">View & submit work</span>
                            </div>
                        </button>
                        
                        <button class="tab-button {{ $activeTab === 'class-posts' ? 'active' : '' }}" 
                                data-tab="class-posts"
                                onclick="switchTab('class-posts')">
                            <div class="tab-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div class="tab-text">
                                <span class="tab-title">Class Posts</span>
                                <span class="tab-subtitle">Announcements</span>
                            </div>
                        </button>
                        
                        <button class="tab-button {{ $activeTab === 'grades' ? 'active' : '' }}" 
                                data-tab="grades"
                                onclick="switchTab('grades')">
                            <div class="tab-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="tab-text">
                                <span class="tab-title">Grades</span>
                                <span class="tab-subtitle">Performance tracking</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <script>
            // Inline script to ensure it loads immediately
            function switchTab(tabName) {
                console.log('Switching to tab:', tabName);
                
                // Remove active from all buttons
                var buttons = document.querySelectorAll('.tab-button');
                buttons.forEach(function(btn) {
                    btn.classList.remove('active');
                });
                
                // Add active to clicked button
                var activeButton = document.querySelector('.tab-button[data-tab="' + tabName + '"]');
                if (activeButton) {
                    activeButton.classList.add('active');
                    console.log('Button activated');
                }
                
                // Hide all panels
                var panels = document.querySelectorAll('.tab-panel');
                panels.forEach(function(panel) {
                    panel.style.display = 'none';
                    panel.classList.remove('active');
                });
                
                // Show selected panel
                var targetPanel = document.getElementById(tabName + '-panel');
                if (targetPanel) {
                    targetPanel.style.display = 'block';
                    targetPanel.classList.add('active');
                    console.log('Panel shown:', tabName + '-panel');
                } else {
                    console.error('Panel not found:', tabName + '-panel');
                }
            }
            </script>

            <!-- Modern Tab Content -->
            <div class="tab-content-container">
                <!-- Assignments Tab -->
                <div class="tab-panel {{ $activeTab === 'assignments' ? 'active' : '' }}" 
                     id="assignments-panel"
                     style="display: {{ $activeTab === 'assignments' ? 'block' : 'none' }}">
                        @if($assignments->count() > 0)
                            <div class="assignments-list">
                                @foreach($assignments as $assignment)
                                    @php
                                        $submission = $assignment->submissions()
                                            ->where('student_id', $student->id)
                                            ->first();
                                        $status = $submission ? $submission->status : 'pending';
                                        $isOverdue = now() > $assignment->due_date;
                                    @endphp
                                    
                                    <div class="assignment-card {{ $isOverdue && $status === 'pending' ? 'overdue' : '' }}">
                                        <div class="assignment-header">
                                            <div class="assignment-title">
                                                <h5>{{ $assignment->title }}</h5>
                                                <span class="assignment-code">#{{ $assignment->id }}</span>
                                            </div>
                                            <div class="assignment-status">
                                                @if($status === 'submitted')
                                                    <span class="badge bg-info">Submitted</span>
                                                @elseif($status === 'graded')
                                                    <span class="badge bg-success">Graded</span>
                                                @elseif($status === 'late')
                                                    <span class="badge bg-warning">Late</span>
                                                @elseif($isOverdue)
                                                    <span class="badge bg-danger">Overdue</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="assignment-body">
                                            <div class="assignment-details">
                                                <div class="detail-item">
                                                    <i class="fas fa-user text-primary"></i>
                                                    <span>{{ $assignment->teacher->name ?? 'Teacher' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <i class="fas fa-calendar text-warning"></i>
                                                    <span>Due: {{ $assignment->due_date->format('M d, Y g:i A') }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <i class="fas fa-star text-success"></i>
                                                    <span>Max Score: {{ $assignment->max_score }}</span>
                                                </div>
                                                @if($submission)
                                                    <div class="detail-item">
                                                        <i class="fas fa-upload text-info"></i>
                                                        <span>Submitted: {{ $submission->submitted_at->format('M d, Y g:i A') }}</span>
                                                    </div>
                                                    @if($submission->score)
                                                        <div class="detail-item">
                                                            <i class="fas fa-trophy text-warning"></i>
                                                            <span>Score: {{ $submission->score }}/{{ $assignment->max_score }}</span>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                            
                                            @if($assignment->description)
                                                <div class="assignment-description">
                                                    <p>{{ Str::limit($assignment->description, 150) }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="assignment-actions">
                                            @if(!$submission)
                                                <a href="{{ route('student.assignments.show', $assignment) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i> View Assignment
                                                </a>
                                                @if($assignment->canSubmit())
                                                    <a href="{{ route('student.assignments.show', $assignment) }}" class="btn btn-success btn-sm">
                                                        <i class="fas fa-upload"></i> Submit Work
                                                    </a>
                                                @endif
                                            @else
                                                <a href="{{ route('student.assignments.submission', $assignment) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> View Submission
                                                </a>
                                                @if($submission->status === 'submitted' && !$submission->score)
                                                    <span class="text-muted">Waiting for grading...</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <h4>No Assignments Found</h4>
                                <p>There are no assignments available for this class at the moment.</p>
                            </div>
                        @endif
                </div>

                <!-- Class Posts Tab -->
                <div class="tab-panel {{ $activeTab === 'class-posts' ? 'active' : '' }}" 
                     id="class-posts-panel"
                     style="display: {{ $activeTab === 'class-posts' ? 'block' : 'none' }}">
                        @if($classPosts->count() > 0)
                            <div class="class-posts-list">
                                @foreach($classPosts as $post)
                                    <div class="post-card">
                                        <div class="post-header">
                                            <div class="post-title">
                                                <h5>{{ $post->title }}</h5>
                                                <span class="post-code">#{{ $post->id }}</span>
                                            </div>
                                            <div class="post-meta">
                                                <span class="post-date">{{ $post->created_at->format('M d, Y g:i A') }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="post-body">
                                            <div class="post-details">
                                                <div class="detail-item">
                                                    <i class="fas fa-user text-primary"></i>
                                                    <span>{{ $post->teacher->name ?? 'Teacher' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <i class="fas fa-tag text-info"></i>
                                                    <span>{{ $post->type ?? 'General' }}</span>
                                                </div>
                                            </div>
                                            
                                            @if($post->content)
                                                <div class="post-content">
                                                    <p>{{ Str::limit($post->content, 200) }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($post->file_path)
                                                <div class="post-attachments">
                                                    <i class="fas fa-paperclip text-muted"></i>
                                                    <span class="text-muted">Has attachments</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <h4>No Class Posts Found</h4>
                                <p>There are no class posts available for this class at the moment.</p>
                            </div>
                        @endif
                </div>

                <!-- Grades Tab -->
                <div class="tab-panel {{ $activeTab === 'grades' ? 'active' : '' }}" 
                     id="grades-panel"
                     style="display: {{ $activeTab === 'grades' ? 'block' : 'none' }}">
                        @if($grades->count() > 0)
                            <div class="grades-list">
                                @foreach($grades as $grade)
                                    <div class="grade-card">
                                        <div class="grade-header">
                                            <div class="grade-title">
                                                <h5>{{ $grade->component->name ?? 'Grade Component' }}</h5>
                                                <span class="grade-code">#{{ $grade->id }}</span>
                                            </div>
                                            <div class="grade-score">
                                                <span class="score-badge {{ $grade->percentage >= 90 ? 'bg-success' : ($grade->percentage >= 75 ? 'bg-info' : 'bg-warning') }}">
                                                    {{ $grade->percentage }}%
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="grade-body">
                                            <div class="grade-details">
                                                <div class="detail-item">
                                                    <i class="fas fa-user text-primary"></i>
                                                    <span>{{ $grade->teacher->name ?? 'Teacher' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <i class="fas fa-calendar text-warning"></i>
                                                    <span>Posted: {{ $grade->created_at->format('M d, Y') }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <i class="fas fa-star text-success"></i>
                                                    <span>Score: {{ $grade->score }}/{{ $grade->max_score }}</span>
                                                </div>
                                                @if($grade->remarks)
                                                    <div class="detail-item">
                                                        <i class="fas fa-comment text-info"></i>
                                                        <span>{{ $grade->remarks }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <!-- Grade Summary -->
                                <div class="grade-summary mt-4">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="summary-card text-center">
                                                <div class="summary-icon">
                                                    <i class="fas fa-trophy text-warning"></i>
                                                </div>
                                                <div class="summary-value">{{ $grades->avg('percentage') ? round($grades->avg('percentage'), 1) : 0 }}%</div>
                                                <div class="summary-label">Average Grade</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="summary-card text-center">
                                                <div class="summary-icon">
                                                    <i class="fas fa-chart-line text-success"></i>
                                                </div>
                                                <div class="summary-value">{{ $grades->max('percentage') ?? 0 }}%</div>
                                                <div class="summary-label">Highest Grade</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="summary-card text-center">
                                                <div class="summary-icon">
                                                    <i class="fas fa-chart-bar text-info"></i>
                                                </div>
                                                <div class="summary-value">{{ $grades->min('percentage') ?? 0 }}%</div>
                                                <div class="summary-label">Lowest Grade</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="summary-card text-center">
                                                <div class="summary-icon">
                                                    <i class="fas fa-list text-primary"></i>
                                                </div>
                                                <div class="summary-value">{{ $grades->count() }}</div>
                                                <div class="summary-label">Total Grades</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <h4>No Grades Posted</h4>
                                <p>No grades have been posted for this class yet.</p>
                            </div>
                        @endif
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
// Pure JavaScript tab switching (more reliable)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Tab system initializing...');
    
    // Get all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    console.log('Found tab buttons:', tabButtons.length);
    
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const tabName = this.getAttribute('data-tab');
            console.log('=== Tab Clicked ===');
            console.log('Tab name:', tabName);
            
            // Remove active from all buttons
            document.querySelectorAll('.tab-button').forEach(function(btn) {
                btn.classList.remove('active');
            });
            
            // Add active to clicked button
            this.classList.add('active');
            console.log('Button activated:', tabName);
            
            // Hide all panels
            document.querySelectorAll('.tab-panel').forEach(function(panel) {
                panel.classList.remove('active');
                panel.style.display = 'none';
            });
            
            // Show clicked panel
            const targetPanel = document.getElementById(tabName + '-panel');
            console.log('Target panel:', tabName + '-panel');
            console.log('Panel found:', targetPanel !== null);
            
            if (targetPanel) {
                targetPanel.classList.add('active');
                targetPanel.style.display = 'block';
                console.log('Panel shown successfully!');
            } else {
                console.error('Panel not found:', tabName + '-panel');
            }
            
            // Update URL
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('tab', tabName);
            window.history.pushState({}, '', currentUrl);
        });
    });
    
    // Initialize tooltips if available
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
});
</script>
@endpush

@endsection
