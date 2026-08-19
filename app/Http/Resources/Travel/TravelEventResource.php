<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'description' => $this->description,
            'location' => $this->location,
            'place_id' => $this->place_id,
            'place' => $this->whenLoaded('place', fn() => $this->place?->name),
            'place_detail' => new TravelPlaceResource($this->whenLoaded('place')),
            'province_id' => $this->province_id,
            'province' => $this->whenLoaded('province', fn() => $this->province?->name),
            'province_detail' => new TravelProvinceResource($this->whenLoaded('province')),
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
            'start_time' => $this->start_time,
            'attendees_count' => (int) $this->attendees_count,
            'price' => $this->price,
            'organizer' => $this->organizer,
            'featured' => (bool) $this->featured,
            'rating' => (float) $this->rating,
            'image_url' => $this->image_url,
            'status' => $this->computed_status ?? $this->status,
            'raw_status' => $this->status,
            'tags' => $this->relationLoaded('tags') ? $this->tags->pluck('tag_name')->toArray() : [],
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
