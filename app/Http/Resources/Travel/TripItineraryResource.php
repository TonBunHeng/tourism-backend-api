<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripItineraryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trip_id' => $this->trip_id,
            'place_id' => $this->place_id,
            'place' => $this->whenLoaded('place', function () {
                return $this->place ? [
                    'id' => $this->place->id,
                    'name' => $this->place->name,
                    'location' => $this->place->location,
                    'thumbnail' => $this->place->thumbnail,
                    'rating' => (float) $this->place->rating,
                    'category' => $this->place->category?->name,
                    'province' => $this->place->province?->name,
                ] : null;
            }),
            'day_number' => $this->day_number,
            'time_slot' => $this->time_slot,
            'activity' => $this->activity,
            'estimated_cost' => (float) $this->estimated_cost,
            'duration_minutes' => $this->duration_minutes,
            'notes' => $this->notes,
            'sort_order' => $this->sort_order,
            'is_completed' => (bool) $this->is_completed,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
