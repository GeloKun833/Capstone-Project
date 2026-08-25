<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;

class CachedEloquentUserProvider extends EloquentUserProvider
{
    public static function cacheKey($identifier): string
    {
        return 'auth.user.'.$identifier;
    }

    public function retrieveById($identifier)
    {
        return Cache::remember(self::cacheKey($identifier), 60, function () use ($identifier) {
            return $this->hydrateRoleProfile(parent::retrieveById($identifier));
        });
    }

    public function retrieveByToken($identifier, $token)
    {
        $user = parent::retrieveByToken($identifier, $token);
        if ($user) {
            Cache::put(self::cacheKey($user->getAuthIdentifier()), $this->hydrateRoleProfile($user), 60);
        }

        return $user;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        parent::updateRememberToken($user, $token);
        Cache::forget(self::cacheKey($user->getAuthIdentifier()));
    }

    private function hydrateRoleProfile(?Authenticatable $user): ?Authenticatable
    {
        if (!$user instanceof User) {
            return $user;
        }

        if ($user->role_name === User::ROLE_TEACHER) {
            $user->load('teacher');
        } elseif ($user->role_name === User::ROLE_STUDENT) {
            $user->load('student');
        }

        return $user;
    }
}
