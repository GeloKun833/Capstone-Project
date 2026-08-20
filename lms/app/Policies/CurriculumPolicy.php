<?php

namespace App\Policies;

use App\Models\Curriculum;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CurriculumPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role_name === 'Admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Curriculum $curriculum): bool
    {
        return $user->role_name === 'Admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role_name === 'Admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Curriculum $curriculum): bool
    {
        return $user->role_name === 'Admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Curriculum $curriculum): bool
    {
        return $user->role_name === 'Admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Curriculum $curriculum): bool
    {
        return $user->role_name === 'Admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Curriculum $curriculum): bool
    {
        return $user->role_name === 'Admin';
    }

    /**
     * Perform pre-authorization checks.
     */
    public function before($user, $ability)
    {
        // Only allow admins
        if ($user->role_name === 'Admin') {
            return true;
        }
        return null;
    }
}
