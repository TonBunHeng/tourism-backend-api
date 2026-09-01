<?php

namespace App\Http\Resources;

use App\Traits\NormalizesMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessImageResource extends JsonResource
{
    use NormalizesMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'image_url' => $this->normalizeUrl($this->image_url),
            'caption' => $this->caption,
            'is_cover' => (bool)$this->is_cover,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
