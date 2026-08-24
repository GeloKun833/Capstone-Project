<?php

use App\Support\SchemaForeign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'enrollment_application_id')) {
                $table->unsignedBigInteger('enrollment_application_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('students', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('students', 'address')) {
                $table->string('address')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('students', 'parent_email')) {
                $table->string('parent_email')->nullable()->after('email');
            }
            if (!Schema::hasColumn('students', 'parent_name')) {
                $table->string('parent_name')->nullable();
            }
            if (!Schema::hasColumn('students', 'parent_phone')) {
                $table->string('parent_phone')->nullable();
            }
            if (!Schema::hasColumn('students', 'parent_relationship')) {
                $table->string('parent_relationship')->nullable();
            }
            if (!Schema::hasColumn('students', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable();
            }
            if (!Schema::hasColumn('students', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable();
            }
            if (!Schema::hasColumn('students', 'previous_school')) {
                $table->string('previous_school')->nullable();
            }
            if (!Schema::hasColumn('students', 'enrollment_status')) {
                $table->enum('enrollment_status', ['active', 'inactive', 'transferred', 'graduated'])->default('active');
            }
        });

        SchemaForeign::ensure('students', 'enrollment_application_id', 'enrollment_applications', 'set null');
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $columns = [
                'enrollment_application_id',
                'middle_name',
                'address',
                'parent_name',
                'parent_phone',
                'parent_relationship',
                'emergency_contact_name',
                'emergency_contact_phone',
                'previous_school',
                'enrollment_status',
            ];

            $drop = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('students', $column)));
            if ($drop !== []) {
                try {
                    $table->dropForeign(['enrollment_application_id']);
                } catch (\Throwable $e) {
                    // Foreign key may not exist on partial deploys.
                }
                $table->dropColumn($drop);
            }
        });
    }
};
