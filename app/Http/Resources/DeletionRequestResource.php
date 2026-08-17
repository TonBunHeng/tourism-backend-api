<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeletionRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', fn() => $this->user->name),
            'user_email' => $this->whenLoaded('user', fn() => $this->user->email),
            'request_type' => $this->request_type,
            'reason' => $this->reason,
            'additional_info' => $this->additional_info,
            'status' => $this->status,
            'urgency' => $this->urgency,
            'admin_notes' => $this->admin_notes,
            'processed_by_user_id' => $this->processed_by_user_id,
            'processed_by_name' => $this->whenLoaded('processedBy', fn() => $this->processedBy?->name),
            'processed_at' => $this->processed_at ? $this->processed_at->toIso8601String() : null,
            'items' => $this->whenLoaded('items'),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
