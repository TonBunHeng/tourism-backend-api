<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelFavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $place = $this->relationLoaded('place') ? $this->place : null;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'place_id' => $this->place_id,
            'visited' => (bool) $this->visited,
            'saved_date' => $this->saved_date ? $this->saved_date->format('Y-m-d') : ($this->created_at ? $this->created_at->format('Y-m-d') : null),
            'place' => $place ? new TravelPlaceResource($place) : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
