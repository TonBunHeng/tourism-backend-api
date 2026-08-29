<?php

namespace App\Policies;

use App\Models\GalleryMedia;
use App\Models\User;

class GalleryPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, GalleryMedia $media): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['Super Admin', 'Admin', 'Guide / Editor'], true);
    }

    public function update(User $user, GalleryMedia $media): bool
    {
        return in_array($user->role, ['Super Admin', 'Admin', 'Guide / Editor'], true);
    }

    public function delete(User $user, GalleryMedia $media): bool
    {
        return in_array($user->role, ['Super Admin', 'Admin'], true);
    }
}
