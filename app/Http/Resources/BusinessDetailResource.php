<?php

namespace App\Http\Resources;

use App\Traits\NormalizesMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessDetailResource extends JsonResource
{
    use NormalizesMediaUrl;

    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isOwnerOrAdmin = $user && ($user->id === $this->owner_id || $user->isAdmin());

        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'owner' => $this->whenLoaded('owner', fn () => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
                'phone' => $this->owner->phone,
                'avatar' => $this->normalizeUrl($this->owner->avatar),
            ]),
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
            'verified_at' => $this->verified_at?->toISOString(),
            'rejection_reason' => $isOwnerOrAdmin ? $this->rejection_reason : null,
            'rating' => (float)$this->rating,
            'review_count' => (int)$this->review_count,
            'cover_image_url' => $this->normalizeUrl($this->cover_image_url),
            'images' => BusinessImageResource::collection($this->whenLoaded('images')),
            'services' => BusinessServiceResource::collection($this->whenLoaded('services')),
            'hours' => BusinessHourResource::collection($this->whenLoaded('hours')),
            'promotions' => BusinessPromotionResource::collection($this->whenLoaded('promotions')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
