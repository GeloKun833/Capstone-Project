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
        if (!Schema::hasTable('enrollments')) {
            return;
        }

        Schema::table('enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollments', 'enrollment_date')) {
                $table->timestamp('enrollment_date')->nullable()->after('semester_id');
            }
            if (!Schema::hasColumn('enrollments', 'status')) {
                $table->enum('status', ['active', 'inactive', 'completed', 'dropped'])->default('active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['enrollment_date', 'status']);
        });
    }
};
