<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_email', 'admin@panorama.edu');
        $password = config('app.admin_password');

        if (empty($password)) {
            $this->command?->warn('ADMIN_PASSWORD is not set. Skipping admin user seed.');
            return;
        }

        foreach (['Admin', 'Registrar', 'Teacher', 'Student', 'Parent'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'user_id' => 'ADM-001',
                'name' => 'Administrator',
                'password' => Hash::make($password),
                'role_name' => User::ROLE_ADMIN,
                'status' => 'Active',
                'join_date' => now()->toDateString(),
            ]
        );

        if ($user->wasRecentlyCreated === false && $user->role_name !== User::ROLE_ADMIN) {
            $user->role_name = User::ROLE_ADMIN;
            $user->status = 'Active';
            $user->save();
        }

        $user->email_verified_at = $user->email_verified_at ?? now();
        $user->save();

        $user->assignRole('Admin');

        $this->command?->info("Admin ready: {$user->email}");
    }
}
