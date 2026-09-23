<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds for all tourism categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Temple',
                'description' => 'Ancient religious monuments, Angkorian temples, pagodas, and sacred UNESCO World Heritage sanctuaries.',
                'color' => '#8B5CF6',
                'status' => 'Active',
            ],
            [
                'name' => 'Historical Site',
                'description' => 'Historic monuments, archaeological ruins, colonial architecture, and national cultural legacy sites.',
                'color' => '#F59E0B',
                'status' => 'Active',
            ],
            [
                'name' => 'Palace',
                'description' => 'Royal palaces, throne halls, pavilions, and official royal state residences.',
                'color' => '#EF4444',
                'status' => 'Active',
            ],
            [
                'name' => 'Nature',
                'description' => 'National parks, waterfalls, mountain ranges, lush valleys, and pristine natural landscapes.',
                'color' => '#10B981',
                'status' => 'Active',
            ],
            [
                'name' => 'Museum',
                'description' => 'Fine arts galleries, historical artifact exhibitions, national archives, and memorial museums.',
                'color' => '#3B82F6',
                'status' => 'Active',
            ],
            [
                'name' => 'Dining',
                'description' => 'Authentic Khmer royal gastronomy, fine dining, traditional street food delicacies, and riverfront cafes.',
                'color' => '#EC4899',
                'status' => 'Active',
            ],
            [
                'name' => 'Resort & Hotel',
                'description' => 'Boutique heritage retreats, luxury eco-resorts, island villas, and traditional bamboo lodges.',
                'color' => '#14B8A6',
                'status' => 'Active',
            ],
            [
                'name' => 'Adventure & Tour',
                'description' => 'Guided jungle treks, river cruises, sunset kayaking, zip-lining, and off-road quad biking excursions.',
                'color' => '#6366F1',
                'status' => 'Active',
            ],
            [
                'name' => 'Cultural & Heritage',
                'description' => 'Traditional silk weaving centers, bronze sculpture foundries, pottery villages, and living heritage communities.',
                'color' => '#D97706',
                'status' => 'Active',
            ],
            [
                'name' => 'Island & Beach',
                'description' => 'Tropical islands, coral reef diving, turquoise bays, and pristine white powdery sand beaches.',
                'color' => '#0EA5E9',
                'status' => 'Active',
            ],
            [
                'name' => 'Eco-Tourism & Wildlife',
                'description' => 'Protected wildlife sanctuaries, elephant welfare valleys, Irrawaddy dolphin habitats, and bird reserves.',
                'color' => '#059669',
                'status' => 'Active',
            ],
            [
                'name' => 'Local Markets & Shopping',
                'description' => 'Vibrant night bazaars, colonial art deco markets, floating river trading posts, and artisan handicraft souks.',
                'color' => '#EA580C',
                'status' => 'Active',
            ],
            [
                'name' => 'Wellness & Spa',
                'description' => 'Traditional Khmer herbal body therapies, holistic wellness sanctuaries, aroma healing, and yoga retreats.',
                'color' => '#2DD4BF',
                'status' => 'Active',
            ],
            [
                'name' => 'Nightlife & Entertainment',
                'description' => 'Lively pub streets, rooftop sky lounges with sunset river panoramas, and acoustic jazz hideaways.',
                'color' => '#A855F7',
                'status' => 'Active',
            ],
            [
                'name' => 'Arts & Performance',
                'description' => 'Royal Ballet of Cambodia, live Apsara dance theatres, contemporary Phare circus, and shadow puppet theatre.',
                'color' => '#E11D48',
                'status' => 'Active',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name']],
                [
                    'description' => $cat['description'],
                    'color' => $cat['color'],
                    'status' => $cat['status'],
                ]
            );
        }
    }
}
