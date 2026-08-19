<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelAchievementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id ?? null,
            'name' => $this->achievement_name,
            'achievement_name' => $this->achievement_name,
            'description' => $this->description,
            'icon' => $this->icon,
            'unlocked' => (bool) ($this->unlocked ?? false),
            'unlocked_at' => $this->unlocked_at ? $this->unlocked_at->toIso8601String() : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
