<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Place;
use App\Models\Province;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds for Places/Destinations across all 25 provinces of Cambodia.
     */
    public function run(): void
    {
        // Helper map for categories & provinces
        $categories = Category::pluck('id', 'name')->toArray();
        $provinces  = Province::pluck('id', 'name')->toArray();

        $cTemple     = $categories['Temple'] ?? 1;
        $cHistory    = $categories['Historical Site'] ?? 2;
        $cPalace     = $categories['Palace'] ?? 3;
        $cNature     = $categories['Nature'] ?? 4;
        $cMuseum     = $categories['Museum'] ?? 5;
        $cDining     = $categories['Dining'] ?? 6;
        $cResort     = $categories['Resort & Hotel'] ?? 7;
        $cAdventure  = $categories['Adventure & Tour'] ?? 8;

        $places = [
            // ------------------------------------------------------------------
            // 1. PHNOM PENH (Capital City)
            // ------------------------------------------------------------------
            [
                'name' => 'Royal Palace & Silver Pagoda',
                'category_id' => $cPalace,
                'province_name' => 'Phnom Penh',
                'address' => 'Samdach Sothearos Blvd, Phnom Penh',
                'coordinates' => '11.5625° N, 104.9312° E',
                'latitude' => 11.56250000,
                'longitude' => 104.93120000,
                'description' => 'Official residence of His Majesty King Norodom Sihamoni, housing over 5,000 silver floor tiles and the Emerald Buddha.',
                'best_time' => 'Morning (8:00 AM - 10:30 AM)',
                'duration' => '2 Hours',
                'price' => '$10 USD',
                'rating' => 4.75,
                'reviews_count' => 620,
                'visitors_count' => 950000,
                'image_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'National Museum of Cambodia',
                'category_id' => $cMuseum,
                'province_name' => 'Phnom Penh',
                'address' => 'Preah Ang Eng St (13), Phnom Penh',
                'coordinates' => '11.5658° N, 104.9290° E',
                'latitude' => 11.56580000,
                'longitude' => 104.92900000,
                'description' => 'Cambodia\'s premier historical and archaeological museum housing world-renowned Khmer terracotta and bronze statues.',
                'best_time' => 'Morning (9:00 AM - 12:00 PM)',
                'duration' => '2 Hours',
                'price' => '$10 USD',
                'rating' => 4.70,
                'reviews_count' => 410,
                'visitors_count' => 450000,
                'image_url' => 'https://images.unsplash.com/photo-1584646098378-0874589d76b1?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],
            [
                'name' => 'Wat Phnom Historical Hill',
                'category_id' => $cTemple,
                'province_name' => 'Phnom Penh',
                'address' => 'Street 96, Norodom Blvd, Phnom Penh',
                'coordinates' => '11.5761° N, 104.9230° E',
                'latitude' => 11.57610000,
                'longitude' => 104.92300000,
                'description' => 'The founding hilltop Buddhist temple of Phnom Penh built in 1372 by Lady Penh.',
                'best_time' => 'Late Afternoon (4:00 PM - 6:00 PM)',
                'duration' => '1 Hour',
                'price' => '$1 USD',
                'rating' => 4.60,
                'reviews_count' => 530,
                'visitors_count' => 600000,
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 2. SIEM REAP
            // ------------------------------------------------------------------
            [
                'name' => 'Angkor Wat',
                'category_id' => $cTemple,
                'province_name' => 'Siem Reap',
                'address' => 'Angkor Archaeological Park, Siem Reap',
                'coordinates' => '13.4125° N, 103.8670° E',
                'latitude' => 13.41250000,
                'longitude' => 103.86700000,
                'description' => 'The largest religious structure in the world and supreme symbol of Khmer civilization.',
                'best_time' => 'Sunrise (5:30 AM - 7:00 AM)',
                'duration' => '3 - 4 Hours',
                'price' => '$37 USD',
                'rating' => 4.95,
                'reviews_count' => 1250,
                'visitors_count' => 2500000,
                'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'Bayon Temple',
                'category_id' => $cTemple,
                'province_name' => 'Siem Reap',
                'address' => 'Angkor Thom, Siem Reap',
                'coordinates' => '13.4413° N, 103.8587° E',
                'latitude' => 13.44130000,
                'longitude' => 103.85870000,
                'description' => 'Famous state temple of King Jayavarman VII adorned with 216 giant serene smiling stone faces.',
                'best_time' => 'Late Afternoon (3:30 PM - 5:30 PM)',
                'duration' => '2 Hours',
                'price' => 'Included in Angkor Pass',
                'rating' => 4.88,
                'reviews_count' => 840,
                'visitors_count' => 1800000,
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'Ta Prohm Temple',
                'category_id' => $cTemple,
                'province_name' => 'Siem Reap',
                'address' => 'Angkor Archaeological Park, Siem Reap',
                'coordinates' => '13.4348° N, 103.8893° E',
                'latitude' => 13.43480000,
                'longitude' => 103.88930000,
                'description' => 'Atmospheric 12th-century temple intertwined with massive silk-cotton tree roots, iconic Tomb Raider filming location.',
                'best_time' => 'Early Morning (7:30 AM - 9:00 AM)',
                'duration' => '2 Hours',
                'price' => 'Included in Angkor Pass',
                'rating' => 4.90,
                'reviews_count' => 920,
                'visitors_count' => 2000000,
                'image_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 3. BATTAMBANG
            // ------------------------------------------------------------------
            [
                'name' => 'Phnom Sampov & Bat Caves',
                'category_id' => $cNature,
                'province_name' => 'Battambang',
                'address' => 'Phnom Sampov District, Battambang',
                'coordinates' => '13.0245° N, 103.0988° E',
                'latitude' => 13.02450000,
                'longitude' => 103.09880000,
                'description' => 'Sacred mountain top temple offering panoramic countryside views and the famous dusk exodus of millions of bats.',
                'best_time' => 'Sunset (5:00 PM - 6:30 PM)',
                'duration' => '3 Hours',
                'price' => '$3 USD',
                'rating' => 4.80,
                'reviews_count' => 380,
                'visitors_count' => 350000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'Bamboo Train (Norry)',
                'category_id' => $cAdventure,
                'province_name' => 'Battambang',
                'address' => 'Odambang Village, Battambang',
                'coordinates' => '13.0789° N, 103.2201° E',
                'latitude' => 13.07890000,
                'longitude' => 103.22010000,
                'description' => 'Unique traditional wooden platform train powered by a small engine gliding along rural train tracks.',
                'best_time' => 'Morning (8:00 AM - 10:00 AM)',
                'duration' => '1 Hour',
                'price' => '$5 USD',
                'rating' => 4.75,
                'reviews_count' => 460,
                'visitors_count' => 280000,
                'image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 4. PREAH SIHANOUK
            // ------------------------------------------------------------------
            [
                'name' => 'Koh Rong Island Beaches',
                'category_id' => $cNature,
                'province_name' => 'Preah Sihanouk',
                'address' => 'Koh Rong Archipelago, Preah Sihanouk',
                'coordinates' => '10.7258° N, 103.2254° E',
                'latitude' => 10.72580000,
                'longitude' => 103.22540000,
                'description' => 'Tropical paradise famous for Long Set Beach, bioluminescent plankton, coral reefs, and crystal blue waters.',
                'best_time' => 'November to April (Dry Season)',
                'duration' => '2 - 3 Days',
                'price' => 'Free Access ($25 Ferry)',
                'rating' => 4.90,
                'reviews_count' => 780,
                'visitors_count' => 600000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'Koh Rong Sanloem (Saracen Bay)',
                'category_id' => $cNature,
                'province_name' => 'Preah Sihanouk',
                'address' => 'Koh Rong Sanloem, Preah Sihanouk',
                'coordinates' => '10.5982° N, 103.3089° E',
                'latitude' => 10.59820000,
                'longitude' => 103.30890000,
                'description' => 'Tranquil horseshoe bay with powdery white sand and turquoise calm shallow ocean waters.',
                'best_time' => 'Morning & Sunset',
                'duration' => '2 Days',
                'price' => 'Free Access',
                'rating' => 4.85,
                'reviews_count' => 510,
                'visitors_count' => 400000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 5. KAMPOT
            // ------------------------------------------------------------------
            [
                'name' => 'Bokor National Park',
                'category_id' => $cNature,
                'province_name' => 'Kampot',
                'address' => 'Teuk Chhou District, Kampot',
                'coordinates' => '10.6254° N, 104.0258° E',
                'latitude' => 10.62540000,
                'longitude' => 104.02580000,
                'description' => 'Cool mountain plateau national park featuring historic French colonial station ruins, waterfalls, and panoramic ocean vistas.',
                'best_time' => 'All Day (Cool Foggy Mornings)',
                'duration' => 'Full Day',
                'price' => 'Free Entry',
                'rating' => 4.65,
                'reviews_count' => 310,
                'visitors_count' => 420000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'La Plantation Kampot Pepper Farm',
                'category_id' => $cNature,
                'province_name' => 'Kampot',
                'address' => 'Bosjheng Village, Kampot',
                'coordinates' => '10.6421° N, 104.3012° E',
                'latitude' => 10.64210000,
                'longitude' => 104.30120000,
                'description' => 'Organic GI Kampot Pepper plantation offering guided agricultural tours, spice tastings, and countryside views.',
                'best_time' => 'Morning (9:00 AM - 11:30 AM)',
                'duration' => '2 Hours',
                'price' => 'Free Tour',
                'rating' => 4.80,
                'reviews_count' => 390,
                'visitors_count' => 250000,
                'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 6. KEP
            // ------------------------------------------------------------------
            [
                'name' => 'Kep Crab Market & Boardwalk',
                'category_id' => $cDining,
                'province_name' => 'Kep',
                'address' => 'Crab Market Beach Road, Kep',
                'coordinates' => '10.4821° N, 104.2981° E',
                'latitude' => 10.48210000,
                'longitude' => 104.29810000,
                'description' => 'Famous bustling seaside seafood market selling fresh live blue crabs cooked with green Kampot pepper.',
                'best_time' => 'Lunch & Sunset (11:00 AM - 6:00 PM)',
                'duration' => '2 Hours',
                'price' => '$5 - $15 USD',
                'rating' => 4.70,
                'reviews_count' => 540,
                'visitors_count' => 380000,
                'image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 7. KAMPONG THOM (Province ID 11)
            // ------------------------------------------------------------------
            [
                'name' => 'Sambor Prei Kuk',
                'category_id' => $cHistory,
                'province_name' => 'Kampong Thom',
                'address' => 'Prasat Sambour District, Kampong Thom',
                'coordinates' => '12.8683° N, 105.0408° E',
                'latitude' => 12.86830000,
                'longitude' => 105.04080000,
                'description' => 'UNESCO World Heritage Site pre-Angkorian Ishanapura city featuring over 100 brick temple towers inside tranquil ancient forest.',
                'best_time' => 'Morning (8:00 AM - 11:00 AM)',
                'duration' => '3 Hours',
                'price' => '$10 USD',
                'rating' => 4.85,
                'reviews_count' => 340,
                'visitors_count' => 180000,
                'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'Phnom Santuk Mountain Sanctuary',
                'category_id' => $cTemple,
                'province_name' => 'Kampong Thom',
                'address' => 'Ko Doung Village, Santuk District, Kampong Thom',
                'coordinates' => '12.6201° N, 104.9211° E',
                'latitude' => 12.62010000,
                'longitude' => 104.92110000,
                'description' => 'Sacred mountain featuring 809 stone steps leading to ancient reclining Buddha statues, pagodas, and panoramic valley views.',
                'best_time' => 'Cool Morning (7:00 AM - 10:00 AM)',
                'duration' => '2 - 3 Hours',
                'price' => '$2 USD',
                'rating' => 4.65,
                'reviews_count' => 210,
                'visitors_count' => 120000,
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],
            [
                'name' => 'Prasat Kuhak Nokor',
                'category_id' => $cTemple,
                'province_name' => 'Kampong Thom',
                'address' => 'Treal Village, Baray District, Kampong Thom',
                'coordinates' => '12.3512° N, 104.9814° E',
                'latitude' => 12.35120000,
                'longitude' => 104.98140000,
                'description' => 'Historic 11th-century Suryavarman I laterite and sandstone temple sanctuary surrounded by peaceful moat and lotus ponds.',
                'best_time' => 'Morning (8:30 AM - 11:00 AM)',
                'duration' => '1.5 Hours',
                'price' => 'Free Entry',
                'rating' => 4.55,
                'reviews_count' => 140,
                'visitors_count' => 85000,
                'image_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 8. BANTEAY MEANCHEY
            // ------------------------------------------------------------------
            [
                'name' => 'Banteay Chhmar Temple Ruins',
                'category_id' => $cHistory,
                'province_name' => 'Banteay Meanchey',
                'address' => 'Banteay Chhmar Commune, Thma Puok District',
                'coordinates' => '14.0721° N, 103.1089° E',
                'latitude' => 14.07210000,
                'longitude' => 103.10890000,
                'description' => 'Grand 12th-century Jayavarman VII temple complex famous for intricate multi-armed Avalokiteshvara bas-relief carvings.',
                'best_time' => 'Morning (8:00 AM - 11:30 AM)',
                'duration' => '3 Hours',
                'price' => '$5 USD',
                'rating' => 4.80,
                'reviews_count' => 190,
                'visitors_count' => 95000,
                'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 9. KAMPONG CHAM
            // ------------------------------------------------------------------
            [
                'name' => 'Wat Nokor Bachey Temple',
                'category_id' => $cTemple,
                'province_name' => 'Kampong Cham',
                'address' => 'Krong Kampong Cham, Kampong Cham',
                'coordinates' => '12.0015° N, 105.4412° E',
                'latitude' => 12.00150000,
                'longitude' => 105.44120000,
                'description' => 'Unique architectural fusion of 11th-century Mahayana sandstone temple with colorful modern Theravada pagoda inside.',
                'best_time' => 'Morning (8:00 AM - 10:30 AM)',
                'duration' => '1.5 Hours',
                'price' => '$2 USD',
                'rating' => 4.60,
                'reviews_count' => 220,
                'visitors_count' => 150000,
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],
            [
                'name' => 'Koh Paen Bamboo Bridge',
                'category_id' => $cNature,
                'province_name' => 'Kampong Cham',
                'address' => 'Koh Paen Island, Kampong Cham',
                'coordinates' => '11.9782° N, 105.4712° E',
                'latitude' => 11.97820000,
                'longitude' => 105.47120000,
                'description' => 'World-famous seasonal hand-built bamboo bridge spanning across the Mekong River to Koh Paen island.',
                'best_time' => 'Dry Season (December - May)',
                'duration' => '2 Hours',
                'price' => '$1 USD',
                'rating' => 4.75,
                'reviews_count' => 310,
                'visitors_count' => 220000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 10. KAMPONG CHHNANG
            // ------------------------------------------------------------------
            [
                'name' => 'Ondong Roshey Pottery Village',
                'category_id' => $cHistory,
                'province_name' => 'Kampong Chhnang',
                'address' => 'Rolea B\'ier District, Kampong Chhnang',
                'coordinates' => '12.2412° N, 104.6412° E',
                'latitude' => 12.24120000,
                'longitude' => 104.64120000,
                'description' => 'Heritage village famous for traditional hand-crafted terracotta clay pots, stoves, and Cambodian artisan pottery.',
                'best_time' => 'Morning (8:30 AM - 11:30 AM)',
                'duration' => '2 Hours',
                'price' => 'Free Entry',
                'rating' => 4.65,
                'reviews_count' => 170,
                'visitors_count' => 110000,
                'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 11. KAMPONG SPEU
            // ------------------------------------------------------------------
            [
                'name' => 'Kirirom National Park Pine Plateau',
                'category_id' => $cNature,
                'province_name' => 'Kampong Speu',
                'address' => 'Phnom Sruoch District, Kampong Speu',
                'coordinates' => '11.3124° N, 104.0512° E',
                'latitude' => 11.31240000,
                'longitude' => 104.05120000,
                'description' => 'High altitude pine tree plateau national park featuring cascading Chambok waterfalls, eco-lodges, and mountain bike trails.',
                'best_time' => 'All Day (Cool Mountain Climate)',
                'duration' => 'Full Day',
                'price' => '$5 USD',
                'rating' => 4.75,
                'reviews_count' => 290,
                'visitors_count' => 210000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 12. KANDAL
            // ------------------------------------------------------------------
            [
                'name' => 'Oudong Mountain Royal Stupas',
                'category_id' => $cHistory,
                'province_name' => 'Kandal',
                'address' => 'Ponhea Lueu District, Kandal',
                'coordinates' => '11.8312° N, 104.7512° E',
                'latitude' => 11.83120000,
                'longitude' => 104.75120000,
                'description' => 'Ancient 17th-century capital mountain site adorned with royal stupas enshrining ashes of Khmer kings.',
                'best_time' => 'Morning (7:30 AM - 10:30 AM)',
                'duration' => '3 Hours',
                'price' => '$2 USD',
                'rating' => 4.70,
                'reviews_count' => 380,
                'visitors_count' => 320000,
                'image_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'Koh Dach Silk Weaving Island',
                'category_id' => $cHistory,
                'province_name' => 'Kandal',
                'address' => 'Koh Dach Commune, Kandal',
                'coordinates' => '11.6421° N, 104.9512° E',
                'latitude' => 11.64210000,
                'longitude' => 104.95120000,
                'description' => 'Peaceful island on the Mekong river renowned for traditional Khmer handloom silk weaving villages.',
                'best_time' => 'Morning & Afternoon',
                'duration' => 'Half Day',
                'price' => '$1 Ferry',
                'rating' => 4.65,
                'reviews_count' => 260,
                'visitors_count' => 190000,
                'image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 13. KOH KONG
            // ------------------------------------------------------------------
            [
                'name' => 'Tatai Waterfall & River Sanctuary',
                'category_id' => $cNature,
                'province_name' => 'Koh Kong',
                'address' => 'Tatai Commune, Koh Kong',
                'coordinates' => '11.5812° N, 103.1212° E',
                'latitude' => 11.58120000,
                'longitude' => 103.12120000,
                'description' => 'Majestic double-tier river waterfall nestled inside the pristine Cardamom Mountains rainforest.',
                'best_time' => 'Wet & Early Dry Season (Aug - Jan)',
                'duration' => 'Full Day',
                'price' => '$5 USD Boat',
                'rating' => 4.85,
                'reviews_count' => 310,
                'visitors_count' => 170000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 14. KRATIE
            // ------------------------------------------------------------------
            [
                'name' => 'Kampi Irrawaddy Dolphin Sanctuary',
                'category_id' => $cNature,
                'province_name' => 'Kratie',
                'address' => 'Kampi Village, Kratie District, Kratie',
                'coordinates' => '12.6012° N, 106.0212° E',
                'latitude' => 12.60120000,
                'longitude' => 106.02120000,
                'description' => 'The premier habitat along the Mekong River to spot rare endangered freshwater Irrawaddy dolphins from wooden eco-boats.',
                'best_time' => 'Dry Season (Dec - May)',
                'duration' => '2 Hours',
                'price' => '$9 USD Boat',
                'rating' => 4.80,
                'reviews_count' => 420,
                'visitors_count' => 240000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 15. MONDULKIRI
            // ------------------------------------------------------------------
            [
                'name' => 'Bou Sra Double-Tier Waterfall',
                'category_id' => $cNature,
                'province_name' => 'Mondulkiri',
                'address' => 'Pech Chreada District, Mondulkiri',
                'coordinates' => '12.5712° N, 107.4212° E',
                'latitude' => 12.57120000,
                'longitude' => 107.42120000,
                'description' => 'Cambodia\'s largest and most iconic double-tiered waterfall surrounded by lush green jungle canopy.',
                'best_time' => 'Wet & Post-Monsoon Season',
                'duration' => '3 Hours',
                'price' => '$2 USD',
                'rating' => 4.90,
                'reviews_count' => 490,
                'visitors_count' => 280000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 16. ODDAR MEANCHEY
            // ------------------------------------------------------------------
            [
                'name' => 'Prasat Tamuan Ancient Temple',
                'category_id' => $cHistory,
                'province_name' => 'Oddar Meanchey',
                'address' => 'Anlong Veng District, Oddar Meanchey',
                'coordinates' => '14.3512° N, 103.6212° E',
                'latitude' => 14.35120000,
                'longitude' => 103.62120000,
                'description' => 'Ancient border sanctuary temple along the Dangrek Mountain ridge surrounded by forest reserves.',
                'best_time' => 'Morning (8:00 AM - 11:00 AM)',
                'duration' => '2 Hours',
                'price' => 'Free Entry',
                'rating' => 4.45,
                'reviews_count' => 95,
                'visitors_count' => 50000,
                'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 17. PAILIN
            // ------------------------------------------------------------------
            [
                'name' => 'Phnom Yat Pagoda & Ruby Statue',
                'category_id' => $cTemple,
                'province_name' => 'Pailin',
                'address' => 'Krong Pailin, Pailin',
                'coordinates' => '12.8512° N, 102.6112° E',
                'latitude' => 12.85120000,
                'longitude' => 102.61120000,
                'description' => 'Cultural mountain pagoda dedicated to Grandma Yat featuring the giant Peacock statue and Cardamom Mountain views.',
                'best_time' => 'Sunset (4:30 PM - 6:00 PM)',
                'duration' => '1.5 Hours',
                'price' => 'Free Entry',
                'rating' => 4.50,
                'reviews_count' => 140,
                'visitors_count' => 75000,
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 18. PREAH VIHEAR
            // ------------------------------------------------------------------
            [
                'name' => 'Prasat Preah Vihear',
                'category_id' => $cTemple,
                'province_name' => 'Preah Vihear',
                'address' => 'Choam Khsant District, Preah Vihear',
                'coordinates' => '14.3908° N, 104.6801° E',
                'latitude' => 14.39080000,
                'longitude' => 104.68010000,
                'description' => 'UNESCO World Heritage cliffside temple complex perched 525 meters atop the Dângrêk Mountains overlooking Cambodia.',
                'best_time' => 'Early Morning (7:00 AM - 10:30 AM)',
                'duration' => '3 - 4 Hours',
                'price' => '$10 USD',
                'rating' => 4.92,
                'reviews_count' => 610,
                'visitors_count' => 320000,
                'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'Koh Ker 7-Tiered Pyramid Temple',
                'category_id' => $cHistory,
                'province_name' => 'Preah Vihear',
                'address' => 'Srayong Commune, Kulen District, Preah Vihear',
                'coordinates' => '13.7845° N, 104.5361° E',
                'latitude' => 13.78450000,
                'longitude' => 104.53610000,
                'description' => 'UNESCO World Heritage 10th-century capital featuring the colossal 7-tier pyramid temple Prasat Prang.',
                'best_time' => 'Morning (8:30 AM - 11:30 AM)',
                'duration' => '3 Hours',
                'price' => '$15 USD',
                'rating' => 4.88,
                'reviews_count' => 420,
                'visitors_count' => 210000,
                'image_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 19. PREY VENG
            // ------------------------------------------------------------------
            [
                'name' => 'Ba Phnom Sacred Mountain',
                'category_id' => $cHistory,
                'province_name' => 'Prey Veng',
                'address' => 'Ba Phnom District, Prey Veng',
                'coordinates' => '11.2312° N, 105.3512° E',
                'latitude' => 11.23120000,
                'longitude' => 105.35120000,
                'description' => 'Sacred Funan-era holy mountain sanctuary steeped in early Cambodian spiritual history and legends.',
                'best_time' => 'Morning (8:00 AM - 10:30 AM)',
                'duration' => '2 Hours',
                'price' => 'Free Entry',
                'rating' => 4.45,
                'reviews_count' => 130,
                'visitors_count' => 80000,
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 20. PURSAT
            // ------------------------------------------------------------------
            [
                'name' => 'Kampong Luong Floating Village',
                'category_id' => $cNature,
                'province_name' => 'Pursat',
                'address' => 'Krakor District, Pursat',
                'coordinates' => '12.5612° N, 104.2112° E',
                'latitude' => 12.56120000,
                'longitude' => 104.21120000,
                'description' => 'Massive self-contained floating town on the Tonle Sap Lake featuring floating schools, clinics, and shops.',
                'best_time' => 'Morning (8:00 AM - 11:00 AM)',
                'duration' => '2.5 Hours',
                'price' => '$10 USD Boat',
                'rating' => 4.70,
                'reviews_count' => 280,
                'visitors_count' => 160000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 21. RATANAKIRI
            // ------------------------------------------------------------------
            [
                'name' => 'Yeak Laom Volcanic Crater Lake',
                'category_id' => $cNature,
                'province_name' => 'Ratanakiri',
                'address' => 'Banlung District, Ratanakiri',
                'coordinates' => '13.7312° N, 107.0112° E',
                'latitude' => 13.73120000,
                'longitude' => 107.01120000,
                'description' => 'Pristine 700,000-year-old emerald green volcanic crater lake sacred to local indigenous Tompuon people.',
                'best_time' => 'All Day (Swimming & Nature Walk)',
                'duration' => 'Half Day',
                'price' => '$2 USD',
                'rating' => 4.90,
                'reviews_count' => 410,
                'visitors_count' => 230000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 22. STUNG TRENG
            // ------------------------------------------------------------------
            [
                'name' => 'Ramsar Mekong Flooded Forest',
                'category_id' => $cNature,
                'province_name' => 'Stung Treng',
                'address' => 'O\'Svay Commune, Stung Treng',
                'coordinates' => '13.8512° N, 105.9512° E',
                'latitude' => 13.85120000,
                'longitude' => 105.95120000,
                'description' => 'Internationally recognized wetland sanctuary featuring submerged trees, kayaking channels, and migratory bird species.',
                'best_time' => 'Dry Season (Kayaking)',
                'duration' => 'Full Day',
                'price' => '$15 USD Tour',
                'rating' => 4.75,
                'reviews_count' => 220,
                'visitors_count' => 110000,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 23. SVAY RIENG
            // ------------------------------------------------------------------
            [
                'name' => 'Prasat Chek Ancient Mound',
                'category_id' => $cHistory,
                'province_name' => 'Svay Rieng',
                'address' => 'Svay Chrum District, Svay Rieng',
                'coordinates' => '11.1212° N, 105.7812° E',
                'latitude' => 11.12120000,
                'longitude' => 105.78120000,
                'description' => 'Historic archaeological site featuring ancient brick temple foundations and serene rural lotus fields.',
                'best_time' => 'Morning (8:00 AM - 10:30 AM)',
                'duration' => '1.5 Hours',
                'price' => 'Free Entry',
                'rating' => 4.40,
                'reviews_count' => 110,
                'visitors_count' => 65000,
                'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 24. TAKEO
            // ------------------------------------------------------------------
            [
                'name' => 'Phnom Da & Asram Maha Rosei',
                'category_id' => $cHistory,
                'province_name' => 'Takeo',
                'address' => 'Angkor Borei District, Takeo',
                'coordinates' => '10.9812° N, 104.9812° E',
                'latitude' => 10.98120000,
                'longitude' => 104.98120000,
                'description' => 'Cradle of Khmer civilization featuring 6th-century Funan-era hill temple and rare basalt stone Rosei ashram.',
                'best_time' => 'Morning (Boat from Angkor Borei)',
                'duration' => 'Half Day',
                'price' => '$5 USD Boat',
                'rating' => 4.70,
                'reviews_count' => 240,
                'visitors_count' => 130000,
                'image_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'status' => 'Active',
            ],
            [
                'name' => 'Phnom Chisor Temple',
                'category_id' => $cTemple,
                'province_name' => 'Takeo',
                'address' => 'Sla District, Takeo',
                'coordinates' => '11.0312° N, 104.7812° E',
                'latitude' => 11.03120000,
                'longitude' => 104.78120000,
                'description' => '11th-century Suryavarman I temple perched atop Chisor Hill with 412 steps offering sweeping countryside panoramas.',
                'best_time' => 'Morning (7:30 AM - 10:30 AM)',
                'duration' => '3 Hours',
                'price' => '$2 USD',
                'rating' => 4.65,
                'reviews_count' => 210,
                'visitors_count' => 140000,
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],

            // ------------------------------------------------------------------
            // 25. TBOUNG KHMUM
            // ------------------------------------------------------------------
            [
                'name' => 'Luong Preah Sdech Kan Fortress',
                'category_id' => $cHistory,
                'province_name' => 'Tboung Khmum',
                'address' => 'Ponhea Kraek District, Tboung Khmum',
                'coordinates' => '11.9124° N, 105.8124° E',
                'latitude' => 11.91240000,
                'longitude' => 105.81240000,
                'description' => 'Historical 16th-century royal citadel site of King Sdech Kan featuring ancient earthen ramparts and monuments.',
                'best_time' => 'Morning (8:30 AM - 11:30 AM)',
                'duration' => '2 Hours',
                'price' => 'Free Entry',
                'rating' => 4.50,
                'reviews_count' => 150,
                'visitors_count' => 90000,
                'image_url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'status' => 'Active',
            ],
        ];

        foreach ($places as $placeData) {
            $provinceName = $placeData['province_name'];
            unset($placeData['province_name']);

            $provinceId = $provinces[$provinceName] ?? null;
            if ($provinceId) {
                $placeData['province_id'] = $provinceId;
            }

            Place::updateOrCreate(
                ['name' => $placeData['name']],
                $placeData
            );
        }
    }
}
