<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE enrollment_applications MODIFY phone_number VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::table('enrollment_applications')
            ->whereNull('phone_number')
            ->update(['phone_number' => '']);

        DB::statement('ALTER TABLE enrollment_applications MODIFY phone_number VARCHAR(255) NOT NULL');
    }
};