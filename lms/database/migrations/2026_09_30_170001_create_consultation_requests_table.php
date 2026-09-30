<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('consultation_requests')) {
            return;
        }

        Schema::create('consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('calendar_event_id')->nullable()->constrained('calendar_events')->nullOnDelete();
            $table->dateTime('requested_start_at');
            $table->dateTime('requested_end_at');
            $table->dateTime('scheduled_start_at')->nullable();
            $table->dateTime('scheduled_end_at')->nullable();
            $table->text('student_message')->nullable();
            $table->text('teacher_response')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['teacher_id', 'status', 'requested_start_at'], 'consultation_teacher_status_start_idx');
            $table->index(['student_id', 'status', 'requested_start_at'], 'consultation_student_status_start_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_requests');
    }
};