<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'title' => $this->title,
            'destination' => $this->destination,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'duration_days' => ($this->start_date && $this->end_date)
                ? $this->start_date->diffInDays($this->end_date) + 1
                : 1,
            'budget' => (float) $this->budget,
            'currency' => $this->currency ?? 'USD',
            'travelers' => (int) $this->travelers,
            'status' => $this->status,
            'notes' => $this->notes,
            'cover_image' => $this->cover_image,
            'is_public' => (bool) $this->is_public,
            'itineraries_count' => $this->itineraries_count ?? $this->itineraries()->count(),
            'itineraries' => TripItineraryResource::collection($this->whenLoaded('itineraries')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
