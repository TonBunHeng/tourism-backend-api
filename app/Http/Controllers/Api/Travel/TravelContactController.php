<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Notification;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelContactController extends Controller
{
    use ApiResponse;

    /**
     * Submit visitor contact & support inquiry.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:100',
            'message' => 'required|string|min:5|max:2000',
        ]);

        $ticketCode = 'INQ-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $currentUser = $request->user('sanctum');

        // Create Chat thread if authenticated, or find/create system guest user
        $userId = $currentUser?->id;
        if (!$userId) {
            $userByEmail = User::where('email', $validated['email'])->first();
            $userId = $userByEmail?->id;
        }

        if ($userId) {
            $chat = Chat::create([
                'user_id' => $userId,
                'category' => $validated['subject'] ?: 'Customer Support',
                'priority' => 'medium',
                'status' => 'active',
                'last_message' => substr($validated['message'], 0, 150),
                'last_message_time' => now()->format('H:i'),
            ]);

            ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_type' => 'user',
                'sender_user_id' => $userId,
                'message_text' => "[Ticket: {$ticketCode}] " . $validated['message'],
            ]);
        }

        // Notify Admins
        $adminUsers = User::whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN])->get();
        foreach ($adminUsers as $admin) {
            Notification::createNotification([
                'user_id' => $admin->id,
                'type' => 'support_inquiry',
                'category' => 'General Inquiry',
                'title' => "Support Inquiry: {$validated['name']} ({$ticketCode})",
                'description' => "Subject: " . ($validated['subject'] ?: 'General') . " - " . substr($validated['message'], 0, 120),
                'link' => '/notifications',
                'data' => [
                    'ticket_code' => $ticketCode,
                    'sender_name' => $validated['name'],
                    'sender_email' => $validated['email'],
                    'sender_phone' => $validated['phone'] ?? null,
                ],
            ]);
        }

        return $this->successResponse([
            'ticket_code' => $ticketCode,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'message' => 'Thank you for contacting AngkorVerses. Your message has been received by our support team.',
        ], 'Your inquiry has been submitted successfully.');
    }
}
