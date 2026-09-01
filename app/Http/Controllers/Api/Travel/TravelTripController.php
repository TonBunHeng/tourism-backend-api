<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Travel\StoreTripRequest;
use App\Http\Requests\Travel\UpdateTripRequest;
use App\Http\Resources\Travel\TripItineraryResource;
use App\Http\Resources\Travel\TripResource;
use App\Models\Trip;
use App\Models\TripItinerary;
use App\Services\AchievementManager;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TravelTripController extends Controller
{
    use ApiResponse;

    /**
     * List current authenticated user's trips.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Trip::where('user_id', $user->id)
            ->withCount('itineraries')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 12), 50);
        $trips = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Trips retrieved successfully.',
            'data' => TripResource::collection($trips->items()),
            'pagination' => [
                'current_page' => $trips->currentPage(),
                'last_page' => $trips->lastPage(),
                'per_page' => $trips->perPage(),
                'total' => $trips->total(),
            ],
        ]);
    }

    /**
     * Create a new trip plan with optional itinerary items.
     */
    public function store(StoreTripRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $trip = DB::transaction(function () use ($user, $validated) {
            $itinerariesData = $validated['itineraries'] ?? [];
            unset($validated['itineraries']);

            $validated['user_id'] = $user->id;
            $trip = Trip::create($validated);

            if (!empty($itinerariesData)) {
                foreach ($itinerariesData as $index => $item) {
                    $trip->itineraries()->create([
                        'place_id' => $item['place_id'] ?? null,
                        'day_number' => $item['day_number'] ?? 1,
                        'time_slot' => $item['time_slot'] ?? null,
                        'activity' => $item['activity'],
                        'estimated_cost' => $item['estimated_cost'] ?? 0,
                        'duration_minutes' => $item['duration_minutes'] ?? null,
                        'notes' => $item['notes'] ?? null,
                        'sort_order' => $item['sort_order'] ?? $index,
                        'is_completed' => $item['is_completed'] ?? false,
                    ]);
                }
            }

            return $trip;
        });

        // Trigger gamification check
        AchievementManager::checkAndAward($user);

        // Audit log
        AuditLogger::log('trip_created', 'Trip', $trip->id, "Created trip: {$trip->title}");

        $trip->load(['itineraries.place.category', 'itineraries.place.province']);

        return $this->successResponse(
            new TripResource($trip),
            'Trip created successfully.',
            201
        );
    }

    /**
     * Get single trip with full itineraries.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $trip = Trip::with(['itineraries.place.category', 'itineraries.place.province', 'user'])
            ->find($id);

        if (!$trip) {
            return $this->errorResponse('Trip not found.', 404);
        }

        $user = $request->user();
        if (!$trip->is_public && (!$user || ($trip->user_id !== $user->id && !$user->isAdmin()))) {
            return $this->errorResponse('Access denied. This trip is private.', 403);
        }

        return $this->successResponse(
            new TripResource($trip),
            'Trip details retrieved successfully.'
        );
    }

    /**
     * Update trip and optionally sync itinerary items.
     */
    public function update(UpdateTripRequest $request, $id): JsonResponse
    {
        $trip = Trip::find($id);

        if (!$trip) {
            return $this->errorResponse('Trip not found.', 404);
        }

        $user = $request->user();
        if ($trip->user_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied. You can only modify your own trips.', 403);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($trip, $validated) {
            $itinerariesData = $validated['itineraries'] ?? null;
            unset($validated['itineraries']);

            $trip->update($validated);

            if ($itinerariesData !== null) {
                // If provided, re-create / update itineraries
                $trip->itineraries()->delete();
                foreach ($itinerariesData as $index => $item) {
                    $trip->itineraries()->create([
                        'place_id' => $item['place_id'] ?? null,
                        'day_number' => $item['day_number'] ?? 1,
                        'time_slot' => $item['time_slot'] ?? null,
                        'activity' => $item['activity'],
                        'estimated_cost' => $item['estimated_cost'] ?? 0,
                        'duration_minutes' => $item['duration_minutes'] ?? null,
                        'notes' => $item['notes'] ?? null,
                        'sort_order' => $item['sort_order'] ?? $index,
                        'is_completed' => $item['is_completed'] ?? false,
                    ]);
                }
            }
        });

        $trip->load(['itineraries.place.category', 'itineraries.place.province']);

        return $this->successResponse(
            new TripResource($trip),
            'Trip updated successfully.'
        );
    }

    /**
     * Delete a trip.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $trip = Trip::find($id);

        if (!$trip) {
            return $this->errorResponse('Trip not found.', 404);
        }

        $user = $request->user();
        if ($trip->user_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied. You can only delete your own trips.', 403);
        }

        $tripTitle = $trip->title;
        $trip->delete();

        AuditLogger::log('trip_deleted', 'Trip', (int) $id, "Deleted trip: {$tripTitle}");

        return $this->successResponse(
            null,
            'Trip deleted successfully.'
        );
    }

    /**
     * Duplicate an existing trip into current user's trips.
     */
    public function duplicate(Request $request, $id): JsonResponse
    {
        $original = Trip::with('itineraries')->find($id);

        if (!$original) {
            return $this->errorResponse('Trip not found.', 404);
        }

        $user = $request->user();
        if (!$original->is_public && $original->user_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied to duplicate this trip.', 403);
        }

        $newTrip = DB::transaction(function () use ($original, $user) {
            $copy = Trip::create([
                'user_id' => $user->id,
                'title' => $original->title . ' (Copy)',
                'destination' => $original->destination,
                'start_date' => $original->start_date,
                'end_date' => $original->end_date,
                'budget' => $original->budget,
                'currency' => $original->currency,
                'travelers' => $original->travelers,
                'status' => 'planning',
                'notes' => $original->notes,
                'cover_image' => $original->cover_image,
                'is_public' => false,
            ]);

            foreach ($original->itineraries as $itinerary) {
                $copy->itineraries()->create([
                    'place_id' => $itinerary->place_id,
                    'day_number' => $itinerary->day_number,
                    'time_slot' => $itinerary->time_slot,
                    'activity' => $itinerary->activity,
                    'estimated_cost' => $itinerary->estimated_cost,
                    'duration_minutes' => $itinerary->duration_minutes,
                    'notes' => $itinerary->notes,
                    'sort_order' => $itinerary->sort_order,
                    'is_completed' => false,
                ]);
            }

            return $copy;
        });

        $newTrip->load(['itineraries.place.category', 'itineraries.place.province']);

        return $this->successResponse(
            new TripResource($newTrip),
            'Trip duplicated successfully.',
            201
        );
    }

    /**
     * Add single itinerary item to trip.
     */
    public function addItinerary(Request $request, $id): JsonResponse
    {
        $trip = Trip::find($id);

        if (!$trip) {
            return $this->errorResponse('Trip not found.', 404);
        }

        $user = $request->user();
        if ($trip->user_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $validated = $request->validate([
            'place_id' => 'nullable|integer|exists:places,id',
            'day_number' => 'required|integer|min:1',
            'time_slot' => 'nullable|string|max:50',
            'activity' => 'required|string|max:255',
            'estimated_cost' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
        ]);

        $maxSort = $trip->itineraries()->where('day_number', $validated['day_number'])->max('sort_order') ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? ($maxSort + 1);

        $itinerary = $trip->itineraries()->create($validated);
        $itinerary->load(['place.category', 'place.province']);

        return $this->successResponse(
            new TripItineraryResource($itinerary),
            'Itinerary item added successfully.',
            201
        );
    }

    /**
     * Delete single itinerary item.
     */
    public function deleteItinerary(Request $request, $id, $itineraryId): JsonResponse
    {
        $trip = Trip::find($id);
        if (!$trip) {
            return $this->errorResponse('Trip not found.', 404);
        }

        $user = $request->user();
        if ($trip->user_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $itinerary = TripItinerary::where('trip_id', $trip->id)->find($itineraryId);
        if (!$itinerary) {
            return $this->errorResponse('Itinerary item not found.', 404);
        }

        $itinerary->delete();

        return $this->successResponse(
            null,
            'Itinerary item removed successfully.'
        );
    }

    /**
     * Batch reorder itineraries.
     */
    public function reorderItineraries(Request $request, $id): JsonResponse
    {
        $trip = Trip::find($id);
        if (!$trip) {
            return $this->errorResponse('Trip not found.', 404);
        }

        $user = $request->user();
        if ($trip->user_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:trip_itineraries,id',
            'items.*.day_number' => 'required|integer|min:1',
            'items.*.sort_order' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated, $trip) {
            foreach ($validated['items'] as $item) {
                TripItinerary::where('id', $item['id'])
                    ->where('trip_id', $trip->id)
                    ->update([
                        'day_number' => $item['day_number'],
                        'sort_order' => $item['sort_order'],
                    ]);
            }
        });

        $trip->load(['itineraries.place.category', 'itineraries.place.province']);

        return $this->successResponse(
            new TripResource($trip),
            'Itinerary reordered successfully.'
        );
    }
}
