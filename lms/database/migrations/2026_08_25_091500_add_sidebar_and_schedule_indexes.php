<?php

use App\Support\SafeSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SafeSchema::addIndex('class_schedules', 'subject_id', 'class_schedules_subject_id_index');
        SafeSchema::addIndex('class_schedules', ['teacher_id', 'subject_id'], 'class_schedules_teacher_subject_index');
        SafeSchema::addIndex('enrollments', 'student_id', 'enrollments_student_id_index');
        SafeSchema::addIndex('enrollments', 'subject_id', 'enrollments_subject_id_index');
    }

    public function down(): void
    {
        // Additive production indexes.
    }
};
