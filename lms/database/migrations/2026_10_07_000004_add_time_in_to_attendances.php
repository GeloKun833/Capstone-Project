<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendances') && ! Schema::hasColumn('attendances', 'time_in')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->time('time_in')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'time_in')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('time_in');
            });
        }
    }
};
