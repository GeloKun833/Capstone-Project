<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE enrollment_applications MODIFY status ENUM('draft','pending','under_review','approved','rejected','needs_documents') NOT NULL DEFAULT 'pending'");
        DB::statement('ALTER TABLE enrollment_applications MODIFY email VARCHAR(255) NULL, MODIFY address VARCHAR(255) NULL, MODIFY parent_name VARCHAR(255) NULL, MODIFY parent_phone VARCHAR(255) NULL, MODIFY parent_email VARCHAR(255) NULL, MODIFY parent_relationship VARCHAR(255) NULL, MODIFY emergency_contact_name VARCHAR(255) NULL, MODIFY emergency_contact_phone VARCHAR(255) NULL');

        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->uuid('enrollment_group_token')->nullable()->index();
        });
    }

    public function down(): void
    {
        DB::table('enrollment_applications')->where('status', 'draft')->update(['status' => 'pending']);
        DB::table('enrollment_applications')->whereNull('email')->update(['email' => '']);
        DB::table('enrollment_applications')->whereNull('address')->update(['address' => '']);
        DB::table('enrollment_applications')->whereNull('parent_name')->update(['parent_name' => '']);
        DB::table('enrollment_applications')->whereNull('parent_phone')->update(['parent_phone' => '']);
        DB::table('enrollment_applications')->whereNull('parent_email')->update(['parent_email' => '']);
        DB::table('enrollment_applications')->whereNull('parent_relationship')->update(['parent_relationship' => '']);
        DB::table('enrollment_applications')->whereNull('emergency_contact_name')->update(['emergency_contact_name' => '']);
        DB::table('enrollment_applications')->whereNull('emergency_contact_phone')->update(['emergency_contact_phone' => '']);

        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->dropIndex(['enrollment_group_token']);
            $table->dropColumn('enrollment_group_token');
        });

        DB::statement("ALTER TABLE enrollment_applications MODIFY status ENUM('pending','under_review','approved','rejected','needs_documents') NOT NULL DEFAULT 'pending'");
        DB::statement('ALTER TABLE enrollment_applications MODIFY email VARCHAR(255) NOT NULL, MODIFY address VARCHAR(255) NOT NULL, MODIFY parent_name VARCHAR(255) NOT NULL, MODIFY parent_phone VARCHAR(255) NOT NULL, MODIFY parent_email VARCHAR(255) NOT NULL, MODIFY parent_relationship VARCHAR(255) NOT NULL, MODIFY emergency_contact_name VARCHAR(255) NOT NULL, MODIFY emergency_contact_phone VARCHAR(255) NOT NULL');
    }
};