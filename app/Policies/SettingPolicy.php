<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['Super Admin', 'Admin'], true);
    }

    public function update(User $user): bool
    {
        return in_array($user->role, ['Super Admin'], true);
    }
}
