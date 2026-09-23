<?php

namespace Database\Seeders;

use App\Models\DeletionRequest;
use App\Models\DeletionRequestItem;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ----------------------------------------------------------------------
        // 1. Core Geographical & Categorical Foundations
        // ----------------------------------------------------------------------
        $this->call(ProvinceSeeder::class);
        $this->call(CategorySeeder::class);

        // ----------------------------------------------------------------------
        // 2. User Accounts Across All 5 Roles
        // ----------------------------------------------------------------------
        $this->call(UserSeeder::class);

        // ----------------------------------------------------------------------
        // 3. Destinations / Places (All 25 Provinces + Iconic Sites)
        // ----------------------------------------------------------------------
        $this->call(PlaceSeeder::class);

        // ----------------------------------------------------------------------
        // 4. Businesses (Dining, Resorts, Spas, Kayaks, Circuses)
        // ----------------------------------------------------------------------
        $this->call(BusinessSeeder::class);

        // ----------------------------------------------------------------------
        // 5. Cultural Events & National Festivals
        // ----------------------------------------------------------------------
        $this->call(EventSeeder::class);

        // ----------------------------------------------------------------------
        // 6. Media Gallery (4K Photos, Aerial Drone Videos, Tags, Comments)
        // ----------------------------------------------------------------------
        $this->call(GallerySeeder::class);

        // ----------------------------------------------------------------------
        // 7. Ratings, Reviews & Official Replies
        // ----------------------------------------------------------------------
        $this->call(ReviewSeeder::class);

        // ----------------------------------------------------------------------
        // 8. Favorite Wishlists
        // ----------------------------------------------------------------------
        $this->call(FavoriteSeeder::class);

        // ----------------------------------------------------------------------
        // 9. Deletion Requests (For Super Admin Moderation Queue)
        // ----------------------------------------------------------------------
        $superAdmin = User::where('email', 'admin@tourism.gov.kh')->first() ?? User::first();
        $userMarcus = User::where('email', 'marcus.becker@example.com')->first();
        $userSarah  = User::where('email', 'sarah.jenkins@example.com')->first();
        $guideDara  = User::where('email', 'dara.guide@tourism.gov.kh')->first();

        if ($userMarcus) {
            $del1 = DeletionRequest::firstOrCreate(
                ['user_id' => $userMarcus->id, 'request_type' => 'account'],
                [
                    'reason' => 'Moving to another region and would like all stored booking history and travel logs permanently erased.',
                    'urgency' => 'high',
                    'status' => 'pending',
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ]
            );
        }

        if ($guideDara) {
            $del2 = DeletionRequest::firstOrCreate(
                ['user_id' => $guideDara->id, 'reason' => 'Duplicate colonial heritage building draft created during system testing.'],
                [
                    'request_type' => 'item',
                    'urgency' => 'medium',
                    'status' => 'pending',
                    'created_at' => now()->subDays(4),
                    'updated_at' => now()->subDays(4),
                ]
            );
            if ($del2) {
                DeletionRequestItem::firstOrCreate(
                    ['deletion_request_id' => $del2->id, 'item_type' => 'place'],
                    ['item_id' => 1, 'item_name' => 'Colonial Heritage Building Draft', 'category' => 'Historical Site', 'date_added' => '2026-08-01']
                );
            }
        }

        if ($userSarah) {
            DeletionRequest::firstOrCreate(
                ['user_id' => $userSarah->id, 'request_type' => 'account'],
                [
                    'reason' => 'Completed volunteering tour and requesting data purge per European GDPR privacy rights.',
                    'urgency' => 'critical',
                    'status' => 'approved',
                    'processed_by_user_id' => $superAdmin?->id,
                    'processed_at' => now()->subDays(10),
                    'admin_notes' => 'Approved and verified by Super Admin Ton Bunheng.',
                    'created_at' => now()->subDays(15),
                    'updated_at' => now()->subDays(10),
                ]
            );
        }

        // ----------------------------------------------------------------------
        // 10. System Settings
        // ----------------------------------------------------------------------
        SystemSetting::updateOrCreate(
            ['setting_key' => 'site_title'],
            ['setting_value' => 'AngkorVerses Tourism Information System', 'setting_group' => 'general', 'description' => 'Main portal header title']
        );
        SystemSetting::updateOrCreate(
            ['setting_key' => 'contact_email'],
            ['setting_value' => 'info@tourism.gov.kh', 'setting_group' => 'contact', 'description' => 'Official national support contact address']
        );
        SystemSetting::updateOrCreate(
            ['setting_key' => 'contact_phone'],
            ['setting_value' => '+855 23 888 777', 'setting_group' => 'contact', 'description' => 'Official tourism hotline hotline']
        );
        SystemSetting::updateOrCreate(
            ['setting_key' => 'enable_user_reviews'],
            ['setting_value' => 'true', 'setting_group' => 'features', 'description' => 'Allow tourist user review submissions']
        );
        SystemSetting::updateOrCreate(
            ['setting_key' => 'enable_ai_travel_assistant'],
            ['setting_value' => 'true', 'setting_group' => 'features', 'description' => 'Enable Gemini-powered AI tourist chat assistant']
        );
        SystemSetting::updateOrCreate(
            ['setting_key' => 'maintenance_mode'],
            ['setting_value' => 'false', 'setting_group' => 'system', 'description' => 'Global maintenance toggle']
        );

        // ----------------------------------------------------------------------
        // 11. Notifications Center
        // ----------------------------------------------------------------------
        $this->call(NotificationSeeder::class);
    }
}
