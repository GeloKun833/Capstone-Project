<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_years') || Schema::hasColumn('academic_years', 'class_ends_at')) {
            return;
        }

        Schema::table('academic_years', function (Blueprint $table) {
            $table->date('class_ends_at')->nullable()->after('class_starts_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('academic_years') || ! Schema::hasColumn('academic_years', 'class_ends_at')) {
            return;
        }

        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('class_ends_at');
        });
    }
};
