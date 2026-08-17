<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserAchievementResource;
use App\Models\UserAchievement;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAchievementController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $achievements = UserAchievement::where('user_id', $userId)->get();

        return $this->successResponse(UserAchievementResource::collection($achievements), 'Achievements retrieved.');
    }

    public function userAchievements(string $userId): JsonResponse
    {
        $achievements = UserAchievement::where('user_id', $userId)->get();

        return $this->successResponse(UserAchievementResource::collection($achievements), 'User achievements retrieved.');
    }

    public function toggleUnlocked(Request $request, string $id): JsonResponse
    {
        $achievement = UserAchievement::find($id);

        if (!$achievement) {
            return $this->errorResponse('Achievement not found.', 404);
        }

        $unlocked = !$achievement->unlocked;
        $achievement->update([
            'unlocked' => $unlocked,
            'unlocked_at' => $unlocked ? now() : null,
        ]);

        return $this->successResponse(new UserAchievementResource($achievement), 'Achievement status updated.');
    }
}
