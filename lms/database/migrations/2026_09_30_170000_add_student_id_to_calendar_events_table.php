<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('calendar_events') || Schema::hasColumn('calendar_events', 'student_id')) {
            return;
        }

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('teacher_id')->constrained('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('calendar_events') && Schema::hasColumn('calendar_events', 'student_id')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->dropConstrainedForeignId('student_id');
            });
        }
    }
};