<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Place;
use App\Models\Province;
use App\Models\Review;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $totalPlaces = Place::count();
        $totalProvinces = Province::count();
        $totalCategories = Category::count();
        $totalEvents = Event::count();
        $totalUsers = User::count();
        $totalReviews = Review::count();
        $averageRating = Review::avg('rating') ?: 0;

        $topPlaces = Place::with(['category', 'province'])
            ->orderBy('rating', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->limit(5)
            ->get();

        $recentPlaces = Place::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $categoryDistribution = Category::withCount('places')->get();

        return $this->successResponse([
            'stats' => [
                'total_places' => $totalPlaces,
                'total_provinces' => $totalProvinces,
                'total_categories' => $totalCategories,
                'total_events' => $totalEvents,
                'total_users' => $totalUsers,
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating, 2),
            ],
            'top_places' => $topPlaces,
            'recent_places' => $recentPlaces,
            'category_distribution' => $categoryDistribution,
        ], 'Dashboard statistics retrieved.');
    }
}
