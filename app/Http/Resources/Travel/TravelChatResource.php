<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => $this->status,
            'unread_count' => (int) $this->unread_count,
            'last_message' => $this->last_message,
            'last_message_time' => $this->last_message_time,
            'messages' => TravelChatMessageResource::collection($this->whenLoaded('messages')),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
