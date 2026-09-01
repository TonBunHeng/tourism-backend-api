<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Event $event): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->canModerate() || $user->isBusinessOwner();
    }

    public function update(User $user, Event $event): bool
    {
        if ($user->canModerate()) {
            return true;
        }

        if ($user->isBusinessOwner() && $event->business_id && $event->business && $event->business->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isBusinessOwner() && $event->business_id && $event->business && $event->business->owner_id === $user->id) {
            return true;
        }

        return false;
    }
}
