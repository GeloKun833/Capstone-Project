<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Automatically create role-specific records when a user is created
        try {
            if ($user->role_name === 'Teacher') {
                // Check if teacher record doesn't already exist
                if (!Teacher::where('user_id', $user->user_id)->exists()) {
                    Teacher::create([
                        'user_id' => $user->user_id,
                        'full_name' => $user->name,
                        'phone_number' => $user->phone_number ?: 'Not specified',
                        'address' => 'Not specified',
                        'city' => 'Not specified',
                        'state' => 'Not specified',
                        'zip_code' => 'Not specified',
                        'country' => 'Philippines',
                        'gender' => 'Not specified',
                        'date_of_birth' => $user->date_of_birth ?: 'Not specified',
                        'qualification' => 'Not specified',
                        'experience' => 'Not specified',
                        'avatar' => $user->avatar ?: 'photo_defaults.jpg'
                    ]);
                    Log::info("✅ Auto-created Teacher record for user: {$user->name} (ID: {$user->user_id})");
                }
            }
            // Add similar logic for other roles if needed (Registrar, Head Teacher, etc.)
        } catch (\Exception $e) {
            Log::error("Failed to auto-create role-specific record for user {$user->user_id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Sync updated user information to teacher record
        try {
            if ($user->role_name === 'Teacher') {
                $teacher = Teacher::where('user_id', $user->user_id)->first();
                if ($teacher) {
                    $teacher->update([
                        'full_name' => $user->name,
                        'phone_number' => $user->phone_number ?: $teacher->phone_number,
                    ]);
                    Log::info("✅ Updated Teacher record for user: {$user->name} (ID: {$user->user_id})");
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync user update to teacher record {$user->user_id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
