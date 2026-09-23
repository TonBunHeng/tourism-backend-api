<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\GalleryComment;
use App\Models\GalleryLike;
use App\Models\GalleryMedia;
use App\Models\GalleryMediaTag;
use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    /**
     * Run the database seeds for Media Gallery items, tags, comments, and likes.
     */
    public function run(): void
    {
        $superAdmin = User::where('email', 'admin@tourism.gov.kh')->first() ?? User::first();
        $guide1     = User::where('email', 'sopheaktra@tourism.gov.kh')->first() ?? $superAdmin;
        $guide2     = User::where('email', 'dara.guide@tourism.gov.kh')->first() ?? $superAdmin;
        $guide3     = User::where('email', 'rathana.eco@tourism.gov.kh')->first() ?? $superAdmin;
        $userVit    = User::where('email', 'vit.vong@example.com')->first() ?? $superAdmin;
        $userSreylin= User::where('email', 'ou.sreylin@example.com')->first() ?? $superAdmin;
        $userLiam   = User::where('email', 'liam.smith@example.com')->first() ?? $superAdmin;
        $userKenji  = User::where('email', 'kenji.takahashi@example.com')->first() ?? $superAdmin;
        $userPark   = User::where('email', 'minjun.park@example.com')->first() ?? $superAdmin;

        $categories = Category::pluck('id', 'name')->toArray();
        $cTemple    = $categories['Temple'] ?? 1;
        $cNature    = $categories['Nature'] ?? 2;
        $cPalace    = $categories['Palace'] ?? 3;
        $cCulture   = $categories['Cultural & Heritage'] ?? 4;
        $cIsland    = $categories['Island & Beach'] ?? 5;
        $cEco       = $categories['Eco-Tourism & Wildlife'] ?? 6;
        $cDining    = $categories['Dining'] ?? 7;
        $cArts      = $categories['Arts & Performance'] ?? $cCulture;
        $cAdventure = $categories['Adventure & Tour'] ?? $cNature;

        $pAngkorWat = Place::where('name', 'Angkor Wat')->first();
        $pBayon     = Place::where('name', 'Bayon Temple')->first();
        $pTaProhm   = Place::where('name', 'Ta Prohm (Tomb Raider Temple)')->first();
        $pBanteaySr = Place::where('name', 'Banteay Srei (Citadel of Women)')->first();
        $pRoyalPal  = Place::where('name', 'Royal Palace & Silver Pagoda')->first();
        $pBokor     = Place::where('name', 'Bokor National Park')->first();
        $pSaracen   = Place::where('name', 'Saracen Bay (Koh Rong Sanloem)')->first();
        $pBousra    = Place::where('name', 'Bou Sra Double-Tier Waterfall')->first() ?? Place::where('name', 'like', '%Bou Sra%')->first();
        $pYeakLaom  = Place::where('name', 'Yeak Laom Volcanic Lake')->first();
        $pElephant  = Place::where('name', 'Kulen Elephant Forest Sanctuary')->first();
        $pKepCrab   = Place::where('name', 'Kep Crab Market (Phsar Kdam)')->first();
        $pBambooTr  = Place::where('name', 'Bamboo Train (Norry)')->first();

        $mediaItems = [
            // 1. Angkor Wat Sunrise
            [
                'title' => 'Dawn Reflections over Angkor Wat Lotus Towers',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cTemple,
                'place_id' => $pAngkorWat?->id,
                'file_size' => '4.2 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $guide1->id,
                'views_count' => 3840,
                'likes_count' => 520,
                'status' => 'Published',
                'tags' => ['AngkorWat', 'Sunrise', 'Reflections', 'WorldHeritage', 'Cambodia'],
            ],
            // 2. Bayon Temple Faces
            [
                'title' => 'Serene Stone Smiles of Avalokiteshvara at Bayon',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cTemple,
                'place_id' => $pBayon?->id,
                'file_size' => '3.9 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userKenji->id,
                'views_count' => 2910,
                'likes_count' => 415,
                'status' => 'Published',
                'tags' => ['Bayon', 'KhmerArt', 'AngkorThom', 'StoneCarving', 'Buddhism'],
            ],
            // 3. Ta Prohm Roots
            [
                'title' => 'Giant Strangler Fig Roots Hugging Ta Prohm Corridor',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cTemple,
                'place_id' => $pTaProhm?->id,
                'file_size' => '5.1 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userVit->id,
                'views_count' => 4120,
                'likes_count' => 630,
                'status' => 'Published',
                'tags' => ['TaProhm', 'JungleTemple', 'NatureVsStone', 'TombRaider', 'AncientArchitecture'],
            ],
            // 4. Banteay Srei Carvings
            [
                'title' => 'Intricate Pink Sandstone Bas-Reliefs of Banteay Srei',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1518684079-3c830dcef090?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cTemple,
                'place_id' => $pBanteaySr?->id,
                'file_size' => '3.6 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $guide1->id,
                'views_count' => 2150,
                'likes_count' => 310,
                'status' => 'Published',
                'tags' => ['BanteaySrei', 'PinkSandstone', 'JewelOfAngkor', 'HinduMythology', 'Apsara'],
            ],
            // 5. Royal Palace Phnom Penh
            [
                'title' => 'Golden Spire Throne Hall of the Royal Palace at Sunset',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1508807526345-15e9b5f4eaff?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cPalace,
                'place_id' => $pRoyalPal?->id,
                'file_size' => '4.8 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $guide2->id,
                'views_count' => 3450,
                'likes_count' => 480,
                'status' => 'Published',
                'tags' => ['RoyalPalace', 'PhnomPenh', 'GoldenArchitecture', 'SilverPagoda', 'CambodianRoyalty'],
            ],
            // 6. Saracen Bay Koh Rong Sanloem
            [
                'title' => 'Crystal Turquoise Shallows of Saracen Bay Beach',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cIsland,
                'place_id' => $pSaracen?->id,
                'file_size' => '3.4 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userLiam->id,
                'views_count' => 3950,
                'likes_count' => 590,
                'status' => 'Published',
                'tags' => ['SaracenBay', 'KohRongSanloem', 'TropicalBeach', 'WhiteSand', 'IslandGetaway'],
            ],
            // 7. Bousra Waterfall
            [
                'title' => 'Thunderous Double Cascades of Bousra Waterfall in Rainy Season',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cNature,
                'place_id' => $pBousra?->id,
                'file_size' => '5.6 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $guide3->id,
                'views_count' => 1980,
                'likes_count' => 280,
                'status' => 'Published',
                'tags' => ['BousraWaterfall', 'Mondulkiri', 'HighlandNature', 'Cascades', 'JungleTrek'],
            ],
            // 8. Yeak Laom Volcanic Lake
            [
                'title' => 'Emerald Circular Crater Lake of Yeak Laom from Above',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cNature,
                'place_id' => $pYeakLaom?->id,
                'file_size' => '4.5 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userPark->id,
                'views_count' => 2240,
                'likes_count' => 360,
                'status' => 'Published',
                'tags' => ['YeakLaom', 'Ratanakiri', 'VolcanicLake', 'SacredWaters', 'DronePhotography'],
            ],
            // 9. Bokor Mountain Fog
            [
                'title' => 'Atmospheric Mist Rolling over Bokor Hill Station Plateau',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1518684079-3c830dcef090?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cNature,
                'place_id' => $pBokor?->id,
                'file_size' => '4.1 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userVit->id,
                'views_count' => 1760,
                'likes_count' => 240,
                'status' => 'Published',
                'tags' => ['BokorNationalPark', 'MountainMist', 'Kampot', 'HighlandViews', 'Rainforest'],
            ],
            // 10. Kulen Elephant Sanctuary
            [
                'title' => 'Gentle Giants Grazing in Kulen Forest River Shallows',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1557050543-4d5f4e07ef46?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cEco,
                'place_id' => $pElephant?->id,
                'file_size' => '4.9 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userSreylin->id,
                'views_count' => 2680,
                'likes_count' => 495,
                'status' => 'Published',
                'tags' => ['ElephantForest', 'PhnomKulen', 'WildlifeSanctuary', 'EthicalTourism', 'SiemReap'],
            ],
            // 11. Kep Crab Cooking
            [
                'title' => 'Fresh Ocean Blue Crabs Wok-Tossed with Green Kampot Pepper',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cDining,
                'place_id' => $pKepCrab?->id,
                'file_size' => '3.7 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userSreylin->id,
                'views_count' => 3120,
                'likes_count' => 450,
                'status' => 'Published',
                'tags' => ['KepCrab', 'KampotPepper', 'KhmerGastronomy', 'SeafoodMarket', 'LocalFood'],
            ],
            // 12. 4K Drone Video of Angkor Wat
            [
                'title' => 'Cinematic 4K Aerial Drone Flight across Angkor Park',
                'type' => 'video',
                'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'category_id' => $cTemple,
                'place_id' => $pAngkorWat?->id,
                'file_size' => '48.5 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userPark->id,
                'views_count' => 6520,
                'likes_count' => 840,
                'status' => 'Published',
                'tags' => ['DroneFlight', '4KVideo', 'AerialAngkor', 'MoatView', 'CambodiaFromAbove'],
            ],
            // 13. Drone Video of Koh Rong Archipelago
            [
                'title' => 'Island Paradise Aerial Drone Reel: Koh Rong & Sanloem',
                'type' => 'video',
                'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'category_id' => $cIsland,
                'place_id' => $pSaracen?->id,
                'file_size' => '38.2 MB',
                'dimensions' => '1920x1080',
                'uploaded_by_user_id' => $userPark->id,
                'views_count' => 4820,
                'likes_count' => 610,
                'status' => 'Published',
                'tags' => ['IslandDrone', 'GulfOfThailand', 'SanloemCoral', 'OceanVideo'],
            ],
            // 14. Apsara Celestial Dancer (Draft Media)
            [
                'title' => 'Apsara Celestial Dancer Hand Gestures (Mudra) Study',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cCulture,
                'place_id' => null,
                'file_size' => '2.8 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $guide1->id,
                'views_count' => 140,
                'likes_count' => 18,
                'status' => 'Draft',
                'tags' => ['ApsaraDance', 'ClassicalKhmer', 'Mudra', 'CulturalHeritage'],
            ],
            // 15. Mekong Floating Houses (Draft Media)
            [
                'title' => 'Mekong River Floating Fish Farming Community at Dusk',
                'type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=1600&q=80',
                'category_id' => $cCulture,
                'place_id' => null,
                'file_size' => '3.1 MB',
                'dimensions' => '1920x1080',
                'uploaded_by_user_id' => $guide2->id,
                'views_count' => 85,
                'likes_count' => 9,
                'status' => 'Draft',
                'tags' => ['MekongRiver', 'FloatingVillage', 'RiverLife', 'Sunset'],
            ],
            // 16. Royal Ballet of Cambodia (Video)
            [
                'title' => 'Royal Ballet of Cambodia - Sacred Apsara Dance in Angkor Courtyard',
                'type' => 'video',
                'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'category_id' => $cArts,
                'place_id' => $pAngkorWat?->id,
                'file_size' => '32.4 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $guide1->id,
                'views_count' => 5120,
                'likes_count' => 720,
                'status' => 'Published',
                'tags' => ['RoyalBallet', 'ApsaraDance', 'KhmerCulture', 'UNESCOHeritage', 'AngkorWat', 'LivePerformance'],
            ],
            // 17. Bousra Waterfall Canopy Drone Sweep (Video)
            [
                'title' => 'Misty Cascades of Bousra Waterfall - 4K Drone Canopy Sweep',
                'type' => 'video',
                'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'category_id' => $cNature,
                'place_id' => $pBousra?->id,
                'file_size' => '44.1 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userPark->id,
                'views_count' => 3890,
                'likes_count' => 560,
                'status' => 'Published',
                'tags' => ['BousraWaterfall', 'Mondulkiri', 'DroneCanopy', 'Waterfalls', 'HighlandNature', 'CambodiaWild'],
            ],
            // 18. Golden Hour Sunset Cruise along Phnom Penh Riverfront (Video)
            [
                'title' => 'Golden Hour Sunset Cruise along Sisowath Quay & Tonle Sap Confluence',
                'type' => 'video',
                'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'category_id' => $cPalace,
                'place_id' => $pRoyalPal?->id,
                'file_size' => '28.6 MB',
                'dimensions' => '1920x1080',
                'uploaded_by_user_id' => $guide2->id,
                'views_count' => 4350,
                'likes_count' => 490,
                'status' => 'Published',
                'tags' => ['PhnomPenh', 'RiverfrontCruise', 'MekongSunset', 'RoyalPalace', 'SisowathQuay', 'CityLights'],
            ],
            // 19. Battambang Bamboo Train Countryside Ride (Video)
            [
                'title' => 'Speeding on the Bamboo Train through Battambang Rice Paddies',
                'type' => 'video',
                'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyBlazes.mp4',
                'category_id' => $cAdventure,
                'place_id' => $pBambooTr?->id,
                'file_size' => '26.8 MB',
                'dimensions' => '1920x1080',
                'uploaded_by_user_id' => $userLiam->id,
                'views_count' => 3420,
                'likes_count' => 430,
                'status' => 'Published',
                'tags' => ['BambooTrain', 'Battambang', 'Norry', 'RailwayAdventure', 'CountrysideCambodia', 'HeritageTrack'],
            ],
            // 20. Kulen Elephant Forest Bathing (Video)
            [
                'title' => 'Elephant Bathing & River Splash in Kulen Mountain Rainforest',
                'type' => 'video',
                'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4',
                'category_id' => $cEco,
                'place_id' => $pElephant?->id,
                'file_size' => '35.7 MB',
                'dimensions' => '3840x2160',
                'uploaded_by_user_id' => $userSreylin->id,
                'views_count' => 4780,
                'likes_count' => 680,
                'status' => 'Published',
                'tags' => ['ElephantForest', 'PhnomKulen', 'EthicalWildlife', 'ElephantRiver', 'EcoTourism', 'SiemReap'],
            ],
        ];

        foreach ($mediaItems as $mData) {
            $tags = $mData['tags'] ?? [];
            unset($mData['tags']);

            $media = GalleryMedia::updateOrCreate(
                ['title' => $mData['title']],
                $mData
            );

            foreach ($tags as $tag) {
                GalleryMediaTag::firstOrCreate([
                    'media_id' => $media->id,
                    'tag_name' => $tag,
                ]);
            }

            // Seed engaging user comments
            if ($media->status === 'Published') {
                GalleryComment::firstOrCreate(
                    ['gallery_media_id' => $media->id, 'user_id' => $userVit->id],
                    [
                        'comment' => 'Spectacular light and composition! Captures the timeless spiritual majesty of Cambodia perfectly.',
                        'created_at' => now()->subDays(rand(2, 20)),
                    ]
                );

                GalleryComment::firstOrCreate(
                    ['gallery_media_id' => $media->id, 'user_id' => $userSreylin->id],
                    [
                        'comment' => 'Proud to see our Cambodian heritage showcased with such beauty and respect.',
                        'created_at' => now()->subDays(rand(1, 10)),
                    ]
                );

                // Seed likes
                GalleryLike::firstOrCreate(['gallery_media_id' => $media->id, 'user_id' => $userVit->id]);
                GalleryLike::firstOrCreate(['gallery_media_id' => $media->id, 'user_id' => $userSreylin->id]);
                GalleryLike::firstOrCreate(['gallery_media_id' => $media->id, 'user_id' => $userLiam->id]);
            }
        }
    }
}
