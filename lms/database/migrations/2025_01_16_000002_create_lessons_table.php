<?php

use App\Support\SchemaForeign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lessons')) {
            return;
        }

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('curriculum_objective_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->date('lesson_date');
            $table->enum('status', ['draft', 'published', 'completed'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            SchemaForeign::add($table, 'teacher_id', 'teachers');
            SchemaForeign::add($table, 'subject_id', 'subjects');
            SchemaForeign::add($table, 'section_id', 'sections');
            SchemaForeign::add($table, 'curriculum_objective_id', 'curriculum_objectives');
            SchemaForeign::add($table, 'academic_year_id', 'academic_years');
            SchemaForeign::add($table, 'semester_id', 'semesters');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
