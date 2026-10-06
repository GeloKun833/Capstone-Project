<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sections') && ! Schema::hasColumn('sections', 'academic_year_id')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->foreignId('academic_year_id')
                    ->nullable()
                    ->after('grade_level')
                    ->constrained('academic_years')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sections') && Schema::hasColumn('sections', 'academic_year_id')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropConstrainedForeignId('academic_year_id');
            });
        }
    }
};
