<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quarterly_grades', function (Blueprint $table) {
            foreach (['q1_comment', 'q2_comment', 'q3_comment', 'q4_comment'] as $column) {
                if (! Schema::hasColumn('quarterly_grades', $column)) {
                    $table->text($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('quarterly_grades', function (Blueprint $table) {
            foreach (['q1_comment', 'q2_comment', 'q3_comment', 'q4_comment'] as $column) {
                if (Schema::hasColumn('quarterly_grades', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
