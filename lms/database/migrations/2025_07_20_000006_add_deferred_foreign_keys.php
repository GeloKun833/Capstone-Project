<?php

use App\Support\SchemaForeign;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SchemaForeign::ensure('weight_settings', 'component_id', 'subject_components');
        SchemaForeign::ensure('weight_settings', 'academic_year_id', 'academic_years', 'set null');
        SchemaForeign::ensure('weight_settings', 'semester_id', 'semesters', 'set null');

        SchemaForeign::ensure('student_gpa', 'academic_year_id', 'academic_years');
        SchemaForeign::ensure('student_gpa', 'semester_id', 'semesters');

        SchemaForeign::ensure('grade_alerts', 'academic_year_id', 'academic_years', 'set null');
        SchemaForeign::ensure('grade_alerts', 'semester_id', 'semesters', 'set null');

        SchemaForeign::ensure('lessons', 'section_id', 'sections');
        SchemaForeign::ensure('lessons', 'academic_year_id', 'academic_years');
        SchemaForeign::ensure('lessons', 'semester_id', 'semesters');

        SchemaForeign::ensure('assignments', 'section_id', 'sections');
        SchemaForeign::ensure('assignments', 'academic_year_id', 'academic_years');
        SchemaForeign::ensure('assignments', 'semester_id', 'semesters');

        SchemaForeign::ensure('class_posts', 'section_id', 'sections');
        SchemaForeign::ensure('class_posts', 'academic_year_id', 'academic_years');
        SchemaForeign::ensure('class_posts', 'semester_id', 'semesters');
    }

    public function down(): void
    {
        // Foreign keys are left in place; dropping tables handles cleanup.
    }
};
