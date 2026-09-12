<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->boolean('access_limits_enabled')->default(false)->after('instagram_url');
            $table->unsignedInteger('max_teachers')->nullable()->after('access_limits_enabled');
            $table->unsignedInteger('max_students_per_grade')->nullable()->after('max_teachers');
            $table->unsignedInteger('max_parents_per_grade')->nullable()->after('max_students_per_grade');
            $table->json('access_allowed_grades')->nullable()->after('max_parents_per_grade');
            $table->text('access_limits_message')->nullable()->after('access_allowed_grades');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'access_limits_enabled',
                'max_teachers',
                'max_students_per_grade',
                'max_parents_per_grade',
                'access_allowed_grades',
                'access_limits_message',
            ]);
        });
    }
};
