<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Chat::with(['user', 'messages.sender']);

        // Non-admin users only see their own chats
        if (!in_array($user->role, ['Super Admin', 'Admin'])) {
            $query->where('user_id', $user->id);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        $chats = $query->orderBy('updated_at', 'desc')->get();

        return $this->successResponse(ChatResource::collection($chats), 'Chats retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:100',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'critical'])],
            'initial_message' => 'required|string',
        ]);

        $user = $request->user();

        $chat = Chat::create([
            'user_id' => $user->id,
            'category' => $validated['category'] ?? 'Travel Planning',
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'active',
            'last_message' => $validated['initial_message'],
            'last_message_time' => now()->format('h:i A'),
        ]);

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'user',
            'sender_user_id' => $user->id,
            'message_text' => $validated['initial_message'],
            'is_read' => true,
        ]);

        $chat->load(['user', 'messages.sender']);

        return $this->successResponse(new ChatResource($chat), 'Chat started successfully.', 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $chat = Chat::with(['user', 'messages.sender'])->find($id);

        if (!$chat) {
            return $this->errorResponse('Chat not found.', 404);
        }

        if (!in_array($user->role, ['Super Admin', 'Admin']) && $chat->user_id !== $user->id) {
            return $this->errorResponse('Unauthorized to view this chat.', 403);
        }

        // Mark unread messages as read
        ChatMessage::where('chat_id', $chat->id)
            ->where('sender_user_id', '!=', $user->id)
            ->update(['is_read' => true]);

        $chat->update(['unread_count' => 0]);

        return $this->successResponse(new ChatResource($chat), 'Chat details retrieved successfully.');
    }

    public function sendMessage(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $chat = Chat::find($id);

        if (!$chat) {
            return $this->errorResponse('Chat not found.', 404);
        }

        $validated = $request->validate([
            'message_text' => 'required|string',
        ]);

        $senderType = in_array($user->role, ['Super Admin', 'Admin']) ? 'admin' : 'user';

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => $senderType,
            'sender_user_id' => $user->id,
            'message_text' => $validated['message_text'],
            'is_read' => false,
        ]);

        $chat->update([
            'last_message' => $validated['message_text'],
            'last_message_time' => now()->format('h:i A'),
            'unread_count' => $chat->unread_count + 1,
        ]);

        $message->load('sender');

        return $this->successResponse(new ChatMessageResource($message), 'Message sent successfully.', 201);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $chat = Chat::find($id);

        if (!$chat) {
            return $this->errorResponse('Chat not found.', 404);
        }

        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(['active', 'closed', 'archived'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
        ]);

        $chat->update($validated);
        $chat->load(['user', 'messages.sender']);

        return $this->successResponse(new ChatResource($chat), 'Chat updated successfully.');
    }
}
