<?php

use App\Support\SafeSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SafeSchema::addIndex('calendar_events', ['teacher_id', 'start_time'], 'calendar_events_teacher_start_index');
        SafeSchema::addIndex('calendar_events', ['subject_id', 'start_time'], 'calendar_events_subject_start_index');

        SafeSchema::addIndex('lessons', 'teacher_id', 'lessons_teacher_id_index');
        SafeSchema::addIndex('lessons', ['teacher_id', 'status'], 'lessons_teacher_status_index');
        SafeSchema::addIndex('lessons', ['subject_id', 'academic_year_id', 'semester_id'], 'lessons_subject_year_sem_index');

        SafeSchema::addIndex('assignments', 'teacher_id', 'assignments_teacher_id_index');
        SafeSchema::addIndex('assignments', ['teacher_id', 'is_active', 'status'], 'assignments_teacher_active_status_index');

        SafeSchema::addIndex('assignment_submissions', ['assignment_id', 'status'], 'assignment_submissions_assignment_status_index');
        SafeSchema::addIndex('assignment_submissions', ['student_id', 'status'], 'assignment_submissions_student_status_index');

        SafeSchema::addIndex('student_section_assignments', 'section_id', 'ssa_section_id_index');
        SafeSchema::addIndex('student_section_assignments', 'student_id', 'ssa_student_id_index');

        SafeSchema::addIndex('activity_log', ['causer_type', 'causer_id', 'created_at'], 'activity_log_causer_created_index');
    }

    public function down(): void
    {
        // Additive production indexes; intentional no-op on rollback.
    }
};
