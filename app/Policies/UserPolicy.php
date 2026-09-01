<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Super Admin can edit anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin can edit Users, Guides, Business Owners, but not Super Admins or other Admins
        if ($user->isAdmin()) {
            return (!$model->isSuperAdmin() && (!$model->isAdmin() || $user->id === $model->id));
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

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return !$model->isAdmin();
        }

        return false;
    }
}
