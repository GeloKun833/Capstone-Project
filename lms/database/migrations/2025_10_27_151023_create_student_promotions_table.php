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
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('promoted_by')->constrained('users')->onDelete('cascade');
            $table->string('from_year_level');
            $table->string('to_year_level');
            $table->foreignId('from_academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->foreignId('to_academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->enum('promotion_status', ['promoted', 'retained', 'graduated'])->default('promoted');
            $table->text('remarks')->nullable();
            $table->decimal('final_gpa', 4, 2)->nullable();
            $table->date('promotion_date');
            $table->timestamps();
            
            // Indexes
            $table->index('student_id');
            $table->index('promotion_status');
            $table->index('from_year_level');
            $table->index('to_year_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};
