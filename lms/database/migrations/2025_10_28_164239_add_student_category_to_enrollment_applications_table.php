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
        if (!Schema::hasTable('enrollment_applications')) {
            return;
        }

        Schema::table('enrollment_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollment_applications', 'student_category')) {
                $table->enum('student_category', ['new_student', 'old_student', 'transferee'])
                    ->default('new_student')
                    ->comment('Student type: new, old (returning), or transferee');
            }
            if (!Schema::hasColumn('enrollment_applications', 'existing_student_id')) {
                $table->foreignId('existing_student_id')
                    ->nullable()
                    ->constrained('students')
                    ->onDelete('set null')
                    ->comment('For old students re-enrolling - links to their existing student record');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->dropColumn(['student_category', 'existing_student_id']);
        });
    }
};
