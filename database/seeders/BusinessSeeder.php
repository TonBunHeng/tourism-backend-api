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

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds for Businesses across Cambodia.
     */
    public function run(): void
    {
        $superAdmin    = User::where('email', 'admin@tourism.gov.kh')->first() ?? User::first();
        $admin         = User::where('email', 'staff.admin@tourism.gov.kh')->first() ?? $superAdmin;
        $ownerSokha    = User::where('email', 'owner@angkor-restaurant.com')->first() ?? $superAdmin;
        $ownerMalis    = User::where('email', 'malis.keo@khamisdining.kh')->first() ?? $ownerSokha;
        $ownerDavid    = User::where('email', 'david.miller@shinta-eco.com')->first() ?? $ownerSokha;
        $ownerLaurent  = User::where('email', 'jp.laurent@boutiquekhmer.com')->first() ?? $ownerSokha;
        $ownerThida    = User::where('email', 'thida.bodia@wellness.kh')->first() ?? $ownerSokha;

        $categories = Category::pluck('id', 'name')->toArray();
        $provinces  = Province::pluck('id', 'name')->toArray();

        $cDining    = $categories['Dining'] ?? 1;
        $cResort    = $categories['Resort & Hotel'] ?? 2;
        $cAdventure = $categories['Adventure & Tour'] ?? 3;
        $cWellness  = $categories['Wellness & Spa'] ?? 4;
        $cArts      = $categories['Arts & Performance'] ?? 5;
        $cCulture   = $categories['Cultural & Heritage'] ?? 6;

        $businesses = [
            // ------------------------------------------------------------------
            // 1. APPROVED BUSINESSES
            // ------------------------------------------------------------------
            [
                'slug' => 'angkor-heritage-restaurant-lounge',
                'name' => 'Angkor Heritage Restaurant & Lounge',
                'owner_id' => $ownerSokha->id,
                'category_id' => $cDining,
                'province_id' => $provinces['Siem Reap'] ?? null,
                'description' => 'Authentic royal Khmer culinary experience in the heart of Siem Reap with traditional live Apsara dance performances and candlelit courtyard dining.',
                'address' => 'Street 08, Old Market Area, Krong Siem Reap',
                'latitude' => 13.3532000,
                'longitude' => 103.8561000,
                'phone' => '+855 63 963 888',
                'email' => 'info@angkor-restaurant.com',
                'website' => 'https://angkor-restaurant.com',
                'price_range' => '$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(2),
                'verified_by' => $superAdmin->id,
                'rating' => 4.88,
                'review_count' => 38,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Courtyard Dining & Lily Pond', 'is_cover' => true],
                    ['url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Royal Fish Amok Degustation', 'is_cover' => false],
                ],
                'services' => [
                    ['name' => 'Royal Khmer 5-Course Dinner Set', 'description' => 'Tasting menu with Royal Fish Amok, Lok Lak, Somlor Machu, and Mango Sticky Rice.', 'price' => 38.00, 'duration_minutes' => 90],
                    ['name' => 'Apsara Cultural Show + Dinner Buffet', 'description' => 'Nightly classical dance show with live pinpeat orchestra.', 'price' => 28.00, 'duration_minutes' => 120],
                ],
                'promotions' => [
                    ['title' => 'Early Bird 20% Dinner Discount', 'promo_code' => 'EARLYBIRD20', 'discount_percentage' => 20.00, 'description' => 'Dine before 6:30 PM and enjoy 20% discount on food.', 'banner_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80'],
                ],
            ],
            [
                'slug' => 'malis-restaurant-phnom-penh',
                'name' => 'Malis Restaurant Phnom Penh',
                'owner_id' => $ownerMalis->id,
                'category_id' => $cDining,
                'province_id' => $provinces['Phnom Penh'] ?? null,
                'description' => 'Pioneering Living Cambodian Cuisine by Master Chef Luu Meng. Renowned for recreating lost royal recipes in a colonial garden sanctuary.',
                'address' => 'No. 136 Norodom Blvd, BKK1, Phnom Penh',
                'latitude' => 11.5528000,
                'longitude' => 104.9282000,
                'phone' => '+855 15 814 888',
                'email' => 'booking@malis-restaurant.com',
                'website' => 'https://malis-cambodia.com',
                'price_range' => '$$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(3),
                'verified_by' => $superAdmin->id,
                'rating' => 4.92,
                'review_count' => 64,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Central Garden Pond & Buddha Shrine', 'is_cover' => true],
                    ['url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Kampot Crab Fried Rice with Peppercorn', 'is_cover' => false],
                ],
                'services' => [
                    ['name' => 'Royal Degustation Master Menu', 'description' => 'Seven courses of refined traditional Khmer dishes with wine pairing.', 'price' => 65.00, 'duration_minutes' => 120],
                    ['name' => 'Traditional Khmer Breakfast Banquet', 'description' => 'Kuyteav Phnom Penh rice noodle soup, artisanal dim sum, and fresh local fruits.', 'price' => 18.00, 'duration_minutes' => 60],
                ],
                'promotions' => [
                    ['title' => 'Master Chef Wine Tasting Special', 'promo_code' => 'MALISWINE15', 'discount_percentage' => 15.00, 'description' => 'Complimentary sommelier reserve wine flight on tasting menus.', 'banner_url' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=1200&q=80'],
                ],
            ],
            [
                'slug' => 'cuisine-wat-damnak-siem-reap',
                'name' => 'Cuisine Wat Damnak',
                'owner_id' => $ownerMalis->id,
                'category_id' => $cDining,
                'province_id' => $provinces['Siem Reap'] ?? null,
                'description' => 'Asia\'s 50 Best Restaurants awardee blending French culinary precision with authentic local Cambodian foraged produce and native herbs.',
                'address' => 'Wat Damnak Market Street, Krong Siem Reap',
                'latitude' => 13.3512000,
                'longitude' => 103.8612000,
                'phone' => '+855 77 347 762',
                'email' => 'info@cuisinewatdamnak.com',
                'website' => 'https://cuisinewatdamnak.com',
                'price_range' => '$$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(1),
                'verified_by' => $admin->id,
                'rating' => 4.95,
                'review_count' => 52,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Traditional Wooden House Dining Room', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => '6-Course Foraged Heritage Menu', 'description' => 'Seasonal menu featuring wild lotus stems, Tonle Sap freshwater fish, and fermented pepper.', 'price' => 48.00, 'duration_minutes' => 100],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'kampot-pepper-plantation-eco-lodge',
                'name' => 'Kampot Pepper Plantation & Eco Lodge',
                'owner_id' => $ownerLaurent->id,
                'category_id' => $cResort,
                'province_id' => $provinces['Kampot'] ?? null,
                'description' => 'Organic Protected Geographical Indication (PGI) pepper farm tours, traditional farm-to-table dining, and bamboo bungalows overlooking Bokor Mountain.',
                'address' => 'Phnom Voar, Krong Kampot',
                'latitude' => 10.6120000,
                'longitude' => 104.2800000,
                'phone' => '+855 33 555 888',
                'email' => 'contact@kampot-pepperlodge.com',
                'website' => 'https://kampot-pepperlodge.com',
                'price_range' => '$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(1),
                'verified_by' => $admin->id,
                'rating' => 4.88,
                'review_count' => 29,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Bungalows overlooking Pepper Vines', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Guided Plantation Tour & Pepper Tasting', 'description' => 'Walk through organic vines, harvest ripe corns, and sample black, red, white, and salted pepper.', 'price' => 10.00, 'duration_minutes' => 60],
                    ['name' => 'Pepper Farmhouse Bungalow (Per Night)', 'description' => 'Overnight eco-stay with sunrise views over Bokor Range and farm breakfast.', 'price' => 55.00, 'duration_minutes' => 1440],
                ],
                'promotions' => [
                    ['title' => 'Harvest Season Farm Stay Discount', 'promo_code' => 'HARVEST10', 'discount_percentage' => 10.00, 'description' => '10% off weekday 2-night bookings during peak harvest.', 'banner_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80'],
                ],
            ],
            [
                'slug' => 'shinta-mani-wild-bensley-collection',
                'name' => 'Shinta Mani Wild - A Bensley Collection',
                'owner_id' => $ownerDavid->id,
                'category_id' => $cResort,
                'province_id' => $provinces['Koh Kong'] ?? null,
                'description' => 'World-famous radical luxury glamping camp along 1.5 km of pristine river rapids in the Southern Cardamom National Park, protecting wildlife through eco-tourism.',
                'address' => 'Cardamom Mountains Corridor, Koh Kong',
                'latitude' => 11.2356000,
                'longitude' => 103.8821000,
                'phone' => '+855 86 626 999',
                'email' => 'wild@shintamani.com',
                'website' => 'https://wild.shintamani.com',
                'price_range' => '$$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(2),
                'verified_by' => $superAdmin->id,
                'rating' => 4.98,
                'review_count' => 42,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Luxury Tented Suite Suspended over Rapids', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Waterfall Canopy Zipline Arrival', 'description' => '380-meter zipline over forest canopy and waterfalls directly into the Landing Zone Bar.', 'price' => 120.00, 'duration_minutes' => 45],
                    ['name' => 'All-Inclusive Luxury Tent Suite (Nightly)', 'description' => 'Unlimited forest dining, custom cocktails, river expeditions, and daily spa therapies.', 'price' => 1900.00, 'duration_minutes' => 1440],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'song-saa-private-island-resort',
                'name' => 'Song Saa Private Island',
                'owner_id' => $ownerDavid->id,
                'category_id' => $cResort,
                'province_id' => $provinces['Preah Sihanouk'] ?? null,
                'description' => 'Cambodia\'s premier barefoot luxury private island sanctuary spanning two untouched islands in the Koh Rong Archipelago with overwater villas and marine reserve.',
                'address' => 'Koh Ouen & Koh Bong, Koh Rong Archipelago',
                'latitude' => 10.7012000,
                'longitude' => 103.3512000,
                'phone' => '+855 23 888 921',
                'email' => 'reservations@songsaa.com',
                'website' => 'https://songsaa.com',
                'price_range' => '$$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(4),
                'verified_by' => $superAdmin->id,
                'rating' => 4.96,
                'review_count' => 78,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Overwater Ocean Villa with Private Infinity Pool', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Overwater Villa Experience', 'description' => 'Private sundeck, glass floor ocean viewing portal, and direct reef access.', 'price' => 1450.00, 'duration_minutes' => 1440],
                    ['name' => 'Private Bioluminescent Plankton Night Cruise', 'description' => 'Night speedboat safari swimming among glowing luminous micro-plankton.', 'price' => 180.00, 'duration_minutes' => 90],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'raffles-hotel-le-royal-phnom-penh',
                'name' => 'Raffles Hotel Le Royal',
                'owner_id' => $ownerLaurent->id,
                'category_id' => $cResort,
                'province_id' => $provinces['Phnom Penh'] ?? null,
                'description' => 'Historic grand luxury hotel operating since 1929. Hosted Charlie Chaplin, Jacqueline Kennedy, and heads of state in timeless French-Khmer elegance.',
                'address' => '92 Rukhak Vithei Daun Penh, Sangkat Wat Phnom, Phnom Penh',
                'latitude' => 11.5732000,
                'longitude' => 104.9192000,
                'phone' => '+855 23 981 888',
                'email' => 'phnompenh@raffles.com',
                'website' => 'https://raffles.com/phnom-penh',
                'price_range' => '$$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(5),
                'verified_by' => $superAdmin->id,
                'rating' => 4.91,
                'review_count' => 92,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 'caption' => '1929 Grand Colonial Facade & Tropical Pool', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Heritage Landmark Suite Stay', 'description' => 'Classic clawfoot bathtubs, private balconies, and 24-hour Raffles butler service.', 'price' => 380.00, 'duration_minutes' => 1440],
                    ['name' => 'Famous Elephant Bar Afternoon High Tea', 'description' => 'Artisanal scones, savoury pastries, and signature Madame Butterfly cocktails.', 'price' => 28.00, 'duration_minutes' => 120],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'bodia-spa-botanical-wellness',
                'name' => 'Bodia Spa & Botanical Wellness',
                'owner_id' => $ownerThida->id,
                'category_id' => $cWellness,
                'province_id' => $provinces['Siem Reap'] ?? null,
                'description' => 'Cambodia\'s premier cocoon wellness retreat offering 100% natural, locally made herbal apothecaries, body scrubs, and healing massages.',
                'address' => 'Above Heritage Walk Mall, Siem Reap',
                'latitude' => 13.3618000,
                'longitude' => 103.8582000,
                'phone' => '+855 63 762 424',
                'email' => 'contact@bodia-spa.com',
                'website' => 'https://bodia-spa.com',
                'price_range' => '$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(1),
                'verified_by' => $admin->id,
                'rating' => 4.94,
                'review_count' => 61,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Tranquil Herb Steam Room & Cocoon Lounge', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Bodia Classic Khmer Herbal Compress Massage', 'description' => 'Hot muslin bags packed with ginger, turmeric, and prai root pressed into tension points.', 'price' => 45.00, 'duration_minutes' => 90],
                    ['name' => 'Tamarind & Honey Purifying Body Scrub', 'description' => 'Natural organic body exfoliation followed by gentle moisturising lavender milk.', 'price' => 38.00, 'duration_minutes' => 60],
                ],
                'promotions' => [
                    ['title' => 'After-Temple Recovery 15% Off', 'promo_code' => 'TEMPLERECOVERY', 'discount_percentage' => 15.00, 'description' => 'Show your Angkor Pass for 15% discount on all 90-minute spa treatments.', 'banner_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80'],
                ],
            ],
            [
                'slug' => 'phare-the-cambodian-circus',
                'name' => 'Phare, The Cambodian Circus',
                'owner_id' => $ownerSokha->id,
                'category_id' => $cArts,
                'province_id' => $provinces['Siem Reap'] ?? null,
                'description' => 'Electrifying modern circus blending high-energy acrobatics, live Khmer music, theatre, and visual arts to tell poignant Cambodian stories without animals.',
                'address' => 'Phare Circus Ring, Ring Road, Siem Reap',
                'latitude' => 13.3562000,
                'longitude' => 103.8421000,
                'phone' => '+855 77 557 786',
                'email' => 'booking@pharecircus.org',
                'website' => 'https://pharecircus.org',
                'price_range' => '$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(3),
                'verified_by' => $superAdmin->id,
                'rating' => 4.97,
                'review_count' => 110,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Acrobatic Fire & Aerial Hoop Spectacle', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Section A Premium Front-Row Ticket', 'description' => 'Center reserved seating with complimentary ice cold water and gift.', 'price' => 38.00, 'duration_minutes' => 75],
                    ['name' => 'Section B General Admission Ticket', 'description' => 'Terraced tiered wooden bench seating with unobstructed stage sightlines.', 'price' => 28.00, 'duration_minutes' => 75],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'chi-phat-eco-trekking-kayaks',
                'name' => 'Chi Phat Eco-Adventures & Kayaks',
                'owner_id' => $ownerDavid->id,
                'category_id' => $cAdventure,
                'province_id' => $provinces['Koh Kong'] ?? null,
                'description' => 'Community-owned wilderness adventure center offering guided jungle expeditions, wildlife night boat safaris, mountain biking, and kayaking on the Preak Piphot river.',
                'address' => 'Chi Phat Village, Southern Cardamoms, Koh Kong',
                'latitude' => 11.3524000,
                'longitude' => 103.5182000,
                'phone' => '+855 89 242 060',
                'email' => 'info@ecocardamoms.org',
                'website' => 'https://chi-phat.org',
                'price_range' => '$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(2),
                'verified_by' => $admin->id,
                'rating' => 4.85,
                'review_count' => 34,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Kayaking through Cardamom Jungle Mist', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => '2-Day Cardamom Mountain Survival Trek', 'description' => 'Deep jungle hike, hammock camping under canopy, and traditional campfire cooking.', 'price' => 85.00, 'duration_minutes' => 2880],
                    ['name' => 'Sunset River Kayak & Firefly Tour', 'description' => 'Gentle river paddle watching clouds of glowing fireflies in river mangrove trees.', 'price' => 22.00, 'duration_minutes' => 150],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'artisans-angkor-silk-stoneworks',
                'name' => 'Artisans Angkor Silk & Crafts',
                'owner_id' => $ownerSokha->id,
                'category_id' => $cCulture,
                'province_id' => $provinces['Siem Reap'] ?? null,
                'description' => 'Social enterprise dedicated to the revival of traditional Khmer craftsmanship in silk weaving, stone sculpting, wood carving, and silver plating.',
                'address' => 'Chantiers-Ecoles, Stung Thmey Street, Siem Reap',
                'latitude' => 13.3521000,
                'longitude' => 103.8542000,
                'phone' => '+855 63 963 330',
                'email' => 'contact@artisansdangkor.com',
                'website' => 'https://artisansdangkor.com',
                'price_range' => '$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(3),
                'verified_by' => $superAdmin->id,
                'rating' => 4.89,
                'review_count' => 74,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Artisans carving Sandstone Buddha Statues', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Free Guided Workshop Tour', 'description' => 'Docent-led visit exploring master silk painters, sculptors, and lacquer workshops.', 'price' => 0.00, 'duration_minutes' => 45],
                    ['name' => 'Silk Farm Day Excursion to Puok', 'description' => 'Mulberry tree cultivation, silkworm life cycle, and golden silk spinning demonstration.', 'price' => 15.00, 'duration_minutes' => 180],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'koh-rong-dive-center',
                'name' => 'Koh Rong Dive Center',
                'owner_id' => $ownerDavid->id,
                'category_id' => $cAdventure,
                'province_id' => $provinces['Preah Sihanouk'] ?? null,
                'description' => 'PADI 5-Star Instructor Development Center operating daily dive boats to outer coral reefs, seahorse gardens, and shipwreck sites in the Gulf of Thailand.',
                'address' => 'Main Pier, Koh Touch Beach, Koh Rong',
                'latitude' => 10.6682000,
                'longitude' => 103.2721000,
                'phone' => '+855 34 934 744',
                'email' => 'info@kohrongdivecenter.com',
                'website' => 'https://kohrongdivecenter.com',
                'price_range' => '$$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(2),
                'verified_by' => $admin->id,
                'rating' => 4.91,
                'review_count' => 48,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Diving off Koh Tang Coral Gardens', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => '2-Tank Certified Fun Dives', 'description' => 'Boat charter, full scuba gear, divemaster guide, and fresh tropical lunch.', 'price' => 85.00, 'duration_minutes' => 360],
                    ['name' => 'PADI Open Water Diver Certification', 'description' => '3-day course with confined training, 4 ocean dives, and international license.', 'price' => 360.00, 'duration_minutes' => 4320],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'brown-coffee-roastery-boeng-keng-kang',
                'name' => 'Brown Coffee Roastery BKK1',
                'owner_id' => $ownerMalis->id,
                'category_id' => $cDining,
                'province_id' => $provinces['Phnom Penh'] ?? null,
                'description' => 'Homegrown Cambodian coffee pioneer featuring single-origin Mondulkiri Arabica beans, artisanal French patisserie, and modern architect-designed greenery.',
                'address' => 'Corner of Street 51 & 294, BKK1, Phnom Penh',
                'latitude' => 11.5512000,
                'longitude' => 104.9242000,
                'phone' => '+855 23 215 215',
                'email' => 'hello@browncoffee.com.kh',
                'website' => 'https://browncoffee.com.kh',
                'price_range' => '$$',
                'status' => 'active',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(6),
                'verified_by' => $admin->id,
                'rating' => 4.82,
                'review_count' => 89,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Cold Brew Bar & Tropical Sunlit Courtyard', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Artisanal Mondulkiri Pour-Over Tasting Flight', 'description' => 'Single-origin washed and honey processed local coffee beans.', 'price' => 4.50, 'duration_minutes' => 30],
                    ['name' => 'Eggs Florentine on Fresh Brioche', 'description' => 'Poached farm eggs, spinach, hollandaise, and homemade sourdough.', 'price' => 6.50, 'duration_minutes' => 45],
                ],
                'promotions' => [],
            ],

            // ------------------------------------------------------------------
            // 2. PENDING BUSINESSES (For Admin Moderation & Approval Queue)
            // ------------------------------------------------------------------
            [
                'slug' => 'buger',
                'name' => 'Buger',
                'owner_id' => $ownerSokha->id,
                'category_id' => $cDining,
                'province_id' => $provinces['Siem Reap'] ?? null,
                'description' => 'Gourmet organic burgers, craft shakes, and local Khmer fusion snacks near Pub Street.',
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
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Burger Bar Front', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Kampot Pepper Beef Burger', 'description' => 'Locally grass-fed beef with green pepper glaze and sweet potato fries.', 'price' => 7.50, 'duration_minutes' => 30],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'mekong-river-sunset-cruise-kayaking',
                'name' => 'Mekong River Sunset Cruise & Kayaking',
                'owner_id' => $ownerSokha->id,
                'category_id' => $cAdventure,
                'province_id' => $provinces['Phnom Penh'] ?? null,
                'description' => 'Eco-friendly scenic river cruises and guided sunset kayaking tours along the Mekong River.',
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
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Cruiser at Phnom Penh Sunset Pier', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Sunset Cocktail Cruise', 'description' => '90-minute voyage watching the Royal Palace illuminate from the water.', 'price' => 16.00, 'duration_minutes' => 90],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'mondulkiri-coffee-plantation-homestay',
                'name' => 'Mondulkiri Coffee Homestay & Eco Lodge',
                'owner_id' => $ownerLaurent->id,
                'category_id' => $cResort,
                'province_id' => $provinces['Mondulkiri'] ?? null,
                'description' => 'Rustic mountain coffee lodge in Sen Monorom offering highland coffee bean picking, indigenous Bunong culture meals, and pine forest nature walks.',
                'address' => 'Coffee Valley Road, Krong Sen Monorom',
                'latitude' => 12.4552000,
                'longitude' => 107.1882000,
                'phone' => '+855 73 999 123',
                'email' => 'contact@mondulkiricoffee.kh',
                'website' => 'https://mondulkiricoffee.kh',
                'price_range' => '$',
                'status' => 'active',
                'verification_status' => 'pending',
                'rating' => 0.00,
                'review_count' => 0,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Coffee Hills Mist Sunrise', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Coffee Roasting Workshop', 'description' => 'Roast your own Mondulkiri beans over open wood coals.', 'price' => 15.00, 'duration_minutes' => 90],
                ],
                'promotions' => [],
            ],
            [
                'slug' => 'battambang-bamboo-train-cafe',
                'name' => 'Battambang Bamboo Train Cafe & Bistro',
                'owner_id' => $ownerMalis->id,
                'category_id' => $cDining,
                'province_id' => $provinces['Battambang'] ?? null,
                'description' => 'Open-air riverside cafe and craft food stop located next to the Bamboo Train terminus, serving fresh sugarcane juice and coconut curries.',
                'address' => 'O Sra Lav Terminus, Krong Battambang',
                'latitude' => 13.0812000,
                'longitude' => 103.2182000,
                'phone' => '+855 53 789 456',
                'email' => 'bambootraincafe@example.com',
                'website' => 'https://battambangbambootrain.kh',
                'price_range' => '$',
                'status' => 'active',
                'verification_status' => 'pending',
                'rating' => 0.00,
                'review_count' => 0,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Bamboo Railway Platform Cafe', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Fresh Battambang Coconut Curry Set', 'description' => 'Served with rice and local organic garden salad.', 'price' => 5.50, 'duration_minutes' => 45],
                ],
                'promotions' => [],
            ],

            // ------------------------------------------------------------------
            // 3. REJECTED BUSINESS (Demonstrates Admin Rejection Workflow)
            // ------------------------------------------------------------------
            [
                'slug' => 'sihanoukville-luxury-yacht-charters',
                'name' => 'Sihanoukville Luxury Yacht Charters',
                'owner_id' => $ownerDavid->id,
                'category_id' => $cAdventure,
                'province_id' => $provinces['Preah Sihanouk'] ?? null,
                'description' => 'Private high-speed luxury yacht charters across Koh Rong, Koh Rong Sanloem, and Koh Dek Koul.',
                'address' => 'Ochheuteal Marina Pier, Sihanoukville',
                'latitude' => 10.6182000,
                'longitude' => 103.5212000,
                'phone' => '+855 34 555 123',
                'email' => 'booking@sihanoukyachts.com',
                'website' => 'https://sihanoukyachts.com',
                'price_range' => '$$$$',
                'status' => 'active',
                'verification_status' => 'rejected',
                'verified_at' => now()->subDays(5),
                'verified_by' => $admin->id,
                'rejection_reason' => 'Commercial vessel registration documents expired. Missing mandatory marine passenger safety certification.',
                'rating' => 0.00,
                'review_count' => 0,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Speed Yacht at Sea', 'is_cover' => true],
                ],
                'services' => [],
                'promotions' => [],
            ],

            // ------------------------------------------------------------------
            // 4. SUSPENDED BUSINESS (Demonstrates Admin Suspension Action)
            // ------------------------------------------------------------------
            [
                'slug' => 'angkor-balloon-adventures',
                'name' => 'Angkor Hot Air Balloon Adventures',
                'owner_id' => $ownerSokha->id,
                'category_id' => $cAdventure,
                'province_id' => $provinces['Siem Reap'] ?? null,
                'description' => 'Tethered and free-flight hot air balloon sunrise flights over the Angkor temple ruins.',
                'address' => 'Near Angkor Wat West Gate, Siem Reap',
                'latitude' => 13.4121000,
                'longitude' => 103.8582000,
                'phone' => '+855 63 964 123',
                'email' => 'fly@angkorballoon.com',
                'website' => 'https://angkorballoon.com',
                'price_range' => '$$$',
                'status' => 'suspended',
                'verification_status' => 'approved',
                'verified_at' => now()->subMonths(6),
                'verified_by' => $superAdmin->id,
                'rating' => 4.30,
                'review_count' => 15,
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Sunrise Hot Air Balloon Flight', 'is_cover' => true],
                ],
                'services' => [
                    ['name' => 'Sunrise Hot Air Balloon Flight', 'description' => '15-minute tethered ascent to 120 meters for panoramic temple views.', 'price' => 25.00, 'duration_minutes' => 20],
                ],
                'promotions' => [],
            ],
        ];

        foreach ($businesses as $bData) {
            $images     = $bData['images'] ?? [];
            $services   = $bData['services'] ?? [];
            $promotions = $bData['promotions'] ?? [];
            unset($bData['images'], $bData['services'], $bData['promotions']);

            $business = Business::updateOrCreate(
                ['slug' => $bData['slug']],
                $bData
            );

            // Seed business images
            foreach ($images as $idx => $img) {
                BusinessImage::updateOrCreate(
                    ['business_id' => $business->id, 'image_url' => $img['url']],
                    [
                        'caption' => $img['caption'],
                        'is_cover' => $img['is_cover'] ?? false,
                        'display_order' => $idx + 1,
                    ]
                );
            }

            // Seed business services
            foreach ($services as $svc) {
                BusinessService::updateOrCreate(
                    ['business_id' => $business->id, 'name' => $svc['name']],
                    [
                        'description' => $svc['description'] ?? null,
                        'price' => $svc['price'],
                        'currency' => 'USD',
                        'duration_minutes' => $svc['duration_minutes'] ?? null,
                        'is_available' => true,
                    ]
                );
            }

            // Seed standard 7-day business hours
            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                BusinessHour::updateOrCreate(
                    ['business_id' => $business->id, 'day_of_week' => $day],
                    [
                        'open_time' => '07:30:00',
                        'close_time' => '22:00:00',
                        'is_closed' => false,
                    ]
                );
            }

            // Seed business promotions
            foreach ($promotions as $promo) {
                BusinessPromotion::updateOrCreate(
                    ['business_id' => $business->id, 'promo_code' => $promo['promo_code']],
                    [
                        'title' => $promo['title'],
                        'description' => $promo['description'],
                        'discount_percentage' => $promo['discount_percentage'],
                        'start_date' => now()->subDays(5),
                        'end_date' => now()->addMonths(4),
                        'is_active' => true,
                        'banner_url' => $promo['banner_url'] ?? null,
                    ]
                );
            }
        }
    }
}
