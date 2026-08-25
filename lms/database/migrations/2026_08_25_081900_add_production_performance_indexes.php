<?php

use App\Support\SafeSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SafeSchema::addIndex('users', 'email', 'users_email_lookup_index');
        SafeSchema::addIndex('users', 'user_id', 'users_user_id_lookup_index');
        SafeSchema::addIndex('users', 'role_name', 'users_role_name_index');
        SafeSchema::addIndex('users', 'status', 'users_status_index');

        SafeSchema::addIndex('students', 'user_id', 'students_user_id_index');
        SafeSchema::addIndex('students', 'email', 'students_email_index');
        SafeSchema::addIndex('students', 'year_level', 'students_year_level_index');
        SafeSchema::addIndex('students', 'class', 'students_class_index');
        SafeSchema::addIndex('students', 'enrollment_application_id', 'students_enrollment_application_id_index');
        SafeSchema::addIndex('students', 'enrollment_status', 'students_enrollment_status_index');

        SafeSchema::addIndex('teachers', 'user_id', 'teachers_user_id_index');

        SafeSchema::addIndex('enrollments', 'status', 'enrollments_status_index');
        SafeSchema::addIndex('enrollments', ['student_id', 'status'], 'enrollments_student_status_index');
        SafeSchema::addIndex('enrollments', ['subject_id', 'status'], 'enrollments_subject_status_index');

        SafeSchema::addIndex('grades', ['student_id', 'academic_year_id', 'semester_id'], 'grades_student_year_semester_index');
        SafeSchema::addIndex('grades', ['subject_id', 'academic_year_id'], 'grades_subject_year_index');
        SafeSchema::addIndex('grades', 'created_at', 'grades_created_at_index');

        SafeSchema::addIndex('attendances', ['student_id', 'date'], 'attendances_student_date_index');
        SafeSchema::addIndex('attendances', ['subject_id', 'date'], 'attendances_subject_date_index');
        SafeSchema::addIndex('attendances', 'status', 'attendances_status_index');

        SafeSchema::addIndex('notifications', ['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_notifiable_read_index');

        SafeSchema::addIndex('enrollment_applications', 'email', 'enrollment_applications_email_index');
        SafeSchema::addIndex('enrollment_applications', 'status', 'enrollment_applications_status_index');
    }

    public function down(): void
    {
        // Indexes are additive production optimizations; dropping is unnecessary.
    }
};
