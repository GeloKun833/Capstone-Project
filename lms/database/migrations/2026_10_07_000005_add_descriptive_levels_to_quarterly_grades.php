<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quarterly_grades')) {
            return;
        }

        Schema::table('quarterly_grades', function (Blueprint $table) {
            if (! Schema::hasColumn('quarterly_grades', 'q1_level')) {
                $table->string('q1_level', 1)->nullable()->after('quarter_1');
            }
            if (! Schema::hasColumn('quarterly_grades', 'q2_level')) {
                $table->string('q2_level', 1)->nullable()->after('quarter_2');
            }
            if (! Schema::hasColumn('quarterly_grades', 'q3_level')) {
                $table->string('q3_level', 1)->nullable()->after('quarter_3');
            }
            if (! Schema::hasColumn('quarterly_grades', 'q4_level')) {
                $table->string('q4_level', 1)->nullable()->after('quarter_4');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('quarterly_grades')) {
            return;
        }

        Schema::table('quarterly_grades', function (Blueprint $table) {
            foreach (['q1_level', 'q2_level', 'q3_level', 'q4_level'] as $column) {
                if (Schema::hasColumn('quarterly_grades', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
