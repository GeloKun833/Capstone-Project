<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academic_years') && ! Schema::hasColumn('academic_years', 'enrollment_open')) {
            Schema::table('academic_years', function (Blueprint $table) {
                $table->boolean('enrollment_open')->default(false)->after('status');
            });
        }

        if (Schema::hasTable('class_schedules') && ! Schema::hasColumn('class_schedules', 'is_finalized')) {
            Schema::table('class_schedules', function (Blueprint $table) {
                $table->boolean('is_finalized')->default(false)->after('is_active');
            });
        }

        if (Schema::hasTable('enrollment_applications') && ! Schema::hasColumn('enrollment_applications', 'academic_year_id')) {
            Schema::table('enrollment_applications', function (Blueprint $table) {
                $table->foreignId('academic_year_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('academic_years')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('academic_years')) {
            DB::table('academic_years')->whereNull('enrollment_open')->update(['enrollment_open' => false]);

            $historical = DB::table('academic_years')
                ->whereIn('name', ['2025-2026', '2025–2026'])
                ->value('id');

            if ($historical && Schema::hasTable('enrollment_applications') && Schema::hasColumn('enrollment_applications', 'academic_year_id')) {
                DB::table('enrollment_applications')
                    ->whereNull('academic_year_id')
                    ->update(['academic_year_id' => $historical]);
            }

            $nextYear = DB::table('academic_years')
                ->whereIn('name', ['2026-2027', '2026–2027'])
                ->first();

            if (! $nextYear) {
                DB::table('academic_years')->insert([
                    'name' => '2026–2027',
                    'start_date' => '2026-06-01',
                    'end_date' => '2027-05-31',
                    'status' => 'upcoming',
                    'enrollment_open' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('enrollment_applications') && Schema::hasColumn('enrollment_applications', 'academic_year_id')) {
            Schema::table('enrollment_applications', function (Blueprint $table) {
                $table->dropConstrainedForeignId('academic_year_id');
            });
        }

        if (Schema::hasTable('class_schedules') && Schema::hasColumn('class_schedules', 'is_finalized')) {
            Schema::table('class_schedules', function (Blueprint $table) {
                $table->dropColumn('is_finalized');
            });
        }

        if (Schema::hasTable('academic_years') && Schema::hasColumn('academic_years', 'enrollment_open')) {
            Schema::table('academic_years', function (Blueprint $table) {
                $table->dropColumn('enrollment_open');
            });
        }
    }
};
