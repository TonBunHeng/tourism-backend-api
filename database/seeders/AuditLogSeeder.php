<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('email', 'admin@tourism.gov.kh')->first();
        $admin = User::where('email', 'staff.admin@tourism.gov.kh')->first();
        $editor = User::where('email', 'sopheaktra@tourism.gov.kh')->first();
        $owner = User::where('email', 'owner@angkor-restaurant.com')->first();
        $tourist1 = User::where('email', 'vit.vong@example.com')->first();
        $tourist2 = User::where('email', 'ou.sreylin@example.com')->first();

        $sampleLogs = [
            // Admin actions
            [
                'user_id' => $admin?->id,
                'user_name' => $admin?->name ?? 'Kosal Visal',
                'user_role' => 'admin',
                'action' => 'business_approved',
                'entity_type' => 'Business',
                'entity_id' => 1,
                'description' => 'Approved and activated business listing "Angkor Heritage Restaurant"',
                'old_values' => ['verification_status' => 'pending'],
                'new_values' => ['verification_status' => 'approved', 'status' => 'active'],
                'ip_address' => '103.242.12.45',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/124.0.0.0',
                'created_at' => Carbon::now()->subMinutes(12),
            ],
            [
                'user_id' => $admin?->id,
                'user_name' => $admin?->name ?? 'Kosal Visal',
                'user_role' => 'admin',
                'action' => 'place_approved',
                'entity_type' => 'Place',
                'entity_id' => 3,
                'description' => 'Approved destination "Banteay Srei (Citadel of Women)" into active directory',
                'old_values' => ['status' => 'Pending'],
                'new_values' => ['status' => 'Active'],
                'ip_address' => '103.242.12.45',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/124.0.0.0',
                'created_at' => Carbon::now()->subHours(1)->subMinutes(30),
            ],
            [
                'user_id' => $admin?->id,
                'user_name' => $admin?->name ?? 'Kosal Visal',
                'user_role' => 'admin',
                'action' => 'booking_status_updated',
                'entity_type' => 'Booking',
                'entity_id' => 12,
                'description' => 'Updated reservation #BK-2026-0012 status from pending to confirmed',
                'old_values' => ['status' => 'pending'],
                'new_values' => ['status' => 'confirmed'],
                'ip_address' => '103.242.12.45',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/124.0.0.0',
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'user_id' => $admin?->id,
                'user_name' => $admin?->name ?? 'Kosal Visal',
                'user_role' => 'admin',
                'action' => 'ip_blocked',
                'entity_type' => 'SecurityAlert',
                'entity_id' => 4,
                'description' => 'Enforced IP blacklist against 185.220.101.5 due to repeated credential stuffing',
                'old_values' => null,
                'new_values' => ['ip' => '185.220.101.5', 'reason' => 'Brute force attempts'],
                'ip_address' => '103.242.12.45',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/124.0.0.0',
                'created_at' => Carbon::now()->subHours(5),
            ],

            // Tourism Content Editor actions
            [
                'user_id' => $editor?->id,
                'user_name' => $editor?->name ?? 'Sophal Sopheaktra',
                'user_role' => 'guide_editor',
                'action' => 'place_created',
                'entity_type' => 'Place',
                'entity_id' => 15,
                'description' => 'Created new historical destination "Prasat Preah Vihear Northern Sanctuary"',
                'old_values' => null,
                'new_values' => ['name' => 'Prasat Preah Vihear Northern Sanctuary', 'province' => 'Preah Vihear', 'status' => 'Pending'],
                'ip_address' => '119.15.88.201',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/123.0.0.0',
                'created_at' => Carbon::now()->subMinutes(35),
            ],
            [
                'user_id' => $editor?->id,
                'user_name' => $editor?->name ?? 'Sophal Sopheaktra',
                'user_role' => 'guide_editor',
                'action' => 'event_created',
                'entity_type' => 'Event',
                'entity_id' => 8,
                'description' => 'Published regional cultural celebration "Siem Reap Giant Puppet Festival 2026"',
                'old_values' => null,
                'new_values' => ['title' => 'Siem Reap Giant Puppet Festival 2026', 'start_date' => '2026-11-20'],
                'ip_address' => '119.15.88.201',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/123.0.0.0',
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'user_id' => $editor?->id,
                'user_name' => $editor?->name ?? 'Sophal Sopheaktra',
                'user_role' => 'guide_editor',
                'action' => 'gallery_uploaded',
                'entity_type' => 'GalleryMedia',
                'entity_id' => 24,
                'description' => 'Uploaded 6 high-resolution aerial photographs for Bayon Temple East Wing',
                'old_values' => null,
                'new_values' => ['images_count' => 6, 'album' => 'Angkor Thom Faces'],
                'ip_address' => '119.15.88.201',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/123.0.0.0',
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'user_id' => $editor?->id,
                'user_name' => $editor?->name ?? 'Sophal Sopheaktra',
                'user_role' => 'guide_editor',
                'action' => 'guide_updated',
                'entity_type' => 'Guide',
                'entity_id' => 2,
                'description' => 'Updated accredited languages and certification credentials for Tour Guide #2',
                'old_values' => ['languages' => ['Khmer', 'English']],
                'new_values' => ['languages' => ['Khmer', 'English', 'French', 'Japanese']],
                'ip_address' => '119.15.88.201',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/123.0.0.0',
                'created_at' => Carbon::now()->subDays(1),
            ],

            // Business Owner actions
            [
                'user_id' => $owner?->id,
                'user_name' => $owner?->name ?? 'Sokha Chanthou',
                'user_role' => 'business_owner',
                'action' => 'business_profile_updated',
                'entity_type' => 'Business',
                'entity_id' => 1,
                'description' => 'Updated operating schedule and dining capacity for "Angkor Heritage Restaurant"',
                'old_values' => ['capacity' => 60],
                'new_values' => ['capacity' => 90, 'outdoor_seating' => true],
                'ip_address' => '96.9.72.110',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15',
                'created_at' => Carbon::now()->subMinutes(50),
            ],
            [
                'user_id' => $owner?->id,
                'user_name' => $owner?->name ?? 'Sokha Chanthou',
                'user_role' => 'business_owner',
                'action' => 'booking_confirmed',
                'entity_type' => 'Booking',
                'entity_id' => 18,
                'description' => 'Confirmed table reservation for party of 4 on 2026-10-25',
                'old_values' => ['status' => 'pending'],
                'new_values' => ['status' => 'confirmed'],
                'ip_address' => '96.9.72.110',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15',
                'created_at' => Carbon::now()->subHours(4),
            ],

            // Tourist / User actions
            [
                'user_id' => $tourist1?->id,
                'user_name' => $tourist1?->name ?? 'VIT Vong',
                'user_role' => 'user',
                'action' => 'review_created',
                'entity_type' => 'Review',
                'entity_id' => 31,
                'description' => 'Submitted 5-star review: "Mesmerizing sunrise experience at Angkor Wat!"',
                'old_values' => null,
                'new_values' => ['rating' => 5, 'place_id' => 1],
                'ip_address' => '45.115.208.14',
                'user_agent' => 'Mozilla/5.0 (Android 14; Mobile; rv:124.0) Gecko/124.0 Firefox/124.0',
                'created_at' => Carbon::now()->subMinutes(18),
            ],
            [
                'user_id' => $tourist1?->id,
                'user_name' => $tourist1?->name ?? 'VIT Vong',
                'user_role' => 'user',
                'action' => 'trip_created',
                'entity_type' => 'Trip',
                'entity_id' => 5,
                'description' => 'Created custom 3-day travel itinerary "Siem Reap Temple Discovery"',
                'old_values' => null,
                'new_values' => ['days' => 3, 'destination' => 'Siem Reap'],
                'ip_address' => '45.115.208.14',
                'user_agent' => 'Mozilla/5.0 (Android 14; Mobile; rv:124.0) Gecko/124.0 Firefox/124.0',
                'created_at' => Carbon::now()->subHours(7),
            ],
            [
                'user_id' => $tourist2?->id,
                'user_name' => $tourist2?->name ?? 'Ou Sreylin',
                'user_role' => 'user',
                'action' => 'favorite_added',
                'entity_type' => 'Favorite',
                'entity_id' => 42,
                'description' => 'Saved "Koh Rong Sanloem Secret Cove" to personal bucketlist',
                'old_values' => null,
                'new_values' => ['place_id' => 7],
                'ip_address' => '110.74.195.8',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15',
                'created_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'user_id' => $tourist2?->id,
                'user_name' => $tourist2?->name ?? 'Ou Sreylin',
                'user_role' => 'user',
                'action' => 'user_login',
                'entity_type' => 'User',
                'entity_id' => $tourist2?->id,
                'description' => 'Logged into tourism-travel web portal via email authentication',
                'old_values' => null,
                'new_values' => null,
                'ip_address' => '110.74.195.8',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15',
                'created_at' => Carbon::now()->subMinutes(25),
            ],

            // Super Admin actions
            [
                'user_id' => $superAdmin?->id,
                'user_name' => $superAdmin?->name ?? 'Ton Bunheng',
                'user_role' => 'super_admin',
                'action' => 'deletion_request_approved',
                'entity_type' => 'DeletionRequest',
                'entity_id' => 2,
                'description' => 'Approved staff deletion ticket and executed permanent item cleanup',
                'old_values' => ['status' => 'pending'],
                'new_values' => ['status' => 'approved'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/124.0.0.0',
                'created_at' => Carbon::now()->subHours(8),
            ],
            [
                'user_id' => $superAdmin?->id,
                'user_name' => $superAdmin?->name ?? 'Ton Bunheng',
                'user_role' => 'super_admin',
                'action' => 'system_settings_updated',
                'entity_type' => 'SystemSetting',
                'entity_id' => 1,
                'description' => 'Updated platform maintenance contact email & operational parameters',
                'old_values' => ['contact' => 'info@tourism.gov.kh'],
                'new_values' => ['contact' => 'admin@tourism.gov.kh'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/124.0.0.0',
                'created_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($sampleLogs as $logData) {
            AuditLog::create($logData);
        }
    }
}
