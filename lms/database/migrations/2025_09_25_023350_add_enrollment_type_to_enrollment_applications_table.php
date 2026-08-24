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
        if (!Schema::hasTable('enrollment_applications') || Schema::hasColumn('enrollment_applications', 'enrollment_type')) {
            return;
        }

        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->enum('enrollment_type', ['parent', 'student'])->default('parent')->after('application_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->dropColumn('enrollment_type');
        });
    }
};
