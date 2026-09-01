<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessHour;
use App\Models\BusinessImage;
use App\Models\BusinessPromotion;
use App\Models\BusinessService;
use App\Models\Category;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacAndBusinessSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure all 5 RBAC Users Exist
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
                'bio' => 'Lead Administrator for AngkorVerses Information System.',
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
                'bio' => 'Tourism board operations manager.',
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
                'bio' => 'Certified Khmer Culture & Temple Tour Guide.',
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
                'bio' => 'Owner of Angkor Heritage Dining & Boutique Retreat.',
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
                'bio' => 'Avid traveler and photography enthusiast exploring Southeast Asia.',
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
                'bio' => 'Local heritage preserver and eco-tourism advocate.',
            ]
        );

        $pSiemReap = Province::where('name', 'Siem Reap')->first();
        $pPhnomPenh = Province::where('name', 'Phnom Penh')->first();
        $cDining = Category::where('name', 'Dining')->first() ?? Category::first();
        $cAdventure = Category::where('name', 'Adventure')->first() ?? Category::first();

        // 2. Businesses
        $b1 = Business::updateOrCreate(
            ['slug' => 'angkor-heritage-restaurant-lounge'],
            [
                'owner_id' => $businessOwner->id,
                'name' => 'Angkor Heritage Restaurant & Lounge',
                'description' => 'Authentic royal Khmer culinary experience in the heart of Siem Reap with traditional Apsara dance performances.',
                'category_id' => $cDining?->id,
                'province_id' => $pSiemReap?->id,
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

        // Images
        BusinessImage::firstOrCreate(
            ['business_id' => $b1->id, 'image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80'],
            ['caption' => 'Main Dining Hall & Courtyard', 'is_cover' => true, 'display_order' => 1]
        );

        // Services
        BusinessService::firstOrCreate(
            ['business_id' => $b1->id, 'name' => 'Royal Khmer 5-Course Dinner Set'],
            [
                'description' => 'Chef degustation menu featuring Fish Amok, Lok Lak, and Lemongrass Soup.',
                'price' => 35.00,
                'currency' => 'USD',
                'duration_minutes' => 90,
                'is_available' => true,
            ]
        );

        // Hours
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            BusinessHour::firstOrCreate(
                ['business_id' => $b1->id, 'day_of_week' => $day],
                ['open_time' => '10:00:00', 'close_time' => '22:30:00', 'is_closed' => false]
            );
        }

        // Promotions
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

        // Second business
        Business::updateOrCreate(
            ['slug' => 'mekong-river-sunset-cruise-kayaking'],
            [
                'owner_id' => $businessOwner->id,
                'name' => 'Mekong River Sunset Cruise & Kayaking',
                'description' => 'Eco-friendly scenic river cruises and guided kayaking tours along the Mekong River.',
                'category_id' => $cAdventure?->id,
                'province_id' => $pPhnomPenh?->id,
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
    }
}
