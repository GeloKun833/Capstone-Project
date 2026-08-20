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
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollment_application_id')->nullable()->after('id');
            $table->string('middle_name')->nullable()->after('last_name');
            $table->string('address')->nullable()->after('phone_number');
            $table->string('parent_name')->nullable()->after('parent_email');
            $table->string('parent_phone')->nullable()->after('parent_name');
            $table->string('parent_relationship')->nullable()->after('parent_phone');
            $table->string('emergency_contact_name')->nullable()->after('parent_relationship');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('previous_school')->nullable()->after('emergency_contact_phone');
            $table->enum('enrollment_status', ['active', 'inactive', 'transferred', 'graduated'])->default('active')->after('previous_school');
            
            $table->foreign('enrollment_application_id')->references('id')->on('enrollment_applications')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['enrollment_application_id']);
            $table->dropColumn([
                'enrollment_application_id',
                'middle_name',
                'address',
                'parent_name',
                'parent_phone',
                'parent_relationship',
                'emergency_contact_name',
                'emergency_contact_phone',
                'previous_school',
                'enrollment_status'
            ]);
        });
    }
};
