<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activities') && ! Schema::hasColumn('activities', 'quiz_questions')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->json('quiz_questions')->nullable()->after('activity_type');
            });
        }

        if (Schema::hasTable('activity_submissions') && ! Schema::hasColumn('activity_submissions', 'quiz_answers')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->json('quiz_answers')->nullable()->after('comments');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('activities') && Schema::hasColumn('activities', 'quiz_questions')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('quiz_questions');
            });
        }

        if (Schema::hasTable('activity_submissions') && Schema::hasColumn('activity_submissions', 'quiz_answers')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->dropColumn('quiz_answers');
            });
        }
    }
};
