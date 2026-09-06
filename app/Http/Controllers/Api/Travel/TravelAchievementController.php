<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Resources\Travel\TravelAchievementResource;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\UserAchievement;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelAchievementController extends Controller
{
    use ApiResponse;

    private array $defaultBadges = [
        ['name' => 'Angkor Explorer', 'description' => 'Visit and bookmark Angkor Archaeological Park heritage sites', 'icon' => 'Landmark', 'points' => 50],
        ['name' => 'Eco Traveler', 'description' => 'Explore 2 national parks, waterfalls or eco-tourism sanctuaries', 'icon' => 'Trees', 'points' => 50],
        ['name' => 'Cultural Enthusiast', 'description' => 'Attend a traditional Khmer festival, museum or cultural performance', 'icon' => 'Sparkles', 'points' => 75],
        ['name' => 'Coastal Wanderer', 'description' => 'Visit coastal beaches and islands in Sihanoukville or Koh Rong', 'icon' => 'Compass', 'points' => 50],
        ['name' => 'Heritage Master', 'description' => 'Write verified destination reviews with community tips', 'icon' => 'Award', 'points' => 100],
        ['name' => 'Province Adventurer', 'description' => 'Discover must-see places across different Cambodian provinces', 'icon' => 'MapPin', 'points' => 120],
    ];

    public function index(Request $request): JsonResponse
    {
        $catalog = array_map(function ($badge, $index) {
            return [
                'id' => $index + 1,
                'name' => $badge['name'],
                'badge_name' => $badge['name'],
                'achievement_name' => $badge['name'],
                'description' => $badge['description'],
                'icon' => $badge['icon'],
                'points' => $badge['points'],
                'unlocked' => false,
                'is_unlocked' => false,
                'unlocked_at' => null,
            ];
        }, $this->defaultBadges, array_keys($this->defaultBadges));

        return $this->successResponse(
            $catalog,
            'Available tourism achievements retrieved successfully.'
        );
    }

    public function myAchievements(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->index($request);
        }

        // Initialize user achievements if missing
        foreach ($this->defaultBadges as $badge) {
            UserAchievement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'achievement_name' => $badge['name'],
                ],
                [
                    'description' => $badge['description'],
                    'icon' => $badge['icon'],
                    'unlocked' => false,
                ]
            );
        }

        if ($user->isSuperAdmin()) {
            UserAchievement::where('user_id', $user->id)
                ->where('unlocked', false)
                ->update(['unlocked' => true, 'unlocked_at' => now()]);
        }

        // Check user activities to automatically award badges
        $favCount = Favorite::where('user_id', $user->id)->count();
        $reviewCount = Review::where('user_id', $user->id)->count();

        if ($favCount >= 1) {
            UserAchievement::where('user_id', $user->id)
                ->where('achievement_name', 'Angkor Explorer')
                ->where('unlocked', false)
                ->update(['unlocked' => true, 'unlocked_at' => now()]);
        }

        if ($reviewCount >= 1) {
            UserAchievement::where('user_id', $user->id)
                ->where('achievement_name', 'Heritage Master')
                ->where('unlocked', false)
                ->update(['unlocked' => true, 'unlocked_at' => now()]);
        }

        $achievements = UserAchievement::where('user_id', $user->id)
            ->orderBy('unlocked', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return $this->successResponse(
            TravelAchievementResource::collection($achievements),
            'My achievements retrieved successfully.'
        );
    }
}
