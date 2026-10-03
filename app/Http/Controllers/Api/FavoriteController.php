<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Place;
use App\Models\Province;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Favorite::with(['user', 'place.category', 'place.province']);

        // Admins and Super Admins can see all users' saved favorites or filter by user
        if ($user->isAdmin()) {
            if ($request->has('user_id') && $request->query('user_id') !== 'All') {
                $query->where('user_id', $request->query('user_id'));
            }
        } else {
            $query->where('user_id', $user->id);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('place', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('address', 'like', "%{$search}%");
                })->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $favorites = $query->orderBy('id', 'desc')->get();

        return $this->successResponse(FavoriteResource::collection($favorites), 'Favorite places retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'place_id' => 'required|exists:places,id',
            'visited' => 'boolean',
        ]);

        $userId = $request->user()->id;

        $favorite = Favorite::where('user_id', $userId)->where('place_id', $validated['place_id'])->first();

        if ($favorite) {
            return $this->errorResponse('Place is already in your favorites.', 409);
        }

        $favorite = Favorite::create([
            'user_id' => $userId,
            'place_id' => $validated['place_id'],
            'visited' => $validated['visited'] ?? false,
            'saved_date' => now()->toDateString(),
        ]);

        $favorite->load('place.category', 'place.province');

        return $this->successResponse(new FavoriteResource($favorite), 'Place added to favorites.', 201);
    }

    public function destroy(Request $request, string $placeId): JsonResponse
    {
        $userId = $request->user()->id;

        $favorite = Favorite::where('user_id', $userId)
            ->where(function ($q) use ($placeId) {
                $q->where('id', $placeId)->orWhere('place_id', $placeId);
            })->first();

        if (!$favorite) {
            return $this->errorResponse('Favorite record not found.', 404);
        }

        $favorite->delete();

        return $this->successResponse(null, 'Place removed from favorites.');
    }

    public function toggleVisited(Request $request, string $id): JsonResponse
    {
        $userId = $request->user()->id;
        $favorite = Favorite::where('user_id', $userId)->where('id', $id)->first();

        if (!$favorite) {
            return $this->errorResponse('Favorite record not found.', 404);
        }

        $favorite->update(['visited' => !$favorite->visited]);
        $favorite->load('place.category', 'place.province');

        return $this->successResponse(new FavoriteResource($favorite), 'Visited status updated.');
    }

    public function analytics(Request $request): JsonResponse
    {
        $timeframe = $request->query('timeframe', (string) date('Y'));
        $targetYear = is_numeric($timeframe) ? (int) $timeframe : (int) date('Y');
        $selectedCategory = $request->query('category', 'ALL');
        $statusInput = $request->query('visit_status', $request->query('status', 'ALL'));
        $statusUpper = strtoupper((string) $statusInput);

        $query = Favorite::with(['place.category', 'place.province', 'user']);

        if ($timeframe !== 'ALL' && is_numeric($timeframe)) {
            $query->where(function ($q) use ($targetYear) {
                $q->whereYear('saved_date', $targetYear)
                  ->orWhere(function ($sq) use ($targetYear) {
                      $sq->whereNull('saved_date')->whereYear('created_at', $targetYear);
                  });
            });
        }

        if ($selectedCategory !== 'ALL' && !empty($selectedCategory)) {
            $query->whereHas('place.category', function ($q) use ($selectedCategory) {
                if (is_numeric($selectedCategory)) {
                    $q->where('id', $selectedCategory);
                } else {
                    $q->where('name', $selectedCategory);
                }
            });
        }

        if ($statusUpper === 'VISITED') {
            $query->where('visited', true);
        } elseif ($statusUpper === 'PLANNED' || $statusUpper === 'WISHLIST' || $statusUpper === 'TO VISIT' || $statusUpper === 'TO_VISIT') {
            $query->where('visited', false);
        }

        $allFavorites = $query->orderBy('id', 'desc')->get();
        $totalFavorites = $allFavorites->count();
        $visitedCount = $allFavorites->where('visited', true)->count();
        $wishlistCount = $totalFavorites - $visitedCount;
        $conversionRate = $totalFavorites > 0 ? round(($visitedCount / $totalFavorites) * 100, 1) : 0.0;

        $uniqueUsers = $allFavorites->pluck('user_id')->filter()->unique()->count();

        // Calculate average rating of favorited places
        $placesWithRating = $allFavorites->map(function ($fav) {
            return $fav->place ? (float) $fav->place->rating : null;
        })->filter();

        $avgRating = $placesWithRating->count() > 0 ? round((float) $placesWithRating->avg(), 2) : 0.0;

        // Categories breakdown
        $colors = ['bg-[#003E83]', 'bg-rose-500', 'bg-emerald-500', 'bg-amber-500', 'bg-purple-500', 'bg-cyan-500', 'bg-indigo-500'];
        $fillColors = ['#003E83', '#f43f5e', '#10b981', '#f59e0b', '#a855f7', '#06b6d4', '#6366f1'];
        $categories = Category::all();

        $catScopeQuery = Favorite::query();
        if ($timeframe !== 'ALL' && is_numeric($timeframe)) {
            $catScopeQuery->where(function ($q) use ($targetYear) {
                $q->whereYear('saved_date', $targetYear)
                  ->orWhere(function ($sq) use ($targetYear) {
                      $sq->whereNull('saved_date')->whereYear('created_at', $targetYear);
                  });
            });
        }
        if ($statusUpper === 'VISITED') {
            $catScopeQuery->where('visited', true);
        } elseif ($statusUpper === 'PLANNED' || $statusUpper === 'WISHLIST' || $statusUpper === 'TO VISIT' || $statusUpper === 'TO_VISIT') {
            $catScopeQuery->where('visited', false);
        }
        $catScopeTotal = (clone $catScopeQuery)->count();

        $categoryData = [];
        foreach ($categories as $i => $cat) {
            $catPlaceIds = Place::where('category_id', $cat->id)->pluck('id');
            $catFavCount = (clone $catScopeQuery)->whereIn('place_id', $catPlaceIds)->count();
            if ($catFavCount > 0 || $catScopeTotal === 0) {
                $categoryData[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'count' => $catFavCount,
                    'percentage' => $catScopeTotal > 0 ? round(($catFavCount / $catScopeTotal) * 100) : 0,
                    'color' => $colors[$i % count($colors)],
                    'fillColor' => $fillColors[$i % count($fillColors)],
                ];
            }
        }
        usort($categoryData, fn($a, $b) => $b['count'] <=> $a['count']);

        // Status breakdown
        $statusBreakdown = [
            [
                'label' => 'Marked as Visited',
                'count' => $visitedCount,
                'percentage' => $totalFavorites > 0 ? round(($visitedCount / $totalFavorites) * 100) : 0,
                'color' => 'bg-emerald-500',
                'subtext' => 'Places traveler has explored'
            ],
            [
                'label' => 'Pending Wishlist',
                'count' => $wishlistCount,
                'percentage' => $totalFavorites > 0 ? round(($wishlistCount / $totalFavorites) * 100) : 0,
                'color' => 'bg-rose-500',
                'subtext' => 'Saved for future Cambodian trips'
            ]
        ];

        // Top Favorited Places
        $topPlaceCounts = $allFavorites->groupBy('place_id')->map->count()->sortDesc()->take(5);
        $topFavorites = [];
        $rank = 1;
        foreach ($topPlaceCounts as $placeId => $count) {
            $place = Place::with(['category', 'province'])->find($placeId);
            if ($place) {
                $placeVisited = $allFavorites->where('place_id', $placeId)->where('visited', true)->count();
                $topFavorites[] = [
                    'rank' => $rank++,
                    'id' => $place->id,
                    'name' => $place->name,
                    'image_url' => $place->image_url,
                    'image' => $place->image_url,
                    'category' => $place->category ? $place->category->name : 'Destination',
                    'province' => $place->province ? $place->province->name : 'Cambodia',
                    'rating' => (float) $place->rating,
                    'saves_count' => $count,
                    'visited_count' => $placeVisited,
                    'percentage' => $totalFavorites > 0 ? round(($count / $totalFavorites) * 100) : 100,
                ];
            }
        }

        // Monthly trends
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyData = [];
        $runningCumulative = 0;

        $monthBaseQuery = Favorite::query();
        if ($selectedCategory !== 'ALL' && !empty($selectedCategory)) {
            $monthBaseQuery->whereHas('place.category', function ($q) use ($selectedCategory) {
                if (is_numeric($selectedCategory)) {
                    $q->where('id', $selectedCategory);
                } else {
                    $q->where('name', $selectedCategory);
                }
            });
        }
        if ($statusUpper === 'VISITED') {
            $monthBaseQuery->where('visited', true);
        } elseif ($statusUpper === 'PLANNED' || $statusUpper === 'WISHLIST' || $statusUpper === 'TO VISIT' || $statusUpper === 'TO_VISIT') {
            $monthBaseQuery->where('visited', false);
        }

        foreach ($months as $idx => $mName) {
            $mNum = $idx + 1;
            $mQuery = (clone $monthBaseQuery)->where(function ($q) use ($mNum) {
                $q->whereMonth('saved_date', $mNum)
                  ->orWhere(function ($sq) use ($mNum) {
                      $sq->whereNull('saved_date')->whereMonth('created_at', $mNum);
                  });
            });
            if ($timeframe !== 'ALL' && is_numeric($timeframe)) {
                $mQuery->where(function ($q) use ($targetYear) {
                    $q->whereYear('saved_date', $targetYear)
                      ->orWhere(function ($sq) use ($targetYear) {
                          $sq->whereNull('saved_date')->whereYear('created_at', $targetYear);
                      });
                });
            }

            $realCount = $mQuery->count();
            $realVisited = (clone $mQuery)->where('visited', true)->count();
            $runningCumulative += $realCount;

            $monthlyData[] = [
                'month' => $mName,
                'newSaves' => $realCount,
                'count' => $realCount,
                'saves' => $realCount,
                'totalFavorites' => $realCount,
                'visitedCount' => $realVisited,
                'cumulative' => $runningCumulative,
                'total' => $runningCumulative,
            ];
        }

        $recentFavorites = $allFavorites->take(50);

        return $this->successResponse([
            'overview' => [
                'total_favorites' => $totalFavorites,
                'total' => $totalFavorites,
                'visited_count' => $visitedCount,
                'wishlist_count' => $wishlistCount,
                'planned_count' => $wishlistCount,
                'conversion_rate' => $conversionRate,
                'visited_pct' => $conversionRate,
                'unique_travelers' => max($uniqueUsers, $totalFavorites > 0 ? 1 : 0),
                'avg_rating' => $avgRating,
            ],
            'monthly_trends' => $monthlyData,
            'category_distribution' => $categoryData,
            'status_breakdown' => $statusBreakdown,
            'top_favorites' => $topFavorites,
            'categories' => Category::pluck('name')->toArray(),
            'favorites' => FavoriteResource::collection($recentFavorites),
            'recent_favorites' => FavoriteResource::collection($recentFavorites),
        ], 'Favorite places analytics retrieved successfully.');
    }
}
