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
        Schema::table('lessons', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['curriculum_objective_id']);
            
            // Make the column nullable
            $table->foreignId('curriculum_objective_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable
            $table->foreign('curriculum_objective_id')->references('id')->on('curriculum_objectives')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['curriculum_objective_id']);
            
            // Make the column required again
            $table->foreignId('curriculum_objective_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('curriculum_objective_id')->references('id')->on('curriculum_objectives')->onDelete('cascade');
        });
    }
};
