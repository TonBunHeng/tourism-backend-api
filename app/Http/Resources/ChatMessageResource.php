<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'sender_type' => $this->sender_type,
            'sender_user_id' => $this->sender_user_id,
            'sender_name' => $this->whenLoaded('sender', fn() => $this->sender?->name),
            'sender_avatar' => $this->whenLoaded('sender', fn() => $this->sender?->avatar),
            'message_text' => $this->message_text,
            'is_read' => (bool) $this->is_read,
            'is_ai' => (bool) $this->is_ai,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
