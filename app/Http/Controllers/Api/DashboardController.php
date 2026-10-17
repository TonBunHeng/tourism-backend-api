<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Category;
use App\Models\Event;
use App\Models\Favorite;
use App\Models\GalleryMedia;
use App\Models\Place;
use App\Models\Province;
use App\Models\Review;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $timeRange = strtolower($request->query('range', $request->query('time_range', 'weekly')));
        if (!in_array($timeRange, ['today', 'weekly', 'monthly', 'yearly'])) {
            $timeRange = 'weekly';
        }

        $now = Carbon::now();

        // Define periods for comparison based on requested timeRange
        if ($timeRange === 'today') {
            $currentStart = $now->copy()->startOfDay();
            $currentEnd = $now->copy()->endOfDay();
            $prevStart = $now->copy()->subDay()->startOfDay();
            $prevEnd = $now->copy()->subDay()->endOfDay();
            $periodLabel = 'Today (' . $now->format('M j, Y') . ')';
        } elseif ($timeRange === 'weekly') {
            $currentStart = $now->copy()->subDays(6)->startOfDay();
            $currentEnd = $now->copy()->endOfDay();
            $prevStart = $now->copy()->subDays(13)->startOfDay();
            $prevEnd = $now->copy()->subDays(7)->endOfDay();
            $periodLabel = 'Last 7 Days';
        } elseif ($timeRange === 'monthly') {
            $currentStart = $now->copy()->startOfMonth();
            $currentEnd = $now->copy()->endOfDay();
            $prevStart = $now->copy()->subMonth()->startOfMonth();
            $prevEnd = $now->copy()->subMonth()->endOfMonth();
            $periodLabel = 'This Month (' . $now->format('F Y') . ')';
        } else {
            $targetYear = $now->year ?: 2026;
            $currentStart = Carbon::create($targetYear, 1, 1, 0, 0, 0);
            $currentEnd = Carbon::create($targetYear, 12, 31, 23, 59, 59);
            $prevStart = Carbon::create($targetYear - 1, 1, 1, 0, 0, 0);
            $prevEnd = Carbon::create($targetYear - 1, 12, 31, 23, 59, 59);
            $periodLabel = "This Year ({$targetYear})";
        }

        // Helper to compute period comparison trends
        $calcTrend = function ($modelClass, $dateCol = 'created_at') use ($currentStart, $currentEnd, $prevStart, $prevEnd) {
            $currentCount = $modelClass::whereBetween($dateCol, [$currentStart, $currentEnd])->count();
            $prevCount = $modelClass::whereBetween($dateCol, [$prevStart, $prevEnd])->count();

            if ($prevCount > 0) {
                $pct = round((($currentCount - $prevCount) / $prevCount) * 100, 1);
                return [
                    'change' => ($pct >= 0 ? '+' : '') . $pct . '%',
                    'trend' => $pct >= 0 ? 'up' : 'down',
                    'current' => $currentCount,
                    'previous' => $prevCount,
                ];
            }

            if ($currentCount > 0) {
                return [
                    'change' => '+100%',
                    'trend' => 'up',
                    'current' => $currentCount,
                    'previous' => 0,
                ];
            }

            return [
                'change' => '+0%',
                'trend' => 'up',
                'current' => 0,
                'previous' => 0,
            ];
        };

        $usersTrend = $calcTrend(User::class);
        $placesTrend = $calcTrend(Place::class);
        $businessesTrend = $calcTrend(Business::class);
        $reviewsTrend = $calcTrend(Review::class);
        $eventsTrend = $calcTrend(Event::class);
        $favoritesTrend = $calcTrend(Favorite::class, 'created_at');
        $totalPlaces = Place::count();
        $pendingPlaces = Place::where('status', 'Pending')->count();
        $activePlaces = Place::where('status', 'Active')->count();

        $totalBusinesses = Business::count();
        $pendingBusinesses = Business::where('verification_status', 'pending')->count();
        $approvedBusinesses = Business::where('verification_status', 'approved')->count();
        $rejectedBusinesses = Business::where('verification_status', 'rejected')->count();

        $totalProvinces = Province::count();
        $totalCategories = Category::count();
        $totalEvents = Event::count();
        $totalUsers = User::count();
        $totalReviews = Review::count();
        $pendingReviews = Review::where('status', 'Pending')->count();
        $totalFavorites = Favorite::count();
        $totalGalleries = GalleryMedia::count();
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();

        $pendingVerifications = $pendingBusinesses + $pendingPlaces + $pendingReviews;

        $avgReviewRating = Review::avg('rating');
        $avgPlaceRating = Place::avg('rating');
        $averageRating = $avgReviewRating ?: ($avgPlaceRating ?: 0.0);

        // User status & active tracking counts
        $activeUsers = User::where('status', 'Active')->count();
        $inactiveUsers = User::where('status', 'Inactive')->count();
        $suspendedUsers = User::where('status', 'Suspended')->count();
        $onlineUsers = User::where('last_active_at', '>=', now()->subMinutes(5))->count();
        $newThisWeek = User::where('created_at', '>=', now()->subDays(7))->count();

        // Top places by rating and reviews, prioritized by time range
        $placesQuery = Place::with(['category', 'province'])
            ->withCount([
                'reviews as period_reviews_count' => function ($q) use ($currentStart, $currentEnd) {
                    $q->whereBetween('created_at', [$currentStart, $currentEnd]);
                }
            ]);

        if ($timeRange === 'today' || $timeRange === 'weekly' || $timeRange === 'monthly') {
            $placesQuery->orderBy('period_reviews_count', 'desc')
                ->orderBy('rating', 'desc')
                ->orderBy('reviews_count', 'desc');
        } else {
            $placesQuery->orderBy('rating', 'desc')
                ->orderBy('reviews_count', 'desc');
        }

        $topPlaces = $placesQuery->limit(10)
            ->get()
            ->map(function ($place) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                    'category' => $place->category?->name ?? 'Attraction',
                    'rating' => (float) ($place->rating ?? 0.0),
                    'reviews' => (int) ($place->reviews_count ?? 0),
                    'period_reviews' => (int) ($place->period_reviews_count ?? 0),
                    'visits' => (int) ($place->visitors_count ?? 0),
                    'address' => $place->address,
                    'image' => $place->image_url ?? $place->image ?? '',
                    'status' => $place->status ?? 'Active',
                ];
            });

        // Category distribution with real percentages and colors, responsive to timeRange
        $palette = ['bg-blue-500', 'bg-purple-500', 'bg-cyan-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-indigo-500'];
        $categories = Category::withCount('places')->orderBy('places_count', 'desc')->take(10)->get();

        $categoryActivityMap = [];
        $totalPeriodActivity = 0;
        foreach ($categories as $cat) {
            $activityCount = Favorite::whereHas('place', function ($q) use ($cat) {
                $q->where('category_id', $cat->id);
            })->whereBetween('created_at', [$currentStart, $currentEnd])->count()
            + Review::whereHas('place', function ($q) use ($cat) {
                $q->where('category_id', $cat->id);
            })->whereBetween('created_at', [$currentStart, $currentEnd])->count();

            $categoryActivityMap[$cat->id] = $activityCount;
            $totalPeriodActivity += $activityCount;
        }

        $categoryDistribution = $categories->map(function ($cat, $index) use ($palette, $categoryActivityMap, $totalPeriodActivity, $timeRange) {
            $activity = $categoryActivityMap[$cat->id] ?? 0;
            $count = ($timeRange !== 'yearly' && $totalPeriodActivity > 0) ? $activity : (int) $cat->places_count;

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'count' => $count,
                'places_count' => (int) $cat->places_count,
                'activity_count' => $activity,
                'color' => $palette[$index % count($palette)],
            ];
        });

        // Recent Activity feed
        $recentActivity = [];

        // Recent Places
        $recentPlaces = Place::with('category')->orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentPlaces as $rp) {
            $recentActivity[] = [
                'id' => 'place_' . $rp->id,
                'user' => 'Admin / Contributor',
                'type' => 'place',
                'action' => 'Added new destination',
                'target' => $rp->name,
                'time' => $rp->created_at ? $rp->created_at->diffForHumans() : 'Recently',
                'timestamp' => $rp->created_at ? $rp->created_at->timestamp : 0,
            ];
        }

        // Recent Reviews
        $recentReviews = Review::with(['user', 'place'])->orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentReviews as $rr) {
            $recentActivity[] = [
                'id' => 'review_' . $rr->id,
                'user' => $rr->user?->name ?? 'App Traveler',
                'type' => 'review',
                'action' => 'Rated ' . ($rr->rating ?? 5) . ' stars for',
                'target' => $rr->place?->name ?? 'Attraction',
                'time' => $rr->created_at ? $rr->created_at->diffForHumans() : 'Recently',
                'timestamp' => $rr->created_at ? $rr->created_at->timestamp : 0,
            ];
        }

        // Recent Media
        $recentMedia = GalleryMedia::with('user')->orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentMedia as $rm) {
            $recentActivity[] = [
                'id' => 'media_' . $rm->id,
                'user' => $rm->user?->name ?? 'Photographer',
                'type' => 'gallery',
                'action' => 'Uploaded photo/video for',
                'target' => $rm->title,
                'time' => $rm->created_at ? $rm->created_at->diffForHumans() : 'Recently',
                'timestamp' => $rm->created_at ? $rm->created_at->timestamp : 0,
            ];
        }

        // Recent Users
        $recentUsers = User::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentUsers as $ru) {
            $recentActivity[] = [
                'id' => 'user_' . $ru->id,
                'user' => $ru->name,
                'type' => 'user',
                'action' => 'Joined as',
                'target' => $ru->role ?? 'User',
                'time' => $ru->created_at ? $ru->created_at->diffForHumans() : 'Recently',
                'timestamp' => $ru->created_at ? $ru->created_at->timestamp : 0,
            ];
        }

        // Sort combined activities by latest timestamp
        usort($recentActivity, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        $recentActivity = array_slice($recentActivity, 0, 6);

        // Time-range adaptive user growth & activity data from database
        $userGrowth = [];

        if ($timeRange === 'today') {
            $hourlySegments = [
                ['label' => '12 AM', 'startH' => 0, 'endH' => 3],
                ['label' => '4 AM', 'startH' => 4, 'endH' => 7],
                ['label' => '8 AM', 'startH' => 8, 'endH' => 11],
                ['label' => '12 PM', 'startH' => 12, 'endH' => 15],
                ['label' => '4 PM', 'startH' => 16, 'endH' => 19],
                ['label' => '8 PM', 'startH' => 20, 'endH' => 23],
            ];

            foreach ($hourlySegments as $seg) {
                $segStart = $now->copy()->setTime($seg['startH'], 0, 0);
                $segEnd = $now->copy()->setTime($seg['endH'], 59, 59);

                $uCount = User::whereBetween('created_at', [$segStart, $segEnd])->count();
                $actCount = Favorite::whereBetween('created_at', [$segStart, $segEnd])->count()
                    + Review::whereBetween('created_at', [$segStart, $segEnd])->count();

                $userGrowth[] = [
                    'month' => $seg['label'],
                    'period' => $seg['label'],
                    'label' => $seg['label'],
                    'fullLabel' => "{$seg['label']} (Today)",
                    'unitsSold' => $uCount,
                    'totalTransaction' => $actCount,
                    'users' => $uCount,
                    'visits' => $actCount,
                ];
            }
        } elseif ($timeRange === 'weekly') {
            for ($i = 6; $i >= 0; $i--) {
                $day = $now->copy()->subDays($i);
                $dayStart = $day->copy()->startOfDay();
                $dayEnd = $day->copy()->endOfDay();
                $dayShort = $day->format('D');
                $dayDate = $day->format('M j');

                $uCount = User::whereBetween('created_at', [$dayStart, $dayEnd])->count();
                $actCount = Favorite::whereBetween('created_at', [$dayStart, $dayEnd])->count()
                    + Review::whereBetween('created_at', [$dayStart, $dayEnd])->count();

                $userGrowth[] = [
                    'month' => $dayShort,
                    'period' => $dayShort,
                    'label' => $dayShort,
                    'fullLabel' => "{$dayShort} ({$dayDate})",
                    'unitsSold' => $uCount,
                    'totalTransaction' => $actCount,
                    'users' => $uCount,
                    'visits' => $actCount,
                ];
            }
        } elseif ($timeRange === 'monthly') {
            $daysInMonth = $now->daysInMonth;
            $weekSegments = [
                ['label' => 'Week 1', 'start' => 1, 'end' => 7],
                ['label' => 'Week 2', 'start' => 8, 'end' => 14],
                ['label' => 'Week 3', 'start' => 15, 'end' => 21],
                ['label' => 'Week 4', 'start' => 22, 'end' => $daysInMonth],
            ];

            foreach ($weekSegments as $seg) {
                $wStart = $now->copy()->day($seg['start'])->startOfDay();
                $wEnd = $now->copy()->day($seg['end'])->endOfDay();
                $fullLabel = "{$seg['label']} (" . $wStart->format('M j') . " - " . $wEnd->format('M j') . ")";

                $uCount = User::whereBetween('created_at', [$wStart, $wEnd])->count();
                $actCount = Favorite::whereBetween('created_at', [$wStart, $wEnd])->count()
                    + Review::whereBetween('created_at', [$wStart, $wEnd])->count();

                $userGrowth[] = [
                    'month' => $seg['label'],
                    'period' => $seg['label'],
                    'label' => $seg['label'],
                    'fullLabel' => $fullLabel,
                    'unitsSold' => $uCount,
                    'totalTransaction' => $actCount,
                    'users' => $uCount,
                    'visits' => $actCount,
                ];
            }
        } else {
            // Yearly (12 Months)
            $targetYear = $now->year ?: 2026;
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            foreach ($months as $idx => $mName) {
                $monthNum = $idx + 1;
                $userCount = User::whereYear('created_at', $targetYear)->whereMonth('created_at', $monthNum)->count();
                $activityCount = Favorite::whereYear(DB::raw('COALESCE(saved_date, created_at)'), $targetYear)->whereMonth(DB::raw('COALESCE(saved_date, created_at)'), $monthNum)->count()
                    + Review::whereYear('created_at', $targetYear)->whereMonth('created_at', $monthNum)->count();

                $userGrowth[] = [
                    'month' => $mName,
                    'period' => $mName,
                    'label' => $mName,
                    'fullLabel' => "{$mName} {$targetYear}",
                    'unitsSold' => $userCount,
                    'totalTransaction' => $activityCount,
                    'users' => $userCount,
                    'visits' => $activityCount,
                ];
            }
        }

        $pendingRequests = [];

        // Pending Businesses from Business Owners
        $pendingBizList = Business::with('owner')->where('verification_status', 'pending')->latest()->limit(10)->get();
        foreach ($pendingBizList as $pb) {
            $pendingRequests[] = [
                'id' => $pb->id,
                'type' => 'business',
                'request_type' => 'business_registration',
                'title' => $pb->name,
                'submitter_name' => $pb->owner?->name ?? 'Business Owner',
                'submitter_email' => $pb->owner?->email ?? $pb->email,
                'status' => 'Pending Review',
                'verification_status' => 'pending',
                'submitted_at' => $pb->created_at ? $pb->created_at->diffForHumans() : 'Recently',
                'approve_url' => "/api/businesses/{$pb->id}/approve",
                'reject_url' => "/api/businesses/{$pb->id}/reject",
            ];
        }

        // Pending Places from Guides
        $pendingPlaceList = Place::where('status', 'Pending')->latest()->limit(10)->get();
        foreach ($pendingPlaceList as $pp) {
            $pendingRequests[] = [
                'id' => $pp->id,
                'type' => 'place',
                'request_type' => 'place_upload',
                'title' => $pp->name,
                'submitter_name' => 'Tourism Content Editor',
                'submitter_email' => 'editor@tourism.gov.kh',
                'status' => 'Pending Review',
                'verification_status' => 'pending',
                'submitted_at' => $pp->created_at ? $pp->created_at->diffForHumans() : 'Recently',
                'approve_url' => "/api/places/{$pp->id}/approve",
                'reject_url' => "/api/places/{$pp->id}/reject",
            ];
        }

        return $this->successResponse([
            'stats' => [
                'total_places' => $totalPlaces,
                'pending_places' => $pendingPlaces,
                'active_places' => $activePlaces,
                'total_businesses' => $totalBusinesses,
                'pending_businesses' => $pendingBusinesses,
                'approved_businesses' => $approvedBusinesses,
                'rejected_businesses' => $rejectedBusinesses,
                'pending_verifications' => $pendingVerifications,
                'total_provinces' => $totalProvinces,
                'total_categories' => $totalCategories,
                'total_events' => $totalEvents,
                'total_users' => $totalUsers,
                'total_reviews' => $totalReviews,
                'pending_reviews' => $pendingReviews,
                'total_favorites' => $totalFavorites,
                'total_galleries' => $totalGalleries,
                'total_bookings' => $totalBookings,
                'pending_bookings' => $pendingBookings,
                'confirmed_bookings' => $confirmedBookings,
                'avg_rating' => round((float)$averageRating, 1),
                'average_rating' => round((float)$averageRating, 1),
                'active_users' => $activeUsers,
                'online_users' => $onlineUsers,
                'inactive_users' => $inactiveUsers,
                'suspended_users' => $suspendedUsers,
                'new_this_week' => $newThisWeek,
                'time_range' => $timeRange,
                'period_label' => $periodLabel,
                'period_changes' => [
                    'places' => $placesTrend,
                    'users' => $usersTrend,
                    'businesses' => $businessesTrend,
                    'reviews' => $reviewsTrend,
                    'events' => $eventsTrend,
                    'favorites' => $favoritesTrend,
                ],
            ],
            'time_range' => $timeRange,
            'period_label' => $periodLabel,
            'pending_verifications_count' => $pendingVerifications,
            'pending_requests' => $pendingRequests,
            'top_places' => $topPlaces,
            'recent_places' => $recentPlaces,
            'category_distribution' => $categoryDistribution,
            'recent_activity' => $recentActivity,
            'user_growth' => $userGrowth,
            'user_status' => [
                'active' => $activeUsers,
                'online' => $onlineUsers,
                'inactive' => $inactiveUsers,
                'suspended' => $suspendedUsers,
                'new_this_week' => $newThisWeek,
            ],
        ], 'Dashboard statistics retrieved.');
    }
}
