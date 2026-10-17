<?php

namespace App\Support;

class SchoolReportCatalog
{
    /**
     * @return list<array{key:string,category:string,title:string,description:string,roles:list<string>,priority:string,href?:string}>
     */
    public static function all(): array
    {
        $staff = ['Admin', 'Principal', 'Registrar'];
        $academic = ['Admin', 'Principal'];
        $admin = ['Admin', 'Principal'];
        $docs = ['Admin', 'Principal', 'Registrar'];
        $learning = ['Admin', 'Principal'];
        $teacher = ['Teacher'];

        return [
            self::item('enrollment-by-year', 'Enrollment and Student Reports', 'Enrollment Summary by Academic Year', 'Student counts for each academic year.', $staff, 'high'),
            self::item('enrollment-by-grade', 'Enrollment and Student Reports', 'Enrollment Summary by Grade Level', 'Students placed in each grade for the current academic year.', $staff, 'high'),
            self::item('enrolled-master-list', 'Enrollment and Student Reports', 'Enrolled Students Master List', 'Students enrolled in the current academic year.', $staff, 'high'),
            self::item('new-vs-returning', 'Enrollment and Student Reports', 'New vs. Returning Students Report', 'Students whose first placement is this year, and students who were already enrolled before.', $staff, 'high'),
            self::item('pending-applications', 'Enrollment and Student Reports', 'Pending Enrollment Applications', 'Applications that are not yet approved or rejected.', $staff, 'high'),
            self::item('enrollment-status', 'Enrollment and Student Reports', 'Enrollment Status Report', 'Application counts by status for the current academic year.', $staff, 'high'),
            self::item('section-assignment', 'Enrollment and Student Reports', 'Student Section Assignment Report', 'Grade and section placement for the current academic year.', $staff, 'high'),
            self::item('student-profile', 'Enrollment and Student Reports', 'Student Profile and Academic History', 'Student identity, placement, and saved grade average for the current year.', $admin, 'high'),
            self::item('withdrawn-transferred', 'Enrollment and Student Reports', 'Withdrawn and Transferred Students Report', 'Students whose enrollment status or promotion record shows they left or transferred.', $staff, 'high'),
            self::item('incomplete-requirements', 'Enrollment and Student Reports', 'Students with Incomplete Requirements', 'Applications still missing documents or marked as needing documents.', $docs, 'high'),

            self::item('student-report-card', 'Academic and Grading Reports', 'Student Report Card', 'Printable report card for one student or a section. Uses the existing report generator.', $teacher, 'high', 'reports.index'),
            self::item('student-grade-slip', 'Academic and Grading Reports', 'Student Grade Slip', 'Printable period grade slip. Uses the existing report generator.', $teacher, 'high', 'reports.index'),
            self::item('class-grade-sheet', 'Academic and Grading Reports', 'Class Grade Sheet', 'Each student’s subject average for the current academic year.', $teacher, 'high'),
            self::item('subject-grade-summary', 'Academic and Grading Reports', 'Subject Grade Summary', 'Each student’s subject average for the current academic year.', $academic, 'high'),
            self::item('grade-level-performance', 'Academic and Grading Reports', 'Grade Level Performance Summary', 'Each student’s average, with grade and section.', $academic, 'high'),
            self::item('quarterly-performance', 'Academic and Grading Reports', 'Term Academic Performance Report', 'Each student’s average for every term in the current academic year.', $academic, 'high'),
            self::item('academic-ranking', 'Academic and Grading Reports', 'Academic Ranking', 'Students ordered by general average.', $academic, 'high'),
            self::item('failing-grades', 'Academic and Grading Reports', 'Students with Failing or Low Grades', 'Students whose general average is below the 75% passing mark.', $teacher, 'high'),
            self::item('missing-grades', 'Academic and Grading Reports', 'Incomplete or Missing Grades', 'Enrolled students with no subject average for the current semester.', $teacher, 'high'),
            self::item('grade-change-history', 'Academic and Grading Reports', 'Grade Changes and Correction History', 'Recorded changes to grade records.', $admin, 'high'),
            self::item('descriptive-assessment', 'Academic and Grading Reports', 'Nursery–Grade 3 Descriptive Assessment Report', 'Descriptive levels saved for Nursery through Grade 3.', $academic, 'high'),
            self::item('academic-risk', 'Academic and Grading Reports', 'Academic Risk and Performance Trends Report', 'Students below passing, with open grade alerts.', [], 'high'),

            self::item('daily-attendance', 'Attendance Reports', 'Daily Attendance Summary', 'Each student’s attendance mark for today on the school clock.', $teacher),
            self::item('monthly-attendance', 'Attendance Reports', 'Monthly Attendance Report', 'Each student’s attendance marks for the current month.', $academic),
            self::item('student-attendance', 'Attendance Reports', 'Student Attendance History', 'Attendance rate for each student inside the current academic year dates.', $academic),
            self::item('class-attendance', 'Attendance Reports', 'Class Attendance Summary', 'Each student’s attendance rate for the current academic year.', $teacher),
            self::item('absences-by-grade', 'Attendance Reports', 'Absences by Student or Grade Level', 'Each student with at least one absence, shown with grade and section.', $academic),
            self::item('excessive-absences', 'Attendance Reports', 'Excessive Absences Report', 'Students with 5 or more absences in the current academic year.', $teacher),
            self::item('attendance-percentage', 'Attendance Reports', 'Attendance Percentage Report', 'Present and late marks, with excused marks left out of the rate.', []),
            self::item('unexcused-absences', 'Attendance Reports', 'Unexcused Absences Report', 'Students marked absent. Excused marks are listed separately.', $academic),
            self::item('attendance-changes', 'Attendance Reports', 'Attendance Correction and Modification History', 'Recorded changes to attendance records.', $admin),
            self::item('attendance-by-year', 'Attendance Reports', 'Attendance Summary by Academic Year', 'Attendance counted inside each academic year’s own start and end dates.', $admin),

            self::item('teacher-master-list', 'Teacher, Subject, and Scheduling Reports', 'Teacher Master List', 'Teachers and their account status.', $admin),
            self::item('teaching-load', 'Teacher, Subject, and Scheduling Reports', 'Teacher Teaching Load Report', 'Active class meetings assigned to each teacher this year.', $admin),
            self::item('teaching-load-by-grade', 'Teacher, Subject, and Scheduling Reports', 'Teaching Load by Grade Level', 'Scheduled meetings grouped by grade level.', $admin),
            self::item('subject-assignment', 'Teacher, Subject, and Scheduling Reports', 'Subject Assignment Report', 'Subjects assigned to teachers for the current academic year.', $teacher),
            self::item('class-section-list', 'Teacher, Subject, and Scheduling Reports', 'Class and Section List', 'Sections in the current academic year and how many students are placed in each.', $staff),
            self::item('class-schedule', 'Teacher, Subject, and Scheduling Reports', 'Class Schedule Report', 'Active class schedule for the current academic year.', $teacher),
            self::item('schedule-conflicts', 'Teacher, Subject, and Scheduling Reports', 'Room or Schedule Conflict Report', 'Rooms or teachers booked for overlapping times on the same day.', $admin),
            self::item('unassigned-subjects', 'Teacher, Subject, and Scheduling Reports', 'Unassigned Subjects and Sections', 'Subjects and sections in this year that do not yet have a teacher assignment.', $admin),
            self::item('teacher-availability', 'Teacher, Subject, and Scheduling Reports', 'Teacher Availability Report', 'Teachers assigned to a class who do not yet have an active schedule.', $admin),
            self::item('curriculum-offering', 'Teacher, Subject, and Scheduling Reports', 'Curriculum and Subject Offering Report', 'Subjects configured on each grade’s curriculum.', $admin),

            self::item('course-outlines', 'Lessons, Activities, and Learning Reports', 'Course Outline and Curriculum Report', 'Course outlines saved for the current academic year.', $learning),
            self::item('lesson-inventory', 'Lessons, Activities, and Learning Reports', 'Lesson and Learning Material Inventory', 'Lessons posted for the current academic year.', $learning),
            self::item('activities-list', 'Lessons, Activities, and Learning Reports', 'Activities and Assignments List', 'Activities and assignments for the current academic year.', $teacher),
            self::item('submission-status', 'Lessons, Activities, and Learning Reports', 'Student Submission Status Report', 'How many submissions each published activity has received.', $learning),
            self::item('missing-submissions', 'Lessons, Activities, and Learning Reports', 'Missing or Late Submissions Report', 'Activities where submitted work is still short of the section size, plus late assignment submissions.', $teacher),
            self::item('assignment-completion', 'Lessons, Activities, and Learning Reports', 'Assignment Completion Summary', 'Assignment submission counts for the current academic year.', $learning),
            self::item('activity-performance', 'Lessons, Activities, and Learning Reports', 'Student Activity Performance Report', 'Average activity score by subject.', $learning),
            self::item('upcoming-deadlines', 'Lessons, Activities, and Learning Reports', 'Upcoming Deadlines Report', 'Published work that is still due.', $teacher),
            self::item('lesson-posting', 'Lessons, Activities, and Learning Reports', 'Teacher Lesson Posting Report', 'How many lessons each teacher has posted this year.', $learning),
            self::item('material-uploads', 'Lessons, Activities, and Learning Reports', 'Learning Material Upload History', 'Lesson files uploaded for the current academic year.', $learning),

            self::item('announcements-history', 'Communication and Notification Reports', 'Announcements History', 'Announcements posted in the school portal.', $admin),
            self::item('notifications-sent', 'Communication and Notification Reports', 'Notifications Sent Report', 'Notifications stored for portal users.', $admin),
            self::item('notification-delivery', 'Communication and Notification Reports', 'Notification Delivery Status', 'How many stored notifications have been opened. Device delivery is not tracked separately.', $admin),
            self::item('consultation-requests', 'Communication and Notification Reports', 'Parent–Teacher Consultation Requests', 'Consultation requests and their status.', $teacher),
            self::item('consultation-schedule', 'Communication and Notification Reports', 'Consultation Appointment Schedule', 'Consultations that have a scheduled time.', $learning),
            self::item('consultation-status', 'Communication and Notification Reports', 'Completed and Pending Consultations', 'Consultation counts by status.', $learning),
            self::item('school-events', 'Communication and Notification Reports', 'School Events and Calendar Report', 'Calendar events.', $admin),
            self::item('communication-history', 'Communication and Notification Reports', 'Communication Activity History', 'Recent announcements and messages.', $admin),

            self::item('document-checklist', 'Student Documents and Records', 'Student Document Checklist', 'Required document rows saved on enrollment applications for the current year.', $docs),
            self::item('missing-documents', 'Student Documents and Records', 'Missing Requirements Report', 'Applications with no verified copy of a required document.', $docs),
            self::item('submitted-documents', 'Student Documents and Records', 'Submitted Documents Report', 'Documents uploaded on current-year applications.', $docs),
            self::item('documents-pending', 'Student Documents and Records', 'Documents Pending Verification', 'Uploaded documents that are still pending review.', $docs),
            self::item('documents-verified', 'Student Documents and Records', 'Verified and Rejected Documents Report', 'Documents that were verified or rejected.', $docs),
            self::item('record-completeness', 'Student Documents and Records', 'Student Record Completeness Report', 'Share of each application’s documents that are verified.', $docs),
            self::item('document-history', 'Student Documents and Records', 'Document Upload and Verification History', 'When documents were uploaded and reviewed.', $docs),
            self::item('transfer-documents', 'Student Documents and Records', 'Transfer and Clearance Document Status', 'Document status for transferee applications.', $docs),

            self::item('enrollment-statistics', 'Principal and Administrative Reports', 'Overall Enrollment Statistics', 'Current-year enrollment totals by grade.', [], 'high'),
            self::item('enrollment-grade-year', 'Principal and Administrative Reports', 'Enrollment by Grade Level and Academic Year', 'Placement counts for every academic year and grade. Each row stays inside its own year.', $admin, 'high'),
            self::item('school-performance', 'Principal and Administrative Reports', 'School-wide Academic Performance', 'Passing, below passing, and incomplete counts for the current semester.', $admin, 'high'),
            self::item('absenteeism-trends', 'Principal and Administrative Reports', 'Attendance and Absenteeism Trends', 'Monthly attendance rate inside the current academic year.', $admin),
            self::item('students-at-risk', 'Principal and Administrative Reports', 'Students At Academic Risk', 'Students below the 75% passing average.', $admin, 'high'),
            self::item('grade-level-comparison', 'Principal and Administrative Reports', 'Grade Level Comparison', 'Average performance compared across grade levels.', []),
            self::item('teacher-workload', 'Principal and Administrative Reports', 'Teacher Workload Summary', 'Scheduled meetings and subject assignments per teacher.', $admin),
            self::item('curriculum-implementation', 'Principal and Administrative Reports', 'Curriculum Implementation Summary', 'Course outlines posted for the current academic year against the subject catalog.', $admin),
            self::item('incomplete-records', 'Principal and Administrative Reports', 'Incomplete Academic Records', 'Enrolled students who still have no subject average.', $admin),
            self::item('operations-summary', 'Principal and Administrative Reports', 'School Operations Summary', 'Counts for enrollment, classes, grading, and open applications.', $admin),

            self::item('user-accounts', 'System and User Activity Reports', 'User Accounts Master List', 'Portal accounts. Passwords are never included.', []),
            self::item('account-status', 'System and User Activity Reports', 'Active and Inactive Accounts', 'Accounts grouped by status.', []),
            self::item('login-history', 'System and User Activity Reports', 'Login and Access History', 'Sign-in events when they have been recorded.', []),
            self::item('account-changes', 'System and User Activity Reports', 'Account Creation and Deactivation History', 'When accounts were created, and recorded account updates.', []),
            self::item('grade-audit', 'System and User Activity Reports', 'Grade Modification Audit Trail', 'Recorded grade record changes.', []),
            self::item('attendance-audit', 'System and User Activity Reports', 'Attendance Modification Audit Trail', 'Recorded attendance record changes.', []),
            self::item('student-audit', 'System and User Activity Reports', 'Student Record Modification History', 'Recorded student record changes.', []),
            self::item('export-history', 'System and User Activity Reports', 'Data Export and Document Access History', 'Export and document access events when they have been recorded.', []),
            self::item('failed-operations', 'System and User Activity Reports', 'System Error and Failed Operation Report', 'Failed background jobs when they have been recorded.', []),
        ];
    }

    public static function find(string $key): ?array
    {
        foreach (self::all() as $report) {
            if ($report['key'] === $key) {
                return $report;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $roles
     * @return array{key:string,category:string,title:string,description:string,roles:list<string>,priority:string,href:?string}
     */
    private static function item(string $key, string $category, string $title, string $description, array $roles, string $priority = 'standard', ?string $href = null): array
    {
        return compact('key', 'category', 'title', 'description', 'roles', 'priority', 'href');
    }
}
