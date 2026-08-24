<?php

use App\Support\SchemaForeign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_gpa')) {
            return;
        }

        Schema::create('student_gpa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->decimal('gpa', 3, 2)->default(0);
            $table->integer('total_units')->default(0);
            $table->integer('total_grade_points')->default(0);
            $table->integer('rank')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            SchemaForeign::add($table, 'student_id', 'students');
            SchemaForeign::add($table, 'academic_year_id', 'academic_years');
            SchemaForeign::add($table, 'semester_id', 'semesters');

            $table->unique(['student_id', 'academic_year_id', 'semester_id'], 'student_gpa_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_gpa');
    }
};
