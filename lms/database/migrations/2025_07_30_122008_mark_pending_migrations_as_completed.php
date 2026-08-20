<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mark pending migrations as completed since the tables already exist
        $pendingMigrations = [
            '2025_01_15_000001_create_weight_settings_table',
            '2025_01_16_000001_create_curriculum_objectives_table',
            '2025_01_16_000007_create_curriculum_objectives_simple_table'
        ];

        foreach ($pendingMigrations as $migration) {
            // Check if migration is not already in migrations table
            $exists = DB::table('migrations')->where('migration', $migration)->exists();
            
            if (!$exists) {
                // Get the next batch number
                $nextBatch = DB::table('migrations')->max('batch') + 1;
                
                // Insert the migration as completed
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $nextBatch
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the manually added migration records
        $migrations = [
            '2025_01_15_000001_create_weight_settings_table',
            '2025_01_16_000001_create_curriculum_objectives_table',
            '2025_01_16_000007_create_curriculum_objectives_simple_table'
        ];

        foreach ($migrations as $migration) {
            DB::table('migrations')->where('migration', $migration)->delete();
        }
    }
};
