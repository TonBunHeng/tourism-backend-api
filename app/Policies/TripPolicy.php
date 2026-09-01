<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

class TripPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Trip $trip): bool
    {
        return $trip->is_public || $trip->user_id === $user->id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Trip $trip): bool
    {
        return $trip->user_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Trip $trip): bool
    {
        return $trip->user_id === $user->id || $user->isAdmin();
    }
}
