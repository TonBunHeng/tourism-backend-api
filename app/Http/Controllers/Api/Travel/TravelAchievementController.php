<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Resources\Travel\TravelAchievementResource;
use App\Models\UserAchievement;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelAchievementController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        // Get all unique achievement badge templates
        $achievements = UserAchievement::select('achievement_name', 'description', 'icon')
            ->distinct()
            ->get();

        return $this->successResponse(
            TravelAchievementResource::collection($achievements),
            'Available tourism achievements retrieved successfully.'
        );
    }

    public function myAchievements(Request $request): JsonResponse
    {
        $user = $request->user();

        $achievements = UserAchievement::where('user_id', $user->id)
            ->orderBy('unlocked', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        // If no achievements initialized for this user, populate default badges
        if ($achievements->isEmpty()) {
            $defaultBadges = [
                ['name' => 'Angkor Explorer', 'description' => 'Visit at least 3 temples in Angkor Archaeological Park', 'icon' => 'Landmark'],
                ['name' => 'Eco Traveler', 'description' => 'Explore 2 national parks or eco-tourism sites', 'icon' => 'Trees'],
                ['name' => 'Cultural Enthusiast', 'description' => 'Attend a traditional festival or museum', 'icon' => 'Sparkles'],
                ['name' => 'Coastal Wanderer', 'description' => 'Visit coastal beaches in Sihanoukville or Koh Rong', 'icon' => 'Compass'],
                ['name' => 'Heritage Master', 'description' => 'Write 5 verified destination reviews', 'icon' => 'Award'],
            ];

            foreach ($defaultBadges as $badge) {
                UserAchievement::create([
                    'user_id' => $user->id,
                    'achievement_name' => $badge['name'],
                    'description' => $badge['description'],
                    'icon' => $badge['icon'],
                    'unlocked' => false,
                ]);
            }

            $achievements = UserAchievement::where('user_id', $user->id)->get();
        }

        return $this->successResponse(
            TravelAchievementResource::collection($achievements),
            'My achievements retrieved successfully.'
        );
    }
}
