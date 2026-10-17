<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_settings')) {
            return;
        }

        Schema::table('school_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('school_settings', 'encoding_starts_on')) {
                $table->date('encoding_starts_on')->nullable()->after('encoding_open_grades');
            }
            if (! Schema::hasColumn('school_settings', 'encoding_due_on')) {
                $table->date('encoding_due_on')->nullable()->after('encoding_starts_on');
            }
        });

        \App\Models\SchoolSetting::clearSettingsCache();
    }

    public function down(): void
    {
        if (! Schema::hasTable('school_settings')) {
            return;
        }

        Schema::table('school_settings', function (Blueprint $table) {
            if (Schema::hasColumn('school_settings', 'encoding_due_on')) {
                $table->dropColumn('encoding_due_on');
            }
            if (Schema::hasColumn('school_settings', 'encoding_starts_on')) {
                $table->dropColumn('encoding_starts_on');
            }
        });

        \App\Models\SchoolSetting::clearSettingsCache();
    }
};
