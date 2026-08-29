<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Review $review): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Review $review): bool
    {
        return $review->user_id === $user->id || in_array($user->role, ['Super Admin', 'Admin'], true);
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->id || in_array($user->role, ['Super Admin', 'Admin'], true);
    }

    public function moderate(User $user): bool
    {
        return in_array($user->role, ['Super Admin', 'Admin'], true);
    }

    public function reply(User $user): bool
    {
        return in_array($user->role, ['Super Admin', 'Admin', 'Guide / Editor'], true);
    }
}
