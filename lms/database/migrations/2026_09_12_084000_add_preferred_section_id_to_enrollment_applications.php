<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollment_applications', 'preferred_section_id')) {
                $table->foreignId('preferred_section_id')
                    ->nullable()
                    ->after('grade_level_applying_for')
                    ->constrained('sections')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            if (Schema::hasColumn('enrollment_applications', 'preferred_section_id')) {
                $table->dropConstrainedForeignId('preferred_section_id');
            }
        });
    }
};
