<?php

use App\Support\SchemaForeign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('class_posts')) {
            return;
        }

        Schema::create('class_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->enum('type', ['announcement', 'resource', 'discussion', 'reminder'])->default('announcement');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('allows_comments')->default(true);
            $table->boolean('requires_confirmation')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
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
        Schema::dropIfExists('class_posts');
    }
};
