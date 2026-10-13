<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('school_settings') && ! Schema::hasColumn('school_settings', 'encoding_open_grades')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->json('encoding_open_grades')->nullable();
            });
        }

        if (Schema::hasTable('activities') && ! Schema::hasColumn('activities', 'activity_type')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->string('activity_type', 40)->nullable()->after('title');
            });
        }

        \App\Models\SchoolSetting::clearSettingsCache();
    }

    public function down(): void
    {
        if (Schema::hasColumn('school_settings', 'encoding_open_grades')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->dropColumn('encoding_open_grades');
            });
        }

        if (Schema::hasColumn('activities', 'activity_type')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('activity_type');
            });
        }
    }
};
