<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\GalleryLike;
use App\Models\GalleryMedia;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use App\Models\UserAchievement;

class AchievementManager
{
    public static array $badges = [
        [
            'name' => 'Angkor Explorer',
            'description' => 'Bookmark your first Cambodia heritage destination',
            'icon' => 'Landmark',
            'points' => 50,
            'rule' => 'favorites_1',
        ],
        [
            'name' => 'Heritage Master',
            'description' => 'Write your first verified destination review',
            'icon' => 'Award',
            'points' => 100,
            'rule' => 'reviews_1',
        ],
        [
            'name' => 'Wanderlust Explorer',
            'description' => 'Save 5 or more places in your travel wishlist',
            'icon' => 'Compass',
            'points' => 120,
            'rule' => 'favorites_5',
        ],
        [
            'name' => 'Trip Planner Pioneer',
            'description' => 'Create your first personalized travel itinerary',
            'icon' => 'MapPin',
            'points' => 75,
            'rule' => 'trips_1',
        ],
        [
            'name' => 'Gallery Contributor',
            'description' => 'Engage with destination photo & video galleries',
            'icon' => 'Sparkles',
            'points' => 50,
            'rule' => 'gallery_1',
        ],
        [
            'name' => 'Cambodia Heritage Champion',
            'description' => 'Actively explore destinations across Cambodian provinces',
            'icon' => 'Trees',
            'points' => 150,
            'rule' => 'activity_high',
        ],
    ];

    /**
     * Check and award achievements for a user.
     */
    public static function checkAndAward(User $user): void
    {
        // Ensure achievement records exist
        foreach (self::$badges as $badge) {
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

        $favCount = Favorite::where('user_id', $user->id)->count();
        $reviewCount = Review::where('user_id', $user->id)->count();
        $tripCount = Trip::where('user_id', $user->id)->count();
        $likeCount = GalleryLike::where('user_id', $user->id)->count();
        $mediaCount = GalleryMedia::where('author_name', $user->name)->count();

        // 1. Favorites >= 1
        if ($favCount >= 1) {
            self::unlockBadge($user, 'Angkor Explorer');
        }

        // 2. Reviews >= 1
        if ($reviewCount >= 1) {
            self::unlockBadge($user, 'Heritage Master');
        }

        // 3. Favorites >= 5
        if ($favCount >= 5) {
            self::unlockBadge($user, 'Wanderlust Explorer');
        }

        // 4. Trips >= 1
        if ($tripCount >= 1) {
            self::unlockBadge($user, 'Trip Planner Pioneer');
        }

        // 5. Gallery engagement
        if ($likeCount >= 1 || $mediaCount >= 1) {
            self::unlockBadge($user, 'Gallery Contributor');
        }

        // 6. Cambodia Heritage Champion (Trips >= 2 or Favorites >= 10)
        if ($tripCount >= 2 || $favCount >= 10 || ($favCount >= 3 && $reviewCount >= 2)) {
            self::unlockBadge($user, 'Cambodia Heritage Champion');
        }
    }

    private static function unlockBadge(User $user, string $badgeName): void
    {
        $achievement = UserAchievement::where('user_id', $user->id)
            ->where('achievement_name', $badgeName)
            ->where('unlocked', false)
            ->first();

        if ($achievement) {
            $achievement->update([
                'unlocked' => true,
                'unlocked_at' => now(),
            ]);

            // Create notification for unlocked achievement
            Notification::create([
                'user_id' => $user->id,
                'type' => 'achievement',
                'title' => 'Achievement Unlocked: ' . $badgeName,
                'message' => 'Congratulations! You unlocked the "' . $badgeName . '" badge.',
                'link' => '/achievements',
                'is_read' => false,
            ]);
        }
    }
}
