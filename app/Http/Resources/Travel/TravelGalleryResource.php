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

        $user = $request->user();
        $isLiked = false;
        if ($user && method_exists($this->resource, 'isLikedBy')) {
            $isLiked = $this->isLikedBy($user);
        }

        $commentsCount = $this->relationLoaded('allComments')
            ? $this->allComments->count()
            : (int) ($this->all_comments_count ?? $this->comments()->count());

        $views = (int) ($this->views_count ?? 0);
        $likes = (int) ($this->likes_count ?? 0);

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
            'views_count' => $views,
            'view_count' => $views,
            'views' => $views,
            'likes_count' => $likes,
            'like_count' => $likes,
            'likes' => $likes,
            'is_liked' => $isLiked,
            'isLiked' => $isLiked,
            'liked' => $isLiked,
            'comments_count' => $commentsCount,
            'comment_count' => $commentsCount,
            'commentsCount' => $commentsCount,
            'comments' => $this->relationLoaded('comments')
                ? TravelGalleryCommentResource::collection($this->comments)
                : [],
            'tags' => $this->relationLoaded('tags') ? $this->tags->pluck('tag_name')->toArray() : [],
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
