<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roles = ['Admin', 'Registrar', 'Teacher', 'Student', 'Parent'];

        foreach ($roles as $role) {
            $exists = DB::table('role_type_users')->where('role_type', $role)->exists();
            if (! $exists) {
                DB::table('role_type_users')->insert([
                    'role_type' => $role,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Normalize legacy "Teachers" label to "Teacher"
        DB::table('role_type_users')->where('role_type', 'Teachers')->update(['role_type' => 'Teacher']);
        DB::table('users')->where('role_name', 'Teachers')->update(['role_name' => 'Teacher']);
    }

    public function down(): void
    {
        //
    }
};
