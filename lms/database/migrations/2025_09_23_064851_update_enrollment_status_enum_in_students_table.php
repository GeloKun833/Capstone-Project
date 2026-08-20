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
        // First, modify the column to allow the new enum values
        DB::statement("ALTER TABLE students MODIFY COLUMN enrollment_status ENUM('pending', 'active', 'inactive', 'transferred', 'graduated') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE students MODIFY COLUMN enrollment_status ENUM('active', 'inactive', 'transferred', 'graduated') DEFAULT 'active'");
    }
};