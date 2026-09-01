<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price !== null ? (float)$this->price : null,
            'currency' => $this->currency ?? 'USD',
            'duration_minutes' => $this->duration_minutes,
            'is_available' => (bool)$this->is_available,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
