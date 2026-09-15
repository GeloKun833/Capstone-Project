@php
    $s = $student;
    $hasStudent = $s['hasStudent'] ?? false;
    $record = $s['student'] ?? null;
    $enrollments = $s['enrollments'] ?? collect();
    $section = $s['section'] ?? ($record?->sections?->first());
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = $s['firstName'] ?? ($record?->first_name ?? 'Student');
    $displayName = $s['displayName'] ?? trim(($record?->first_name ?? '').' '.($record?->last_name ?? '')) ?: 'Student';
    $photo = !empty($record?->upload) ? asset('storage/'.$record->upload) : URL::to('assets/img/profiles/avatar-01.jpg');
    $enrollmentApplication = $record?->enrollmentApplication;
    $gpa = optional($s['currentGpa'] ?? null)->gpa;
@endphp

@if(!$hasStudent)
    <div class="sd-dashboard">
        <section class="sd-panel sd-empty-state">
            <i class="fas fa-user-graduate"></i>
            <h4>Student profile not found</h4>
            <p>Your student profile is being set up. Please contact the registrar if this continues.</p>
        </section>
    </div>
@else
<div class="sd-dashboard">
    <div class="sd-welcome">
        <div class="sd-welcome-who">
            <img src="{{ $photo }}" alt="{{ $displayName }}" class="sd-avatar">
            <div>
                <h1>{{ $greeting }}, {{ $firstName }}!</h1>
                <p>
                    {{ $record->year_level ?? 'Student' }}
                    @if($section)
                        · Section {{ $section->name }}
                    @endif
                    @if($gpa !== null)
                        · GPA {{ number_format((float) $gpa, 2) }}
                    @endif
                </p>
            </div>
        </div>
        <div class="sd-welcome-date">
            <i class="far fa-calendar-alt"></i>
            <span>{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>

    <div class="sd-stats">
        <div class="sd-stat-card">
            <div class="sd-stat-icon"><i class="fas fa-book"></i></div>
            <div>
                <div class="sd-stat-value">{{ $s['classCount'] ?? $enrollments->count() }}</div>
                <div class="sd-stat-label">My Classes</div>
                <div class="sd-stat-meta">Active enrollments</div>
            </div>
        </div>
        <div class="sd-stat-card">
            <div class="sd-stat-icon sd-stat-icon-warn"><i class="fas fa-clipboard-list"></i></div>
            <div>
                <div class="sd-stat-value">{{ $s['pendingAssignmentCount'] ?? 0 }}</div>
                <div class="sd-stat-label">Due Assignments</div>
                <div class="sd-stat-meta">Still need your work</div>
            </div>
        </div>
        <div class="sd-stat-card">
            <div class="sd-stat-icon"><i class="fas fa-clock"></i></div>
            <div>
                <div class="sd-stat-value">{{ $s['todayClassCount'] ?? 0 }}</div>
                <div class="sd-stat-label">Classes Today</div>
                <div class="sd-stat-meta">{{ now()->format('l') }} schedule</div>
            </div>
        </div>
        <div class="sd-stat-card">
            <div class="sd-stat-icon"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="sd-stat-value">{{ $s['attendancePercentage'] ?? 0 }}%</div>
                <div class="sd-stat-label">Attendance</div>
                <div class="sd-stat-meta">Present this term</div>
            </div>
        </div>
    </div>

    @if($enrollmentApplication && $enrollmentApplication->status !== 'approved')
        @php
            $requiredDocuments = [
                'birth_certificate' => 'Birth Certificate',
                'sf9' => 'SF9',
                'sf10' => 'SF10',
                'good_moral' => 'Good Moral',
                'id_photo' => 'ID Photo',
                'parent_guardian_id' => 'Parent/Guardian ID',
            ];
            $uploadedTypes = $enrollmentApplication->documents->pluck('document_type')->all();
            $missingDocuments = array_diff(array_keys($requiredDocuments), $uploadedTypes);
        @endphp
        <section class="sd-panel sd-enroll-banner">
            <div>
                <h2>Enrollment application</h2>
                <p class="sd-muted">Status: {{ ucfirst(str_replace('_', ' ', $enrollmentApplication->status)) }}
                    · Documents {{ $enrollmentApplication->documents->count() }}/6
                    @if(count($missingDocuments)) · Missing {{ count($missingDocuments) }} file(s)@endif
                </p>
            </div>
            <button type="button" class="sd-btn-outline" data-bs-toggle="modal" data-bs-target="#studentEnrollmentModal">View details</button>
        </section>
    @endif

    <div class="sd-layout">
        <div class="sd-main">
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>My Classes</h2>
                </div>
                <div class="sd-class-grid">
                    @forelse($enrollments as $enrollment)
                        <article class="sd-class-card">
                            <div class="sd-class-top">
                                <span class="sd-pill">{{ $enrollment->subject->class ?? ($record->year_level ?? 'Class') }}</span>
                                <span class="sd-status">Active</span>
                            </div>
                            <h3>{{ $enrollment->subject->subject_name ?? 'Subject' }}</h3>
                            <p class="sd-muted">
                                {{ $enrollment->academicYear->name ?? 'Academic year' }}
                                · {{ $enrollment->semester->name ?? 'Semester' }}
                            </p>
                            @if($section)
                                <p class="sd-meta"><i class="fas fa-users"></i> Section {{ $section->name }}</p>
                            @endif
                            <a href="{{ route('student.class.detail', $enrollment->id) }}" class="sd-btn-outline">Open class</a>
                        </article>
                    @empty
                        <div class="sd-empty">No classes yet. They will appear after enrollment is approved.</div>
                    @endforelse
                </div>
            </section>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Assignments</h2>
                    <a href="{{ route('student.assignments.index') }}" class="sd-link">View all</a>
                </div>
                <div class="sd-list">
                    @forelse(($s['upcomingAssignments'] ?? collect()) as $asg)
                        @php
                            $due = $asg->due_date ? $asg->due_date->format('M j, Y') : 'No due date';
                            $done = !empty($asg->is_submitted);
                        @endphp
                        <div class="sd-list-item">
                            <div class="sd-list-main">
                                <h4>{{ $asg->title }}</h4>
                                <p class="sd-muted">{{ $asg->subject->subject_name ?? 'Subject' }}</p>
                                <p class="sd-meta">Due {{ $due }}</p>
                            </div>
                            <div class="sd-list-actions">
                                <span class="sd-badge {{ $done ? 'sd-badge-ok' : 'sd-badge-warn' }}">{{ $done ? 'Submitted' : 'Pending' }}</span>
                                <a href="{{ route('student.assignments.show', $asg) }}" class="sd-btn-ghost">Open</a>
                            </div>
                        </div>
                    @empty
                        <div class="sd-empty">No assignments right now.</div>
                    @endforelse
                </div>
            </section>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Upcoming Lessons</h2>
                </div>
                <div class="sd-list">
                    @forelse(($s['upcomingLessons'] ?? collect()) as $lesson)
                        <div class="sd-list-item">
                            <div class="sd-list-main">
                                <h4>{{ $lesson->title }}</h4>
                                <p class="sd-muted">{{ $lesson->subject->subject_name ?? 'Subject' }}</p>
                                <p class="sd-meta">
                                    <i class="far fa-calendar"></i>
                                    {{ optional($lesson->lesson_date)->format('F j, Y') ?? 'Date TBD' }}
                                </p>
                            </div>
                            <span class="sd-badge sd-badge-ok">{{ ucfirst($lesson->status ?? 'published') }}</span>
                        </div>
                    @empty
                        <div class="sd-empty">No upcoming lessons posted yet.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="sd-side">
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Today&rsquo;s Schedule</h2>
                    <a href="{{ route('student.my-schedule') }}" class="sd-link">Full week</a>
                </div>
                <div class="sd-timeline">
                    @forelse(($s['todaysSchedule'] ?? collect()) as $slot)
                        <div class="sd-timeline-item">
                            <div class="sd-timeline-time">{{ $slot->start_label }}</div>
                            <div class="sd-timeline-body">
                                <h4>{{ $slot->subject_name }}</h4>
                                <p class="sd-muted">{{ $slot->teacher_name }}</p>
                                <p class="sd-meta">{{ $slot->room }} · {{ $slot->end_label }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="sd-empty">No classes scheduled for today.</div>
                    @endforelse
                </div>
            </section>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Class Posts</h2>
                </div>
                <div class="sd-list sd-list-compact">
                    @forelse(($s['classPosts'] ?? collect()) as $post)
                        <div class="sd-list-item">
                            <div class="sd-list-main">
                                <h4>{{ $post->title }}</h4>
                                <p class="sd-muted">{{ $post->subject->subject_name ?? 'Class' }}</p>
                                <p class="sd-meta">{{ optional($post->published_at ?? $post->created_at)->format('M j') }}</p>
                            </div>
                            <a href="{{ route('class-posts.show', $post) }}" class="sd-btn-ghost">Read</a>
                        </div>
                    @empty
                        <div class="sd-empty">No class posts yet.</div>
                    @endforelse
                </div>
            </section>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Upcoming Events</h2>
                    <a href="{{ route('calendar.index') }}" class="sd-link">Calendar</a>
                </div>
                <div class="sd-list sd-list-compact">
                    @forelse(($s['upcomingEvents'] ?? collect())->take(5) as $event)
                        <div class="sd-list-item">
                            <div class="sd-list-main">
                                <h4>{{ $event->title ?? 'Event' }}</h4>
                                <p class="sd-muted">{{ $event->subject->subject_name ?? 'School event' }}</p>
                                <p class="sd-meta">
                                    {{ optional($event->start_time)->format('M j, Y') }}
                                    · {{ optional($event->start_time)->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="sd-empty">No upcoming events.</div>
                    @endforelse
                </div>
            </section>

            <section class="sd-panel">
                <div class="sd-panel-head">
                    <h2>Announcements</h2>
                    <a href="{{ route('announcements.index') }}" class="sd-link">See all</a>
                </div>
                <div class="sd-list sd-list-compact">
                    @forelse(($s['announcements'] ?? collect()) as $notice)
                        <div class="sd-list-item">
                            <div class="sd-list-main">
                                <h4>{{ $notice->title }}</h4>
                                <p class="sd-meta">{{ optional($notice->created_at)->format('M j, Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="sd-empty">No announcements.</div>
                    @endforelse
                </div>
            </section>

            <section class="sd-panel sd-panel-soft">
                <div class="sd-panel-head">
                    <h2>Quick links</h2>
                </div>
                <div class="sd-quick-links">
                    <a href="{{ route('student.my-schedule') }}"><i class="fas fa-calendar-alt"></i> My Schedule</a>
                    <a href="{{ route('student.grades') }}"><i class="fas fa-clipboard-list"></i> Grades</a>
                    <a href="{{ route('student.assignments.index') }}"><i class="fas fa-tasks"></i> Assignments</a>
                    <a href="{{ route('analytics.student-dashboard') }}"><i class="fas fa-chart-line"></i> My Analytics</a>
                    <a href="{{ route('calendar.index') }}"><i class="fas fa-calendar"></i> Calendar</a>
                </div>
            </section>
        </aside>
    </div>
</div>

@if($enrollmentApplication)
<div class="modal fade" id="studentEnrollmentModal" tabindex="-1" aria-labelledby="studentEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentEnrollmentModalLabel">My Enrollment Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $enrollmentApplication->status)) }}</p>
                <p class="mb-2"><strong>Application number:</strong> {{ $enrollmentApplication->application_number }}</p>
                <p class="mb-3"><strong>Submitted:</strong> {{ $enrollmentApplication->created_at->format('M d, Y g:i A') }}</p>
                <p class="mb-1"><strong>Name:</strong> {{ $enrollmentApplication->full_name }}</p>
                <p class="mb-1"><strong>Grade applying for:</strong> {{ $enrollmentApplication->grade_level_applying_for }}</p>
                <p class="mb-3"><strong>Documents:</strong> {{ $enrollmentApplication->documents->count() }}/6</p>
                @if($enrollmentApplication->notes)
                    <div class="alert alert-info">{{ $enrollmentApplication->notes }}</div>
                @endif
                @if($enrollmentApplication->rejection_reason)
                    <div class="alert alert-danger">{{ $enrollmentApplication->rejection_reason }}</div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endif

<style>
:root {
    --sd-accent: #e67e22;
    --sd-accent-dark: #d35400;
    --sd-accent-soft: #fff4eb;
    --sd-bg: #f5f6f8;
    --sd-card: #ffffff;
    --sd-text: #1f2937;
    --sd-muted: #6b7280;
    --sd-border: #e8eaed;
    --sd-radius: 12px;
    --sd-shadow: 0 1px 3px rgba(16,24,40,.06), 0 1px 2px rgba(16,24,40,.04);
}
.page-wrapper .content.container-fluid {
    background: var(--sd-bg);
    max-width: none !important;
    width: 100% !important;
    padding-left: 1.75rem !important;
    padding-right: 1.25rem !important;
}
.sd-dashboard {
    color: var(--sd-text);
    width: 100%;
    margin: 0;
    padding-bottom: 1.5rem;
}
.sd-welcome {
    display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem;
    margin-bottom: 1.25rem; flex-wrap: wrap;
}
.sd-welcome-who { display: flex; align-items: center; gap: 0.9rem; }
.sd-avatar {
    width: 56px; height: 56px; border-radius: 50%; object-fit: cover;
    border: 2px solid #fff; box-shadow: var(--sd-shadow);
}
.sd-welcome h1 { font-size: 1.65rem; font-weight: 700; margin: 0 0 .25rem; letter-spacing: -0.02em; }
.sd-welcome p { margin: 0; color: var(--sd-muted); font-size: .95rem; }
.sd-welcome-date {
    display: inline-flex; align-items: center; gap: .5rem;
    background: var(--sd-card); border: 1px solid var(--sd-border); border-radius: 999px;
    padding: .45rem .9rem; font-size: .875rem; color: var(--sd-muted); box-shadow: var(--sd-shadow);
}
.sd-welcome-date i { color: var(--sd-accent); }

.sd-stats {
    display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem;
}
.sd-stat-card {
    background: var(--sd-card); border-radius: var(--sd-radius); box-shadow: var(--sd-shadow);
    border: 1px solid var(--sd-border); padding: 1.1rem 1.15rem;
    display: flex; gap: .9rem; align-items: flex-start;
}
.sd-stat-icon {
    width: 42px; height: 42px; border-radius: 10px; background: var(--sd-accent-soft);
    color: var(--sd-accent); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sd-stat-icon-warn { background: #fff0e8; }
.sd-stat-value { font-size: 1.55rem; font-weight: 700; line-height: 1.1; }
.sd-stat-label { font-size: .9rem; font-weight: 600; margin-top: .15rem; }
.sd-stat-meta { font-size: .75rem; color: var(--sd-muted); margin-top: .15rem; }

.sd-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(300px, 380px); gap: 1.25rem; align-items: start; }
.sd-panel {
    background: var(--sd-card); border: 1px solid var(--sd-border); border-radius: var(--sd-radius);
    box-shadow: var(--sd-shadow); padding: 1.1rem 1.2rem; margin-bottom: 1.15rem;
}
.sd-panel-soft { background: #fcfcfd; }
.sd-panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .9rem; gap: .75rem; }
.sd-panel-head h2 { font-size: 1.05rem; font-weight: 700; margin: 0; }
.sd-link { color: var(--sd-accent); font-size: .85rem; font-weight: 600; text-decoration: none; }
.sd-link:hover { color: var(--sd-accent-dark); text-decoration: underline; }
.sd-muted { color: var(--sd-muted); margin: 0; }
.sd-meta { font-size: .8rem; color: var(--sd-muted); margin: .35rem 0 0; }
.sd-empty { color: var(--sd-muted); font-size: .9rem; padding: .75rem 0; }
.sd-empty-state { text-align: center; padding: 3rem 1.5rem; }
.sd-empty-state i { font-size: 2.4rem; color: #f0c9a6; margin-bottom: .75rem; display: block; }

.sd-enroll-banner {
    display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.sd-enroll-banner h2 { margin: 0 0 .2rem; font-size: 1.05rem; }

.sd-class-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 1rem;
}
.sd-class-card {
    border: 1px solid var(--sd-border); border-radius: 10px; padding: .9rem 1rem; background: #fff;
    min-width: 0;
}
.sd-class-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: .45rem; }
.sd-pill {
    background: var(--sd-accent-soft); color: var(--sd-accent-dark); font-size: .72rem; font-weight: 700;
    padding: .2rem .5rem; border-radius: 999px; text-transform: uppercase; letter-spacing: .03em;
}
.sd-status { font-size: .72rem; color: #059669; font-weight: 600; }
.sd-class-card h3 { font-size: 1rem; margin: 0 0 .25rem; font-weight: 700; }
.sd-btn-outline, .sd-btn-ghost {
    display: inline-block; margin-top: .4rem; font-size: .8rem; font-weight: 600;
    border-radius: 8px; padding: .35rem .7rem; text-decoration: none; border: none; background: transparent;
}
.sd-btn-outline {
    border: 1px solid var(--sd-accent); color: var(--sd-accent);
}
.sd-btn-outline:hover { background: var(--sd-accent); color: #fff; }
.sd-btn-ghost { color: var(--sd-accent); background: var(--sd-accent-soft); }
.sd-btn-ghost:hover { background: #ffe4d1; color: var(--sd-accent-dark); }

.sd-list-item {
    display: flex; justify-content: space-between; align-items: flex-start; gap: .75rem;
    padding: .75rem 0; border-bottom: 1px solid var(--sd-border);
}
.sd-list-item:last-child { border-bottom: none; }
.sd-list-item h4 { font-size: .95rem; margin: 0 0 .15rem; font-weight: 650; }
.sd-list-actions { display: flex; flex-direction: column; align-items: flex-end; gap: .35rem; }
.sd-badge {
    font-size: .7rem; font-weight: 700; padding: .25rem .55rem; border-radius: 999px; white-space: nowrap;
}
.sd-badge-ok { background: #ecfdf5; color: #047857; }
.sd-badge-warn { background: #fff7ed; color: #c2410c; }

.sd-timeline-item { display: grid; grid-template-columns: 72px 1fr; gap: .65rem; padding: .65rem 0; position: relative; }
.sd-timeline-item:not(:last-child)::before {
    content: ''; position: absolute; left: 66px; top: 1.6rem; bottom: -.1rem; width: 2px; background: #f0e4d8;
}
.sd-timeline-time { font-size: .78rem; font-weight: 700; color: var(--sd-accent); padding-top: .1rem; }
.sd-timeline-body h4 { font-size: .92rem; margin: 0 0 .15rem; }

.sd-quick-links { display: flex; flex-direction: column; gap: .35rem; }
.sd-quick-links a {
    display: flex; align-items: center; gap: .55rem; padding: .55rem .65rem; border-radius: 8px;
    color: var(--sd-text); text-decoration: none; font-size: .875rem; font-weight: 550;
    border: 1px solid transparent;
}
.sd-quick-links a i { color: var(--sd-accent); width: 16px; text-align: center; }
.sd-quick-links a:hover { background: var(--sd-accent-soft); border-color: #f3d5bb; }

@media (max-width: 1100px) {
    .sd-layout { grid-template-columns: 1fr; }
    .sd-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .page-wrapper .content.container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .sd-stats { display: flex; overflow-x: auto; gap: .75rem; padding-bottom: .25rem; }
    .sd-stat-card { min-width: 200px; flex: 0 0 auto; }
    .sd-welcome h1 { font-size: 1.35rem; }
}
</style>
