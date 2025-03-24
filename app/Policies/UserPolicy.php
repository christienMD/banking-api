<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can create new users.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user has admin access.
     */
    public function admin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user has manager access.
     */
    public function manager(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine if the user has teller access.
     */
    public function teller(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'teller']);
    }
}