<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->computed_status ?? $this->status ?? 'Upcoming';

        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'description' => $this->description,
            'location' => $this->location,
            'place_id' => $this->place_id,
            'place_name' => $this->whenLoaded('place', fn() => $this->place->name),
            'province_id' => $this->province_id,
            'province_name' => $this->whenLoaded('province', fn() => $this->province->name),
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
            'date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'start_time' => $this->start_time,
            'time' => $this->start_time,
            'attendees_count' => (int) $this->attendees_count,
            'price' => $this->price,
            'organizer' => $this->organizer,
            'featured' => (bool) $this->featured,
            'rating' => (float) $this->rating,
            'image_url' => $this->image_url,
            'imageUrl' => $this->image_url,
            'status' => $status,
            'computed_status' => $status,
            'raw_status' => $this->status,
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->pluck('tag_name')),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
