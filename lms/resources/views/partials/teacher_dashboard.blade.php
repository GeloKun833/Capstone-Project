<div class="page-header">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-sub-header">
                <h3 class="page-title">Welcome {{ $teacher['teacher']->full_name ?? 'Teacher' }}!</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Teacher</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions Section --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('assignments.create') }}" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-tasks fa-2x mb-2"></i>
                            <span class="fw-bold">Create Assignment</span>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('class-posts.create') }}" class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-bullhorn fa-2x mb-2"></i>
                            <span class="fw-bold">Create Class Post</span>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('assignments.index') }}" class="btn btn-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-list fa-2x mb-2"></i>
                            <span class="fw-bold">View Assignments</span>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('class-posts.index') }}" class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-comments fa-2x mb-2"></i>
                            <span class="fw-bold">View Class Posts</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-sm-6 col-12 d-flex">
        <div class="card bg-comman w-100">
            <div class="card-body">
                <div class="db-widgets d-flex justify-content-between align-items-center">
                    <div class="db-info">
                        <h6>Total Classes</h6>
                        <h3>{{ $teacher['totalClasses'] }}</h3>
                    </div>
                    <div class="db-icon">
                        <img src="{{ URL::to('assets/img/icons/teacher-icon-01.svg') }}" alt="Dashboard Icon">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12 d-flex">
        <div class="card bg-comman w-100">
            <div class="card-body">
                <div class="db-widgets d-flex justify-content-between align-items-center">
                    <div class="db-info">
                        <h6>Total Students</h6>
                        <h3>{{ $teacher['totalStudents'] }}</h3>
                    </div>
                    <div class="db-icon">
                        <img src="{{ URL::to('assets/img/icons/dash-icon-01.svg') }}" alt="Dashboard Icon">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12 d-flex">
        <div class="card bg-comman w-100">
            <div class="card-body">
                <div class="db-widgets d-flex justify-content-between align-items-center">
                    <div class="db-info">
                        <h6>Total Lessons</h6>
                        <h3>{{ $teacher['totalLessons'] }}</h3>
                    </div>
                    <div class="db-icon">
                        <img src="{{ URL::to('assets/img/icons/teacher-icon-02.svg') }}" alt="Dashboard Icon">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12 d-flex">
        <div class="card bg-comman w-100">
            <div class="card-body">
                <div class="db-widgets d-flex justify-content-between align-items-center">
                    <div class="db-info">
                        <h6>Total Hours</h6>
                        <h3>{{ $teacher['totalHours'] }}</h3>
                    </div>
                    <div class="db-icon">
                        <img src="{{ URL::to('assets/img/icons/teacher-icon-03.svg') }}" alt="Dashboard Icon">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Teacher's Assigned Subjects by Grade Level --}}
