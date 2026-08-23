<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelGalleryCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userName = $this->user?->name ?? 'Traveler';
        $userAvatar = $this->user?->avatar ?? null;
        $commentText = $this->comment;

        return [
            'id' => $this->id,
            'gallery_media_id' => $this->gallery_media_id,
            'media_id' => $this->gallery_media_id,
            'parent_id' => $this->parent_id,
            'comment' => $commentText,
            'text' => $commentText,
            'content' => $commentText,
            'message' => $commentText,
            'user_id' => $this->user_id,
            'user_name' => $userName,
            'author' => $userName,
            'author_name' => $userName,
            'name' => $userName,
            'user_avatar' => $userAvatar,
            'avatar' => $userAvatar,
            'avatar_url' => $userAvatar,
            'user' => [
                'id' => $this->user_id,
                'name' => $userName,
                'user_name' => $userName,
                'avatar' => $userAvatar,
                'avatar_url' => $userAvatar,
            ],
            'replies' => $this->relationLoaded('replies')
                ? self::collection($this->replies)
                : [],
            'date' => $this->created_at ? $this->created_at->diffForHumans() : null,
            'timestamp' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'created_at_human' => $this->created_at ? $this->created_at->diffForHumans() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
