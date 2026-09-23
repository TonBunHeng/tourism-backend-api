<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Chat;
use App\Models\DeletionRequest;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Place;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('email', 'admin@tourism.gov.kh')->first() ?? User::first();
        $admin      = User::where('email', 'staff.admin@tourism.gov.kh')->first() ?? $superAdmin;

        // 1. Sync from Businesses (Pending & Approved)
        $businesses = Business::with('owner')->get();
        foreach ($businesses as $biz) {
            $ownerName = $biz->owner ? $biz->owner->name : 'Business Owner';

            if ($biz->verification_status === 'pending') {
                Notification::firstOrCreate(
                    [
                        'type' => 'business_pending',
                        'link' => "/businesses/{$biz->id}",
                        'data->business_id' => $biz->id,
                    ],
                    [
                        'category' => 'Business',
                        'title' => 'New Business Pending Verification',
                        'description' => "Owner {$ownerName} submitted '{$biz->name}' for tourism accreditation review.",
                        'read' => false,
                        'data' => [
                            'business_id' => $biz->id,
                            'business_name' => $biz->name,
                            'owner_name' => $ownerName,
                            'status' => 'pending',
                        ],
                        'created_at' => now()->subHours(rand(2, 24)),
                        'updated_at' => now()->subHours(rand(2, 24)),
                    ]
                );
            } elseif ($biz->verification_status === 'approved') {
                // Business owner notification
                if ($biz->owner_id) {
                    Notification::firstOrCreate(
                        [
                            'user_id' => $biz->owner_id,
                            'type' => 'business_approved',
                            'link' => "/business/businesses/{$biz->id}",
                        ],
                        [
                            'category' => 'Business',
                            'title' => 'Business Officially Verified & Approved!',
                            'description' => "Congratulations! '{$biz->name}' has been verified by the National Tourism Board.",
                            'read' => true,
                            'read_at' => now()->subDays(rand(1, 10)),
                            'data' => [
                                'business_id' => $biz->id,
                                'status' => 'approved',
                            ],
                            'created_at' => now()->subDays(rand(1, 15)),
                            'updated_at' => now()->subDays(rand(1, 15)),
                        ]
                    );
                }
            } elseif ($biz->verification_status === 'rejected') {
                if ($biz->owner_id) {
                    Notification::firstOrCreate(
                        [
                            'user_id' => $biz->owner_id,
                            'type' => 'business_rejected',
                            'link' => "/business/businesses/{$biz->id}",
                        ],
                        [
                            'category' => 'Business',
                            'title' => 'Business Verification Update Required',
                            'description' => "'{$biz->name}' accreditation was rejected: {$biz->rejection_reason}",
                            'read' => false,
                            'data' => [
                                'business_id' => $biz->id,
                                'status' => 'rejected',
                                'reason' => $biz->rejection_reason,
                            ],
                            'created_at' => now()->subDays(rand(1, 5)),
                            'updated_at' => now()->subDays(rand(1, 5)),
                        ]
                    );
                }
            }
        }

        // 2. Sync from Pending Destinations (Places)
        $pendingPlaces = Place::where('status', 'Pending')->get();
        foreach ($pendingPlaces as $place) {
            Notification::firstOrCreate(
                [
                    'type' => 'place_pending',
                    'link' => "/places/{$place->id}",
                    'data->place_id' => $place->id,
                ],
                [
                    'category' => 'Alerts',
                    'title' => "New Destination Awaiting Review: {$place->name}",
                    'description' => "A certified Tour Guide has submitted '{$place->name}' for catalog listing approval.",
                    'read' => false,
                    'data' => [
                        'place_id' => $place->id,
                        'place_name' => $place->name,
                    ],
                    'created_at' => now()->subHours(rand(1, 12)),
                    'updated_at' => now()->subHours(rand(1, 12)),
                ]
            );
        }

        // 3. Sync from Reviews (Moderation Queue)
        $reviews = Review::with(['user', 'place', 'business'])->get();
        foreach ($reviews as $rev) {
            $userName = $rev->user ? $rev->user->name : 'A tourist';
            $targetName = $rev->place ? $rev->place->name : ($rev->business ? $rev->business->name : 'Destination');

            if ($rev->status === 'Pending') {
                Notification::firstOrCreate(
                    [
                        'type' => 'review_pending',
                        'link' => '/reviews',
                        'data->review_id' => $rev->id,
                    ],
                    [
                        'category' => 'Reviews',
                        'title' => "New Review Pending Moderation on \"{$targetName}\"",
                        'description' => "{$userName} submitted a {$rev->rating}-star review: \"{$rev->title}\"",
                        'read' => false,
                        'data' => [
                            'review_id' => $rev->id,
                            'rating' => $rev->rating,
                            'status' => 'Pending',
                        ],
                        'created_at' => now()->subHours(rand(1, 18)),
                        'updated_at' => now()->subHours(rand(1, 18)),
                    ]
                );
            } elseif ($rev->status === 'Flagged') {
                Notification::firstOrCreate(
                    [
                        'type' => 'review_flagged',
                        'link' => '/reviews',
                        'data->review_id' => $rev->id,
                    ],
                    [
                        'category' => 'Alerts',
                        'title' => "Review Flagged for Inappropriate Content",
                        'description' => "A user flagged review #{$rev->id} on \"{$targetName}\" for potential policy violation.",
                        'read' => false,
                        'data' => [
                            'review_id' => $rev->id,
                            'status' => 'Flagged',
                        ],
                        'created_at' => now()->subHours(rand(4, 36)),
                        'updated_at' => now()->subHours(rand(4, 36)),
                    ]
                );
            }
        }

        // 4. Sync from Events
        $featuredEvents = Event::where('featured', true)->where('status', '!=', 'Cancelled')->take(3)->get();
        foreach ($featuredEvents as $ev) {
            Notification::firstOrCreate(
                [
                    'type' => 'event',
                    'link' => "/events/{$ev->id}",
                    'data->event_id' => $ev->id,
                ],
                [
                    'category' => 'Events',
                    'title' => "Upcoming Featured Festival: {$ev->title}",
                    'description' => "Scheduled for {$ev->start_date} at {$ev->location}. Expected attendees: " . number_format($ev->attendees_count) . ".",
                    'read' => true,
                    'read_at' => now()->subDays(2),
                    'data' => [
                        'event_id' => $ev->id,
                        'start_date' => $ev->start_date,
                    ],
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDays(3),
                ]
            );
        }

        // 5. Sync from Deletion Requests
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
                    'title' => 'User Deletion Request Submitted',
                    'description' => "User {$userName} submitted a deletion request: \"{$dr->reason}\".",
                    'read' => $dr->status !== 'pending',
                    'read_at' => $dr->status !== 'pending' ? now() : null,
                    'data' => [
                        'request_id' => $dr->id,
                        'user_id' => $dr->user_id,
                        'urgency' => $dr->urgency,
                    ],
                    'created_at' => $dr->created_at ?? now(),
                    'updated_at' => $dr->updated_at ?? now(),
                ]
            );
        }

        // 6. Sync from Latest Registered Users
        $latestUsers = User::where('role', 'user')->latest()->take(5)->get();
        foreach ($latestUsers as $u) {
            Notification::firstOrCreate(
                [
                    'type' => 'user',
                    'link' => '/users',
                    'data->user_id' => $u->id,
                ],
                [
                    'category' => 'Users',
                    'title' => "New Tourist Registered: {$u->name}",
                    'description' => "{$u->name} ({$u->email}) from {$u->location} joined AngkorVerses.",
                    'read' => true,
                    'read_at' => now()->subHours(5),
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

        // 7. Security Alerts & System Notifications
        Notification::firstOrCreate(
            [
                'type' => 'security_alert',
                'title' => 'Security Shield: Brute Force Attempt Thwarted',
            ],
            [
                'category' => 'Alerts',
                'description' => 'Automated firewall blocked IP 185.220.101.5 after 5 failed administrative login attempts.',
                'link' => '/security-alerts',
                'read' => false,
                'data' => ['blocked_ip' => '185.220.101.5', 'attempts' => 5],
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]
        );

        Notification::firstOrCreate(
            [
                'type' => 'system',
                'title' => 'Automated Database Snapshot & Encryption Verified',
            ],
            [
                'category' => 'System',
                'description' => 'Nightly automated database backup completed successfully. AES-256 data encryption intact.',
                'link' => '/settings',
                'read' => true,
                'read_at' => now()->subHours(12),
                'data' => ['status' => 'success', 'backup_size' => '84.2 MB'],
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ]
        );
    }
}
