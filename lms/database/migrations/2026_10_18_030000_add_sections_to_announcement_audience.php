<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE announcements MODIFY target_audience ENUM('all','students','teachers','parents','admins','sections') NOT NULL DEFAULT 'all'");
    }

    public function down(): void
    {
        DB::table('announcements')->where('target_audience', 'sections')->update(['target_audience' => 'all']);
        DB::statement("ALTER TABLE announcements MODIFY target_audience ENUM('all','students','teachers','parents','admins') NOT NULL DEFAULT 'all'");
    }
};
