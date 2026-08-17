<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'population' => $this->population,
            'area' => $this->area,
            'districts_count' => (int) $this->districts_count,
            'communes_count' => (int) $this->communes_count,
            'status' => $this->status,
            'icon' => $this->icon,
            'description' => $this->description,
            'rating' => (float) $this->rating,
            'places_count' => $this->whenCounted('places'),
            'events_count' => $this->whenCounted('events'),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
