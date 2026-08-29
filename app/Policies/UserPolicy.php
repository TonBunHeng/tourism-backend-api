<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['Super Admin', 'Admin'], true);
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || in_array($user->role, ['Super Admin', 'Admin'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['Super Admin', 'Admin'], true);
    }

    public function update(User $user, User $model): bool
    {
        // Super Admin can edit anyone
        if ($user->role === 'Super Admin') {
            return true;
        }

        // Admin can edit Users and Guides, but not other Admins or Super Admins
        if ($user->role === 'Admin') {
            return !in_array($model->role, ['Super Admin', 'Admin'], true) || $user->id === $model->id;
        }

        // Users can only edit themselves
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Cannot delete self
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->role === 'Super Admin') {
            return true;
        }

        if ($user->role === 'Admin') {
            return !in_array($model->role, ['Super Admin', 'Admin'], true);
        }

        return false;
    }
}
