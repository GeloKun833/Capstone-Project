<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear enrollment data in correct order (child tables first)
        \App\Models\EnrollmentDocument::truncate();
        \App\Models\EnrollmentApplication::truncate();
        
        // Clear students created through enrollment portal
        \App\Models\Student::whereNotNull('enrollment_application_id')->delete();
        
        // Clear users created through enrollment portal (Student and Parent roles)
        \App\Models\User::whereIn('role_name', ['Student', 'Parent'])->delete();
        
        // Re-enable foreign key checks
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed as data is permanently deleted
        // You would need to restore from backup if needed
    }
};
