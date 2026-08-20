<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\DeletionRequest;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sync from Deletion Requests
        $deletionRequests = DeletionRequest::with('user')->get();
        foreach ($deletionRequests as $dr) {
            $userName = $dr->user ? $dr->user->name : 'A user';
            Notification::firstOrCreate(
                [
                    'type' => 'deletion_request',
                    'link' => '/deletion-requests',
                    'data->request_id' => $dr->id,
                ],
                [
                    'category' => 'Alerts',
                    'title' => 'Deletion Request Pending Approval',
                    'description' => "User {$userName} submitted a deletion request: \"{$dr->reason}\".",
                    'read' => $dr->status !== 'pending',
                    'read_at' => $dr->status !== 'pending' ? now() : null,
                    'data' => [
                        'request_id' => $dr->id,
                        'user_id' => $dr->user_id,
                        'user_name' => $userName,
                        'urgency' => $dr->urgency,
                    ],
                    'created_at' => $dr->created_at ?? now(),
                    'updated_at' => $dr->updated_at ?? now(),
                ]
            );
        }

        // 2. Sync from Reviews
        $reviews = Review::with(['user', 'place'])->get();
        foreach ($reviews as $rev) {
            $userName = $rev->user ? $rev->user->name : 'A tourist';
            $placeName = $rev->place ? $rev->place->name : 'Destination';
            Notification::firstOrCreate(
                [
                    'type' => 'review',
                    'link' => '/ratings',
                    'data->review_id' => $rev->id,
                ],
                [
                    'category' => 'Reviews',
                    'title' => "New {$rev->rating}-Star Review on \"{$placeName}\"",
                    'description' => "{$userName} wrote: \"{$rev->title}\" - {$rev->comment}",
                    'read' => $rev->status === 'Approved',
                    'read_at' => $rev->status === 'Approved' ? now() : null,
                    'data' => [
                        'review_id' => $rev->id,
                        'rating' => $rev->rating,
                        'place_id' => $rev->place_id,
                        'place_name' => $placeName,
                    ],
                    'created_at' => $rev->created_at ?? now(),
                    'updated_at' => $rev->updated_at ?? now(),
                ]
            );
        }

        // 3. Sync from Chats
        $chats = Chat::with(['user', 'messages'])->get();
        foreach ($chats as $chat) {
            $userName = $chat->user ? $chat->user->name : 'A tourist';
            $lastMsg = $chat->last_message ?: 'New chat conversation started';
            Notification::firstOrCreate(
                [
                    'type' => 'chat',
                    'link' => '/chat',
                    'data->chat_id' => $chat->id,
                ],
                [
                    'category' => 'Messages',
                    'title' => "New Support Message from {$userName}",
                    'description' => "Category: {$chat->category} | \"{$lastMsg}\"",
                    'read' => ($chat->unread_count == 0),
                    'read_at' => ($chat->unread_count == 0) ? now() : null,
                    'data' => [
                        'chat_id' => $chat->id,
                        'category' => $chat->category,
                        'priority' => $chat->priority,
                    ],
                    'created_at' => $chat->created_at ?? now(),
                    'updated_at' => $chat->updated_at ?? now(),
                ]
            );
        }

        // 4. Sync from Latest Registered Users
        $latestUsers = User::where('role', 'User')->latest()->take(5)->get();
        foreach ($latestUsers as $u) {
            Notification::firstOrCreate(
                [
                    'type' => 'user',
                    'link' => '/users',
                    'data->user_id' => $u->id,
                ],
                [
                    'category' => 'Users',
                    'title' => "New User Registered: {$u->name}",
                    'description' => "{$u->name} ({$u->email}) joined AngkorVerses platform.",
                    'read' => false,
                    'data' => [
                        'user_id' => $u->id,
                        'email' => $u->email,
                        'location' => $u->location,
                    ],
                    'created_at' => $u->created_at ?? now(),
                    'updated_at' => $u->updated_at ?? now(),
                ]
            );
        }

        // 5. System Notifications
        Notification::firstOrCreate(
            [
                'type' => 'system',
                'title' => 'Database Cloud Backup & Security Active',
            ],
            [
                'category' => 'System',
                'description' => 'Nightly automated database snapshot created and system encryption verified.',
                'link' => '/settings',
                'read' => true,
                'read_at' => now(),
                'data' => ['status' => 'success'],
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]
        );
    }
}
