<div class="sidebar sidebar-role-<?php echo e(strtolower(Session::get('role_name') ?? 'guest')); ?>" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">
                    <span>Main Menu</span>
                </li>

                
                <?php if(Session::get('role_name') === 'Admin'): ?>
                    <?php
                        $aDash = request()->routeIs('dashboard', 'home');
                        $aUsers = request()->routeIs('list/users', 'enrollments.*', 'teacher/list/page', 'student/list', 'list/parents', 'activity.log');
                        $aAcademic = request()->routeIs('class-subject.*', 'admin.schedules.*', 'academic_years.*', 'semesters.*', 'curriculum.*', 'promotions.*');
                        $aCal = request()->routeIs('calendar.*');
                        $aAnalytics = request()->routeIs('analytics.*', 'admin.grading.*', 'announcements.*', 'chat.*', 'setting/page', 'admin.backup.*');
                        $aReports = request()->routeIs('reports.*');
                    ?>

                    <li class="<?php echo e($aDash ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('dashboard')); ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu <?php echo e($aUsers ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-users-cog"></i> <span>User Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('list/users')); ?>"><i class="fas fa-list"></i> <span>All Users</span></a></li>
                            <li><a href="<?php echo e(route('enrollments.create')); ?>"><i class="fas fa-user-plus"></i> <span>Create User</span></a></li>
                            <li><a href="<?php echo e(route('teacher/list/page')); ?>"><i class="fas fa-chalkboard-teacher"></i> <span>Teachers</span></a></li>
                            <li><a href="<?php echo e(route('student/list')); ?>"><i class="fas fa-user-graduate"></i> <span>Students</span></a></li>
                            <li><a href="<?php echo e(route('list/parents')); ?>"><i class="fas fa-user-friends"></i> <span>Parents</span></a></li>
                            <li><a href="<?php echo e(route('enrollments.index')); ?>"><i class="fas fa-list"></i> <span>Enrollments</span></a></li>
                            <li><a href="<?php echo e(route('activity.log')); ?>"><i class="fas fa-history"></i> <span>Activity Log</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu <?php echo e($aAcademic ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-graduation-cap"></i> <span>Academic Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('class-subject.unified-management')); ?>"><i class="fas fa-cogs"></i> <span>Classes & Subjects</span></a></li>
                            <li><a href="<?php echo e(route('admin.schedules.index')); ?>"><i class="fas fa-clock"></i> <span>Class Schedules</span></a></li>
                            <li><a href="<?php echo e(route('academic_years.index')); ?>"><i class="fas fa-calendar-alt"></i> <span>Academic Years</span></a></li>
                            <li><a href="<?php echo e(route('semesters.index')); ?>"><i class="fas fa-calendar-week"></i> <span>Semesters</span></a></li>
                            <li><a href="<?php echo e(route('curriculum.index')); ?>"><i class="fas fa-book"></i> <span>Curriculum</span></a></li>
                            <li><a href="<?php echo e(route('promotions.index')); ?>"><i class="fas fa-user-graduate"></i> <span>Student Promotions</span></a></li>
                        </ul>
                    </li>

                    <li class="<?php echo e($aCal ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('calendar.index')); ?>"><i class="fas fa-calendar-alt"></i> <span>Calendar & Events</span></a>
                    </li>

                    <li class="submenu <?php echo e($aAnalytics ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-chart-line"></i> <span>Analytics & Settings</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('analytics.admin-dashboard')); ?>"><i class="fas fa-chart-bar"></i> <span>School Analytics</span></a></li>
                            <li><a href="<?php echo e(route('admin.grading.performance-hub')); ?>"><i class="fas fa-chart-line"></i> <span>Performance Hub</span></a></li>
                            <li><a href="<?php echo e(route('announcements.index')); ?>"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                            <li><a href="<?php echo e(route('chat.index')); ?>"><i class="fas fa-comments"></i> <span>Chat</span></a></li>
                            <li><a href="<?php echo e(route('setting/page')); ?>"><i class="fas fa-cog"></i> <span>System Settings</span></a></li>
                            <li><a href="<?php echo e(route('admin.backup.index')); ?>"><i class="fas fa-database"></i> <span>Backup & Recovery</span></a></li>
                        </ul>
                    </li>

                    <li class="<?php echo e($aReports ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('reports.index')); ?>"><i class="fas fa-file-pdf"></i> <span>Reports & Documents</span></a>
                    </li>
                <?php endif; ?>

                
                <?php if(Session::get('role_name') === 'Registrar'): ?>
                    <?php
                        $rDash = request()->routeIs('dashboard', 'home');
                        $rEnroll = request()->routeIs('enrollment.registrar.*', 'enrollment.portal.*');
                        $rSubjects = request()->routeIs('class-subject.*', 'subject/list/page', 'subject/add/page');
                        $rChat = request()->routeIs('chat.*');
                    ?>

                    <li class="<?php echo e($rDash ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('dashboard')); ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu <?php echo e($rEnroll ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-file-alt"></i> <span>Enrollment Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('enrollment.registrar.index')); ?>"><i class="fas fa-list"></i> <span>Applications</span></a></li>
                            <li><a href="<?php echo e(route('enrollment.registrar.statistics')); ?>"><i class="fas fa-chart-bar"></i> <span>Statistics</span></a></li>
                            <li><a href="<?php echo e(route('enrollment.registrar.archive')); ?>"><i class="fas fa-archive"></i> <span>Archive</span></a></li>
                            <li><a href="<?php echo e(route('enrollment.portal.index')); ?>" target="_blank"><i class="fas fa-external-link-alt"></i> <span>Portal View</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu <?php echo e($rSubjects ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-book"></i> <span>Classes &amp; Subjects</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('class-subject.unified-management')); ?>"><i class="fas fa-cogs"></i> <span>Subject Catalog</span></a></li>
                            <li><a href="<?php echo e(route('subject/list/page')); ?>"><i class="fas fa-list"></i> <span>All Subjects</span></a></li>
                            <li><a href="<?php echo e(route('subject/add/page')); ?>"><i class="fas fa-plus"></i> <span>Add Subject</span></a></li>
                        </ul>
                    </li>

                    <li class="<?php echo e($rChat ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('chat.index')); ?>"><i class="fas fa-comments"></i> <span>Chat</span></a>
                    </li>
                <?php endif; ?>

                
                <?php if(Session::get('role_name') === 'Teacher'): ?>
                    <?php
                        $tDash = request()->routeIs('dashboard', 'home');
                        $tTeach = request()->routeIs('teacher.my-schedule', 'teacher.classes', 'lessons.*');
                        $tAssign = request()->routeIs('assignments.*', 'teacher.grading.*');
                        $tClass = request()->routeIs('class-posts.*', 'attendance.*');
                        $tCal = request()->routeIs('calendar.*');
                        $tAnnounce = request()->routeIs('announcements.*');
                        $tChat = request()->routeIs('chat.*');
                        $tReports = request()->routeIs('reports.*');
                    ?>

                    <li class="<?php echo e($tDash ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('dashboard')); ?>"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu <?php echo e($tTeach ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-graduation-cap"></i> <span>Teaching & Learning</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('teacher.my-schedule')); ?>"><i class="far fa-calendar-alt"></i> <span>My Schedule</span></a></li>
                            <li><a href="<?php echo e(route('lessons.index')); ?>"><i class="fas fa-book"></i> <span>My Lessons</span></a></li>
                            <li><a href="<?php echo e(route('teacher.classes')); ?>"><i class="fas fa-chalkboard-teacher"></i> <span>My Classes & Subjects</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu <?php echo e($tAssign ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-tasks"></i> <span>Assignments & Grades</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('assignments.index')); ?>"><i class="fas fa-list"></i> <span>All Assignments</span></a></li>
                            <li><a href="<?php echo e(route('teacher.grading.grade-entry')); ?>"><i class="fas fa-edit"></i> <span>Grade Entry</span></a></li>
                            <li><a href="<?php echo e(route('teacher.grading.performance-hub')); ?>"><i class="fas fa-chart-line"></i> <span>Performance Hub</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu <?php echo e($tClass ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-users"></i> <span>Class Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('class-posts.index')); ?>"><i class="fas fa-list"></i> <span>All Posts</span></a></li>
                            <li><a href="<?php echo e(route('class-posts.create')); ?>"><i class="fas fa-plus"></i> <span>Create Post</span></a></li>
                            <li><a href="<?php echo e(route('attendance.index')); ?>"><i class="fas fa-user-check"></i> <span>Attendance</span></a></li>
                        </ul>
                    </li>

                    <li class="<?php echo e($tCal ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('calendar.index')); ?>"><i class="far fa-calendar"></i> <span>Calendar & Events</span></a>
                    </li>

                    <li class="<?php echo e($tAnnounce ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('announcements.index')); ?>"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a>
                    </li>

                    <li class="<?php echo e($tChat ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('chat.index')); ?>"><i class="far fa-comments"></i> <span>Chat</span></a>
                    </li>

                    <li class="<?php echo e($tReports ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('reports.index')); ?>"><i class="far fa-file-alt"></i> <span>Reports & Documents</span></a>
                    </li>
                <?php endif; ?>

                
                <?php if(Session::get('role_name') === 'Student'): ?>
                    <?php
                        $sDash = request()->routeIs('dashboard', 'home');
                        $sClasses = request()->routeIs('student.class.*');
                        $sCal = request()->routeIs('calendar.*');
                        $sRecords = request()->routeIs(
                            'student.my-schedule',
                            'student.grades',
                            'student.attendance',
                            'student.assignments.*',
                            'student.recommendations',
                            'analytics.student-dashboard',
                            'announcements.*',
                            'chat.*',
                            'student.report-card',
                            'notifications.*'
                        );
                    ?>

                    <li class="<?php echo e($sDash ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('dashboard')); ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="submenu <?php echo e($sClasses ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-graduation-cap"></i> <span>My Classes</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <?php
                                $enrollments = $sidebarEnrollments ?? collect();
                            ?>
                            <?php $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('student.class.detail', $enrollment->id)); ?>">
                                        <i class="fas fa-book"></i>
                                        <span><?php echo e($enrollment->subject->subject_name ?? ($enrollment->subject->subject_code ?? 'Subject ' . $enrollment->subject->id)); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($enrollments->count() == 0): ?>
                                <li><a href="javascript:void(0);"><i class="fas fa-info-circle"></i> <span>No classes enrolled</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <li class="<?php echo e($sCal ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('calendar.index')); ?>"><i class="fas fa-calendar"></i> <span>Calendar & Events</span></a>
                    </li>

                    <li class="submenu <?php echo e($sRecords ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-chart-line"></i> <span>Academic Records</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('student.my-schedule')); ?>"><i class="fas fa-calendar-alt"></i> <span>My Schedule</span></a></li>
                            <li><a href="<?php echo e(route('student.grades')); ?>"><i class="fas fa-clipboard-list"></i> <span>Grades</span></a></li>
                            <li><a href="<?php echo e(route('student.attendance')); ?>"><i class="fas fa-user-check"></i> <span>Attendance Records</span></a></li>
                            <li><a href="<?php echo e(route('student.assignments.index')); ?>"><i class="fas fa-tasks"></i> <span>Assignments</span></a></li>
                            <li><a href="<?php echo e(route('student.recommendations')); ?>"><i class="fas fa-lightbulb"></i> <span>Study Recommendations</span></a></li>
                            <li><a href="<?php echo e(route('analytics.student-dashboard')); ?>"><i class="fas fa-chart-line"></i> <span>My Analytics</span></a></li>
                            <li><a href="<?php echo e(route('announcements.index')); ?>"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                            <li><a href="<?php echo e(route('chat.index')); ?>"><i class="fas fa-comments"></i> <span>Chat</span></a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                
                <?php if(Session::get('role_name') === 'Parent'): ?>
                    <?php
                        $children = $sidebarChildren ?? collect();
                        $pDash = request()->routeIs('dashboard', 'home');
                        $pPortal = request()->routeIs('parent.index');
                        $pCal = request()->routeIs('calendar.*');
                        $pChildren = request()->routeIs('parent.child.*');
                        $pSchedule = request()->routeIs('parent.schedule');
                        $pComm = request()->routeIs('announcements.*', 'chat.*', 'notifications.*');
                    ?>

                    <li class="<?php echo e($pDash ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('dashboard')); ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                    </li>

                    <li class="<?php echo e($pPortal ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('parent.index')); ?>"><i class="fas fa-home"></i> <span>Parent Portal</span></a>
                    </li>

                    <li class="<?php echo e($pCal ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('calendar.index')); ?>"><i class="fas fa-calendar"></i> <span>Calendar & Events</span></a>
                    </li>

                    <?php if($children->count() > 0): ?>
                    <li class="submenu <?php echo e($pChildren ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-user-graduate"></i> <span>My Children</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('parent.child.hub', ['childId' => $child->id, 'tab' => 'overview'])); ?>">
                                        <i class="fas fa-user"></i> <span><?php echo e($child->full_name); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <li class="<?php echo e($pSchedule ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('parent.schedule')); ?>"><i class="fas fa-calendar-alt"></i> <span>Class Schedule</span></a>
                    </li>

                    <li class="submenu <?php echo e($pComm ? 'active' : ''); ?>">
                        <a href="javascript:void(0);"><i class="fas fa-comments"></i> <span>Communication</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo e(route('announcements.index')); ?>"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                            <li><a href="<?php echo e(route('chat.index')); ?>"><i class="fas fa-comments"></i> <span>Chat</span></a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function ($) {
    'use strict';
    $(function () {
        // Parent-module indicator only + no bounce for Student/Parent/Admin/Teacher
        $('#sidebar-menu .submenu ul a').removeClass('active');
        $('#sidebar-menu li.submenu.active').each(function () {
            var $li = $(this);
            $li.children('a').addClass('subdrop');
            $li.children('ul').stop(true, true).show();
        });
    });
})(jQuery);
</script>
<?php $__env->stopPush(); ?>

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
    transition: background 0.2s ease;
}

