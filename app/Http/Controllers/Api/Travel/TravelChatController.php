<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Travel\TravelChatMessageRequest;
use App\Http\Requests\Travel\TravelChatRequest;
use App\Http\Resources\Travel\TravelChatMessageResource;
use App\Http\Resources\Travel\TravelChatResource;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelChatController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $chats = Chat::with(['messages' => fn($q) => $q->orderBy('created_at', 'asc')])
            ->where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return $this->successResponse(
            TravelChatResource::collection($chats),
            'Support chats retrieved successfully.'
        );
    }

    public function store(TravelChatRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $chat = Chat::create([
            'user_id' => $user->id,
            'category' => $validated['category'] ?? 'General Inquiry',
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'active',
            'unread_count' => 0,
            'last_message' => $validated['message'],
            'last_message_time' => 'Just now',
        ]);

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'user',
            'sender_user_id' => $user->id,
            'message_text' => $validated['message'],
            'is_read' => false,
            'is_ai' => false,
            'created_at' => now(),
        ]);

        $chat->load(['messages.sender']);

        return $this->successResponse(
            new TravelChatResource($chat),
            'Support conversation started successfully.',
            201
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $chat = Chat::with(['messages' => fn($q) => $q->orderBy('created_at', 'asc')])->find($id);

        if (!$chat) {
            return $this->errorResponse('Support chat not found.', 404);
        }

        if ($chat->user_id !== $user->id) {
            return $this->errorResponse('Unauthorized. You can only view your own support chats.', 403);
        }

        return $this->successResponse(
            new TravelChatResource($chat),
            'Support chat conversation retrieved successfully.'
        );
    }

    public function sendMessage(TravelChatMessageRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        $chat = Chat::find($id);

        if (!$chat) {
            return $this->errorResponse('Support chat not found.', 404);
        }

        if ($chat->user_id !== $user->id) {
            return $this->errorResponse('Unauthorized. You can only send messages to your own chats.', 403);
        }

        $validated = $request->validated();

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'user',
            'sender_user_id' => $user->id,
            'message_text' => $validated['message'],
            'is_read' => false,
            'is_ai' => false,
            'created_at' => now(),
        ]);

        $chat->update([
            'last_message' => $validated['message'],
            'last_message_time' => 'Just now',
            'updated_at' => now(),
        ]);

        $message->load('sender');

        return $this->successResponse(
            new TravelChatMessageResource($message),
            'Message sent successfully.',
            201
        );
    }
}
