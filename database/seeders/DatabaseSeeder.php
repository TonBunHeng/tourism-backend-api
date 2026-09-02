<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessHour;
use App\Models\BusinessImage;
use App\Models\BusinessPromotion;
use App\Models\BusinessService;
use App\Models\Category;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\DeletionRequest;
use App\Models\DeletionRequestItem;
use App\Models\Event;
use App\Models\EventTag;
use App\Models\Favorite;
use App\Models\GalleryComment;
use App\Models\GalleryLike;
use App\Models\GalleryMedia;
use App\Models\GalleryMediaTag;
use App\Models\LoginAttempt;
use App\Models\Notification;
use App\Models\Place;
use App\Models\Province;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewReply;
use App\Models\SecurityAlert;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------------------------
        // 1. USERS (5 Core Roles)
        // ----------------------------------------------------------------------
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@tourism.gov.kh'],
            [
                'name' => 'Ton Bunheng',
                'phone' => '+855 12 345 678',
                'password_hash' => Hash::make('password123'),
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => 'Active',
                'location' => 'Phnom Penh',
                'verified' => true,
                'two_factor_auth' => true,
                'subscription' => 'Premium',
                'activity_level' => 'High',
                'bio' => 'Lead Administrator & Technical Architect for AngkorVerses Tourism System.',
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'staff.admin@tourism.gov.kh'],
            [
                'name' => 'Kosal Visal',
                'phone' => '+855 23 888 123',
                'password_hash' => Hash::make('password123'),
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=300&q=80',
                'role' => User::ROLE_ADMIN,
                'status' => 'Active',
                'location' => 'Phnom Penh',
                'verified' => true,
                'two_factor_auth' => true,
                'subscription' => 'Premium',
                'activity_level' => 'High',
                'bio' => 'National Tourism Board operations and business verification manager.',
            ]
        );

        $guide = User::updateOrCreate(
            ['email' => 'sopheaktra@tourism.gov.kh'],
            [
                'name' => 'Sophal Sopheaktra',
                'phone' => '+855 92 888 999',
                'password_hash' => Hash::make('password123'),
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
                'role' => User::ROLE_GUIDE_EDITOR,
                'status' => 'Active',
                'location' => 'Siem Reap',
                'verified' => true,
                'subscription' => 'Basic',
                'activity_level' => 'High',
                'bio' => 'Senior Khmer Culture & UNESCO World Heritage Certified Tour Guide.',
            ]
        );

        $businessOwner = User::updateOrCreate(
            ['email' => 'owner@angkor-restaurant.com'],
            [
                'name' => 'Sokha Chanthou',
                'phone' => '+855 63 963 888',
                'password_hash' => Hash::make('password123'),
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80',
                'role' => User::ROLE_BUSINESS_OWNER,
                'status' => 'Active',
                'location' => 'Siem Reap',
                'verified' => true,
                'subscription' => 'Premium',
                'activity_level' => 'High',
                'bio' => 'Owner of Angkor Heritage Dining, Buger Cafe & Mekong Eco Cruises.',
            ]
        );

        $user1 = User::updateOrCreate(
            ['email' => 'vit.vong@example.com'],
            [
                'name' => 'VIT Vong',
                'phone' => '+1 555 123 4567',
                'password_hash' => Hash::make('password123'),
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80',
                'role' => User::ROLE_USER,
                'status' => 'Active',
                'location' => 'United States',
                'verified' => true,
                'subscription' => 'Free',
                'activity_level' => 'Medium',
                'bio' => 'Avid travel writer and heritage photographer exploring Southeast Asia.',
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'ou.sreylin@example.com'],
            [
                'name' => 'Ou Sreylin',
                'phone' => '+855 10 999 000',
                'password_hash' => Hash::make('password123'),
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=300&q=80',
                'role' => User::ROLE_USER,
                'status' => 'Active',
                'location' => 'Battambang',
                'verified' => true,
                'subscription' => 'Basic',
                'activity_level' => 'High',
                'bio' => 'Local heritage preservationist and eco-tourism enthusiast.',
            ]
        );

        // ----------------------------------------------------------------------
        // 2. PROVINCES (All 25 Provinces / Capital of Cambodia)
        // ----------------------------------------------------------------------
        $this->call(ProvinceSeeder::class);

        $pSiemReap   = Province::where('name', 'Siem Reap')->first();
        $pPhnomPenh  = Province::where('name', 'Phnom Penh')->first();
        $pKampot     = Province::where('name', 'Kampot')->first();
        $pSihanouk   = Province::where('name', 'Preah Sihanouk')->first();
        $pBattambang = Province::where('name', 'Battambang')->first();
        $pKep        = Province::where('name', 'Kep')->first();


        // ----------------------------------------------------------------------
        // 3. CATEGORIES
        // ----------------------------------------------------------------------
        $cTemple = Category::updateOrCreate(
            ['name' => 'Temple'],
            ['description' => 'Ancient religious monuments, pagodas, and UNESCO World Heritage sites.', 'color' => '#8B5CF6', 'status' => 'Active']
        );

        $cHistory = Category::updateOrCreate(
            ['name' => 'Historical Site'],
            ['description' => 'Historic monuments, archaeological ruins, and national cultural legacy sites.', 'color' => '#F59E0B', 'status' => 'Active']
        );

        $cPalace = Category::updateOrCreate(
            ['name' => 'Palace'],
            ['description' => 'Royal palaces, throne halls, and official state residences.', 'color' => '#EF4444', 'status' => 'Active']
        );

        $cNature = Category::updateOrCreate(
            ['name' => 'Nature'],
            ['description' => 'National parks, waterfalls, mountain ranges, and wildlife sanctuaries.', 'color' => '#10B981', 'status' => 'Active']
        );

        $cMuseum = Category::updateOrCreate(
            ['name' => 'Museum'],
            ['description' => 'Galleries, artifact museums, and historical archives.', 'color' => '#3B82F6', 'status' => 'Active']
        );

        $cDining = Category::updateOrCreate(
            ['name' => 'Dining'],
            ['description' => 'Authentic Khmer restaurants, fine dining, street food, and cafes.', 'color' => '#EC4899', 'status' => 'Active']
        );

        $cResort = Category::updateOrCreate(
            ['name' => 'Resort & Hotel'],
            ['description' => 'Boutique hotels, eco-resorts, luxury stays, and heritage lodges.', 'color' => '#14B8A6', 'status' => 'Active']
        );

        $cAdventure = Category::updateOrCreate(
            ['name' => 'Adventure & Tour'],
            ['description' => 'Guided eco-tours, river cruises, kayaking, and outdoor expeditions.', 'color' => '#6366F1', 'status' => 'Active']
        );

        // ----------------------------------------------------------------------
        // 4. PLACES (All 25 Provinces Seeded)
        // ----------------------------------------------------------------------
        $this->call(PlaceSeeder::class);

        $place1 = Place::where('name', 'Angkor Wat')->first();
        $place2 = Place::where('name', 'Bayon Temple')->first();
        $place3 = Place::where('name', 'Royal Palace & Silver Pagoda')->first();
        $place4 = Place::where('name', 'Bokor National Park')->first();
        $place5 = Place::where('name', 'Koh Rong Island Beaches')->first();


        // ----------------------------------------------------------------------
        // 5. BUSINESSES (Owned by Business Owner & Managed by Admin)
        // ----------------------------------------------------------------------
        $b1 = Business::updateOrCreate(
            ['slug' => 'angkor-heritage-restaurant-lounge'],
            [
                'owner_id' => $businessOwner->id,
                'name' => 'Angkor Heritage Restaurant & Lounge',
                'description' => 'Authentic royal Khmer culinary experience in the heart of Siem Reap with traditional live Apsara dance performances.',
                'category_id' => $cDining->id,
                'province_id' => $pSiemReap->id,
                'address' => 'Street 08, Old Market Area, Krong Siem Reap',
                'latitude' => 13.3532000,
                'longitude' => 103.8561000,
                'phone' => '+855 63 963 888',
                'email' => 'info@angkor-restaurant.com',
                'website' => 'https://angkor-restaurant.com',
                'price_range' => '$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(1),
                'verified_by' => $superAdmin->id,
                'rating' => 4.85,
                'review_count' => 12,
            ]
        );

        BusinessImage::firstOrCreate(
            ['business_id' => $b1->id, 'image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80'],
            ['caption' => 'Main Dining Hall & Courtyard', 'is_cover' => true, 'display_order' => 1]
        );

        BusinessService::firstOrCreate(
            ['business_id' => $b1->id, 'name' => 'Royal Khmer 5-Course Dinner Set'],
            [
                'description' => 'Chef degustation menu featuring Fish Amok, Beef Lok Lak, and Fresh Mango Sticky Rice.',
                'price' => 35.00,
                'currency' => 'USD',
                'duration_minutes' => 90,
                'is_available' => true,
            ]
        );

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            BusinessHour::firstOrCreate(
                ['business_id' => $b1->id, 'day_of_week' => $day],
                ['open_time' => '10:00:00', 'close_time' => '22:30:00', 'is_closed' => false]
            );
        }

        BusinessPromotion::firstOrCreate(
            ['business_id' => $b1->id, 'promo_code' => 'EARLYBIRD20'],
            [
                'title' => 'Early Bird 20% Dinner Discount',
                'description' => 'Enjoy 20% off all food items when dining between 5:00 PM and 6:30 PM.',
                'discount_percentage' => 20.00,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addMonths(3),
                'is_active' => true,
                'banner_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80',
            ]
        );

        // Pending Business 1
        $b2 = Business::updateOrCreate(
            ['slug' => 'buger'],
            [
                'owner_id' => $businessOwner->id,
                'name' => 'Buger',
                'description' => 'Gourmet organic burgers, craft shakes, and local Khmer fusion snacks near Pub Street.',
                'category_id' => $cDining->id,
                'province_id' => $pSiemReap->id,
                'address' => 'Street 09, Pub Street Area, Siem Reap',
                'latitude' => 13.3541000,
                'longitude' => 103.8550000,
                'phone' => '+855 63 111 222',
                'email' => 'tonbunheng1122@gmail.com',
                'website' => 'https://bugercafe.com',
                'price_range' => '$$',
                'status' => 'active',
                'verification_status' => 'pending',
                'rating' => 0.00,
                'review_count' => 0,
            ]
        );

        // Pending Business 2
        $b3 = Business::updateOrCreate(
            ['slug' => 'mekong-river-sunset-cruise-kayaking'],
            [
                'owner_id' => $businessOwner->id,
                'name' => 'Mekong River Sunset Cruise & Kayaking',
                'description' => 'Eco-friendly scenic river cruises and guided sunset kayaking tours along the Mekong River.',
                'category_id' => $cAdventure->id,
                'province_id' => $pPhnomPenh->id,
                'address' => 'Sisowath Quay, Phnom Penh Riverfront',
                'latitude' => 11.5682000,
                'longitude' => 104.9312000,
                'phone' => '+855 23 777 999',
                'email' => 'booking@mekong-cruise.com',
                'website' => 'https://mekong-cruise.com',
                'price_range' => '$$',
                'status' => 'active',
                'verification_status' => 'pending',
                'rating' => 0.00,
                'review_count' => 0,
            ]
        );

        // Approved Business 3
        $b4 = Business::updateOrCreate(
            ['slug' => 'kampot-pepper-plantation-eco-lodge'],
            [
                'owner_id' => $businessOwner->id,
                'name' => 'Kampot Pepper Plantation & Eco Lodge',
                'description' => 'Organic pepper farm tours, traditional farm-to-table dining, and bamboo bungalows overlooking Bokor Mountain.',
                'category_id' => $cResort->id,
                'province_id' => $pKampot->id,
                'address' => 'Phnom Voar, Kampot Province',
                'latitude' => 10.6120000,
                'longitude' => 104.2800000,
                'phone' => '+855 33 555 888',
                'email' => 'contact@kampot-pepperlodge.com',
                'website' => 'https://kampot-pepperlodge.com',
                'price_range' => '$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subWeeks(2),
                'verified_by' => $admin->id,
                'rating' => 4.90,
                'review_count' => 18,
            ]
        );

        // ----------------------------------------------------------------------
        // 6. EVENTS
        // ----------------------------------------------------------------------
        $event1 = Event::updateOrCreate(
            ['title' => 'Bon Om Touk (Water Festival)'],
            [
                'category' => 'Festival',
                'description' => 'Celebrates the reversing flow of the Tonle Sap River with grand dragon boat racing, moon worship, and fireworks.',
                'location' => 'Sisowath Quay, Phnom Penh',
                'place_id' => $place3->id,
                'province_id' => $pPhnomPenh->id,
                'business_id' => $b3->id,
                'start_date' => '2026-11-15',
                'end_date' => '2026-11-17',
                'start_time' => '08:00 AM',
                'attendees_count' => 1500000,
                'price' => 'Free',
                'organizer' => 'Ministry of Tourism & National Committee',
                'featured' => true,
                'rating' => 4.95,
                'image_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=800&q=80',
                'status' => 'Upcoming',
            ]
        );
        EventTag::firstOrCreate(['event_id' => $event1->id, 'tag_name' => 'Boat Race']);
        EventTag::firstOrCreate(['event_id' => $event1->id, 'tag_name' => 'Culture']);

        $event2 = Event::updateOrCreate(
            ['title' => 'Angkor Wat International Half Marathon'],
            [
                'category' => 'Sports',
                'description' => 'Annual international charity marathon running through the ancient temple complex of Angkor Wat.',
                'location' => 'Angkor Wat Main Entrance, Siem Reap',
                'place_id' => $place1->id,
                'province_id' => $pSiemReap->id,
                'start_date' => '2026-12-06',
                'end_date' => '2026-12-06',
                'start_time' => '05:30 AM',
                'attendees_count' => 12000,
                'price' => '$60 USD',
                'organizer' => 'Cambodia Events Committee & Olympic Committee',
                'featured' => true,
                'rating' => 4.85,
                'image_url' => 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?auto=format&fit=crop&w=800&q=80',
                'status' => 'Scheduled',
            ]
        );
        EventTag::firstOrCreate(['event_id' => $event2->id, 'tag_name' => 'Marathon']);

        // ----------------------------------------------------------------------
        // 7. REVIEWS & REPLIES
        // ----------------------------------------------------------------------
        $review1 = Review::updateOrCreate(
            ['user_id' => $user1->id, 'place_id' => $place1->id],
            [
                'rating' => 5,
                'title' => 'Breathtaking Sunrise Experience!',
                'comment' => 'Watching the sun rise behind the five lotus towers of Angkor Wat is truly a once-in-a-lifetime memory.',
                'likes_count' => 42,
                'dislikes_count' => 1,
                'is_verified' => true,
                'status' => 'Approved',
            ]
        );

        ReviewImage::firstOrCreate([
            'review_id' => $review1->id,
            'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80'
        ]);

        ReviewReply::firstOrCreate([
            'review_id' => $review1->id,
            'user_id' => $guide->id,
            'comment' => 'Thank you VIT Vong! We are delighted that you enjoyed your morning at Angkor Wat.',
        ]);

        $review2 = Review::updateOrCreate(
            ['user_id' => $user2->id, 'business_id' => $b1->id],
            [
                'rating' => 5,
                'title' => 'Exquisite Royal Amok & Apsara Show',
                'comment' => 'The Fish Amok served in coconut shell was divine. The live Apsara dance performance added so much magic.',
                'likes_count' => 19,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
            ]
        );

        ReviewReply::firstOrCreate([
            'review_id' => $review2->id,
            'user_id' => $businessOwner->id,
            'comment' => 'Orkun chreun Sreylin! We look forward to welcoming you back again soon.',
        ]);

        // ----------------------------------------------------------------------
        // 8. FAVORITES
        // ----------------------------------------------------------------------
        Favorite::updateOrCreate(
            ['user_id' => $user1->id, 'place_id' => $place1->id],
            ['visited' => true, 'saved_date' => '2026-08-01']
        );
        Favorite::updateOrCreate(
            ['user_id' => $user1->id, 'place_id' => $place3->id],
            ['visited' => false, 'saved_date' => '2026-08-10']
        );

        // ----------------------------------------------------------------------
        // 9. GALLERY & MEDIA
        // ----------------------------------------------------------------------
        $g1 = GalleryMedia::updateOrCreate(
            ['title' => 'Sunset over Angkor Wat Reflection Pond'],
            [
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=1200&q=80',
                'category_id' => $cTemple->id,
                'place_id' => $place1->id,
                'file_size' => '2.4 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $guide->id,
                'views_count' => 1450,
                'likes_count' => 320,
                'status' => 'Published',
            ]
        );
        GalleryMediaTag::firstOrCreate(['media_id' => $g1->id, 'tag_name' => 'Angkor']);
        GalleryMediaTag::firstOrCreate(['media_id' => $g1->id, 'tag_name' => 'Sunset']);

        // ----------------------------------------------------------------------
        // 10. ACHIEVEMENTS
        // ----------------------------------------------------------------------
        UserAchievement::updateOrCreate(
            ['user_id' => $user1->id, 'achievement_name' => 'Heritage Explorer'],
            [
                'description' => 'Visited 5 UNESCO World Heritage sites across Cambodia.',
                'icon' => 'Award',
                'unlocked' => true,
                'unlocked_at' => now()->subDays(5),
            ]
        );

        UserAchievement::updateOrCreate(
            ['user_id' => $user1->id, 'achievement_name' => 'Culture Enthusiast'],
            [
                'description' => 'Attended live traditional Khmer Apsara dance performance.',
                'icon' => 'Sparkles',
                'unlocked' => true,
                'unlocked_at' => now()->subDays(2),
            ]
        );

        // ----------------------------------------------------------------------
        // 11. SYSTEM SETTINGS
        // ----------------------------------------------------------------------
        SystemSetting::updateOrCreate(['setting_key' => 'site_title'], ['setting_value' => 'AngkorVerses Tourism Information System', 'setting_group' => 'general', 'description' => 'Main portal header title']);
        SystemSetting::updateOrCreate(['setting_key' => 'contact_email'], ['setting_value' => 'info@tourism.gov.kh', 'setting_group' => 'contact', 'description' => 'Official support contact address']);
        SystemSetting::updateOrCreate(['setting_key' => 'enable_user_reviews'], ['setting_value' => 'true', 'setting_group' => 'features', 'description' => 'Allow tourist user review submissions']);

        // ----------------------------------------------------------------------
        // 12. NOTIFICATIONS
        // ----------------------------------------------------------------------
        Notification::createNotification([
            'user_id' => $businessOwner->id,
            'type' => 'business_approved',
            'category' => 'Business',
            'title' => 'Business Approved!',
            'description' => "Congratulations! Your business 'Angkor Heritage Restaurant & Lounge' has been verified and approved by the tourism board.",
            'link' => "/business/businesses/{$b1->id}",
            'data' => ['business_id' => $b1->id, 'status' => 'approved'],
            'read' => true,
        ]);
    }
}
