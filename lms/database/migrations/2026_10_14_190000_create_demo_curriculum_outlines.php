<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('curriculum_course_outlines')) {
            Schema::create('curriculum_course_outlines', function (Blueprint $table) {
                $table->id();
                $table->string('demo_key', 80)->unique();
                $table->foreignId('curriculum_id')->nullable()->constrained('curricula')->nullOnDelete();
                $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->string('grade_level', 40);
                $table->string('title');
                $table->text('description');
                $table->text('learning_objectives');
                $table->json('topics');
                $table->text('assessment_methods');
                $table->string('sequence_note');
                $table->string('status', 20)->default('published');
                $table->string('document_type', 40)->default('DEMO — NOT OFFICIAL');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('curriculum_optlb_records')) {
            Schema::create('curriculum_optlb_records', function (Blueprint $table) {
                $table->id();
                $table->string('demo_key', 80)->unique();
                $table->foreignId('course_outline_id')->constrained('curriculum_course_outlines')->cascadeOnDelete();
                $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->string('grade_level', 40);
                $table->string('title');
                $table->text('learning_objectives');
                $table->text('topic_sequence');
                $table->text('suggested_activities');
                $table->text('assessment');
                $table->text('references');
                $table->string('status', 20)->default('published');
                $table->string('demo_label', 40)->default('NOT OFFICIAL');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('curriculum_demo_materials')) {
            Schema::create('curriculum_demo_materials', function (Blueprint $table) {
                $table->id();
                $table->string('demo_key', 80)->unique();
                $table->foreignId('course_outline_id')->nullable()->constrained('curriculum_course_outlines')->cascadeOnDelete();
                $table->foreignId('optlb_record_id')->nullable()->constrained('curriculum_optlb_records')->cascadeOnDelete();
                $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->string('grade_level', 40);
                $table->string('title');
                $table->string('file_path');
                $table->string('file_name');
                $table->string('demo_label', 40)->default('NOT OFFICIAL');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_demo_materials');
        Schema::dropIfExists('curriculum_optlb_records');
        Schema::dropIfExists('curriculum_course_outlines');
    }
};
