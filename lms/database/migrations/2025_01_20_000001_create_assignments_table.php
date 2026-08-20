<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('file_path')->nullable(); // For assignment files (PDF, DOCX)
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable(); // PDF, DOCX, etc.
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
            $table->integer('late_submission_penalty')->default(0); // Percentage penalty
            $table->boolean('requires_file_upload')->default(true);
            $table->text('submission_instructions')->nullable();
            $table->json('allowed_file_types')->nullable(); // ['pdf', 'docx', 'jpg', etc.]
            $table->integer('max_file_size')->default(10); // MB
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
