<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role_name', 'Teacher')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    if (DB::table('teachers')->where('user_id', $user->user_id)->exists()) {
                        continue;
                    }

                    DB::table('teachers')->insert([
                        'user_id' => $user->user_id,
                        'full_name' => $user->name,
                        'phone_number' => $user->phone_number,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Keep profiles: they may have acquired schedule assignments after this migration.
    }
};
