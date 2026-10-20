<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Tour;
use Illuminate\Database\Seeder;

class TourSeeder extends Seeder
{
    /**
     * Run the database seeds for Cambodian tour packages.
     */
    public function run(): void
    {
        $provSiemReap = Province::where('name', 'like', '%Siem Reap%')->first();
        $provPhnomPenh = Province::where('name', 'like', '%Phnom Penh%')->first();
        $provSihanouk = Province::where('name', 'like', '%Sihanouk%')->first();
        $provKampot = Province::where('name', 'like', '%Kampot%')->first();
        $provMondulkiri = Province::where('name', 'like', '%Mondulkiri%')->first();
        $provBattambang = Province::where('name', 'like', '%Battambang%')->first();

        $tours = [
            [
                'title' => 'Angkor Wat Sunrise & Sacred Grand Circuit',
                'slug' => 'angkor-wat-sunrise-sacred-grand-circuit',
                'destination' => 'Siem Reap',
                'province_id' => $provSiemReap?->id,
                'province' => 'Siem Reap',
                'duration' => '3 Days / 2 Nights',
                'duration_days' => 3,
                'price' => 185.00,
                'currency' => 'USD',
                'rating' => 4.96,
                'reviews_count' => 342,
                'badge' => 'Best Seller',
                'category' => 'Cultural & Heritage',
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'description' => 'Experience an unforgettable sunrise over the lotus ponds of Angkor Wat, followed by Bayon stone faces, Ta Prohm tree root ruins, and Banteay Srei pink sandstone carvings with a certified English-speaking Khmer historian.',
                'highlights' => [
                    'Angkor Wat VIP sunrise access with photography guide',
                    'Ta Prohm (Tomb Raider) overgrown jungle temple',
                    'Bayon Temple 216 giant smiling stone faces',
                    'Authentic Khmer banquet lunch in rural village',
                    'AC private transportation & bottled chilled water',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Sunrise Angkor Wat & Giant Smiling Bayon'],
                    ['day' => 2, 'title' => 'Ta Prohm, Banteay Srei & Preah Khan'],
                    ['day' => 3, 'title' => 'Tonle Sap Floating Village & Sunset Boat'],
                ],
                'included' => ['Certified Tour Guide', 'AC Minivan', 'Cold towels & water', 'Hotel pickup & drop-off'],
                'max_travelers' => 10,
                'group_size' => 'Max 10 guests',
                'difficulty' => 'Easy / Moderate Walking',
                'status' => 'active',
            ],
            [
                'title' => 'Phnom Penh Royal Heritage & Sunset Mekong Cruise',
                'slug' => 'phnom-penh-royal-heritage-sunset-mekong-cruise',
                'destination' => 'Phnom Penh',
                'province_id' => $provPhnomPenh?->id,
                'province' => 'Phnom Penh',
                'duration' => '2 Days / 1 Night',
                'duration_days' => 2,
                'price' => 120.00,
                'currency' => 'USD',
                'rating' => 4.89,
                'reviews_count' => 198,
                'badge' => 'Cultural Immersion',
                'category' => 'City & Heritage',
                'image_url' => 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80',
                'description' => 'Explore the grand architecture of the Royal Palace and Silver Pagoda, visit the National Museum, explore Russian Market crafts, and unwind on a traditional wooden boat cruising the junction of the Mekong and Tonle Sap rivers.',
                'highlights' => [
                    'Royal Palace & Emerald Buddha Silver Pagoda',
                    'National Museum of Cambodia ancient Angkor sculptures',
                    'Central Market & Russian Market souvenir shopping',
                    'Two-hour golden hour cruise on the Mekong river',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Royal Palace, Silver Pagoda & Riverside Stroll'],
                    ['day' => 2, 'title' => 'National Museum, Markets & Sunset River Cruise'],
                ],
                'included' => ['Licensed City Guide', 'Private Tuk-Tuk / Van', 'All Entrance Fees', 'Cruise Refreshments'],
                'max_travelers' => 8,
                'group_size' => 'Max 8 guests',
                'difficulty' => 'Easy City Walk',
                'status' => 'active',
            ],
            [
                'title' => 'Koh Rong Sanloem Reef & Bioluminescence',
                'slug' => 'koh-rong-sanloem-reef-bioluminescence',
                'destination' => 'Koh Rong',
                'province_id' => $provSihanouk?->id,
                'province' => 'Preah Sihanouk',
                'duration' => '3 Days / 2 Nights',
                'duration_days' => 3,
                'price' => 210.00,
                'currency' => 'USD',
                'rating' => 4.93,
                'reviews_count' => 156,
                'badge' => 'Tropical Island',
                'category' => 'Beach & Marine',
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'description' => 'Bask in crystal turquoise waters at Saracen Bay, snorkel vibrant offshore coral reefs, and experience the mystical nighttime glow of bioluminescent phytoplankton under the starlit Cambodian sky.',
                'highlights' => [
                    'Round-trip high-speed catamaran ferry transfers',
                    'Guided coral garden snorkeling with equipment included',
                    'Night swimming tour with glowing bioluminescent plankton',
                    'Sunset cocktail overlooking lazy hammock beaches',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Catamaran to Saracen Bay & Beachfront Check-in'],
                    ['day' => 2, 'title' => 'Island Hopping Snorkel & Bioluminescent Night Swim'],
                    ['day' => 3, 'title' => 'Lazy Hammock Morning & Return Boat'],
                ],
                'included' => ['Catamaran Tickets', 'Snorkel Equipment', 'Night Plankton Tour Boat', 'Beach Resort Host'],
                'max_travelers' => 12,
                'group_size' => 'Small Group',
                'difficulty' => 'Swimming Ability Helpful',
                'status' => 'active',
            ],
            [
                'title' => 'Kampot Pepper & Bokor Mountain Mist Expedition',
                'slug' => 'kampot-pepper-bokor-mountain-mist-expedition',
                'destination' => 'Kampot',
                'province_id' => $provKampot?->id,
                'province' => 'Kampot',
                'duration' => '2 Days / 1 Night',
                'duration_days' => 2,
                'price' => 95.00,
                'currency' => 'USD',
                'rating' => 4.88,
                'reviews_count' => 114,
                'badge' => 'Eco & Culinary',
                'category' => 'Eco & Nature',
                'image_url' => 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=800&q=80',
                'description' => 'Taste world-famous GI certified Kampot peppercorns straight from organic vine estates, kayak through the serene Green Cathedral river loop, and ascend Bokor Mountain to explore historical ruins shrouded in mountain mist.',
                'highlights' => [
                    'Organic pepper plantation tour & gourmet pepper tasting',
                    'Green Cathedral mangrove kayak trail at golden hour',
                    'Bokor Hill Station, Old Catholic Church & Sampov Pram Pagoda',
                    'Fresh Kep crab market dining with green peppercorn glaze',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Organic Pepper Estate & Sunset River Kayaking'],
                    ['day' => 2, 'title' => 'Bokor National Park High Peak & Kep Crab Market'],
                ],
                'included' => ['Certified Eco Guide', 'Kayak Rental', 'Pepper Tasting', 'AC Transportation'],
                'max_travelers' => 10,
                'group_size' => 'Max 10 guests',
                'difficulty' => 'Easy / Leisurely',
                'status' => 'active',
            ],
            [
                'title' => 'Mondulkiri Elephant Sanctuary & Highland Jungle Trek',
                'slug' => 'mondulkiri-elephant-sanctuary-highland-jungle-trek',
                'destination' => 'Mondulkiri',
                'province_id' => $provMondulkiri?->id,
                'province' => 'Mondulkiri',
                'duration' => '3 Days / 2 Nights',
                'duration_days' => 3,
                'price' => 245.00,
                'currency' => 'USD',
                'rating' => 4.98,
                'reviews_count' => 230,
                'badge' => 'Eco Wilderness',
                'category' => 'Wildlife & Trekking',
                'image_url' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=800&q=80',
                'description' => 'Trek into the lush valleys of Sen Monorom to walk alongside rescued Asian elephants in their natural forest habitat, swim beneath Bou Sra waterfall, and experience traditional Bunong indigenous culture.',
                'highlights' => [
                    'Ethical elephant valley walk & river bathing (no riding)',
                    'Bou Sra double-tier massive cascade waterfall',
                    'Indigenous Bunong community cultural dinner & rice wine',
                    'Mondulkiri organic highland coffee plantation visit',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Sen Monorom Arrival & Pine Tree Ridge Sunset'],
                    ['day' => 2, 'title' => 'Full Day Ethical Elephant Observation & Forest River'],
                    ['day' => 3, 'title' => 'Bou Sra Giant Waterfall & Organic Coffee Tasting'],
                ],
                'included' => ['Sanctuary Ranger Guide', 'Ethical Elephant Project Contribution', 'Jungle Lunch', 'Forest Transfers'],
                'max_travelers' => 8,
                'group_size' => 'Max 8 guests',
                'difficulty' => 'Moderate Hiking',
                'status' => 'active',
            ],
            [
                'title' => 'Battambang Heritage, Bamboo Train & Bat Cave Sunset',
                'slug' => 'battambang-heritage-bamboo-train-bat-cave-sunset',
                'destination' => 'Battambang',
                'province_id' => $provBattambang?->id,
                'province' => 'Battambang',
                'duration' => '2 Days / 1 Night',
                'duration_days' => 2,
                'price' => 85.00,
                'currency' => 'USD',
                'rating' => 4.91,
                'reviews_count' => 175,
                'badge' => 'Top Value',
                'category' => 'Culture & Countryside',
                'image_url' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=800&q=80',
                'description' => 'Ride the exhilarating historic Norry (Bamboo Train) through scenic rice paddies, tour French colonial shophouses, and witness millions of bats streaming across the dusk sky at Phnom Sampov cliff caves.',
                'highlights' => [
                    'Exciting Bamboo Train (Norry) ride along rustic rails',
                    'Phnom Sampov million-bat dusk exodus spectacle',
                    'Ek Phnom 11th century temple & rice paper making village',
                    'Phare Ponleu Selpak arts school performance ticket',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'French Quarter, Bamboo Train & Bat Cave Sunset'],
                    ['day' => 2, 'title' => 'Ek Phnom Temple, Local Craft Villages & Phare Circus'],
                ],
                'included' => ['Local Tuk-Tuk Driver Guide', 'Bamboo Train Ticket', 'Bat Cave Viewpoint Access', 'Cold Drinks'],
                'max_travelers' => 6,
                'group_size' => 'Max 6 guests',
                'difficulty' => 'Easy / Leisurely',
                'status' => 'active',
            ],
        ];

        foreach ($tours as $tourData) {
            Tour::updateOrCreate(
                ['slug' => $tourData['slug']],
                $tourData
            );
        }
    }
}
