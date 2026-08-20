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
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->enum('student_category', ['new_student', 'old_student', 'transferee'])
                ->default('new_student')
                ->after('enrollment_type')
                ->comment('Student type: new, old (returning), or transferee');
            
            $table->foreignId('existing_student_id')
                ->nullable()
                ->after('student_category')
                ->constrained('students')
                ->onDelete('set null')
                ->comment('For old students re-enrolling - links to their existing student record');
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
