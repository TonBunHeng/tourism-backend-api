<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelGalleryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $catName = $this->relationLoaded('category') ? $this->category?->name : null;
        $placeName = $this->relationLoaded('place') ? $this->place?->name : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? null,
            'type' => strtolower($this->type ?? 'image'),
            'media_type' => strtolower($this->type ?? 'image'),
            'url' => $this->url,
            'media_url' => $this->url,
            'category_id' => $this->category_id,
            'category' => $catName,
            'place_id' => $this->place_id,
            'place' => $placeName,
            'views_count' => (int) ($this->views_count ?? 0),
            'view_count' => (int) ($this->views_count ?? 0),
            'likes_count' => (int) ($this->likes_count ?? 0),
            'tags' => $this->relationLoaded('tags') ? $this->tags->pluck('tag_name')->toArray() : [],
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
