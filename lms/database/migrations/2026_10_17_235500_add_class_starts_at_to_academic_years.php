<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_years') || Schema::hasColumn('academic_years', 'class_starts_at')) {
            return;
        }

        Schema::table('academic_years', function (Blueprint $table) {
            $table->date('class_starts_at')->nullable()->after('end_date');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('academic_years') || ! Schema::hasColumn('academic_years', 'class_starts_at')) {
            return;
        }

        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('class_starts_at');
        });
    }
};
