<?php

use App\Support\SchemaForeign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assignments')) {
            return;
        }

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->boolean('allows_late_submission')->default(false);
            $table->integer('late_submission_penalty')->default(0);
            $table->boolean('requires_file_upload')->default(true);
            $table->text('submission_instructions')->nullable();
            $table->json('allowed_file_types')->nullable();
            $table->integer('max_file_size')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            SchemaForeign::add($table, 'teacher_id', 'teachers');
            SchemaForeign::add($table, 'subject_id', 'subjects');
            SchemaForeign::add($table, 'section_id', 'sections');
            SchemaForeign::add($table, 'academic_year_id', 'academic_years');
            SchemaForeign::add($table, 'semester_id', 'semesters');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
