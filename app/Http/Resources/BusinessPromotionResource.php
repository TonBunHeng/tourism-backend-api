<?php

namespace App\Http\Resources;

use App\Traits\NormalizesMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessPromotionResource extends JsonResource
{
    use NormalizesMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'title' => $this->title,
            'description' => $this->description,
            'discount_percentage' => $this->discount_percentage !== null ? (float)$this->discount_percentage : null,
            'discount_amount' => $this->discount_amount !== null ? (float)$this->discount_amount : null,
            'promo_code' => $this->promo_code,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'is_active' => (bool)$this->is_active,
            'banner_url' => $this->normalizeUrl($this->banner_url),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
