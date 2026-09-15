<div class="sidebar sidebar-role-{{ strtolower(Session::get('role_name') ?? 'guest') }}" id="sidebar">
    <div class="sidebar-inner">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">
                    <span>Main Menu</span>
                </li>

                {{-- ADMIN SIDEBAR --}}
                @if (Session::get('role_name') === 'Admin')
                    @php
                        $aDash = request()->routeIs('dashboard', 'home');
                        $aUsers = request()->routeIs('list/users', 'enrollments.*', 'teacher/list/page', 'student/list', 'list/parents', 'activity.log');
                        $aAcademic = request()->routeIs('class-subject.*', 'admin.schedules.*', 'academic_years.*', 'semesters.*', 'curriculum.*', 'promotions.*');
                        $aCal = request()->routeIs('calendar.*');
                        $aAnalytics = request()->routeIs('analytics.*', 'admin.grading.*', 'announcements.*', 'chat.*', 'setting/page', 'admin.backup.*');
                        $aReports = request()->routeIs('reports.*');
                    @endphp

                    <li class="{{ $aDash ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu {{ $aUsers ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-users-cog"></i> <span>User Management</span> <span class="menu-arrow"></span></a>
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

                    <li class="submenu {{ $aAcademic ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-graduation-cap"></i> <span>Academic Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('class-subject.unified-management') }}"><i class="fas fa-cogs"></i> <span>Classes & Subjects</span></a></li>
                            <li><a href="{{ route('admin.schedules.index') }}"><i class="fas fa-clock"></i> <span>Class Schedules</span></a></li>
                            <li><a href="{{ route('academic_years.index') }}"><i class="fas fa-calendar-alt"></i> <span>Academic Years</span></a></li>
                            <li><a href="{{ route('semesters.index') }}"><i class="fas fa-calendar-week"></i> <span>Semesters</span></a></li>
                            <li><a href="{{ route('promotions.index') }}"><i class="fas fa-user-graduate"></i> <span>Student Promotions</span></a></li>
                        </ul>
                    </li>

                    <li class="{{ $aCal ? 'active' : '' }}">
                        <a href="{{ route('calendar.index') }}"><i class="fas fa-calendar-alt"></i> <span>Calendar & Events</span></a>
                    </li>

                    <li class="submenu {{ $aAnalytics ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-chart-line"></i> <span>Analytics & Settings</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('analytics.admin-dashboard') }}"><i class="fas fa-chart-bar"></i> <span>School Analytics</span></a></li>
                            <li><a href="{{ route('admin.grading.performance-hub') }}"><i class="fas fa-chart-line"></i> <span>Performance Hub</span></a></li>
                            <li><a href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                            <li><a href="{{ route('setting/page') }}"><i class="fas fa-cog"></i> <span>System Settings</span></a></li>
                            <li><a href="{{ route('admin.backup.index') }}"><i class="fas fa-database"></i> <span>Backup & Recovery</span></a></li>
                        </ul>
                    </li>

                    <li class="{{ $aReports ? 'active' : '' }}">
                        <a href="{{ route('reports.index') }}"><i class="fas fa-file-pdf"></i> <span>Reports & Documents</span></a>
                    </li>
                @endif

                {{-- REGISTRAR-ONLY SIDEBAR --}}
                @if (Session::get('role_name') === 'Registrar')
                    @php
                        $rDash = request()->routeIs('dashboard', 'home');
                        $rEnroll = request()->routeIs('enrollment.registrar.*', 'enrollment.portal.*');
                        $rChat = request()->routeIs('chat.*');
                    @endphp

                    <li class="{{ $rDash ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu {{ $rEnroll ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-file-alt"></i> <span>Enrollment Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('enrollment.registrar.index') }}"><i class="fas fa-list"></i> <span>Applications</span></a></li>
                            <li><a href="{{ route('enrollment.registrar.statistics') }}"><i class="fas fa-chart-bar"></i> <span>Statistics</span></a></li>
                            <li><a href="{{ route('enrollment.registrar.archive') }}"><i class="fas fa-archive"></i> <span>Archive</span></a></li>
                            <li><a href="{{ route('enrollment.portal.index') }}" target="_blank"><i class="fas fa-external-link-alt"></i> <span>Portal View</span></a></li>
                        </ul>
                    </li>

                    <li class="{{ $rChat ? 'active' : '' }}">
                        <a href="{{ route('chat.index') }}"><i class="fas fa-comments"></i> <span>Chat</span></a>
                    </li>
                @endif

                {{-- TEACHER SIDEBAR --}}
                @if (Session::get('role_name') === 'Teacher')
                    @php
                        $tDash = request()->routeIs('dashboard', 'home');
                        $tTeach = request()->routeIs('teacher.my-schedule', 'teacher.classes', 'lessons.*');
                        $tAssign = request()->routeIs('assignments.*', 'teacher.grading.*');
                        $tClass = request()->routeIs('class-posts.*', 'attendance.*');
                        $tCal = request()->routeIs('calendar.*');
                        $tAnnounce = request()->routeIs('announcements.*');
                        $tReports = request()->routeIs('reports.*');
                    @endphp

                    <li class="{{ $tDash ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu {{ $tTeach ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-graduation-cap"></i> <span>Teaching & Learning</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('teacher.my-schedule') }}"><i class="far fa-calendar-alt"></i> <span>My Schedule</span></a></li>
                            <li><a href="{{ route('lessons.index') }}"><i class="fas fa-book"></i> <span>My Lessons</span></a></li>
                            <li><a href="{{ route('teacher.classes') }}"><i class="fas fa-chalkboard-teacher"></i> <span>My Classes & Subjects</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu {{ $tAssign ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-tasks"></i> <span>Assignments & Grades</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('assignments.index') }}"><i class="fas fa-list"></i> <span>All Assignments</span></a></li>
                            <li><a href="{{ route('teacher.grading.grade-entry') }}"><i class="fas fa-edit"></i> <span>Grade Entry</span></a></li>
                            <li><a href="{{ route('teacher.grading.performance-hub') }}"><i class="fas fa-chart-line"></i> <span>Performance Hub</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu {{ $tClass ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-users"></i> <span>Class Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('class-posts.index') }}"><i class="fas fa-list"></i> <span>All Posts</span></a></li>
                            <li><a href="{{ route('attendance.index') }}"><i class="fas fa-user-check"></i> <span>Attendance</span></a></li>
                        </ul>
                    </li>

                    <li class="{{ $tCal ? 'active' : '' }}">
                        <a href="{{ route('calendar.index') }}"><i class="far fa-calendar"></i> <span>Calendar & Events</span></a>
                    </li>

                    <li class="{{ $tAnnounce ? 'active' : '' }}">
                        <a href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a>
                    </li>

                    <li class="{{ $tReports ? 'active' : '' }}">
                        <a href="{{ route('reports.index') }}"><i class="far fa-file-alt"></i> <span>Reports & Documents</span></a>
                    </li>
                @endif

                {{-- STUDENT SIDEBAR --}}
                @if (Session::get('role_name') === 'Student')
                    @php
                        $sDash = request()->routeIs('dashboard', 'home');
                        $sClasses = request()->routeIs('student.class.*');
                        $sCal = request()->routeIs('calendar.*');
                        $sRecords = request()->routeIs(
                            'student.my-schedule',
                            'student.grades',
                            'student.report-card',
                            'attendance.student',
                            'analytics.student-dashboard',
                            'announcements.*'
                        );
                    @endphp

                    <li class="{{ $sDash ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu {{ $sClasses ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-graduation-cap"></i> <span>My Classes</span> <span class="menu-arrow"></span></a>
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
                                <li><a href="javascript:void(0);"><i class="fas fa-info-circle"></i> <span>No classes enrolled</span></a></li>
                            @endif
                        </ul>
                    </li>

                    <li class="{{ $sCal ? 'active' : '' }}">
                        <a href="{{ route('calendar.index') }}"><i class="fas fa-calendar"></i> <span>Calendar & Events</span></a>
                    </li>

                    <li class="submenu {{ $sRecords ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-chart-line"></i> <span>Academic Records</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('student.my-schedule') }}"><i class="fas fa-calendar-alt"></i> <span>My Schedule</span></a></li>
                            <li><a href="{{ route('student.grades') }}"><i class="fas fa-clipboard-list"></i> <span>Grades</span></a></li>
                            <li><a href="{{ route('attendance.student') }}"><i class="fas fa-user-check"></i> <span>Attendance</span></a></li>
                            <li><a href="{{ route('analytics.student-dashboard') }}"><i class="fas fa-chart-line"></i> <span>My Analytics</span></a></li>
                            <li><a href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                        </ul>
                    </li>
                @endif

                {{-- PARENT SIDEBAR --}}
                @if (Session::get('role_name') === 'Parent')
                    @php
                        $children = $sidebarChildren ?? collect();
                        $pDash = request()->routeIs('dashboard', 'home');
                        $pPortal = request()->routeIs('parent.index');
                        $pCal = request()->routeIs('calendar.*');
                        $pChildren = request()->routeIs('parent.child.*');
                        $pSchedule = request()->routeIs('parent.schedule');
                        $pComm = request()->routeIs('announcements.*', 'chat.*', 'notifications.*');
                    @endphp

                    <li class="{{ $pDash ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="{{ $pPortal ? 'active' : '' }}">
                        <a href="{{ route('parent.index') }}"><i class="fas fa-home"></i> <span>Parent Portal</span></a>
                    </li>

                    <li class="{{ $pCal ? 'active' : '' }}">
                        <a href="{{ route('calendar.index') }}"><i class="fas fa-calendar"></i> <span>Calendar & Events</span></a>
                    </li>

                    @if($children->count() > 0)
                    <li class="submenu {{ $pChildren ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-user-graduate"></i> <span>My Children</span> <span class="menu-arrow"></span></a>
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

                    <li class="{{ $pSchedule ? 'active' : '' }}">
                        <a href="{{ route('parent.schedule') }}"><i class="fas fa-calendar-alt"></i> <span>Class Schedule</span></a>
                    </li>
                    <li class="{{ request()->routeIs('attendance.parent') ? 'active' : '' }}">
                        <a href="{{ route('attendance.parent') }}"><i class="fas fa-user-check"></i> <span>Attendance</span></a>
                    </li>

                    <li class="submenu {{ $pComm ? 'active' : '' }}">
                        <a href="javascript:void(0);"><i class="fas fa-comments"></i> <span>Communication</span> <span class="menu-arrow"></span></a>
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
