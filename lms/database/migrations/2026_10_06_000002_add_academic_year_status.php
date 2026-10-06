<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academic_years') && ! Schema::hasColumn('academic_years', 'status')) {
            Schema::table('academic_years', function (Blueprint $table) {
                $table->string('status', 20)->default('upcoming')->after('end_date');
            });
        }

        if (Schema::hasTable('semesters') && ! Schema::hasColumn('semesters', 'status')) {
            Schema::table('semesters', function (Blueprint $table) {
                $table->string('status', 20)->default('upcoming')->after('academic_year_id');
            });
        }

        if (Schema::hasTable('class_schedules') && ! Schema::hasColumn('class_schedules', 'academic_year_id')) {
            Schema::table('class_schedules', function (Blueprint $table) {
                $table->foreignId('academic_year_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('academic_years')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('academic_years')) {
            DB::table('academic_years')->where('name', '2024-2025')->update(['status' => 'completed']);
            DB::table('academic_years')->where('name', '2025-2026')->update(['status' => 'current']);
            DB::table('academic_years')->whereNull('status')->update(['status' => 'upcoming']);
        }

        if (Schema::hasTable('semesters') && Schema::hasColumn('semesters', 'status')) {
            DB::statement('UPDATE semesters INNER JOIN academic_years ON academic_years.id = semesters.academic_year_id SET semesters.status = academic_years.status');
        }

        if (Schema::hasTable('class_schedules') && Schema::hasColumn('class_schedules', 'academic_year_id')) {
            $currentId = DB::table('academic_years')->where('status', 'current')->value('id');
            if ($currentId) {
                DB::table('class_schedules')->whereNull('academic_year_id')->update(['academic_year_id' => $currentId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('class_schedules') && Schema::hasColumn('class_schedules', 'academic_year_id')) {
            Schema::table('class_schedules', function (Blueprint $table) {
                $table->dropConstrainedForeignId('academic_year_id');
            });
        }

        if (Schema::hasTable('semesters') && Schema::hasColumn('semesters', 'status')) {
            Schema::table('semesters', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasTable('academic_years') && Schema::hasColumn('academic_years', 'status')) {
            Schema::table('academic_years', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