.submenu ul li a:hover {
    background: rgba(255, 255, 255, 0.1);
}

.submenu ul li a i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}

.submenu {
    overflow: hidden;
}

/* Role active colors — parent module only (no child blue bars) */
.sidebar-role-teacher {
    --sb-active: #e67e22;
    --sb-active-soft: rgba(230, 126, 34, 0.28);
    --sb-active-icon: #ffb266;
}
.sidebar-role-admin,
.sidebar-role-registrar {
    --sb-active: #2563eb;
    --sb-active-soft: rgba(37, 99, 235, 0.28);
    --sb-active-icon: #93c5fd;
}
.sidebar-role-student {
    --sb-active: #16a34a;
    --sb-active-soft: rgba(22, 163, 74, 0.28);
    --sb-active-icon: #86efac;
}
.sidebar-role-parent {
    --sb-active: #0d9488;
    --sb-active-soft: rgba(13, 148, 136, 0.28);
    --sb-active-icon: #5eead4;
}

.sidebar-menu > ul > li.active > a,
.sidebar-menu > ul > li.submenu.active > a {
    background: var(--sb-active-soft, rgba(230, 126, 34, 0.28)) !important;
    border-left: 3px solid var(--sb-active, #e67e22);
    color: #fff !important;
    font-weight: 600;
}
.sidebar-menu > ul > li.active > a i,
.sidebar-menu > ul > li.submenu.active > a i,
.sidebar-menu > ul > li.submenu.active > a .menu-arrow {
    color: var(--sb-active-icon, #ffb266);
}
/* No separate active indicator on nested items */
.sidebar-menu .submenu ul li a.active {
    background: transparent !important;
    border-left: none !important;
    font-weight: inherit;
    color: inherit;
}
</style>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/sidebar/sidebar.blade.php ENDPATH**/ ?>