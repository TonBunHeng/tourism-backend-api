<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewReplyResource;
use App\Http\Resources\ReviewResource;
use App\Models\Category;
use App\Models\Place;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewReply;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'place.category', 'images', 'replies.user']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        if ($placeId = $request->query('place_id')) {
            $query->where('place_id', $placeId);
        }

        if ($status = $request->query('status')) {
            if ($status !== 'All') {
                $query->where('status', $status);
            }
        }

        if ($rating = $request->query('rating')) {
            if ($rating !== 'All') {
                $query->where('rating', $rating);
            }
        }

        $perPage = (int) $request->query('per_page', 10);
        $reviews = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->successResponse(ReviewResource::collection($reviews), 'Reviews retrieved successfully.', 200, [
            'total' => $reviews->total(),
            'per_page' => $reviews->perPage(),
            'current_page' => $reviews->currentPage(),
            'last_page' => $reviews->lastPage(),
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $timeframe = $request->query('timeframe', '2026');
        $targetYear = is_numeric($timeframe) ? (int) $timeframe : (int) date('Y');
        $selectedCategory = $request->query('category', 'ALL');
        $selectedRating = $request->query('rating', 'ALL');

        // Base query with active filters
        $baseQuery = Review::with(['user', 'place.category', 'images']);

        if ($timeframe !== 'ALL' && is_numeric($timeframe)) {
            $baseQuery->whereYear('created_at', $targetYear);
        }

        if ($selectedCategory !== 'ALL' && !empty($selectedCategory)) {
            $baseQuery->whereHas('place.category', function ($q) use ($selectedCategory) {
                if (is_numeric($selectedCategory)) {
                    $q->where('id', $selectedCategory);
                } else {
                    $q->where('name', $selectedCategory);
                }
            });
        }

        if ($selectedRating !== 'ALL' && !empty($selectedRating)) {
            if ($selectedRating === 'positive') {
                $baseQuery->where('rating', '>=', 4);
            } elseif ($selectedRating === 'critical') {
                $baseQuery->where('rating', '<=', 3);
            } elseif (is_numeric($selectedRating)) {
                $baseQuery->where('rating', (int) $selectedRating);
            }
        }

        $filteredReviews = (clone $baseQuery)->orderBy('id', 'desc')->get();
        $totalReviews = $filteredReviews->count();
        $avgScore = $totalReviews > 0 ? $filteredReviews->avg('rating') : 0.0;
        $avgRating = round((float) $avgScore, 2);

        $posCount = $filteredReviews->where('rating', '>=', 4)->count();
        $criticalCount = $filteredReviews->where('rating', '<=', 3)->count();
        $positiveSentimentPct = $totalReviews > 0 ? round(($posCount / $totalReviews) * 100, 1) : 0.0;
        $criticalSentimentPct = $totalReviews > 0 ? round(($criticalCount / $totalReviews) * 100, 1) : 0.0;

        $verifiedCount = $filteredReviews->where('status', 'Approved')->count();
        $verificationPct = $totalReviews > 0 ? round(($verifiedCount / $totalReviews) * 100, 1) : 0.0;

        // Rating Stars breakdown scoped to category & timeframe
        $ratingScopeQuery = Review::query();
        if ($timeframe !== 'ALL' && is_numeric($timeframe)) {
            $ratingScopeQuery->whereYear('created_at', $targetYear);
        }
        if ($selectedCategory !== 'ALL' && !empty($selectedCategory)) {
            $ratingScopeQuery->whereHas('place.category', function ($q) use ($selectedCategory) {
                if (is_numeric($selectedCategory)) {
                    $q->where('id', $selectedCategory);
                } else {
                    $q->where('name', $selectedCategory);
                }
            });
        }
        $ratingScopeTotal = (clone $ratingScopeQuery)->count();

        $ratingDistribution = [];
        $starColors = [
            5 => '#10b981',
            4 => '#3b82f6',
            3 => '#f59e0b',
            2 => '#f97316',
            1 => '#ef4444',
        ];
        foreach ([5, 4, 3, 2, 1] as $star) {
            $starCount = (clone $ratingScopeQuery)->where('rating', $star)->count();
            $ratingDistribution[] = [
                'stars' => $star,
                'rating' => $star,
                'count' => $starCount,
                'total' => $starCount,
                'percentage' => $ratingScopeTotal > 0 ? round(($starCount / $ratingScopeTotal) * 100) : 0,
                'name' => "{$star} Stars",
                'fillColor' => $starColors[$star],
            ];
        }

        // Categories breakdown
        $categories = Category::all();
        $categoryColors = ['bg-[#003E83]', 'bg-rose-500', 'bg-emerald-500', 'bg-amber-500', 'bg-purple-500', 'bg-cyan-500', 'bg-indigo-500'];
        $categoryFillColors = ['#003E83', '#f43f5e', '#10b981', '#f59e0b', '#a855f7', '#06b6d4', '#6366f1'];

        $catScopeQuery = Review::query();
        if ($timeframe !== 'ALL' && is_numeric($timeframe)) {
            $catScopeQuery->whereYear('created_at', $targetYear);
        }
        if ($selectedRating !== 'ALL' && !empty($selectedRating)) {
            if ($selectedRating === 'positive') {
                $catScopeQuery->where('rating', '>=', 4);
            } elseif ($selectedRating === 'critical') {
                $catScopeQuery->where('rating', '<=', 3);
            } elseif (is_numeric($selectedRating)) {
                $catScopeQuery->where('rating', (int) $selectedRating);
            }
        }
        $catScopeTotal = (clone $catScopeQuery)->count();

        $categoryData = [];
        foreach ($categories as $i => $cat) {
            $catPlaceIds = Place::where('category_id', $cat->id)->pluck('id');
            $catReviewCount = (clone $catScopeQuery)->whereIn('place_id', $catPlaceIds)->count();
            if ($catReviewCount > 0 || $catScopeTotal === 0) {
                $categoryData[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'count' => $catReviewCount,
                    'percentage' => $catScopeTotal > 0 ? round(($catReviewCount / $catScopeTotal) * 100) : 0,
                    'color' => $categoryColors[$i % count($categoryColors)],
                    'fillColor' => $categoryFillColors[$i % count($categoryFillColors)],
                ];
            }
        }
        usort($categoryData, fn($a, $b) => $b['count'] <=> $a['count']);

        // Monthly trends
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyData = [];
        $runningTotal = 0;
        $runningRatingSum = 0;
        $runningRatingCount = 0;

        $monthBaseQuery = Review::query();
        if ($selectedCategory !== 'ALL' && !empty($selectedCategory)) {
            $monthBaseQuery->whereHas('place.category', function ($q) use ($selectedCategory) {
                if (is_numeric($selectedCategory)) {
                    $q->where('id', $selectedCategory);
                } else {
                    $q->where('name', $selectedCategory);
                }
            });
        }
        if ($selectedRating !== 'ALL' && !empty($selectedRating)) {
            if ($selectedRating === 'positive') {
                $monthBaseQuery->where('rating', '>=', 4);
            } elseif ($selectedRating === 'critical') {
                $monthBaseQuery->where('rating', '<=', 3);
            } elseif (is_numeric($selectedRating)) {
                $monthBaseQuery->where('rating', (int) $selectedRating);
            }
        }

        foreach ($months as $idx => $mName) {
            $mNum = $idx + 1;
            $mQuery = (clone $monthBaseQuery)->whereMonth('created_at', $mNum);
            if ($timeframe !== 'ALL' && is_numeric($timeframe)) {
                $mQuery->whereYear('created_at', $targetYear);
            }

            $realCount = $mQuery->count();
            $monthSum = $realCount > 0 ? (float) $mQuery->sum('rating') : 0.0;
            $monthAvg = $realCount > 0 ? round((float) $mQuery->avg('rating'), 2) : null;

            if ($realCount > 0) {
                $runningRatingSum += $monthSum;
                $runningRatingCount += $realCount;
            }
            $runningTotal += $realCount;

            // Score trajectory represents prevailing average rating
            $trajectoryScore = $runningRatingCount > 0 
                ? round($runningRatingSum / $runningRatingCount, 2) 
                : ($totalReviews > 0 ? $avgRating : 5.0);

            $monthlyData[] = [
                'month' => $mName,
                'ratingsCount' => $realCount,
                'totalRatings' => $realCount,
                'count' => $realCount,
                'avgRating' => $trajectoryScore,
                'avg_rating' => $trajectoryScore,
                'monthAvg' => $monthAvg,
                'cumulative' => $runningTotal,
            ];
        }

        // Recent reviews matching active filters
        $recentReviews = (clone $baseQuery)->orderBy('id', 'desc')->take(50)->get();

        return $this->successResponse([
            'overview' => [
                'total_ratings' => $totalReviews,
                'total' => $totalReviews,
                'avg_rating' => $avgRating,
                'positive_count' => $posCount,
                'critical_count' => $criticalCount,
                'positive_sentiment_pct' => $positiveSentimentPct,
                'critical_sentiment_pct' => $criticalSentimentPct,
                'verification_count' => $verifiedCount,
                'verification_pct' => $verificationPct,
            ],
            'monthly_trends' => $monthlyData,
            'rating_distribution' => $ratingDistribution,
            'category_distribution' => $categoryData,
            'categories' => Category::pluck('name')->toArray(),
            'reviews' => ReviewResource::collection($recentReviews),
            'recent_reviews' => ReviewResource::collection($recentReviews),
        ], 'Ratings analytics retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'place_id' => 'required|exists:places,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'comment' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'string|max:255',
        ]);

        $review = Review::create([
            'user_id' => $request->user()->id,
            'place_id' => $validated['place_id'],
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'],
            'status' => 'Pending',
        ]);

        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $imgUrl) {
                ReviewImage::create([
                    'review_id' => $review->id,
                    'image_url' => $imgUrl,
                ]);
            }
        }

        // Recalculate place rating & reviews count
        $this->updatePlaceRating($validated['place_id']);

        $review->load(['user', 'place', 'images', 'replies.user']);

        return $this->successResponse(new ReviewResource($review), 'Review submitted successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $review = Review::with(['user', 'place', 'images', 'replies.user'])->find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        return $this->successResponse(new ReviewResource($review), 'Review details retrieved successfully.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'comment' => 'sometimes|required|string',
            'status' => ['sometimes', Rule::in(['Approved', 'Pending', 'Rejected', 'Flagged'])],
            'is_verified' => 'sometimes|boolean',
        ]);

        $review->update($validated);
        $this->updatePlaceRating($review->place_id);

        $review->load(['user', 'place', 'images', 'replies.user']);

        return $this->successResponse(new ReviewResource($review), 'Review updated successfully.');
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Approved', 'Pending', 'Rejected', 'Flagged'])],
        ]);

        $review->update(['status' => $validated['status']]);
        $this->updatePlaceRating($review->place_id);

        $review->load(['user', 'place', 'images', 'replies.user']);

        return $this->successResponse(new ReviewResource($review), 'Review status updated successfully.');
    }

    public function addReply(Request $request, string $id): JsonResponse
    {
        return $this->reply($request, $id);
    }

    public function reply(Request $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $reply = ReviewReply::create([
            'review_id' => $review->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        $reply->load('user');

        return $this->successResponse(new ReviewReplyResource($reply), 'Reply added successfully.', 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        $placeId = $review->place_id;
        $review->delete();

        $this->updatePlaceRating($placeId);

        return $this->successResponse(null, 'Review deleted successfully.');
    }

    private function updatePlaceRating(int $placeId): void
    {
        $place = Place::find($placeId);
        if ($place) {
            $avgRating = Review::where('place_id', $placeId)->where('status', 'Approved')->avg('rating') ?? 0;
            $count = Review::where('place_id', $placeId)->where('status', 'Approved')->count();
            $place->update([
                'rating' => round($avgRating, 2),
                'reviews_count' => $count,
            ]);
        }
    }
}
