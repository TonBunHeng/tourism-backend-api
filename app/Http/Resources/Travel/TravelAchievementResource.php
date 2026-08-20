<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelAchievementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pointsMap = [
            'Angkor Explorer' => 50,
            'Eco Traveler' => 50,
            'Cultural Enthusiast' => 75,
            'Coastal Wanderer' => 50,
            'Heritage Master' => 100,
            'Province Adventurer' => 120,
            'Heritage Explorer' => 50,
        ];

        $badgeName = $this->achievement_name ?? $this->name ?? 'Explorer Badge';
        $unlocked = (bool) ($this->unlocked ?? $this->is_unlocked ?? false);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id ?? null,
            'name' => $badgeName,
            'badge_name' => $badgeName,
            'achievement_name' => $badgeName,
            'description' => $this->description,
            'icon' => $this->icon ?? 'Award',
            'points' => $pointsMap[$badgeName] ?? 50,
            'unlocked' => $unlocked,
            'is_unlocked' => $unlocked,
            'unlocked_at' => $this->unlocked_at ? (is_string($this->unlocked_at) ? $this->unlocked_at : $this->unlocked_at->toIso8601String()) : null,
            'created_at' => $this->created_at ? (is_string($this->created_at) ? $this->created_at : $this->created_at->toIso8601String()) : null,
            'updated_at' => $this->updated_at ? (is_string($this->updated_at) ? $this->updated_at : $this->updated_at->toIso8601String()) : null,
        ];
    }
}
