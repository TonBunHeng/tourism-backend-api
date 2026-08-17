<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'url' => $this->url,
            'category_id' => $this->category_id,
            'category_name' => $this->whenLoaded('category', fn() => $this->category->name),
            'place_id' => $this->place_id,
            'place_name' => $this->whenLoaded('place', fn() => $this->place->name),
            'file_size' => $this->file_size,
            'dimensions' => $this->dimensions,
            'uploaded_by_user_id' => $this->uploaded_by_user_id,
            'uploader_name' => $this->whenLoaded('uploader', fn() => $this->uploader->name),
            'views_count' => (int) $this->views_count,
            'likes_count' => (int) $this->likes_count,
            'status' => $this->status,
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->pluck('tag_name')),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
