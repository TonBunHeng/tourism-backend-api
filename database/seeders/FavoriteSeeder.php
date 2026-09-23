<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds for Favorite / Wishlist Places.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->where('status', 'Active')->get();
        if ($users->isEmpty()) {
            $users = User::take(5)->get();
        }

        $places = Place::where('status', 'Active')->get();
        if ($places->isEmpty()) {
            return;
        }

        // Generate a rich set of realistic favorites across destinations
        $favoritesData = [
            ['email' => 'vit.vong@example.com', 'place' => 'Angkor Wat', 'visited' => true, 'saved_date' => '2026-06-15'],
            ['email' => 'vit.vong@example.com', 'place' => 'Bayon Temple', 'visited' => true, 'saved_date' => '2026-06-16'],
            ['email' => 'vit.vong@example.com', 'place' => 'Ta Prohm (Tomb Raider Temple)', 'visited' => true, 'saved_date' => '2026-06-17'],
            ['email' => 'vit.vong@example.com', 'place' => 'Banteay Srei (Citadel of Women)', 'visited' => true, 'saved_date' => '2026-07-02'],
            ['email' => 'vit.vong@example.com', 'place' => 'Royal Palace & Silver Pagoda', 'visited' => false, 'saved_date' => '2026-08-10'],
            ['email' => 'vit.vong@example.com', 'place' => 'Bokor National Park', 'visited' => false, 'saved_date' => '2026-08-20'],

            ['email' => 'ou.sreylin@example.com', 'place' => 'Phnom Sampov & Bat Cave', 'visited' => true, 'saved_date' => '2026-05-10'],
            ['email' => 'ou.sreylin@example.com', 'place' => 'Wat Banan Temple', 'visited' => true, 'saved_date' => '2026-05-12'],
            ['email' => 'ou.sreylin@example.com', 'place' => 'Banteay Srei (Citadel of Women)', 'visited' => true, 'saved_date' => '2026-06-01'],
            ['email' => 'ou.sreylin@example.com', 'place' => 'Kulen Elephant Forest Sanctuary', 'visited' => true, 'saved_date' => '2026-07-15'],
            ['email' => 'ou.sreylin@example.com', 'place' => 'Bou Sra Double-Tier Waterfall', 'visited' => false, 'saved_date' => '2026-08-01'],
            ['email' => 'ou.sreylin@example.com', 'place' => 'Yeak Laom Volcanic Lake', 'visited' => false, 'saved_date' => '2026-08-05'],

            ['email' => 'liam.smith@example.com', 'place' => 'Saracen Bay (Koh Rong Sanloem)', 'visited' => true, 'saved_date' => '2026-07-10'],
            ['email' => 'liam.smith@example.com', 'place' => 'Koh Rong Island Beaches', 'visited' => true, 'saved_date' => '2026-07-12'],
            ['email' => 'liam.smith@example.com', 'place' => 'Chi Phat Community-Based Eco-Tourism', 'visited' => false, 'saved_date' => '2026-08-15'],
            ['email' => 'liam.smith@example.com', 'place' => 'Bokor National Park', 'visited' => true, 'saved_date' => '2026-08-22'],
            ['email' => 'liam.smith@example.com', 'place' => 'Kep Crab Market (Phsar Kdam)', 'visited' => false, 'saved_date' => '2026-09-01'],

            ['email' => 'claire.dubois@example.com', 'place' => 'Royal Palace & Silver Pagoda', 'visited' => true, 'saved_date' => '2026-04-18'],
            ['email' => 'claire.dubois@example.com', 'place' => 'National Museum of Cambodia', 'visited' => true, 'saved_date' => '2026-04-20'],
            ['email' => 'claire.dubois@example.com', 'place' => 'Tuol Sleng Genocide Museum (S-21)', 'visited' => true, 'saved_date' => '2026-04-22'],
            ['email' => 'claire.dubois@example.com', 'place' => 'Angkor Wat', 'visited' => true, 'saved_date' => '2026-05-01'],
            ['email' => 'claire.dubois@example.com', 'place' => 'Sambor Prei Kuk Brick Temples', 'visited' => false, 'saved_date' => '2026-07-14'],
            ['email' => 'claire.dubois@example.com', 'place' => 'Preah Vihear Temple Cliff', 'visited' => false, 'saved_date' => '2026-08-01'],

            ['email' => 'kenji.takahashi@example.com', 'place' => 'Bayon Temple', 'visited' => true, 'saved_date' => '2026-03-10'],
            ['email' => 'kenji.takahashi@example.com', 'place' => 'Ta Prohm (Tomb Raider Temple)', 'visited' => true, 'saved_date' => '2026-03-12'],
            ['email' => 'kenji.takahashi@example.com', 'place' => 'Banteay Srei (Citadel of Women)', 'visited' => true, 'saved_date' => '2026-03-15'],
            ['email' => 'kenji.takahashi@example.com', 'place' => 'Preah Khan Temple', 'visited' => true, 'saved_date' => '2026-03-18'],
            ['email' => 'kenji.takahashi@example.com', 'place' => 'Koh Ker Pyramid Temple', 'visited' => false, 'saved_date' => '2026-07-20'],

            ['email' => 'elena.rostova@example.com', 'place' => 'Kep Crab Market (Phsar Kdam)', 'visited' => true, 'saved_date' => '2026-06-05'],
            ['email' => 'elena.rostova@example.com', 'place' => 'Central Market (Phsar Thmey)', 'visited' => true, 'saved_date' => '2026-06-10'],
            ['email' => 'elena.rostova@example.com', 'place' => 'Kampong Phluk Stilt & Mangrove Village', 'visited' => false, 'saved_date' => '2026-08-12'],

            ['email' => 'minjun.park@example.com', 'place' => 'Yeak Laom Volcanic Lake', 'visited' => true, 'saved_date' => '2026-07-01'],
            ['email' => 'minjun.park@example.com', 'place' => 'Bou Sra Double-Tier Waterfall', 'visited' => true, 'saved_date' => '2026-07-04'],
            ['email' => 'minjun.park@example.com', 'place' => 'Saracen Bay (Koh Rong Sanloem)', 'visited' => false, 'saved_date' => '2026-08-08'],
            ['email' => 'minjun.park@example.com', 'place' => 'Bokor National Park', 'visited' => false, 'saved_date' => '2026-08-25'],

            ['email' => 'cheat.sok@example.com', 'place' => 'Royal Palace & Silver Pagoda', 'visited' => true, 'saved_date' => '2026-02-14'],
            ['email' => 'cheat.sok@example.com', 'place' => 'Bokor National Park', 'visited' => true, 'saved_date' => '2026-04-15'],
            ['email' => 'cheat.sok@example.com', 'place' => 'Kirirom Pine Forest Sanctuary', 'visited' => false, 'saved_date' => '2026-08-18'],
        ];

        foreach ($favoritesData as $fav) {
            $user = User::where('email', $fav['email'])->first();
            $place = Place::where('name', $fav['place'])->first();

            if ($user && $place) {
                Favorite::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'place_id' => $place->id,
                    ],
                    [
                        'visited' => $fav['visited'],
                        'saved_date' => $fav['saved_date'],
                    ]
                );
            }
        }
    }
}
