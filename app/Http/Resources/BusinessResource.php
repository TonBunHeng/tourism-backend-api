<?php

namespace App\Http\Resources;

use App\Traits\NormalizesMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
{
    use NormalizesMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug ?? null,
            ] : null),
            'province_id' => $this->province_id,
            'province' => $this->whenLoaded('province', fn () => $this->province ? [
                'id' => $this->province->id,
                'name' => $this->province->name,
            ] : null),
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float)$this->latitude : null,
            'longitude' => $this->longitude !== null ? (float)$this->longitude : null,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'price_range' => $this->price_range,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'rating' => (float)$this->rating,
            'review_count' => (int)$this->review_count,
            'cover_image_url' => $this->normalizeUrl($this->cover_image_url),
            'images_count' => $this->whenCounted('images'),
            'services_count' => $this->whenCounted('services'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
