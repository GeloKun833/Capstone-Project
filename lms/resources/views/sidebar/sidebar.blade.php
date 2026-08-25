<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">
                    <span>Main Menu</span>
                </li>

                {{-- ADMIN SIDEBAR --}}
                @if (Session::get('role_name') === 'Admin')
                    <li class="submenu">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>
                    
                    <li class="submenu">
                        <a href="#"><i class="fas fa-users-cog"></i> <span>User Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('list/users') }}"><i class="fas fa-list"></i> <span>All Users</span></a></li>
                            <li><a href="{{ route('enrollments.create') }}"><i class="fas fa-user-plus"></i> <span>Create User</span></a></li>
                            <li><a href="{{ route('teacher/list/page') }}"><i class="fas fa-chalkboard-teacher"></i> <span>Teachers</span></a></li>
                            <li><a href="{{ route('student/list') }}"><i class="fas fa-user-graduate"></i> <span>Students</span></a></li>
                            <li><a href="{{ route('enrollments.index') }}"><i class="fas fa-list"></i> <span>Enrollments</span></a></li>
                            <li><a href="{{ route('enrollment.registrar.index') }}"><i class="fas fa-file-alt"></i> <span>Enrollment Applications</span></a></li>
                            <li><a href="{{ route('activity.log') }}"><i class="fas fa-history"></i> <span>Activity Log</span></a></li>
                            <li><a href="{{ route('sis.hub') }}"><i class="fas fa-database"></i> <span>Information Systems</span></a></li>
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
                    
                    <li class="submenu">
                        <a href="#"><i class="fas fa-calendar-alt"></i> <span>Calendar & Events</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('calendar.events.list') }}"><i class="fas fa-list"></i> <span>All Events</span></a></li>
                            <li><a href="{{ route('calendar.create') }}"><i class="fas fa-plus"></i> <span>Create Event</span></a></li>
                        </ul>
                    </li>
                    
                    <li class="submenu">
                        <a href="#"><i class="fas fa-chart-line"></i> <span>Analytics & Settings</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('analytics.admin-dashboard') }}"><i class="fas fa-chart-bar"></i> <span>School Analytics</span></a></li>
                            <li><a href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                            <li><a href="{{ route('messages.index') }}"><i class="fas fa-envelope"></i> <span>Messages</span></a></li>
                            <li><a href="{{ route('chat.index') }}"><i class="fas fa-comments"></i> <span>Chat</span></a></li>
                            <li><a href="{{ route('setting/page') }}"><i class="fas fa-cog"></i> <span>System Settings</span></a></li>
                            <li><a href="{{ route('admin.backup.index') }}"><i class="fas fa-database"></i> <span>Backup & Recovery</span></a></li>
                        </ul>
                    </li>
                    
                    <li class="submenu">
                        <a href="{{ route('reports.index') }}"><i class="fas fa-file-pdf"></i> <span>Reports & Documents</span></a>
                    </li>
                @endif

                {{-- REGISTRAR-ONLY SIDEBAR (Limited Access) --}}
                @if (Session::get('role_name') === 'Registrar')
                    <li class="submenu">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>
                    
                    <li class="submenu">
                        <a href="#"><i class="fas fa-file-alt"></i> <span>Enrollment Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('enrollment.registrar.index') }}"><i class="fas fa-list"></i> <span>Applications</span></a></li>
                            <li><a href="{{ route('enrollment.registrar.statistics') }}"><i class="fas fa-chart-bar"></i> <span>Statistics</span></a></li>
                            <li><a href="{{ route('enrollment.registrar.archive') }}"><i class="fas fa-archive"></i> <span>Archive</span></a></li>
                            <li><a href="{{ route('enrollment.portal.index') }}" target="_blank"><i class="fas fa-external-link-alt"></i> <span>Portal View</span></a></li>
                            <li><a href="{{ route('sis.hub') }}"><i class="fas fa-database"></i> <span>Information Systems</span></a></li>
                        </ul>   
                    </li>
                @endif

                {{-- TEACHER SIDEBAR --}}
                @if (Session::get('role_name') === 'Teacher')
                    <li class="submenu">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>
                    
                    <li class="submenu">
                        <a href="#"><i class="fas fa-graduation-cap"></i> <span>Teaching & Learning</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('teacher.my-schedule') }}"><i class="fas fa-calendar-alt"></i> <span>My Schedule</span></a></li>
                            <li><a href="{{ route('lessons.index') }}"><i class="fas fa-list"></i> <span>My Lessons</span></a></li>
                            <li><a href="{{ route('lessons.create') }}"><i class="fas fa-plus"></i> <span>Create Lesson</span></a></li>
                            <li><a href="{{ route('teacher.classes') }}"><i class="fas fa-users"></i> <span>My Classes</span></a></li>
                            <li><a href="{{ route('teacher.subjects') }}"><i class="fas fa-book"></i> <span>My Subjects</span></a></li>
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
                    
                    <li class="submenu">
                        <a href="{{ route('reports.index') }}"><i class="fas fa-file-pdf"></i> <span>Reports & Documents</span></a>
                    </li>

                    <li class="submenu">
                        <a href="{{ route('sis.hub') }}"><i class="fas fa-database"></i> <span>Information Systems</span></a>
                    </li>
                    
                    <li class="submenu">
                        <a href="#"><i class="fas fa-bullhorn"></i> <span>Class Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('class-posts.index') }}"><i class="fas fa-list"></i> <span>All Posts</span></a></li>
                            <li><a href="{{ route('class-posts.create') }}"><i class="fas fa-plus"></i> <span>Create Post</span></a></li>
                            <li><a href="{{ route('attendance.index') }}"><i class="fas fa-calendar-check"></i> <span>Attendance</span></a></li>
                        </ul>
                    </li>
                    
                    <li class="submenu">
                        <a href="#"><i class="fas fa-calendar"></i> <span>Calendar & Events</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('calendar.events.list') }}"><i class="fas fa-list"></i> <span>All Events</span></a></li>
                            <li><a href="{{ route('calendar.create') }}"><i class="fas fa-plus"></i> <span>Create Event</span></a></li>
                        </ul>
                    </li>
                    
                    <li class="submenu">
                        <a href="#"><i class="fas fa-comment"></i> <span>Message</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @php
                                $parentUsers = $sidebarParentUsers ?? collect();
                            @endphp
                            @if($parentUsers->count() > 0)
                                @foreach($parentUsers as $parent)
                                    <li>
                                        <a href="{{ route('chat.index') }}?receiver_id={{ $parent->id }}">
                                            <i class="fas fa-user-friends"></i> 
                                            <span>{{ $parent->name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li><a href="#"><i class="fas fa-info-circle"></i> <span>No parents available</span></a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- STUDENT SIDEBAR --}}
                @if (Session::get('role_name') === 'Student')
                    <li class="submenu">
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
                            <li><a href="{{ route('messages.index') }}"><i class="fas fa-envelope"></i> <span>Messages</span></a></li>
                            <li><a href="{{ route('chat.index') }}"><i class="fas fa-comments"></i> <span>Chat</span></a></li>
                        </ul>
                    </li>
                @endif

                {{-- PARENT SIDEBAR --}}
                @if (Session::get('role_name') === 'Parent')
                    @php
                        $children = $sidebarChildren ?? collect();
                    @endphp
                    
                    <li class="submenu">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu">
                        <a href="{{ route('parent.index') }}"><i class="fas fa-home"></i> <span>Parent Portal</span></a>
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
                    
                    <li class="submenu">
                        <a href="{{ route('parent.schedule') }}"><i class="fas fa-calendar-alt"></i> <span>Class Schedule</span></a>
                    </li>
                    <li class="submenu">
                        <a href="#"><i class="fas fa-comments"></i> <span>Communication</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                            <li><a href="{{ route('messages.index') }}"><i class="fas fa-envelope"></i> <span>Messages</span></a></li>
                            <li><a href="{{ route('chat.index') }}"><i class="fas fa-comments"></i> <span>Chat</span></a></li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    console.log('🔧 Initializing custom sidebar menu handler...');
    
    // IMPORTANT: Remove ALL existing event handlers to prevent conflicts
    $('#sidebar-menu a').off('click');
    $('#sidebar-menu .submenu a').off('click');
    $('#sidebar-menu .submenu ul li a').off('click');
    
    console.log('✅ Removed default event handlers');
    
    // Custom submenu handler - more robust and prevents flickering
    $('#sidebar-menu .submenu > a').on('click', function(e) {
        var $this = $(this);
        var $parent = $this.parent('li.submenu');
        var $submenu = $this.next('ul');
        
        console.log('🖱️ Clicked menu:', $this.find('span').first().text());
        
        // Check if this link has a submenu
        if ($submenu.length > 0) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            var isOpen = $submenu.is(':visible');
            console.log('📊 Menu state - isOpen:', isOpen);
            
            // Close all other submenus at the same level
            $parent.siblings('.submenu').each(function() {
                var $sibling = $(this);
                $sibling.removeClass('active');
                $sibling.find('> a').removeClass('subdrop');
                $sibling.find('> ul').stop(true, true).slideUp(250);
            });
            
            // Toggle current submenu
            if (isOpen) {
                console.log('➖ Closing menu');
                $parent.removeClass('active');
                $this.removeClass('subdrop');
                $submenu.stop(true, true).slideUp(250);
            } else {
                console.log('➕ Opening menu');
                $parent.addClass('active');
                $this.addClass('subdrop');
                $submenu.stop(true, true).slideDown(250);
            }
            
            return false;
        } else {
            console.log('🔗 No submenu, allowing navigation');
            return true;
        }
    });
    
    // Prevent child menu items from triggering parent handlers
    $('#sidebar-menu .submenu ul li a').on('click', function(e) {
        e.stopPropagation();
        console.log('🔗 Child menu item clicked, navigating...');
        // Allow normal navigation
    });
    
    // Auto-expand and highlight current page menu
    var currentUrl = window.location.pathname;
    console.log('🔍 Current URL:', currentUrl);
    
    $('#sidebar-menu .submenu ul li a').each(function() {
        var linkPath = $(this).attr('href');
        if (linkPath) {
            // Remove domain and query string for comparison
            var cleanLinkPath = linkPath.split('?')[0];
            if (currentUrl.indexOf(cleanLinkPath) !== -1 || cleanLinkPath.indexOf(currentUrl) !== -1) {
                console.log('✨ Found current page menu item:', $(this).text().trim());
                $(this).addClass('active');
                var $parentSubmenu = $(this).closest('.submenu');
                $parentSubmenu.addClass('active');
                $parentSubmenu.find('> a').addClass('subdrop active');
                $parentSubmenu.find('> ul').css('display', 'block');
            }
        }
    });
    
    console.log('✅ Sidebar menu initialized successfully');
});
</script>

<style>
.sidebar-menu .divider {
    height: 1px;
    background-color: rgba(255, 255, 255, 0.1);
    margin: 8px 15px;
    border: none;
}

/* Enhanced submenu styling */
.submenu ul {
    display: none !important;
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

/* When submenu is being shown, override display none */
.submenu > a.subdrop + ul,
.submenu.active > ul {
    display: block !important;
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

.submenu ul li a.active {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    font-weight: 500;
}

.submenu ul li a i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}

/* Prevent layout shift during animation */
.submenu {
    overflow: hidden;
}
</style>