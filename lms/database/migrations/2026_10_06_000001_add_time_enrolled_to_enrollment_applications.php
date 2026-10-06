<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('enrollment_applications', 'time_enrolled')) {
            Schema::table('enrollment_applications', function (Blueprint $table) {
                $table->time('time_enrolled')->nullable()->after('date_enrolled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('enrollment_applications', 'time_enrolled')) {
            Schema::table('enrollment_applications', function (Blueprint $table) {
                $table->dropColumn('time_enrolled');
            });
        }
    }
};
