<?php

namespace App\Http\Resources;

use App\Traits\NormalizesMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryMediaResource extends JsonResource
{
    use NormalizesMediaUrl;

    public function toArray(Request $request): array
    {
        $catName = $this->relationLoaded('category') ? $this->category?->name : null;
        $placeName = $this->relationLoaded('place') ? $this->place?->name : null;
        $uploaderName = $this->relationLoaded('uploader') ? $this->uploader?->name : null;
        $resolvedUrl = $this->normalizeUrl($this->url);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => strtolower($this->type),
            'url' => $resolvedUrl,
            'category_id' => $this->category_id,
            'category_name' => $catName ?? 'General',
            'category' => $catName ?? 'General',
            'place_id' => $this->place_id,
            'place_name' => $placeName,
            'file_size' => $this->file_size ?? '2.4 MB',
            'size' => $this->file_size ?? '2.4 MB',
            'dimensions' => $this->dimensions ?? '1920x1080',
            'uploaded_by_user_id' => $this->uploaded_by_user_id,
            'uploader_name' => $uploaderName ?? 'Admin',
            'views_count' => (int) ($this->views_count ?? 0),
            'views' => (int) ($this->views_count ?? 0),
            'likes_count' => (int) ($this->likes_count ?? 0),
            'likes' => (int) ($this->likes_count ?? 0),
            'status' => $this->status ?? 'Published',
            'tags' => $this->relationLoaded('tags') ? $this->tags->pluck('tag_name')->toArray() : ['Angkor', 'Cambodia'],
            'uploadDate' => $this->created_at ? $this->created_at->format('M d, Y') : 'Aug 18, 2026',
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
