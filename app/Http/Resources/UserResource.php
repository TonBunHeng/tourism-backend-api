<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'role' => $this->role,
            'status' => $this->status,
            'location' => $this->location,
            'verified' => (bool) $this->verified,
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->toIso8601String() : null,
            'two_factor_auth' => (bool) $this->two_factor_auth,
            'subscription' => $this->subscription,
            'activity_level' => $this->activity_level,
            'bio' => $this->bio,
            'reviews_count' => (int) ($this->reviews_count ?: ($this->relationLoaded('reviews') ? $this->reviews->count() : $this->reviews()->count())),
            'favorites_count' => (int) ($this->relationLoaded('favorites') ? $this->favorites->count() : $this->favorites()->count()),
            'bookings_count' => (int) ($this->relationLoaded('bookings') ? $this->bookings->count() : $this->bookings()->count()),
            'is_online' => $this->isOnline(),
            'online_status' => $this->online_status,
            'last_active_human' => $this->last_active_human,
            'last_active_at' => $this->last_active_at ? $this->last_active_at->toIso8601String() : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