@if(isset($teacher['teacherSubjects']) && $teacher['teacherSubjects']->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-book-open me-2"></i>My Assigned Subjects (Grouped by Grade Level)
                </h5>
            </div>
            <div class="card-body">
                @foreach($teacher['teacherSubjects'] as $gradeLevel => $subjects)
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-graduation-cap me-2"></i>{{ $gradeLevel }}
                        </h6>
                        <div class="row g-3">
                            @foreach($subjects as $subject)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <h6 class="card-title text-dark mb-2">
                                                <i class="fas fa-book me-2 text-primary"></i>{{ $subject->subject_name }}
                                            </h6>
                                            <p class="card-text text-muted mb-2">
                                                <small><strong>Subject ID:</strong> {{ $subject->subject_id }}</small>
                                            </p>
                                            @if($subject->sections && $subject->sections->isNotEmpty())
                                                <p class="card-text mb-2">
                                                    <small><strong>Sections:</strong></small><br>
                                                    @foreach($subject->sections as $section)
                                                        <span class="badge bg-info me-1 mb-1">{{ $section->name }}</span>
                                                    @endforeach
                                                </p>
                                            @else
                                                <p class="card-text text-muted mb-2">
                                                    <small><i class="fas fa-info-circle me-1"></i>No sections assigned</small>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if(!$loop->last)
                        <hr class="my-4">
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12 col-lg-12 col-xl-8">
        <div class="row">
            <div class="col-12 col-lg-8 col-xl-8 d-flex">
                <div class="card flex-fill comman-shadow">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h5 class="card-title">Upcoming Lesson</h5>
                            </div>
                            <div class="col-6">
                                <span class="float-end view-link"><a href="#">View All
                                        Courses</a></span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-3 pb-3">
                        <div class="table-responsive lesson">
                            <table class="table table-center">
                                <tbody>
                                    @forelse($teacher['upcomingLessons'] as $lesson)
                                    <tr>
                                        <td>
                                            <div class="date">
                                                <b>{{ $lesson->subject->subject_name ?? 'Subject' }}</b>
                                                <p>{{ $lesson->title ?? 'Lesson' }}</p>
                                                <ul class="teacher-date-list">
                                                    <li><i class="fas fa-calendar-alt me-2"></i>{{ $lesson->start_time ? $lesson->start_time->format('M d, Y') : 'N/A' }}</li>
                                                    <li>|</li>
                                                    <li><i class="fas fa-clock me-2"></i>{{ $lesson->start_time ? $lesson->start_time->format('h:i A') : 'N/A' }} - {{ $lesson->end_time ? $lesson->end_time->format('h:i A') : 'N/A' }}</li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="lesson-confirm">
                                                <a href="#">Confirmed</a>
                                            </div>
                                            <button type="submit" class="btn btn-info">Reschedule</button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>No upcoming lessons available
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4 col-xl-4 d-flex">
                <div class="card flex-fill comman-shadow">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-12">
                                <h5 class="card-title">Semester Progress</h5>
                            </div>
                        </div>
                    </div>
                    <div class="dash-widget">
                        <div class="circle-bar circle-bar1">
                            <div class="circle-graph1" data-percent="{{ $teacher['semesterProgress'] }}">
                                <div class="progress-less">
                                    <b>{{ $teacher['semesterProgress'] }}%</b>
                                    <p>Lesson Progressed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-12 col-xl-12 d-flex">
                <div class="card flex-fill comman-shadow">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h5 class="card-title">Teaching Activity</h5>
                            </div>
                            <div class="col-6">
                                <ul class="chart-list-out">
                                    <li><span class="circle-blue"></span>Teacher</li>
                                    <li><span class="circle-green"></span>Students</li>
                                    <li class="star-menus"><a href="javascript:;"><i
                                                class="fas fa-ellipsis-v"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="school-area"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-12 col-xl-12 d-flex">
                <div class="card flex-fill comman-shadow">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title">Teaching History</h5>
                        <ul class="chart-list-out student-ellips">
                            <li class="star-menus"><a href="javascript:;"><i
                                        class="fas fa-ellipsis-v"></i></a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="teaching-card">
                            <ul class="steps-history">
                                <li>Sep22</li>
                                <li>Sep23</li>
                                <li>Sep24</li>
                            </ul>
                            <ul class="activity-feed">
                                @forelse($teacher['teachingHistory'] as $history)
                                <li class="feed-item d-flex align-items-center">
                                    <div class="dolor-activity">
                                        <span class="feed-text1"><a>{{ $history->subject->subject_name ?? 'Subject' }}</a></span>
                                        <ul class="teacher-date-list">
                                            <li><i class="fas fa-calendar-alt me-2"></i>{{ $history->start_time ? $history->start_time->format('F d, Y') : 'N/A' }}</li>
                                            <li>|</li>
                                            <li><i class="fas fa-clock me-2"></i>{{ $history->start_time ? $history->start_time->format('h:i A') : 'N/A' }} - {{ $history->end_time ? $history->end_time->format('h:i A') : 'N/A' }}</li>
                                        </ul>
                                    </div>
                                    <div class="activity-btns ms-auto">
                                        <button type="submit" class="btn btn-info">{{ $history->start_time && $history->start_time->isPast() ? 'Completed' : 'In Progress' }}</button>
                                    </div>
                                </li>
                                @empty
                                <li class="feed-item d-flex align-items-center">
                                    <div class="dolor-activity text-center text-muted w-100">
                                        <i class="fas fa-info-circle me-2"></i>No teaching history available
                                    </div>
                                </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-12 col-xl-4 d-flex">
        <div class="card flex-fill comman-shadow">
            <div class="card-body">
                <div id="calendar-doctor" class="calendar-container"></div>
                <div class="calendar-info calendar-info1">
                    <div class="up-come-header">
                        <h2>Upcoming Events</h2>
                        <span><a href="javascript:;"><i class="feather-plus"></i></a></span>
                    </div>
                                            @forelse($teacher['upcomingEvents']->groupBy(function($event) { return $event->start_time->format('d M'); }) as $date => $events)
                    <div class="upcome-event-date">
                        <h3>{{ $date }}</h3>
                        <span><i class="fas fa-ellipsis-h"></i></span>
                    </div>
                    @foreach($events as $event)
                    <div class="calendar-details">
                        <p>{{ $event->start_time->format('h:i A') }}</p>
                        <div class="calendar-box normal-bg">
                            <div class="calandar-event-name">
                                <h4>{{ $event->subject->subject_name ?? 'Subject' }}</h4>
                                <h5>{{ $event->title ?? 'Event' }}</h5>
                            </div>
                            <span>{{ $event->start_time->format('h:i A') }} - {{ $event->end_time->format('h:i A') }}</span>
                        </div>
                    </div>
                    @endforeach
                    @empty
                    <div class="calendar-details">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-info-circle me-2"></i>No upcoming events available
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
