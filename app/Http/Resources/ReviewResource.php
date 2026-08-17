<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', fn() => $this->user->name),
            'user_avatar' => $this->whenLoaded('user', fn() => $this->user->avatar),
            'place_id' => $this->place_id,
            'place_name' => $this->whenLoaded('place', fn() => $this->place->name),
            'rating' => (int) $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'likes_count' => (int) $this->likes_count,
            'dislikes_count' => (int) $this->dislikes_count,
            'is_verified' => (bool) $this->is_verified,
            'status' => $this->status,
            'images' => $this->whenLoaded('images', fn() => $this->images->pluck('image_url')),
            'replies' => ReviewReplyResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
