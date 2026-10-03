<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Place;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds for Reviews, Replies, and Review Images across Places and Businesses.
     */
    public function run(): void
    {
        $guideSiemReap = User::where('email', 'sopheaktra@tourism.gov.kh')->first() ?? User::first();
        $guidePhnomPenh= User::where('email', 'dara.guide@tourism.gov.kh')->first() ?? $guideSiemReap;
        $guideMondulk  = User::where('email', 'rathana.eco@tourism.gov.kh')->first() ?? $guideSiemReap;

        $ownerSokha    = User::where('email', 'owner@angkor-restaurant.com')->first() ?? User::first();
        $ownerMalis    = User::where('email', 'malis.keo@khamisdining.kh')->first() ?? $ownerSokha;
        $ownerDavid    = User::where('email', 'david.miller@shinta-eco.com')->first() ?? $ownerSokha;
        $ownerLaurent  = User::where('email', 'jp.laurent@boutiquekhmer.com')->first() ?? $ownerSokha;
        $ownerThida    = User::where('email', 'thida.bodia@wellness.kh')->first() ?? $ownerSokha;

        $userVit       = User::where('email', 'vit.vong@example.com')->first() ?? User::first();
        $userSreylin   = User::where('email', 'ou.sreylin@example.com')->first() ?? User::first();
        $userLiam      = User::where('email', 'liam.smith@example.com')->first() ?? User::first();
        $userClaire    = User::where('email', 'claire.dubois@example.com')->first() ?? User::first();
        $userKenji     = User::where('email', 'kenji.takahashi@example.com')->first() ?? User::first();
        $userElena     = User::where('email', 'elena.rostova@example.com')->first() ?? User::first();
        $userChen      = User::where('email', 'chen.wei@example.com')->first() ?? User::first();
        $userPark      = User::where('email', 'minjun.park@example.com')->first() ?? User::first();
        $userCheat     = User::where('email', 'cheat.sok@example.com')->first() ?? User::first();

        // Places
        $pAngkorWat = Place::where('name', 'Angkor Wat')->first();
        $pBayon     = Place::where('name', 'Bayon Temple')->first();
        $pTaProhm   = Place::where('name', 'Ta Prohm (Tomb Raider Temple)')->first();
        $pBanteaySr = Place::where('name', 'Banteay Srei (Citadel of Women)')->first();
        $pRoyalPal  = Place::where('name', 'Royal Palace & Silver Pagoda')->first();
        $pTuolSleng = Place::where('name', 'Tuol Sleng Genocide Museum (S-21)')->first();
        $pBokor     = Place::where('name', 'Bokor National Park')->first();
        $pSaracen   = Place::where('name', 'Saracen Bay (Koh Rong Sanloem)')->first();
        $pKepCrab   = Place::where('name', 'Kep Crab Market (Phsar Kdam)')->first();
        $pBousra    = Place::where('name', 'Bou Sra Double-Tier Waterfall')->first() ?? Place::where('name', 'like', '%Bou Sra%')->first();
        $pYeakLaom  = Place::where('name', 'Yeak Laom Volcanic Lake')->first();
        $pElephant  = Place::where('name', 'Kulen Elephant Forest Sanctuary')->first();

        // Businesses
        $bHeritage  = Business::where('slug', 'angkor-heritage-restaurant-lounge')->first();
        $bMalis     = Business::where('slug', 'malis-restaurant-phnom-penh')->first();
        $bDamnak    = Business::where('slug', 'cuisine-wat-damnak-siem-reap')->first();
        $bPepper    = Business::where('slug', 'kampot-pepper-plantation-eco-lodge')->first();
        $bShinta    = Business::where('slug', 'shinta-mani-wild-bensley-collection')->first();
        $bSongSaa   = Business::where('slug', 'song-saa-private-island-resort')->first();
        $bRaffles   = Business::where('slug', 'raffles-hotel-le-royal-phnom-penh')->first();
        $bBodia     = Business::where('slug', 'bodia-spa-botanical-wellness')->first();
        $bPhare     = Business::where('slug', 'phare-the-cambodian-circus')->first();

        $reviews = [
            // ==================================================================
            // 1. PLACE REVIEWS (APPROVED)
            // ==================================================================
            [
                'user_id' => $userVit->id,
                'place_id' => $pAngkorWat?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Breathtaking Sunrise Experience of a Lifetime',
                'comment' => 'Watching the first rays of dawn illuminate the five lotus towers of Angkor Wat mirrored in the northern reflection pond was pure magic. Hire a certified guide to unlock the incredible bas-relief epics like the Churning of the Ocean of Milk.',
                'likes_count' => 64,
                'dislikes_count' => 1,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=800&q=80'],
                'reply' => ['user_id' => $guideSiemReap->id, 'comment' => 'Arkoun chreun VIT Vong! Hearing your appreciation of our Khmer heritage inspires us to continue sharing Angkor\'s history with travelers worldwide.'],
            ],
            [
                'user_id' => $userKenji->id,
                'place_id' => $pBayon?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Mystical Smiles at Every Turn',
                'comment' => 'Bayon is architecturally unlike anything else on Earth. The 216 giant smiling stone faces gazing down from 54 Gothic towers give you chills of wonder. Early morning light around 8 AM creates dramatic shadows on the bas-relief carvings.',
                'likes_count' => 48,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80'],
                'reply' => ['user_id' => $guideSiemReap->id, 'comment' => 'Thank you Kenji! The morning serenity at Bayon is truly spiritual.'],
            ],
            [
                'user_id' => $userClaire->id,
                'place_id' => $pTaProhm?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Jungle and Stone in Eternal Embrace',
                'comment' => 'The harmony between nature and ancient architecture here is poetic. Giant silk-cotton trees wrapping their colossal silver roots around the collapsed doorways look like living sculptures.',
                'likes_count' => 37,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80'],
                'reply' => null,
            ],
            [
                'user_id' => $userKenji->id,
                'place_id' => $pBanteaySr?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Jewel of Classical Khmer Stone Art',
                'comment' => 'The pink sandstone carving depth and precision at Banteay Srei is sublime. The celestial Devata carvings look as sharp and graceful as if sculpted yesterday.',
                'likes_count' => 29,
                'dislikes_count' => 1,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1518684079-3c830dcef090?auto=format&fit=crop&w=800&q=80'],
                'reply' => null,
            ],
            [
                'user_id' => $userChen->id,
                'place_id' => $pRoyalPal?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Gleaming Royal Architecture in Phnom Penh',
                'comment' => 'The Throne Hall and Silver Pagoda floor paved with 5,329 solid silver tiles took my breath away. Don\'t miss the life-sized solid gold Buddha encrusted with over 9,500 diamonds.',
                'likes_count' => 41,
                'dislikes_count' => 2,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1508807526345-15e9b5f4eaff?auto=format&fit=crop&w=800&q=80'],
                'reply' => ['user_id' => $guidePhnomPenh->id, 'comment' => 'We are delighted you appreciated the royal regalia and national sacred treasures, Chen!'],
            ],
            [
                'user_id' => $userClaire->id,
                'place_id' => $pTuolSleng?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Deeply Moving, Essential History to Remember',
                'comment' => 'A somber, dignified memorial. The audio guide is exceptionally produced with survivor testimonies. An emotional but necessary visit to understand the courage and strength of the Cambodian people.',
                'likes_count' => 56,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => [],
                'reply' => ['user_id' => $guidePhnomPenh->id, 'comment' => 'Thank you Claire for paying respect to the victims and honoring Cambodian remembrance.'],
            ],
            [
                'user_id' => $userLiam->id,
                'place_id' => $pSaracen?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Pristine White Sands & Glowing Plankton',
                'comment' => 'Saracen Bay is absolute heaven on earth. Calm, crystal-clear water with no ocean waves, powdery white sand that squeaks underfoot, and swimming in luminous bioluminescent plankton at night was unbelievable.',
                'likes_count' => 52,
                'dislikes_count' => 1,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80'],
                'reply' => null,
            ],
            [
                'user_id' => $userElena->id,
                'place_id' => $pKepCrab?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Best Crab in Southeast Asia!',
                'comment' => 'Crabs plucked right out of the ocean before your eyes, tossed in a sizzling wok with fresh clusters of green Kampot pepper, garlic, and spring onion. Unbeatable coastal dining memory!',
                'likes_count' => 45,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80'],
                'reply' => null,
            ],
            [
                'user_id' => $userPark->id,
                'place_id' => $pYeakLaom?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Sacred Volcanic Crater Lake Oasis',
                'comment' => 'The circular shape of Yeak Laom in the middle of dense jungle is stunning. The volcanic water is remarkably clear and refreshing for swimming. The indigenous Bunong community maintains it impeccably.',
                'likes_count' => 33,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80'],
                'reply' => ['user_id' => $guideMondulk->id, 'comment' => 'We are glad you respected the sacred spirit of the lake. Orkun chreun!'],
            ],
            [
                'user_id' => $userSreylin->id,
                'place_id' => $pElephant?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Truly Ethical and Loving Elephant Care',
                'comment' => 'No riding, no chains, no performances. Just watching elderly retired elephants roam freely through 1,100 acres of rainforest, splashing in the stream. Heartwarming project that deserves everyone\'s support.',
                'likes_count' => 68,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1557050543-4d5f4e07ef46?auto=format&fit=crop&w=800&q=80'],
                'reply' => null,
            ],

            // ==================================================================
            // 2. BUSINESS REVIEWS (APPROVED)
            // ==================================================================
            [
                'user_id' => $userVit->id,
                'place_id' => null,
                'business_id' => $bHeritage?->id,
                'rating' => 5,
                'title' => 'Exquisite Royal Amok & Live Apsara Elegance',
                'comment' => 'The Fish Amok steamed in banana leaves with lemongrass and kaffir lime was sublime. The live classical Apsara dance performance in the courtyard made the dinner feel like a royal banquet.',
                'likes_count' => 31,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80'],
                'reply' => ['user_id' => $ownerSokha->id, 'comment' => 'Thank you VIT Vong! It was an absolute honour serving you our royal recipes.'],
            ],
            [
                'user_id' => $userElena->id,
                'place_id' => null,
                'business_id' => $bMalis?->id,
                'rating' => 5,
                'title' => 'Master Chef Luu Meng\'s Culinary Triumph',
                'comment' => 'Malis sets the gold standard for Cambodian fine dining. The Takeo river lobster with Kampot pepper and the morning glory with crispy pork belly were Michelin-level perfection. The courtyard ambience is unmatched.',
                'likes_count' => 44,
                'dislikes_count' => 1,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80'],
                'reply' => ['user_id' => $ownerMalis->id, 'comment' => 'Orkun chreun Elena! Our culinary team pours deep pride into reviving and honoring Cambodia\'s gastronomic heritage.'],
            ],
            [
                'user_id' => $userClaire->id,
                'place_id' => null,
                'business_id' => $bDamnak?->id,
                'rating' => 5,
                'title' => 'An Unforgettable Foraged Culinary Journey',
                'comment' => 'Every dish on the 6-course degustation tells a story of Cambodian countryside biodiversity. Wild waterlily stems, fermented river fish, and wild sour tamarind balanced with French technique.',
                'likes_count' => 28,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => [],
                'reply' => null,
            ],
            [
                'user_id' => $userLiam->id,
                'place_id' => null,
                'business_id' => $bPepper?->id,
                'rating' => 5,
                'title' => 'Fascinating Farm Tour & Wonderful Mountain Bungalow',
                'comment' => 'We learned the true difference between black, red, and white pepper. The bungalows look straight out at Bokor mountain, and the farm dinners cooked with freshly harvested pepper are fantastic.',
                'likes_count' => 22,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'],
                'reply' => ['user_id' => $ownerLaurent->id, 'comment' => 'Merci Liam! We love sharing the secrets of genuine Kampot pepper with travelers.'],
            ],
            [
                'user_id' => $userPark->id,
                'place_id' => null,
                'business_id' => $bShinta?->id,
                'rating' => 5,
                'title' => 'Ziplining into Paradise: The Greatest Glamping on Earth',
                'comment' => 'Ziplining over a rainforest waterfall right to the bar for check-in set the stage for an unforgettable 3-night stay. The conservation work protecting the Cardamom forest makes every dollar well spent.',
                'likes_count' => 59,
                'dislikes_count' => 1,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80'],
                'reply' => ['user_id' => $ownerDavid->id, 'comment' => 'Thank you Min-jun! Protecting the wild wilderness corridor alongside guests like you is our life passion.'],
            ],
            [
                'user_id' => $userChen->id,
                'place_id' => null,
                'business_id' => $bRaffles?->id,
                'rating' => 5,
                'title' => 'Colonial Elegance & Legendary Elephant Bar',
                'comment' => 'The heritage charm of Raffles Le Royal is unbeatable in Phnom Penh. The afternoon tea and signature Femme Fatale cocktail at the Elephant Bar are legendary.',
                'likes_count' => 35,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => [],
                'reply' => null,
            ],
            [
                'user_id' => $userSreylin->id,
                'place_id' => null,
                'business_id' => $bBodia?->id,
                'rating' => 5,
                'title' => 'Heavenly Herbal Compress Massage',
                'comment' => 'After walking 25,000 steps around Angkor Wat, the 90-minute Bodia herbal compress massage revived my body completely. The lemongrass and ginger aromas are pure relaxation.',
                'likes_count' => 26,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => [],
                'reply' => ['user_id' => $ownerThida->id, 'comment' => 'Thank you Sreylin! It is our joy to provide rejuvenation for temple travelers.'],
            ],
            [
                'user_id' => $userVit->id,
                'place_id' => null,
                'business_id' => $bPhare?->id,
                'rating' => 5,
                'title' => 'Pure Energy, Passion, and Heart',
                'comment' => 'Phare Circus is an absolute must-see in Siem Reap. The young performers display world-class acrobatics while telling touching stories of Cambodian survival and optimism. Deserves 10 stars!',
                'likes_count' => 61,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Approved',
                'images' => ['https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=800&q=80'],
                'reply' => null,
            ],

            // ==================================================================
            // 3. PENDING REVIEWS (For Admin Moderation Queue in tourism-admin)
            // ==================================================================
            [
                'user_id' => $userLiam->id,
                'place_id' => $pBokor?->id,
                'business_id' => null,
                'rating' => 4,
                'title' => 'Misty Ghost Town with Incredible Ocean Vistas',
                'comment' => 'The old casino ruins wrapped in dense cloud cover give an eerie atmosphere. The new highway up the mountain is smooth and scenic. Worth the trip from Kampot!',
                'likes_count' => 0,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Pending',
                'images' => [],
                'reply' => null,
            ],
            [
                'user_id' => $userElena->id,
                'place_id' => null,
                'business_id' => $bHeritage?->id,
                'rating' => 4,
                'title' => 'Delicious Food, Slightly Busy Service',
                'comment' => 'The beef lok lak and duck curry were bursting with flavour. However, we waited about 20 minutes for our table even with reservations during the water festival rush.',
                'likes_count' => 0,
                'dislikes_count' => 0,
                'is_verified' => false,
                'status' => 'Pending',
                'images' => [],
                'reply' => null,
            ],
            [
                'user_id' => $userCheat->id,
                'place_id' => $pSaracen?->id,
                'business_id' => null,
                'rating' => 5,
                'title' => 'Peaceful island retreat during off-peak season',
                'comment' => 'Saracen bay during October had few tourists and crystal clear waters. Highly recommended for couples seeking quiet tropical solitude.',
                'likes_count' => 0,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Pending',
                'images' => [],
                'reply' => null,
            ],
            [
                'user_id' => $userChen->id,
                'place_id' => null,
                'business_id' => $bPepper?->id,
                'rating' => 4,
                'title' => 'Great informative pepper tasting',
                'comment' => 'The guide showed all production steps in clear English. Salted green peppercorns are a must-buy souvenir!',
                'likes_count' => 0,
                'dislikes_count' => 0,
                'is_verified' => true,
                'status' => 'Pending',
                'images' => [],
                'reply' => null,
            ],

            // ==================================================================
            // 4. FLAGGED REVIEWS (For Admin Inspection & Dispute Resolution)
            // ==================================================================
            [
                'user_id' => $userLiam->id,
                'place_id' => $pAngkorWat?->id,
                'business_id' => null,
                'rating' => 2,
                'title' => 'Too many aggressive taxi and souvenir touts near outer parking',
                'comment' => 'The temple itself is glorious, but the outer parking vendors were following tourists aggressively for 10 minutes pushing shirts and guidebooks. Park authorities need to manage the parking perimeter better.',
                'likes_count' => 12,
                'dislikes_count' => 4,
                'is_verified' => true,
                'status' => 'Flagged',
                'images' => [],
                'reply' => null,
            ],
            [
                'user_id' => $userElena->id,
                'place_id' => null,
                'business_id' => $bDamnak?->id,
                'rating' => 1,
                'title' => 'CHECK OUT MY TRAVEL BLOG DISCOUNT CODE FOR 50% OFF',
                'comment' => 'Visit my discount site http://freetraveldiscounts.fake/cambodia to book tickets with 50% promo code! Restaurant was okay.',
                'likes_count' => 0,
                'dislikes_count' => 15,
                'is_verified' => false,
                'status' => 'Flagged',
                'images' => [],
                'reply' => null,
            ],

            // ==================================================================
            // 5. REJECTED REVIEWS (Demonstrates Admin Rejection of Spam)
            // ==================================================================
            [
                'user_id' => $userChen->id,
                'place_id' => $pTaProhm?->id,
                'business_id' => null,
                'rating' => 1,
                'title' => 'Spam Promotional Link Casino Games',
                'comment' => 'Best online gaming casino bonus 100% deposit match link http://spam-casino.example.com',
                'likes_count' => 0,
                'dislikes_count' => 20,
                'is_verified' => false,
                'status' => 'Rejected',
                'images' => [],
                'reply' => null,
            ],
        ];

        $createdDates = [
            '2026-01-15 09:30:00',
            '2026-01-22 14:15:00',
            '2026-02-10 11:20:00',
            '2026-02-28 16:45:00',
            '2026-03-05 10:10:00',
            '2026-03-18 13:00:00',
            '2026-04-02 08:40:00',
            '2026-04-14 17:30:00',
            '2026-05-01 12:00:00',
            '2026-05-20 15:25:00',
            '2026-06-08 09:15:00',
            '2026-06-25 18:00:00',
            '2026-07-04 11:50:00',
            '2026-07-19 14:10:00',
            '2026-08-03 10:30:00',
            '2026-08-16 16:20:00',
            '2026-08-28 13:45:00',
            '2026-09-02 09:00:00',
            '2026-09-12 11:15:00',
            '2026-09-20 15:00:00',
            '2026-09-25 17:40:00',
            '2026-09-29 10:05:00',
        ];

        foreach ($reviews as $idx => $rev) {
            $images = $rev['images'] ?? [];
            $reply  = $rev['reply'] ?? null;
            unset($rev['images'], $rev['reply']);

            // Avoid updating identical record constraint if both null
            if (!$rev['place_id'] && !$rev['business_id']) {
                continue;
            }

            $dateTimestamp = $createdDates[$idx % count($createdDates)];
            $rev['created_at'] = $dateTimestamp;
            $rev['updated_at'] = $dateTimestamp;

            $searchCriteria = [
                'user_id' => $rev['user_id'],
            ];
            if ($rev['place_id']) {
                $searchCriteria['place_id'] = $rev['place_id'];
            }
            if ($rev['business_id']) {
                $searchCriteria['business_id'] = $rev['business_id'];
            }

            $review = Review::updateOrCreate(
                $searchCriteria,
                $rev
            );

            // Seed review images
            foreach ($images as $imgUrl) {
                ReviewImage::firstOrCreate([
                    'review_id' => $review->id,
                    'image_url' => $imgUrl,
                ]);
            }

            // Seed official review reply
            if ($reply) {
                ReviewReply::firstOrCreate(
                    ['review_id' => $review->id],
                    [
                        'user_id' => $reply['user_id'],
                        'comment' => $reply['comment'],
                    ]
                );
            }
        }
    }
}
