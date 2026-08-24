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
        if (!Schema::hasTable('class_schedules') || Schema::hasColumn('class_schedules', 'class_type')) {
            return;
        }

        Schema::table('class_schedules', function (Blueprint $table) {
            $table->enum('class_type', ['lecture', 'laboratory', 'tutorial', 'exam', 'other'])->default('lecture')->after('end_time');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('class_schedules') || !Schema::hasColumn('class_schedules', 'class_type')) {
            return;
        }

        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropColumn('class_type');
        });
    }
}; 