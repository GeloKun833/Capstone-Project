<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">
                    <span>Main Menu</span>
                </li>

                {{-- ADMIN SIDEBAR --}}
                @if (Session::get('role_name') === 'Admin')
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-users-cog"></i> <span>User Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('list/users') }}"><i class="fas fa-list"></i> <span>All Users</span></a></li>
                            <li><a href="{{ route('enrollments.create') }}"><i class="fas fa-user-plus"></i> <span>Create User</span></a></li>
                            <li><a href="{{ route('teacher/list/page') }}"><i class="fas fa-chalkboard-teacher"></i> <span>Teachers</span></a></li>
                            <li><a href="{{ route('student/list') }}"><i class="fas fa-user-graduate"></i> <span>Students</span></a></li>
                            <li><a href="{{ route('list/parents') }}"><i class="fas fa-user-friends"></i> <span>Parents</span></a></li>
                            <li><a href="{{ route('enrollments.index') }}"><i class="fas fa-list"></i> <span>Enrollments</span></a></li>
                            <li><a href="{{ route('activity.log') }}"><i class="fas fa-history"></i> <span>Activity Log</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-graduation-cap"></i> <span>Academic Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('class-subject.unified-management') }}"><i class="fas fa-cogs"></i> <span>Classes & Subjects</span></a></li>
                            <li><a href="{{ route('admin.schedules.index') }}"><i class="fas fa-clock"></i> <span>Class Schedules</span></a></li>
                            <li><a href="{{ route('academic_years.index') }}"><i class="fas fa-calendar-alt"></i> <span>Academic Years</span></a></li>
                            <li><a href="{{ route('semesters.index') }}"><i class="fas fa-calendar-week"></i> <span>Semesters</span></a></li>
                            <li><a href="{{ route('curriculum.index') }}"><i class="fas fa-book"></i> <span>Curriculum</span></a></li>
                            <li><a href="{{ route('promotions.index') }}"><i class="fas fa-user-graduate"></i> <span>Student Promotions</span></a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('calendar.index') }}"><i class="fas fa-calendar-alt"></i> <span>Calendar & Events</span></a>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-chart-line"></i> <span>Analytics & Settings</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('analytics.admin-dashboard') }}"><i class="fas fa-chart-bar"></i> <span>School Analytics</span></a></li>
                            <li><a href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                            <li><a href="{{ route('chat.index') }}"><i class="fas fa-comments"></i> <span>Chat</span></a></li>
                            <li><a href="{{ route('setting/page') }}"><i class="fas fa-cog"></i> <span>System Settings</span></a></li>
                            <li><a href="{{ route('admin.backup.index') }}"><i class="fas fa-database"></i> <span>Backup & Recovery</span></a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('reports.index') }}"><i class="fas fa-file-pdf"></i> <span>Reports & Documents</span></a>
                    </li>
                @endif

                {{-- REGISTRAR-ONLY SIDEBAR --}}
                @if (Session::get('role_name') === 'Registrar')
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-file-alt"></i> <span>Enrollment Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('enrollment.registrar.index') }}"><i class="fas fa-list"></i> <span>Applications</span></a></li>
                            <li><a href="{{ route('enrollment.registrar.statistics') }}"><i class="fas fa-chart-bar"></i> <span>Statistics</span></a></li>
                            <li><a href="{{ route('enrollment.registrar.archive') }}"><i class="fas fa-archive"></i> <span>Archive</span></a></li>
                            <li><a href="{{ route('enrollment.portal.index') }}" target="_blank"><i class="fas fa-external-link-alt"></i> <span>Portal View</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-book"></i> <span>Classes &amp; Subjects</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('class-subject.unified-management') }}"><i class="fas fa-cogs"></i> <span>Subject Catalog</span></a></li>
                            <li><a href="{{ route('subject/list/page') }}"><i class="fas fa-list"></i> <span>All Subjects</span></a></li>
                            <li><a href="{{ route('subject/add/page') }}"><i class="fas fa-plus"></i> <span>Add Subject</span></a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('chat.index') }}"><i class="fas fa-comments"></i> <span>Chat</span></a>
                    </li>
                @endif

                {{-- TEACHER SIDEBAR --}}
                @if (Session::get('role_name') === 'Teacher')
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-graduation-cap"></i> <span>Teaching & Learning</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('teacher.my-schedule') }}"><i class="fas fa-calendar-alt"></i> <span>My Schedule</span></a></li>
                            <li><a href="{{ route('lessons.index') }}"><i class="fas fa-list"></i> <span>My Lessons</span></a></li>
                            <li><a href="{{ route('lessons.create') }}"><i class="fas fa-plus"></i> <span>Create Lesson</span></a></li>
                            <li><a href="{{ route('teacher.classes') }}"><i class="fas fa-chalkboard-teacher"></i> <span>My Classes & Subjects</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-tasks"></i> <span>Assignments & Grading</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('assignments.index') }}"><i class="fas fa-list"></i> <span>All Assignments</span></a></li>
                            <li><a href="{{ route('assignments.create') }}"><i class="fas fa-plus"></i> <span>Create Assignment</span></a></li>
                            <li><a href="{{ route('teacher.grading.grade-entry') }}"><i class="fas fa-edit"></i> <span>Grade Entry</span></a></li>
                            <li><a href="{{ route('teacher.grading.gpa-ranking') }}"><i class="fas fa-chart-bar"></i> <span>GPA Ranking</span></a></li>
                            <li><a href="{{ route('teacher.grading.performance-analytics') }}"><i class="fas fa-chart-line"></i> <span>Performance Analytics</span></a></li>
                            <li><a href="{{ route('teacher.grading.grade-alerts') }}"><i class="fas fa-exclamation-triangle"></i> <span>Grade Alerts</span></a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('reports.index') }}"><i class="fas fa-file-pdf"></i> <span>Reports & Documents</span></a>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-bullhorn"></i> <span>Class Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('class-posts.index') }}"><i class="fas fa-list"></i> <span>All Posts</span></a></li>
                            <li><a href="{{ route('class-posts.create') }}"><i class="fas fa-plus"></i> <span>Create Post</span></a></li>
                            <li><a href="{{ route('attendance.index') }}"><i class="fas fa-calendar-check"></i> <span>Attendance</span></a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('calendar.index') }}"><i class="fas fa-calendar"></i> <span>Calendar & Events</span></a>
                    </li>

                    <li>
                        <a href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a>
                    </li>

                    <li>
                        <a href="{{ route('chat.index') }}"><i class="fas fa-comments"></i> <span>Chat</span></a>
                    </li>
                @endif

                {{-- STUDENT SIDEBAR --}}
                @if (Session::get('role_name') === 'Student')
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-graduation-cap"></i> <span>My Classes</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @php
                                $enrollments = $sidebarEnrollments ?? collect();
                            @endphp
                            @foreach($enrollments as $enrollment)
                                <li>
                                    <a href="{{ route('student.class.detail', $enrollment->id) }}">
                                        <i class="fas fa-book"></i>
                                        <span>{{ $enrollment->subject->subject_name ?? ($enrollment->subject->subject_code ?? 'Subject ' . $enrollment->subject->id) }}</span>
                                    </a>
                                </li>
                            @endforeach
                            @if($enrollments->count() == 0)
                                <li><a href="#"><i class="fas fa-info-circle"></i> <span>No classes enrolled</span></a></li>
                            @endif
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('calendar.index') }}"><i class="fas fa-calendar"></i> <span>Calendar & Events</span></a>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-chart-line"></i> <span>Academic Records</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('student.my-schedule') }}"><i class="fas fa-calendar-alt"></i> <span>My Schedule</span></a></li>
                            <li><a href="{{ route('student.grades') }}"><i class="fas fa-clipboard-list"></i> <span>Grades</span></a></li>
                            <li><a href="{{ route('student.attendance') }}"><i class="fas fa-user-check"></i> <span>Attendance Records</span></a></li>
                            <li><a href="{{ route('student.assignments.index') }}"><i class="fas fa-tasks"></i> <span>Assignments</span></a></li>
                            <li><a href="{{ route('student.recommendations') }}"><i class="fas fa-lightbulb"></i> <span>Study Recommendations</span></a></li>
                            <li><a href="{{ route('analytics.student-dashboard') }}"><i class="fas fa-chart-line"></i> <span>My Analytics</span></a></li>
                            <li><a href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                            <li><a href="{{ route('chat.index') }}"><i class="fas fa-comments"></i> <span>Chat</span></a></li>
                        </ul>
                    </li>
                @endif

                {{-- PARENT SIDEBAR --}}
                @if (Session::get('role_name') === 'Parent')
                    @php
                        $children = $sidebarChildren ?? collect();
                    @endphp

                    <li>
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li>
                        <a href="{{ route('parent.index') }}"><i class="fas fa-home"></i> <span>Parent Portal</span></a>
                    </li>

                    <li>
                        <a href="{{ route('calendar.index') }}"><i class="fas fa-calendar"></i> <span>Calendar & Events</span></a>
                    </li>

                    @if($children->count() > 0)
                    <li class="submenu">
                        <a href="#"><i class="fas fa-user-graduate"></i> <span>My Children</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @foreach($children as $child)
                                <li>
                                    <a href="{{ route('parent.child.hub', ['childId' => $child->id, 'tab' => 'overview']) }}">
                                        <i class="fas fa-user"></i> <span>{{ $child->full_name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif

                    <li>
                        <a href="{{ route('parent.schedule') }}"><i class="fas fa-calendar-alt"></i> <span>Class Schedule</span></a>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-comments"></i> <span>Communication</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                            <li><a href="{{ route('chat.index') }}"><i class="fas fa-comments"></i> <span>Chat</span></a></li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function ($) {
    'use strict';

    function normalizePath(url) {
        if (!url || url === '#' || url.indexOf('javascript:') === 0) {
            return '';
        }
        try {
            var path = url.split('?')[0];
            if (path.indexOf('http') === 0) {
                path = new URL(path).pathname;
            }
            return path.replace(/\/+$/, '') || '/';
        } catch (e) {
            return '';
        }
    }

    $(function () {
        var currentPath = normalizePath(window.location.pathname);

        // Highlight current page + open its parent submenu
        $('#sidebar-menu a').each(function () {
            var href = $(this).attr('href');
            var linkPath = normalizePath(href);
            if (!linkPath) {
                return;
            }

            if (currentPath === linkPath || (linkPath !== '/' && currentPath.indexOf(linkPath) === 0)) {
                $(this).addClass('active');
                var $submenu = $(this).closest('li.submenu');
                if ($submenu.length) {
                    $submenu.addClass('active');
                    $submenu.children('a').addClass('subdrop active');
                    $submenu.children('ul').show();
                }
            }
        });
    });
})(jQuery);
</script>
@endpush

<style>
.sidebar-menu .divider {
    height: 1px;
    background-color: rgba(255, 255, 255, 0.1);
    margin: 8px 15px;
    border: none;
}

.sidebar-menu > ul > li > a {
    cursor: pointer;
}

.submenu > ul {
    display: none;
    background: rgba(0, 0, 0, 0.1);
    border-left: 3px solid rgba(255, 255, 255, 0.2);
}

.submenu.active > a,
.submenu > a.subdrop {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.submenu.active > a .menu-arrow,
.submenu > a.subdrop .menu-arrow {
    transform: rotate(90deg);
    transition: transform 0.2s ease;
}

.submenu > a .menu-arrow {
    transition: transform 0.2s ease;
}

.submenu > a.subdrop + ul,
.submenu.active > ul {
    display: block;
}

.submenu ul li a {
    padding: 10px 20px 10px 50px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.submenu ul li a:hover {
    background: rgba(255, 255, 255, 0.1);
    padding-left: 55px;
}

.submenu ul li a.active,
.sidebar-menu > ul > li > a.active {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    font-weight: 500;
}

.submenu ul li a i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}

.submenu {
    overflow: hidden;
}
</style>
