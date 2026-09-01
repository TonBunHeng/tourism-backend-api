<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Business $business): bool
    {
        // Publicly accessible if approved and active
        if ($business->isApproved() && $business->isActive()) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isBusinessOwner() || $user->isAdmin();
    }

    public function update(User $user, Business $business): bool
    {
        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Business $business): bool
    {
        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function manageImages(User $user, Business $business): bool
    {
        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function manageServices(User $user, Business $business): bool
    {
        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function manageHours(User $user, Business $business): bool
    {
        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function managePromotions(User $user, Business $business): bool
    {
        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function manageEvents(User $user, Business $business): bool
    {
        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function viewStatistics(User $user, Business $business): bool
    {
        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function replyReview(User $user, Business $business): bool
    {
        return $business->owner_id === $user->id || $user->isAdmin();
    }

    public function approve(User $user): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user): bool
    {
        return $user->isAdmin();
    }

    public function suspend(User $user): bool
    {
        return $user->isAdmin();
    }

    public function activate(User $user): bool
    {
        return $user->isAdmin();
    }
}
