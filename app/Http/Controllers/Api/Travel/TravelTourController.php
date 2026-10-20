<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelTourController extends Controller
{
    use ApiResponse;

    /**
     * Public listing of tour packages for tourists.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tour::where('status', 'active');

        // Search by title, destination, or description
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by Destination
        if ($destination = $request->query('destination')) {
            if ($destination !== 'All') {
                $query->where(function ($q) use ($destination) {
                    $q->where('destination', 'like', "%{$destination}%")
                      ->orWhere('province', 'like', "%{$destination}%");
                });
            }
        }

        // Filter by Duration
        if ($duration = $request->query('duration')) {
            if ($duration === '1') {
                $query->where('duration_days', 1);
            } elseif ($duration === '2-3') {
                $query->whereBetween('duration_days', [2, 3]);
            } elseif ($duration === '4+') {
                $query->where('duration_days', '>=', 4);
            }
        }

        // Sorting
        $sortBy = $request->query('sort', 'popular');
        match ($sortBy) {
            'price-low' => $query->orderBy('price', 'asc'),
            'price-high' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderBy('rating', 'desc'),
            'duration' => $query->orderBy('duration_days', 'asc'),
            default => $query->orderBy('rating', 'desc')->orderBy('reviews_count', 'desc'),
        };

        $perPage = min((int) $request->query('per_page', 24), 50);
        $tours = $query->paginate($perPage);

        // Map items to guarantee rich fallback values for UI components
        $formattedTours = collect($tours->items())->map(function ($tour) {
            return [
                'id' => $tour->id,
                'slug' => $tour->slug,
                'title' => $tour->title,
                'destination' => $tour->destination,
                'province' => $tour->province ?: $tour->destination,
                'duration' => $tour->duration ?: "{$tour->duration_days} Days",
                'durationDays' => (int) $tour->duration_days,
                'price' => (float) $tour->price,
                'currency' => $tour->currency ?: 'USD',
                'rating' => (float) $tour->rating,
                'reviewsCount' => (int) $tour->reviews_count,
                'badge' => $tour->badge ?: 'Top Rated',
                'category' => $tour->category ?: 'Cultural',
                'image' => $tour->image_url,
                'description' => $tour->description,
                'highlights' => $tour->highlights ?: [],
                'itinerary' => $tour->itinerary ?: [],
                'included' => $tour->included ?: [],
                'groupSize' => $tour->group_size ?: 'Max 10 guests',
                'difficulty' => $tour->difficulty ?: 'Easy Walking',
                'business_id' => 1,
            ];
        });

        // Unique destinations for dynamic filtering
        $allDestinations = Tour::where('status', 'active')
            ->distinct()
            ->pluck('destination')
            ->filter()
            ->values();

        return $this->successResponse([
            'tours' => $formattedTours,
            'destinations' => ['All', ...$allDestinations],
            'pagination' => [
                'total' => $tours->total(),
                'per_page' => $tours->perPage(),
                'current_page' => $tours->currentPage(),
                'last_page' => $tours->lastPage(),
            ],
        ], 'Tour packages retrieved successfully.');
    }

    /**
     * Show single tour package by ID or slug.
     */
    public function show(string|int $id): JsonResponse
    {
        $tour = Tour::where(function ($q) use ($id) {
            if (is_numeric($id)) {
                $q->where('id', (int) $id);
            } else {
                $q->where('slug', $id);
            }
        })->where('status', 'active')->first();

        if (!$tour) {
            return $this->errorResponse('Tour package not found.', 404);
        }

        $formatted = [
            'id' => $tour->id,
            'slug' => $tour->slug,
            'title' => $tour->title,
            'destination' => $tour->destination,
            'province' => $tour->province ?: $tour->destination,
            'duration' => $tour->duration,
            'durationDays' => (int) $tour->duration_days,
            'price' => (float) $tour->price,
            'currency' => $tour->currency ?: 'USD',
            'rating' => (float) $tour->rating,
            'reviewsCount' => (int) $tour->reviews_count,
            'badge' => $tour->badge,
            'category' => $tour->category,
            'image' => $tour->image_url,
            'description' => $tour->description,
            'highlights' => $tour->highlights ?: [],
            'itinerary' => $tour->itinerary ?: [],
            'included' => $tour->included ?: [],
            'groupSize' => $tour->group_size,
            'difficulty' => $tour->difficulty,
            'business_id' => 1,
        ];

        return $this->successResponse($formatted, 'Tour package details retrieved.');
    }
}
