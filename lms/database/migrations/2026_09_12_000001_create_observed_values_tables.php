<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observed_value_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('core_value');
            $table->string('statement');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('student_observed_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('observed_value_indicators')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('quarter_1', 5)->nullable(); // AO, SO, RO
            $table->string('quarter_2', 5)->nullable();
            $table->string('quarter_3', 5)->nullable();
            $table->string('quarter_4', 5)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id', 'indicator_id'], 'student_observed_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_observed_values');
        Schema::dropIfExists('observed_value_indicators');
    }
};
