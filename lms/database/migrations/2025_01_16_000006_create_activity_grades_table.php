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
        Schema::create('activity_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('activity_submissions')->onDelete('cascade');
            $table->foreignId('rubric_id')->constrained('activity_rubrics')->onDelete('cascade');
            $table->integer('score');
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('graded_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_grades');
    }
}; 