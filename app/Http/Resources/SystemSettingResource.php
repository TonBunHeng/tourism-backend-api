<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->setting_key,
            'setting_key' => $this->setting_key,
            'value' => $this->setting_value,
            'setting_value' => $this->setting_value,
            'group' => $this->setting_group,
            'setting_group' => $this->setting_group,
            'description' => $this->description,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
