<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $place = $this->relationLoaded('place') ? $this->place : null;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'place_id' => $this->place_id,
            'visited' => (bool) $this->visited,
            'saved_date' => $this->saved_date ? $this->saved_date->format('Y-m-d') : ($this->created_at ? $this->created_at->format('Y-m-d') : '2026-08-18'),
            'place' => $place ? new PlaceResource($place) : null,
            'name' => $place?->name ?? 'Attraction',
            'category' => $place?->category?->name ?? 'Attraction',
            'location' => $place?->address ?? 'Cambodia',
            'address' => $place?->address ?? 'Cambodia',
            'rating' => (float) ($place?->rating ?? 5.0),
            'reviews' => (int) ($place?->reviews_count ?? 0),
            'price' => $place?->price ?? 'Free',
            'image' => $place?->image_url ?? $place?->image ?? '',
            'description' => $place?->description ?? '',
            'best_time' => $place?->best_time ?? 'Morning',
            'duration' => $place?->duration ?? '2 Hours',
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
