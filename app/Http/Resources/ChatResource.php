<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->relationLoaded('user') ? $this->user : null;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'status' => $user->status,
                'role' => $user->role,
            ] : [
                'id' => $this->user_id,
                'name' => 'Tourist User',
                'email' => 'user@tourism.gov.kh',
                'avatar' => null,
                'status' => 'Active',
                'role' => 'User',
            ],
            'user_name' => $user?->name ?? 'Tourist User',
            'user_avatar' => $user?->avatar,
            'category' => $this->category ?? 'Support',
            'priority' => $this->priority ?? 'medium',
            'status' => $this->status ?? 'active',
            'unread_count' => (int) ($this->unread_count ?? 0),
            'last_message' => $this->last_message ?? '',
            'last_message_time' => $this->last_message_time ?? 'Just now',
            'messages' => ChatMessageResource::collection($this->whenLoaded('messages')),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
