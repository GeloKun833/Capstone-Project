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
        Schema::table('curriculum_objectives', function (Blueprint $table) {
            if (!Schema::hasColumn('curriculum_objectives', 'grade_level')) {
                $table->integer('grade_level')->default(10)->after('subject_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curriculum_objectives', function (Blueprint $table) {
            if (Schema::hasColumn('curriculum_objectives', 'grade_level')) {
                $table->dropColumn('grade_level');
            }
        });
    }
};
