<?php

use App\Support\SchemaForeign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lessons') || !Schema::hasColumn('lessons', 'curriculum_objective_id')) {
            return;
        }

        try {
            Schema::table('lessons', function ($table) {
                $table->dropForeign(['curriculum_objective_id']);
            });
        } catch (\Throwable $e) {
            // Foreign key may not exist on a fresh cloud database.
        }

        DB::statement('ALTER TABLE lessons MODIFY curriculum_objective_id BIGINT UNSIGNED NULL');
        SchemaForeign::ensure('lessons', 'curriculum_objective_id', 'curriculum_objectives', 'set null');
    }

    public function down(): void
    {
        if (!Schema::hasTable('lessons') || !Schema::hasColumn('lessons', 'curriculum_objective_id')) {
            return;
        }

        try {
            Schema::table('lessons', function ($table) {
                $table->dropForeign(['curriculum_objective_id']);
            });
        } catch (\Throwable $e) {
            // Ignore missing foreign key.
        }

        DB::statement('ALTER TABLE lessons MODIFY curriculum_objective_id BIGINT UNSIGNED NOT NULL');
        SchemaForeign::ensure('lessons', 'curriculum_objective_id', 'curriculum_objectives');
    }
};
