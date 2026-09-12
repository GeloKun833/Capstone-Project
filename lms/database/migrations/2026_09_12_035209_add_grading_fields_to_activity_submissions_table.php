<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->decimal('total_score', 8, 2)->nullable()->after('status');
            $table->decimal('max_possible_score', 8, 2)->nullable()->after('total_score');
            $table->decimal('percentage', 5, 2)->nullable()->after('max_possible_score');
            $table->string('letter_grade', 5)->nullable()->after('percentage');
            $table->text('feedback')->nullable()->after('letter_grade');
            $table->foreignId('graded_by')->nullable()->after('feedback')->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable()->after('graded_by');
        });
    }

    public function down(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('graded_by');
            $table->dropColumn([
                'total_score',
                'max_possible_score',
                'percentage',
                'letter_grade',
                'feedback',
                'graded_at',
            ]);
        });
    }
};
