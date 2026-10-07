<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $exists = DB::table('permissions')
            ->where('name', 'encode grades')
            ->where('guard_name', 'web')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('permissions')->insert([
            'name' => 'encode grades',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $id = DB::table('permissions')
            ->where('name', 'encode grades')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $id) {
            return;
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->where('permission_id', $id)->delete();
        }
        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')->where('permission_id', $id)->delete();
        }

        DB::table('permissions')->where('id', $id)->delete();
    }
};
