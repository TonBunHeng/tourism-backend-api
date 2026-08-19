<?php

namespace App\Http\Resources\Travel;

use App\Http\Resources\ReviewReplyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userName = $this->relationLoaded('user') ? $this->user?->name : 'Traveler';
        $userAvatar = $this->relationLoaded('user') ? $this->user?->avatar : null;
        $placeName = $this->relationLoaded('place') ? $this->place?->name : 'Destination';

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $userName,
            'user_avatar' => $userAvatar,
            'place_id' => $this->place_id,
            'place_name' => $placeName,
            'rating' => (int) $this->rating,
            'title' => $this->title ?? 'Experience Review',
            'comment' => $this->comment,
            'likes_count' => (int) ($this->likes_count ?? 0),
            'dislikes_count' => (int) ($this->dislikes_count ?? 0),
            'is_verified' => (bool) $this->is_verified,
            'status' => $this->status ?? 'Approved',
            'images' => $this->relationLoaded('images') ? $this->images->pluck('image_url')->toArray() : [],
            'replies' => ReviewReplyResource::collection($this->whenLoaded('replies')),
            'date' => $this->created_at ? $this->created_at->format('M d, Y') : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
