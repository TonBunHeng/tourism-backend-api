<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelPlaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn() => $this->category?->name),
            'category_detail' => new TravelCategoryResource($this->whenLoaded('category')),
            'province_id' => $this->province_id,
            'province' => $this->whenLoaded('province', fn() => $this->province?->name),
            'province_detail' => new TravelProvinceResource($this->whenLoaded('province')),
            'address' => $this->address,
            'coordinates' => $this->coordinates,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'description' => $this->description,
            'best_time' => $this->best_time,
            'duration' => $this->duration,
            'price' => $this->price,
            'rating' => (float) $this->rating,
            'reviews_count' => (int) $this->reviews_count,
            'visitors_count' => (int) $this->visitors_count,
            'image_url' => $this->image_url,
            'is_featured' => (bool) $this->is_featured,
            'status' => $this->status,
            'reviews' => TravelReviewResource::collection($this->whenLoaded('reviews')),
            'gallery' => TravelGalleryResource::collection($this->whenLoaded('galleryMedia')),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
