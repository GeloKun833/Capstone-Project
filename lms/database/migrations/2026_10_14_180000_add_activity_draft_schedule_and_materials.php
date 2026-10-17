<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activities')) {
            return;
        }

        Schema::table('activities', function (Blueprint $table) {
            if (! Schema::hasColumn('activities', 'status')) {
                $table->string('status', 20)->default('published')->after('allows_submission');
            }
            if (! Schema::hasColumn('activities', 'due_time')) {
                $table->time('due_time')->nullable()->after('due_date');
            }
            if (! Schema::hasColumn('activities', 'points')) {
                $table->unsignedSmallInteger('points')->nullable()->after('due_time');
            }
            if (! Schema::hasColumn('activities', 'attachments')) {
                $table->json('attachments')->nullable()->after('points');
            }
        });

        DB::statement('ALTER TABLE activities MODIFY due_date DATE NULL');
        DB::table('activities')->whereNull('status')->orWhere('status', '')->update(['status' => 'published']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('activities')) {
            return;
        }

        Schema::table('activities', function (Blueprint $table) {
            foreach (['attachments', 'points', 'due_time', 'status'] as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
