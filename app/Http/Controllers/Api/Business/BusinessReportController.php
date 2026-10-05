<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Business;
use App\Models\BusinessService;
use App\Models\Review;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BusinessReportController extends Controller
{
    use ApiResponse;

    /**
     * Get comprehensive report for owned businesses.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Resolve authorized businesses
        $businessesQuery = Business::query();

        if (!$user->isAdmin()) {
            $businessesQuery->where('owner_id', $user->id);
        }

        if ($businessId = $request->input('business_id')) {
            if (!$user->isAdmin()) {
                $isOwned = Business::where('owner_id', $user->id)->where('id', $businessId)->exists();
                if (!$isOwned) {
                    return $this->errorResponse('Access denied. You do not own this business.', 403);
                }
            }
            $businessesQuery->where('id', $businessId);
        }

        $businesses = $businessesQuery->get(['id', 'name', 'slug', 'rating', 'review_count', 'owner_id', 'status', 'verification_status']);
        $businessIds = $businesses->pluck('id')->toArray();

        // 2. Handle scenario where owner has no businesses yet
        if (empty($businessIds)) {
            return $this->successResponse([
                'timeframe' => [
                    'start_date' => null,
                    'end_date' => null,
                    'label' => 'No Businesses Found',
                    'interval' => 'none',
                ],
                'businesses' => [],
                'summary' => $this->emptySummary(),
                'trends' => [],
                'top_services' => [],
                'customer_insights' => [
                    'total_unique_customers' => 0,
                    'repeat_customers' => 0,
                    'repeat_customer_rate' => 0.0,
                    'top_customers' => [],
                ],
                'ratings_breakdown' => $this->emptyRatingsBreakdown(),
                'booking_status_breakdown' => $this->emptyBookingStatusBreakdown(),
                'payment_status_breakdown' => $this->emptyPaymentBreakdown(),
            ], 'Business report retrieved successfully.');
        }

        // 3. Resolve timeframe and dates
        [$startDate, $endDate, $label, $trendInterval] = $this->resolveDateRange($request);

        // 4. Base Bookings Query (supports date_type = 'booking_date' | 'created_at' | 'any')
        $dateType = $request->input('date_type', 'booking_date');
        $timeframe = strtolower((string) $request->input('timeframe', 'this_year'));

        $baseBookingsQuery = Booking::whereIn('business_id', $businessIds)
            ->where(function ($q) use ($startDate, $endDate, $dateType, $timeframe) {
                if ($dateType === 'created_at') {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                } elseif ($dateType === 'any' || $timeframe === 'today') {
                    $q->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
                      ->orWhereBetween('created_at', [$startDate, $endDate]);
                } else {
                    $q->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          $q2->whereNull('booking_date')->whereBetween('created_at', [$startDate, $endDate]);
                      });
                }
            });

        if ($statusFilter = $request->input('status')) {
            $baseBookingsQuery->where('status', $statusFilter);
        }

        if ($paymentFilter = $request->input('payment_status')) {
            $baseBookingsQuery->where('payment_status', $paymentFilter);
        }

        $allBookings = $baseBookingsQuery->with(['service', 'business'])->get();

        // 5. Calculate Summary Metrics
        $totalBookings = $allBookings->count();
        $completedBookings = $allBookings->where('status', Booking::STATUS_COMPLETED)->count();
        $confirmedBookings = $allBookings->where('status', Booking::STATUS_CONFIRMED)->count();
        $pendingBookings = $allBookings->where('status', Booking::STATUS_PENDING)->count();
        $cancelledBookings = $allBookings->where('status', Booking::STATUS_CANCELLED)->count();
        $rejectedBookings = $allBookings->where('status', Booking::STATUS_REJECTED)->count();

        $totalRevenue = (float) $allBookings
            ->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CONFIRMED])
            ->sum('total_price');

        $completedRevenue = (float) $allBookings
            ->where('status', Booking::STATUS_COMPLETED)
            ->sum('total_price');

        $paidRevenue = (float) $allBookings
            ->where('payment_status', Booking::PAYMENT_PAID)
            ->sum('total_price');

        $pendingRevenue = (float) $allBookings
            ->where('status', Booking::STATUS_PENDING)
            ->sum('total_price');

        $totalGuests = (int) $allBookings->sum('number_of_guests');

        $activePaidCount = $completedBookings + $confirmedBookings;
        $averageBookingValue = $activePaidCount > 0 ? round($totalRevenue / $activePaidCount, 2) : 0.0;
        $averagePartySize = $totalBookings > 0 ? round($totalGuests / $totalBookings, 1) : 0.0;
        $cancellationRate = $totalBookings > 0 ? round((($cancelledBookings + $rejectedBookings) / $totalBookings) * 100, 1) : 0.0;
        $completionRate = $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100, 1) : 0.0;

        // 6. Reviews & Quality Metrics
        $reviewsQuery = Review::whereIn('business_id', $businessIds);
        $totalReviews = (clone $reviewsQuery)->count();
        $approvedReviews = (clone $reviewsQuery)->where('status', 'Approved')->count();
        $avgRatingVal = (clone $reviewsQuery)->where('status', 'Approved')->avg('rating');
        if ($avgRatingVal === null) {
            $avgRatingVal = $businesses->avg('rating') ?: 0.0;
        }
        $repliedReviews = (clone $reviewsQuery)->has('replies')->count();
        $reviewResponseRate = $totalReviews > 0 ? round(($repliedReviews / $totalReviews) * 100, 1) : 0.0;

        // Integer ratings per schema definition (1 to 5)
        $r5 = (clone $reviewsQuery)->where('status', 'Approved')->where('rating', 5)->count();
        $r4 = (clone $reviewsQuery)->where('status', 'Approved')->where('rating', 4)->count();
        $r3 = (clone $reviewsQuery)->where('status', 'Approved')->where('rating', 3)->count();
        $r2 = (clone $reviewsQuery)->where('status', 'Approved')->where('rating', 2)->count();
        $r1 = (clone $reviewsQuery)->where('status', 'Approved')->where('rating', 1)->count();
        $sumRatings = $r5 + $r4 + $r3 + $r2 + $r1;

        $ratingsBreakdown = [
            'average_rating' => round((float) $avgRatingVal, 2),
            'total_reviews' => $totalReviews,
            'approved_reviews' => $approvedReviews,
            'replied_reviews' => $repliedReviews,
            'response_rate' => $reviewResponseRate,
            'stars' => [
                ['label' => '5 Stars (Excellent)', 'count' => $r5, 'percentage' => $sumRatings > 0 ? round(($r5 / $sumRatings) * 100, 1) : 0.0, 'color' => '#10B981'],
                ['label' => '4 Stars (Very Good)', 'count' => $r4, 'percentage' => $sumRatings > 0 ? round(($r4 / $sumRatings) * 100, 1) : 0.0, 'color' => '#3B82F6'],
                ['label' => '3 Stars (Average)', 'count' => $r3, 'percentage' => $sumRatings > 0 ? round(($r3 / $sumRatings) * 100, 1) : 0.0, 'color' => '#F59E0B'],
                ['label' => '2 Stars (Poor)', 'count' => $r2, 'percentage' => $sumRatings > 0 ? round(($r2 / $sumRatings) * 100, 1) : 0.0, 'color' => '#F97316'],
                ['label' => '1 Star (Critical)', 'count' => $r1, 'percentage' => $sumRatings > 0 ? round(($r1 / $sumRatings) * 100, 1) : 0.0, 'color' => '#EF4444'],
            ],
        ];

        // 7. Booking Status Breakdown
        $bookingStatusBreakdown = [
            ['name' => 'Completed', 'status' => Booking::STATUS_COMPLETED, 'count' => $completedBookings, 'percentage' => $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100, 1) : 0.0, 'color' => '#10B981'],
            ['name' => 'Confirmed', 'status' => Booking::STATUS_CONFIRMED, 'count' => $confirmedBookings, 'percentage' => $totalBookings > 0 ? round(($confirmedBookings / $totalBookings) * 100, 1) : 0.0, 'color' => '#3B82F6'],
            ['name' => 'Pending Action', 'status' => Booking::STATUS_PENDING, 'count' => $pendingBookings, 'percentage' => $totalBookings > 0 ? round(($pendingBookings / $totalBookings) * 100, 1) : 0.0, 'color' => '#F59E0B'],
            ['name' => 'Cancelled', 'status' => Booking::STATUS_CANCELLED, 'count' => $cancelledBookings, 'percentage' => $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100, 1) : 0.0, 'color' => '#9CA3AF'],
            ['name' => 'Declined / Rejected', 'status' => Booking::STATUS_REJECTED, 'count' => $rejectedBookings, 'percentage' => $totalBookings > 0 ? round(($rejectedBookings / $totalBookings) * 100, 1) : 0.0, 'color' => '#EF4444'],
        ];

        // 8. Payment Status Breakdown
        $paymentPaid = $allBookings->where('payment_status', Booking::PAYMENT_PAID);
        $paymentUnpaid = $allBookings->where('payment_status', Booking::PAYMENT_UNPAID);
        $paymentRefunded = $allBookings->where('payment_status', Booking::PAYMENT_REFUNDED);

        $paymentStatusBreakdown = [
            'paid' => [
                'count' => $paymentPaid->count(),
                'amount' => round((float) $paymentPaid->sum('total_price'), 2),
            ],
            'unpaid' => [
                'count' => $paymentUnpaid->count(),
                'amount' => round((float) $paymentUnpaid->sum('total_price'), 2),
            ],
            'refunded' => [
                'count' => $paymentRefunded->count(),
                'amount' => round((float) $paymentRefunded->sum('total_price'), 2),
            ],
        ];

        // 9. Time-series Trends Generation
        $trends = $this->buildTrends($allBookings, $startDate, $endDate, $trendInterval);

        // 10. Top Performing Services (including full catalog coverage)
        $topServices = $this->buildTopServices($allBookings, $businessIds, $totalRevenue);

        // 11. Customer Insights & Repeat Rate
        $customerInsights = $this->buildCustomerInsights($allBookings);

        return $this->successResponse([
            'timeframe' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'label' => $label,
                'interval' => $trendInterval,
            ],
            'businesses' => $businesses->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'rating' => (float) $b->rating,
                'review_count' => (int) $b->review_count,
            ]),
            'summary' => [
                'total_revenue' => round($totalRevenue, 2),
                'completed_revenue' => round($completedRevenue, 2),
                'paid_revenue' => round($paidRevenue, 2),
                'pending_revenue' => round($pendingRevenue, 2),
                'total_bookings' => $totalBookings,
                'completed_bookings' => $completedBookings,
                'confirmed_bookings' => $confirmedBookings,
                'pending_bookings' => $pendingBookings,
                'cancelled_bookings' => $cancelledBookings,
                'rejected_bookings' => $rejectedBookings,
                'total_guests' => $totalGuests,
                'average_booking_value' => $averageBookingValue,
                'average_party_size' => $averagePartySize,
                'cancellation_rate' => $cancellationRate,
                'completion_rate' => $completionRate,
                'currency' => 'USD',
            ],
            'trends' => $trends,
            'top_services' => $topServices,
            'customer_insights' => $customerInsights,
            'ratings_breakdown' => $ratingsBreakdown,
            'booking_status_breakdown' => $bookingStatusBreakdown,
            'payment_status_breakdown' => $paymentStatusBreakdown,
            'recent_bookings' => $allBookings->sortByDesc('booking_date')->take(50)->values()->map(fn($b) => [
                'id' => $b->id,
                'booking_reference' => $b->booking_reference,
                'business_name' => $b->business?->name,
                'service_name' => $b->service?->name ?? 'Direct Reservation',
                'customer_name' => $b->customer_name,
                'customer_email' => $b->customer_email,
                'booking_date' => $b->booking_date,
                'booking_time' => $b->booking_time,
                'total_guests' => $b->total_guests,
                'total_price' => (float) $b->total_price,
                'status' => $b->status,
                'payment_status' => $b->payment_status,
                'created_at' => $b->created_at?->toIso8601String(),
            ]),
        ], 'Business report retrieved successfully.');
    }

    /**
     * Get report for a specific owned business.
     */
    public function businessReport(Request $request, int|string $id): JsonResponse
    {
        $request->merge(['business_id' => $id]);
        return $this->index($request);
    }

    /**
     * Export business report data (CSV or JSON).
     */
    public function export(Request $request): JsonResponse|StreamedResponse
    {
        $user = $request->user();

        // 1. Resolve authorized businesses
        $businessesQuery = Business::query();

        if (!$user->isAdmin()) {
            $businessesQuery->where('owner_id', $user->id);
        }

        if ($businessId = $request->input('business_id')) {
            if (!$user->isAdmin()) {
                $isOwned = Business::where('owner_id', $user->id)->where('id', $businessId)->exists();
                if (!$isOwned) {
                    return $this->errorResponse('Access denied. You do not own this business.', 403);
                }
            }
            $businessesQuery->where('id', $businessId);
        }

        $businesses = $businessesQuery->get(['id', 'name', 'slug']);
        $businessIds = $businesses->pluck('id')->toArray();

        [$startDate, $endDate, $label] = $this->resolveDateRange($request);
        $dateType = $request->input('date_type', 'booking_date');
        $timeframe = strtolower((string) $request->input('timeframe', 'this_year'));

        $bookingsQuery = Booking::with(['business', 'service', 'user'])
            ->whereIn('business_id', $businessIds)
            ->where(function ($q) use ($startDate, $endDate, $dateType, $timeframe) {
                if ($dateType === 'created_at') {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                } elseif ($dateType === 'any' || $timeframe === 'today') {
                    $q->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
                      ->orWhereBetween('created_at', [$startDate, $endDate]);
                } else {
                    $q->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          $q2->whereNull('booking_date')->whereBetween('created_at', [$startDate, $endDate]);
                      });
                }
            });

        if ($status = $request->input('status')) {
            $bookingsQuery->where('status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $bookingsQuery->where('payment_status', $paymentStatus);
        }

        $bookings = $bookingsQuery->orderBy('booking_date', 'desc')->orderBy('booking_time', 'desc')->get();

        $format = strtolower((string) $request->input('format', 'csv'));

        // Log export in Audit Logs
        AuditLogger::log(
            action: 'business.report_exported',
            entityType: 'Business',
            entityId: $businessId ? (int) $businessId : null,
            description: "Business report ({$format}) exported by {$user->name} covering {$label}.",
            userId: $user->id
        );

        if ($format === 'json') {
            return $this->successResponse([
                'generated_at' => now()->toIso8601String(),
                'timeframe' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'label' => $label,
                ],
                'total_records' => $bookings->count(),
                'total_revenue' => round((float) $bookings->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CONFIRMED])->sum('total_price'), 2),
                'currency' => 'USD',
                'records' => $bookings,
            ], 'Business report data exported successfully.');
        }

        // CSV Stream Download
        $filename = 'business-report-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($bookings) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM so Microsoft Excel correctly displays UTF-8 strings
            fputs($output, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($output, [
                'Booking Reference',
                'Business Name',
                'Service',
                'Customer Name',
                'Customer Email',
                'Customer Phone',
                'Booking Date',
                'Booking Time',
                'Guests',
                'Total Price (USD)',
                'Status',
                'Payment Status',
                'Confirmed At',
                'Completed At',
                'Special Requests',
                'Created At',
            ]);

            foreach ($bookings as $booking) {
                fputcsv($output, [
                    $booking->booking_reference,
                    $booking->business?->name ?? 'N/A',
                    $booking->service?->name ?? 'General Booking',
                    $booking->customer_name,
                    $booking->customer_email,
                    $booking->customer_phone,
                    $booking->booking_date ? $booking->booking_date->format('Y-m-d') : 'N/A',
                    $booking->booking_time ?? 'N/A',
                    $booking->number_of_guests,
                    number_format($booking->total_price, 2, '.', ''),
                    ucfirst($booking->status),
                    ucfirst($booking->payment_status),
                    $booking->confirmed_at ? $booking->confirmed_at->toIso8601String() : '',
                    $booking->completed_at ? $booking->completed_at->toIso8601String() : '',
                    $booking->special_requests ?? '',
                    $booking->created_at ? $booking->created_at->toIso8601String() : '',
                ]);
            }

            fclose($output);
        }, $filename, $headers);
    }

    /**
     * Export report specifically for a single business.
     */
    public function exportBusiness(Request $request, int|string $id): JsonResponse|StreamedResponse
    {
        $request->merge(['business_id' => $id]);
        return $this->export($request);
    }

    /**
     * Resolve date range from request query parameters.
     */
    protected function resolveDateRange(Request $request): array
    {
        $now = Carbon::now();
        $timeframe = strtolower((string) $request->input('timeframe', 'this_year'));

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $start = $request->filled('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : $now->copy()->startOfYear();
            $end = $request->filled('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : $now->copy()->endOfDay();
            if ($start->gt($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }
            $diffDays = $start->diffInDays($end);
            $interval = $diffDays <= 31 ? 'day' : 'month';
            return [$start, $end, "Custom ({$start->format('Y-m-d')} to {$end->format('Y-m-d')})", $interval];
        }

        if (is_numeric($timeframe) && strlen($timeframe) === 4) {
            $year = (int) $timeframe;
            $start = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $end = Carbon::createFromDate($year, 12, 31)->endOfDay();
            return [$start, $end, "Year {$year}", 'month'];
        }

        return match ($timeframe) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                'Today (' . $now->format('M d, Y') . ')',
                'day',
            ],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
                'Yesterday (' . $now->copy()->subDay()->format('M d, Y') . ')',
                'day',
            ],
            'this_week' => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
                'This Week',
                'day',
            ],
            'last_week' => [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
                'Last Week',
                'day',
            ],
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'This Month (' . $now->format('M Y') . ')',
                'day',
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
                'Last Month (' . $now->copy()->subMonth()->format('M Y') . ')',
                'day',
            ],
            'last_year' => [
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
                'Last Year (' . ($now->year - 1) . ')',
                'month',
            ],
            'all_time' => [
                Carbon::createFromDate(2020, 1, 1)->startOfDay(),
                $now->copy()->addYears(5)->endOfYear(),
                'All Time',
                'month',
            ],
            default => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'This Year (' . $now->year . ')',
                'month',
            ],
        };
    }

    /**
     * Build time-series trends (day by day or month by month).
     */
    protected function buildTrends($bookings, Carbon $startDate, Carbon $endDate, string $interval): array
    {
        $trends = [];

        if ($interval === 'day') {
            $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay());

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $dayItems = $bookings->filter(function ($b) use ($dateStr) {
                    $bDate = $b->booking_date ? $b->booking_date->format('Y-m-d') : $b->created_at?->format('Y-m-d');
                    return $bDate === $dateStr;
                });

                $rev = $dayItems->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CONFIRMED])->sum('total_price');
                $bCount = $dayItems->count();
                $rCount = round((float) $rev, 2);
                $gCount = (int) $dayItems->sum('number_of_guests');

                $trends[] = [
                    'label' => $date->format('M d'),
                    'date' => $dateStr,
                    'bookings' => $bCount,
                    'revenue' => $rCount,
                    'guests' => $gCount,
                    'completed' => $dayItems->where('status', Booking::STATUS_COMPLETED)->count(),
                    'cancelled' => $dayItems->whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_REJECTED])->count(),
                    // Recharts & UI Compatibility Keys
                    'unitsSold' => $bCount,
                    'totalTransaction' => $rCount,
                    'visits' => $gCount,
                ];
            }
        } else {
            $cursor = $startDate->copy()->startOfMonth();
            $endMonth = $endDate->copy()->startOfMonth();

            while ($cursor->lte($endMonth)) {
                $yearMonth = $cursor->format('Y-m');
                $monthLabel = ($startDate->year === $endDate->year) ? $cursor->format('M') : $cursor->format('M Y');

                $monthItems = $bookings->filter(function ($b) use ($yearMonth) {
                    $bDate = $b->booking_date ? $b->booking_date->format('Y-m') : $b->created_at?->format('Y-m');
                    return $bDate === $yearMonth;
                });

                $rev = $monthItems->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CONFIRMED])->sum('total_price');
                $bCount = $monthItems->count();
                $rCount = round((float) $rev, 2);
                $gCount = (int) $monthItems->sum('number_of_guests');

                $trends[] = [
                    'label' => $monthLabel,
                    'date' => $cursor->format('Y-m-01'),
                    'year_month' => $yearMonth,
                    'bookings' => $bCount,
                    'revenue' => $rCount,
                    'guests' => $gCount,
                    'completed' => $monthItems->where('status', Booking::STATUS_COMPLETED)->count(),
                    'cancelled' => $monthItems->whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_REJECTED])->count(),
                    // Recharts & UI Compatibility Keys
                    'unitsSold' => $bCount,
                    'totalTransaction' => $rCount,
                    'visits' => $gCount,
                ];

                $cursor->addMonth();
            }
        }

        return $trends;
    }

    /**
     * Build top performing services breakdown.
     */
    protected function buildTopServices($bookings, array $businessIds, float $totalRevenue): array
    {
        $serviceGroups = $bookings->groupBy('service_id');
        $allServices = BusinessService::whereIn('business_id', $businessIds)->get();
        $servicesMap = $allServices->keyBy('id');
        $topServices = [];
        $processedServiceIds = [];

        foreach ($serviceGroups as $serviceId => $items) {
            if ($serviceId && $servicesMap->has($serviceId)) {
                $service = $servicesMap->get($serviceId);
                $serviceName = $service->name;
                $servicePrice = (float) $service->price;
                $processedServiceIds[] = (int) $serviceId;
            } elseif ($serviceId) {
                $service = BusinessService::find($serviceId);
                $serviceName = $service ? $service->name : "Service #{$serviceId}";
                $servicePrice = $service ? (float) $service->price : null;
                $processedServiceIds[] = (int) $serviceId;
            } else {
                $serviceName = 'Direct Business Reservation';
                $servicePrice = null;
            }

            $rev = (float) $items->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CONFIRMED])->sum('total_price');
            $guests = (int) $items->sum('number_of_guests');
            $count = $items->count();

            $share = $totalRevenue > 0 ? round(($rev / $totalRevenue) * 100, 1) : 0.0;

            $topServices[] = [
                'service_id' => $serviceId ?: null,
                'service_name' => $serviceName,
                'price' => $servicePrice,
                'bookings_count' => $count,
                'revenue' => round($rev, 2),
                'guests_count' => $guests,
                'share_percentage' => $share,
            ];
        }

        // Include all catalog services that currently have 0 bookings in this timeframe
        foreach ($allServices as $catalogService) {
            if (!in_array($catalogService->id, $processedServiceIds, true)) {
                $topServices[] = [
                    'service_id' => $catalogService->id,
                    'service_name' => $catalogService->name,
                    'price' => (float) $catalogService->price,
                    'bookings_count' => 0,
                    'revenue' => 0.0,
                    'guests_count' => 0,
                    'share_percentage' => 0.0,
                ];
            }
        }

        usort($topServices, function ($a, $b) {
            if ($b['revenue'] == $a['revenue']) {
                return $b['bookings_count'] <=> $a['bookings_count'];
            }
            return $b['revenue'] <=> $a['revenue'];
        });

        return $topServices;
    }

    /**
     * Build customer retention insights.
     */
    protected function buildCustomerInsights($bookings): array
    {
        $customerGroups = $bookings->groupBy(function ($b) {
            $email = strtolower(trim((string) $b->customer_email));
            if (!empty($email)) {
                return $email;
            }
            if ($b->user_id) {
                return 'user_' . $b->user_id;
            }
            return 'name_' . strtolower(trim((string) $b->customer_name));
        });

        $totalUniqueCustomers = $customerGroups->count();
        $repeatCustomersCount = 0;
        $topCustomers = [];

        foreach ($customerGroups as $key => $items) {
            $bookingCount = $items->count();
            if ($bookingCount > 1) {
                $repeatCustomersCount++;
            }

            $firstItem = $items->first();
            $customerSpent = (float) $items->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CONFIRMED])->sum('total_price');

            $topCustomers[] = [
                'customer_name' => $firstItem->customer_name,
                'customer_email' => $firstItem->customer_email,
                'customer_phone' => $firstItem->customer_phone,
                'bookings_count' => $bookingCount,
                'total_spent' => round($customerSpent, 2),
                'last_booking_date' => $items->max(fn($i) => $i->booking_date ? $i->booking_date->format('Y-m-d') : $i->created_at?->format('Y-m-d')),
            ];
        }

        usort($topCustomers, function ($a, $b) {
            if ($b['total_spent'] == $a['total_spent']) {
                return $b['bookings_count'] <=> $a['bookings_count'];
            }
            return $b['total_spent'] <=> $a['total_spent'];
        });

        $repeatRate = $totalUniqueCustomers > 0 ? round(($repeatCustomersCount / $totalUniqueCustomers) * 100, 1) : 0.0;

        return [
            'total_unique_customers' => $totalUniqueCustomers,
            'repeat_customers' => $repeatCustomersCount,
            'repeat_customer_rate' => $repeatRate,
            'top_customers' => array_slice($topCustomers, 0, 10),
        ];
    }

    /**
     * Empty summary template.
     */
    protected function emptySummary(): array
    {
        return [
            'total_revenue' => 0.0,
            'completed_revenue' => 0.0,
            'paid_revenue' => 0.0,
            'pending_revenue' => 0.0,
            'total_bookings' => 0,
            'completed_bookings' => 0,
            'confirmed_bookings' => 0,
            'pending_bookings' => 0,
            'cancelled_bookings' => 0,
            'rejected_bookings' => 0,
            'total_guests' => 0,
            'average_booking_value' => 0.0,
            'average_party_size' => 0.0,
            'cancellation_rate' => 0.0,
            'completion_rate' => 0.0,
            'currency' => 'USD',
        ];
    }

    /**
     * Empty ratings breakdown.
     */
    protected function emptyRatingsBreakdown(): array
    {
        return [
            'average_rating' => 0.0,
            'total_reviews' => 0,
            'approved_reviews' => 0,
            'replied_reviews' => 0,
            'response_rate' => 0.0,
            'stars' => [],
        ];
    }

    /**
     * Empty booking status breakdown.
     */
    protected function emptyBookingStatusBreakdown(): array
    {
        return [
            ['name' => 'Completed', 'status' => Booking::STATUS_COMPLETED, 'count' => 0, 'percentage' => 0.0, 'color' => '#10B981'],
            ['name' => 'Confirmed', 'status' => Booking::STATUS_CONFIRMED, 'count' => 0, 'percentage' => 0.0, 'color' => '#3B82F6'],
            ['name' => 'Pending Action', 'status' => Booking::STATUS_PENDING, 'count' => 0, 'percentage' => 0.0, 'color' => '#F59E0B'],
            ['name' => 'Cancelled', 'status' => Booking::STATUS_CANCELLED, 'count' => 0, 'percentage' => 0.0, 'color' => '#9CA3AF'],
            ['name' => 'Declined / Rejected', 'status' => Booking::STATUS_REJECTED, 'count' => 0, 'percentage' => 0.0, 'color' => '#EF4444'],
        ];
    }

    /**
     * Empty payment breakdown.
     */
    protected function emptyPaymentBreakdown(): array
    {
        return [
            'paid' => ['count' => 0, 'amount' => 0.0],
            'unpaid' => ['count' => 0, 'amount' => 0.0],
            'refunded' => ['count' => 0, 'amount' => 0.0],
        ];
    }
}
