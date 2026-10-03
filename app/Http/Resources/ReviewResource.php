<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userName = $this->relationLoaded('user') ? ($this->user?->name ?? 'Traveler') : 'Traveler';
        $userAvatar = $this->relationLoaded('user') ? $this->user?->avatar : null;
        $userEmail = $this->relationLoaded('user') ? $this->user?->email : null;
        $userVerified = $this->relationLoaded('user') ? (bool) ($this->user?->verified ?? false) : false;

        $place = $this->relationLoaded('place') ? $this->place : null;
        $placeName = $place?->name ?? 'Destination';
        $categoryName = $place?->category?->name ?? ($place?->relationLoaded('category') ? $place->category?->name : null);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $userName,
            'user' => [
                'id' => $this->user_id,
                'name' => $userName,
                'email' => $userEmail,
                'avatar' => $userAvatar,
                'verified' => $userVerified,
            ],
            'user_avatar' => $userAvatar,
            'avatar' => $userAvatar,
            'place_id' => $this->place_id,
            'place_name' => $placeName,
            'place' => [
                'id' => $this->place_id,
                'name' => $placeName,
                'category' => $categoryName ?? 'General',
                'address' => $place?->address,
                'rating' => $place ? (float) $place->rating : null,
                'image_url' => $place?->image_url,
            ],
            'category' => $categoryName ?? 'General',
            'rating' => (int) $this->rating,
            'title' => $this->title ?? 'Experience Review',
            'comment' => $this->comment,
            'likes_count' => (int) ($this->likes_count ?? 0),
            'likes' => (int) ($this->likes_count ?? 0),
            'dislikes_count' => (int) ($this->dislikes_count ?? 0),
            'dislikes' => (int) ($this->dislikes_count ?? 0),
            'is_verified' => (bool) $this->is_verified,
            'status' => $this->status ?? 'Approved',
            'images' => $this->relationLoaded('images') ? $this->images->pluck('image_url')->toArray() : [],
            'replies' => ReviewReplyResource::collection($this->whenLoaded('replies')),
            'date' => $this->created_at ? $this->created_at->format('M d, Y') : 'Aug 18, 2026',
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
