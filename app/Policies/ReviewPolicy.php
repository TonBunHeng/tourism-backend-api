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
        return $review->user_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->id || $user->isAdmin();
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }

    public function reply(User $user, ?Review $review = null): bool
    {
        if ($user->canModerate()) {
            return true;
        }

        if ($review && $review->business_id && $review->business && $review->business->owner_id === $user->id) {
            return true;
        }

        return false;
    }
}
