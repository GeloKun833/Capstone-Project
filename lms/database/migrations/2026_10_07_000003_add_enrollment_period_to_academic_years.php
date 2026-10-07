<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_years') || Schema::hasColumn('academic_years', 'enrollment_starts_at')) {
            return;
        }

        Schema::table('academic_years', function (Blueprint $table) {
            $table->date('enrollment_starts_at')->nullable()->after('enrollment_open');
            $table->date('enrollment_ends_at')->nullable()->after('enrollment_starts_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('academic_years') || ! Schema::hasColumn('academic_years', 'enrollment_starts_at')) {
            return;
        }

        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn(['enrollment_starts_at', 'enrollment_ends_at']);
        });
    }
};
