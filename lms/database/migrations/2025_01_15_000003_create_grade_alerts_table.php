<?php

use App\Support\SchemaForeign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grade_alerts')) {
            return;
        }

        Schema::create('grade_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('alert_type');
            $table->text('message');
            $table->decimal('threshold_value', 5, 2)->nullable();
            $table->decimal('current_value', 5, 2)->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->timestamps();

            SchemaForeign::add($table, 'student_id', 'students');
            SchemaForeign::add($table, 'subject_id', 'subjects', 'set null');
            SchemaForeign::add($table, 'resolved_by', 'users', 'set null');
            SchemaForeign::add($table, 'academic_year_id', 'academic_years', 'set null');
            SchemaForeign::add($table, 'semester_id', 'semesters', 'set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_alerts');
    }
};
