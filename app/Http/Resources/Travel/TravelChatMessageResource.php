<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $senderName = $this->relationLoaded('sender') ? $this->sender?->name : ($this->sender_type === 'admin' ? 'Support Agent' : ($this->sender_type === 'ai' ? 'Tourism AI Assistant' : 'You'));
        $senderAvatar = $this->relationLoaded('sender') ? $this->sender?->avatar : null;

        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'sender_type' => $this->sender_type,
            'sender_user_id' => $this->sender_user_id,
            'sender_name' => $senderName,
            'sender_avatar' => $senderAvatar,
            'message_text' => $this->message_text,
            'is_read' => (bool) $this->is_read,
            'is_ai' => (bool) $this->is_ai,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
