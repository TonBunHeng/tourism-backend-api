<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\DeletionRequest;
use App\Models\DeletionRequestItem;
use App\Models\Event;
use App\Models\EventTag;
use App\Models\Favorite;
use App\Models\GalleryMedia;
use App\Models\GalleryMediaTag;
use App\Models\Place;
use App\Models\Province;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewReply;
use App\Models\LoginAttempt;
use App\Models\Notification;
use App\Models\SecurityAlert;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $admin = User::create([
            'name' => 'Ton Bunheng',
            'email' => 'admin@tourism.gov.kh',
            'phone' => '+855 12 345 678',
            'password_hash' => Hash::make('password123'),
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
            'role' => 'Super Admin',
            'status' => 'Active',
            'location' => 'Phnom Penh',
            'verified' => true,
            'two_factor_auth' => true,
            'subscription' => 'Premium',
            'activity_level' => 'High',
            'bio' => 'Lead Administrator for AngkorVerses Information System.',
        ]);


        $guide = User::create([
            'name' => 'Sophal Sopheaktra',
            'email' => 'sopheaktra@tourism.gov.kh',
            'phone' => '+855 92 888 999',
            'password_hash' => Hash::make('password123'),
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
            'role' => 'Guide / Editor',
            'status' => 'Active',
            'location' => 'Siem Reap',
            'verified' => true,
            'subscription' => 'Basic',
            'activity_level' => 'High',
            'bio' => 'Certified Khmer Culture & Temple Tour Guide.',
        ]);

        $user1 = User::create([
            'name' => 'VIT Vong',
            'email' => 'vit.vong@example.com',
            'phone' => '+1 555 123 4567',
            'password_hash' => Hash::make('password123'),
            'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80',
            'role' => 'User',
            'status' => 'Active',
            'location' => 'United States',
            'verified' => true,
            'subscription' => 'Free',
            'activity_level' => 'Medium',
            'bio' => 'Avid traveler and photography enthusiast exploring Southeast Asia.',
        ]);

        $user2 = User::create([
            'name' => 'Ou Sreylin',
            'email' => 'ou.sreylin@example.com',
            'phone' => '+855 10 999 000',
            'password_hash' => Hash::make('password123'),
            'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=300&q=80',
            'role' => 'User',
            'status' => 'Active',
            'location' => 'Battambang',
            'verified' => true,
            'subscription' => 'Basic',
            'activity_level' => 'High',
            'bio' => 'Local heritage preserver and eco-tourism advocate.',
        ]);

        // 2. Provinces
        $pSiemReap = Province::create([
            'name' => 'Siem Reap',
            'type' => 'Province',
            'population' => '1,014,234',
            'area' => '10,299 km²',
            'districts_count' => 12,
            'communes_count' => 100,
            'status' => 'Active',
            'icon' => 'Landmark',
            'description' => 'Home to the magnificent Angkor Wat and ancient Khmer Empire monuments.',
            'rating' => 4.90,
        ]);

        $pPhnomPenh = Province::create([
            'name' => 'Phnom Penh',
            'type' => 'Capital City',
            'population' => '2,281,951',
            'area' => '679 km²',
            'districts_count' => 14,
            'communes_count' => 105,
            'status' => 'Active',
            'icon' => 'Building',
            'description' => 'The vibrant capital city of Cambodia located at the confluence of three rivers.',
            'rating' => 4.75,
        ]);

        $pKampot = Province::create([
            'name' => 'Kampot',
            'type' => 'Province',
            'population' => '627,884',
            'area' => '4,873 km²',
            'districts_count' => 8,
            'communes_count' => 93,
            'status' => 'Active',
            'icon' => 'Mountain',
            'description' => 'Famous for world-renowned Kampot pepper, colonial architecture, and Bokor Mountain.',
            'rating' => 4.65,
        ]);

        $pSihanouk = Province::create([
            'name' => 'Preah Sihanouk',
            'type' => 'Province',
            'population' => '300,000',
            'area' => '2,536 km²',
            'districts_count' => 5,
            'communes_count' => 29,
            'status' => 'Active',
            'icon' => 'Waves',
            'description' => 'Cambodias premier coastal gateway featuring tropical islands and beaches.',
            'rating' => 4.55,
        ]);

        // 3. Categories
        $cTemple = Category::create([
            'name' => 'Temple',
            'description' => 'Ancient religious monuments, pagodas, and UNESCO World Heritage sites.',
            'color' => '#8B5CF6',
            'status' => 'Active',
        ]);

        $cHistory = Category::create([
            'name' => 'Historical Site',
            'description' => 'Historic monuments, archaeological ruins, and cultural legacy sites.',
            'color' => '#F59E0B',
            'status' => 'Active',
        ]);

        $cPalace = Category::create([
            'name' => 'Palace',
            'description' => 'Royal palaces, throne halls, and official state residences.',
            'color' => '#EF4444',
            'status' => 'Active',
        ]);

        $cNature = Category::create([
            'name' => 'Nature',
            'description' => 'National parks, waterfalls, mountains, and wildlife reserves.',
            'color' => '#10B981',
            'status' => 'Active',
        ]);

        $cMuseum = Category::create([
            'name' => 'Museum',
            'description' => 'Galleries, artifact museums, and historical archives.',
            'color' => '#3B82F6',
            'status' => 'Active',
        ]);

        // 4. Places
        $place1 = Place::create([
            'name' => 'Angkor Wat',
            'category_id' => $cTemple->id,
            'province_id' => $pSiemReap->id,
            'address' => 'Angkor Archaeological Park, Siem Reap',
            'coordinates' => '13.4125° N, 103.8670° E',
            'latitude' => 13.41250000,
            'longitude' => 103.86700000,
            'description' => 'The largest religious structure in the world and UNESCO World Heritage Site.',
            'best_time' => 'Sunrise (5:30 AM - 7:00 AM)',
            'duration' => '3 - 4 Hours',
            'price' => '$37 USD',
            'rating' => 4.95,
            'reviews_count' => 1250,
            'visitors_count' => 2500000,
            'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80',
            'is_featured' => true,
            'status' => 'Active',
        ]);

        $place2 = Place::create([
            'name' => 'Bayon Temple',
            'category_id' => $cTemple->id,
            'province_id' => $pSiemReap->id,
            'address' => 'Angkor Thom, Siem Reap',
            'coordinates' => '13.4413° N, 103.8587° E',
            'latitude' => 13.44130000,
            'longitude' => 103.85870000,
            'description' => 'Famous for its 216 giant smiling stone faces of Avalokiteshvara.',
            'best_time' => 'Late Afternoon (3:30 PM - 5:30 PM)',
            'duration' => '2 Hours',
            'price' => 'Included in Angkor Pass',
            'rating' => 4.85,
            'reviews_count' => 840,
            'visitors_count' => 1800000,
            'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
            'is_featured' => true,
            'status' => 'Active',
        ]);

        $place3 = Place::create([
            'name' => 'Royal Palace & Silver Pagoda',
            'category_id' => $cPalace->id,
            'province_id' => $pPhnomPenh->id,
            'address' => 'Samdach Sothearos Blvd, Phnom Penh',
            'coordinates' => '11.5625° N, 104.9312° E',
            'latitude' => 11.56250000,
            'longitude' => 104.93120000,
            'description' => 'Official residence of His Majesty King Norodom Sihamoni, housing thousands of silver floor tiles.',
            'best_time' => 'Morning (8:00 AM - 10:30 AM)',
            'duration' => '2 Hours',
            'price' => '$10 USD',
            'rating' => 4.70,
            'reviews_count' => 620,
            'visitors_count' => 950000,
            'image_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
            'is_featured' => true,
            'status' => 'Active',
        ]);

        $place4 = Place::create([
            'name' => 'Bokor National Park',
            'category_id' => $cNature->id,
            'province_id' => $pKampot->id,
            'address' => 'Teuk Chhou District, Kampot',
            'coordinates' => '10.6254° N, 104.0258° E',
            'latitude' => 10.62540000,
            'longitude' => 104.02580000,
            'description' => 'Cool highland climate national park featuring historic French colonial station and waterfalls.',
            'best_time' => 'All Day (Cool Foggy Mornings)',
            'duration' => 'Full Day',
            'price' => 'Free Entry',
            'rating' => 4.60,
            'reviews_count' => 310,
            'visitors_count' => 420000,
            'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
            'is_featured' => false,
            'status' => 'Active',
        ]);

        // 5. Events
        $event1 = Event::create([
            'title' => 'Bon Om Touk (Water Festival)',
            'category' => 'Festival',
            'description' => 'Celebrates the reversing flow of the Tonle Sap River with boat racing.',
            'location' => 'Sisowath Quay, Phnom Penh',
            'place_id' => $place3->id,
            'province_id' => $pPhnomPenh->id,
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-17',
            'start_time' => '08:00 AM',
            'attendees_count' => 1500000,
            'price' => 'Free',
            'organizer' => 'Ministry of Tourism',
            'featured' => true,
            'rating' => 4.90,
            'image_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=800&q=80',
            'status' => 'Upcoming',
        ]);
        EventTag::create(['event_id' => $event1->id, 'tag_name' => 'Boat Race']);
        EventTag::create(['event_id' => $event1->id, 'tag_name' => 'Culture']);

        $event2 = Event::create([
            'title' => 'Angkor Wat International Half Marathon',
            'category' => 'Sports',
            'description' => 'Charity running event inside the ancient Angkor World Heritage Site.',
            'location' => 'Angkor Wat Main Entrance, Siem Reap',
            'place_id' => $place1->id,
            'province_id' => $pSiemReap->id,
            'start_date' => '2026-12-06',
            'end_date' => '2026-12-06',
            'start_time' => '05:30 AM',
            'attendees_count' => 12000,
            'price' => '$60 USD',
            'organizer' => 'Cambodia Events Committee',
            'featured' => true,
            'rating' => 4.80,
            'image_url' => 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?auto=format&fit=crop&w=800&q=80',
            'status' => 'Scheduled',
        ]);
        EventTag::create(['event_id' => $event2->id, 'tag_name' => 'Marathon']);

        // 6. Reviews & Replies
        $review1 = Review::create([
            'user_id' => $user1->id,
            'place_id' => $place1->id,
            'rating' => 5,
            'title' => 'Breathtaking Sunrise Experience!',
            'comment' => 'Watching the sun rise behind the five towers of Angkor Wat is truly a once-in-a-lifetime experience.',
            'likes_count' => 42,
            'dislikes_count' => 1,
            'is_verified' => true,
            'status' => 'Approved',
        ]);
        ReviewImage::create(['review_id' => $review1->id, 'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80']);
        ReviewReply::create([
            'review_id' => $review1->id,
            'user_id' => $guide->id,
            'comment' => 'Thank you John! We are delighted that you enjoyed your tour at Angkor Wat.',
        ]);

        // 7. Favorites
        Favorite::create([
            'user_id' => $user1->id,
            'place_id' => $place1->id,
            'visited' => true,
            'saved_date' => '2026-08-01',
        ]);
        Favorite::create([
            'user_id' => $user1->id,
            'place_id' => $place3->id,
            'visited' => false,
            'saved_date' => '2026-08-10',
        ]);

        // 8. Gallery
        $g1 = GalleryMedia::create([
            'title' => 'Sunset over Angkor Wat Reflection Pond',
            'type' => 'image',
            'url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=1200&q=80',
            'category_id' => $cTemple->id,
            'place_id' => $place1->id,
            'file_size' => '2.4 MB',
            'dimensions' => '3840x2160',
            'uploaded_by_user_id' => $admin->id,
            'views_count' => 1450,
            'likes_count' => 320,
            'status' => 'Published',
        ]);
        GalleryMediaTag::create(['media_id' => $g1->id, 'tag_name' => 'Angkor']);
        GalleryMediaTag::create(['media_id' => $g1->id, 'tag_name' => 'Sunset']);

        // 9. Chats & Messages
        $chat1 = Chat::create([
            'user_id' => $user1->id,
            'category' => 'Travel Planning',
            'priority' => 'high',
            'status' => 'active',
            'unread_count' => 1,
            'last_message' => 'Can you recommend a certified tour guide for Angkor Thom?',
            'last_message_time' => '10:30 AM',
        ]);
        ChatMessage::create([
            'chat_id' => $chat1->id,
            'sender_type' => 'user',
            'sender_user_id' => $user1->id,
            'message_text' => 'Hi! Can you recommend a certified tour guide for Angkor Thom?',
            'is_read' => true,
        ]);
        ChatMessage::create([
            'chat_id' => $chat1->id,
            'sender_type' => 'admin',
            'sender_user_id' => $admin->id,
            'message_text' => 'Hello John! Yes, our official guide Sokha Chan is available for booking.',
            'is_read' => false,
        ]);

        // 10. Deletion Requests
        $del = DeletionRequest::create([
            'user_id' => $user2->id,
            'request_type' => 'item',
            'reason' => 'Duplicate photo upload',
            'additional_info' => 'Please remove second draft photo in gallery.',
            'status' => 'pending',
            'urgency' => 'low',
        ]);
        DeletionRequestItem::create([
            'deletion_request_id' => $del->id,
            'item_type' => 'Gallery Item',
            'item_id' => $g1->id,
            'item_name' => 'Draft Sunset Photo',
            'category' => 'Gallery',
            'date_added' => '2026-08-12',
        ]);

        // 11. Achievements
        UserAchievement::create([
            'user_id' => $user1->id,
            'achievement_name' => 'Heritage Explorer',
            'description' => 'Visited 5 UNESCO World Heritage sites.',
            'icon' => 'Award',
            'unlocked' => true,
            'unlocked_at' => now(),
        ]);

        // 12. System Settings
        SystemSetting::create(['setting_key' => 'site_title', 'setting_value' => 'AngkorVerses Information System', 'setting_group' => 'general', 'description' => 'Main portal header title']);
        SystemSetting::create(['setting_key' => 'contact_email', 'setting_value' => 'info@tourism.gov.kh', 'setting_group' => 'contact', 'description' => 'Official support contact address']);
        SystemSetting::create(['setting_key' => 'enable_user_reviews', 'setting_value' => 'true', 'setting_group' => 'features', 'description' => 'Allow tourist user review submissions']);

        // 13. Security Alerts & Audit Records
        SecurityAlert::firstOrCreate(
            ['email' => 'attacker_bot@185.220.101.5', 'ip_address' => '185.220.101.5'],
            [
                'type' => 'failed_login_threshold',
                'email' => 'attacker_bot@185.220.101.5',
                'ip_address' => '185.220.101.5',
                'attempts' => 8,
                'message' => 'Multiple failed admin login attempts (8) detected for account attacker_bot@185.220.101.5 from IP 185.220.101.5.',
                'is_read' => false,
                'data' => [
                    'email' => 'attacker_bot@185.220.101.5',
                    'ip_address' => '185.220.101.5',
                    'attempts' => 8,
                    'user_agent' => 'Mozilla/5.0 (Hydra/v9.5 Brute-Force Bot)',
                    'attempted_at' => now()->subMinutes(15)->toIso8601String(),
                ],
                'created_at' => now()->subMinutes(15),
            ]
        );

        SecurityAlert::firstOrCreate(
            ['email' => 'admin@tourism.gov.kh', 'ip_address' => '103.216.58.12'],
            [
                'type' => 'failed_login_threshold',
                'email' => 'admin@tourism.gov.kh',
                'ip_address' => '103.216.58.12',
                'attempts' => 6,
                'message' => 'Multiple failed admin login attempts (6) detected for account admin@tourism.gov.kh from IP 103.216.58.12.',
                'is_read' => false,
                'data' => [
                    'email' => 'admin@tourism.gov.kh',
                    'ip_address' => '103.216.58.12',
                    'attempts' => 6,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'attempted_at' => now()->subHours(2)->toIso8601String(),
                ],
                'created_at' => now()->subHours(2),
            ]
        );

        SecurityAlert::firstOrCreate(
            ['email' => 'unknown_probe@45.154.255.88', 'ip_address' => '45.154.255.88'],
            [
                'type' => 'failed_login_threshold',
                'email' => 'unknown_probe@45.154.255.88',
                'ip_address' => '45.154.255.88',
                'attempts' => 6,
                'message' => 'Multiple failed admin login attempts (6) detected for account unknown_probe@45.154.255.88 from IP 45.154.255.88.',
                'is_read' => true,
                'data' => [
                    'email' => 'unknown_probe@45.154.255.88',
                    'ip_address' => '45.154.255.88',
                    'attempts' => 6,
                    'user_agent' => 'Python-urllib/3.10',
                    'attempted_at' => now()->subDay()->toIso8601String(),
                ],
                'created_at' => now()->subDay(),
            ]
        );

        LoginAttempt::firstOrCreate(
            ['email' => 'attacker_bot@185.220.101.5', 'ip_address' => '185.220.101.5'],
            [
                'user_agent' => 'Mozilla/5.0 (Hydra/v9.5 Brute-Force Bot)',
                'success' => false,
                'failure_reason' => 'User not found',
                'attempted_at' => now()->subMinutes(15),
            ]
        );

        LoginAttempt::firstOrCreate(
            ['email' => 'admin@tourism.gov.kh', 'ip_address' => '103.216.58.12'],
            [
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'success' => false,
                'failure_reason' => 'Invalid password',
                'attempted_at' => now()->subHours(2),
            ]
        );

        LoginAttempt::firstOrCreate(
            ['email' => 'admin@tourism.gov.kh', 'ip_address' => '127.0.0.1'],
            [
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'success' => true,
                'attempted_at' => now()->subMinutes(5),
            ]
        );
    }
}
