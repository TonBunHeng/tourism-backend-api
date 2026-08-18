<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventTag;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['place', 'province', 'tags']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('organizer', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            if ($status !== 'All') {
                $today = Carbon::today()->toDateString();
                if ($status === 'Upcoming') {
                    $query->where(function ($q) use ($today) {
                        $q->where('status', 'Upcoming')
                          ->orWhere(function ($sq) use ($today) {
                              $sq->whereNotIn('status', ['Cancelled', 'Completed'])
                                 ->where('start_date', '>', $today);
                          });
                    });
                } elseif ($status === 'Ongoing') {
                    $query->where(function ($q) use ($today) {
                        $q->where('status', 'Ongoing')
                          ->orWhere(function ($sq) use ($today) {
                              $sq->whereNotIn('status', ['Cancelled'])
                                 ->where('start_date', '<=', $today)
                                 ->where(function ($eq) use ($today) {
                                     $eq->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $today);
                                 });
                          });
                    });
                } elseif ($status === 'Completed') {
                    $query->where(function ($q) use ($today) {
                        $q->where('status', 'Completed')
                          ->orWhere(function ($sq) use ($today) {
                              $sq->whereNotIn('status', ['Cancelled'])
                                 ->whereNotNull('end_date')
                                 ->where('end_date', '<', $today);
                          });
                    });
                } else {
                    $query->where('status', $status);
                }
            }
        }

        if ($provinceId = $request->query('province_id')) {
            $query->where('province_id', $provinceId);
        }

        if ($placeId = $request->query('place_id')) {
            $query->where('place_id', $placeId);
        }

        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN);
            $query->where('featured', $featured);
        }

        $perPage = (int) $request->query('per_page', 10);
        $events = $query->orderBy('start_date', 'asc')->paginate($perPage);

        return $this->successResponse(EventResource::collection($events), 'Events retrieved successfully.', 200, [
            'total' => $events->total(),
            'per_page' => $events->perPage(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // Normalize status alias or auto-compute
        if (!$request->has('status') || $request->input('status') === 'Auto' || $request->input('status') === '') {
            $computed = Event::autoStatusFor($request->input('start_date'), $request->input('end_date'));
            $request->merge(['status' => $computed]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'place_id' => 'nullable|exists:places,id',
            'province_id' => 'nullable|exists:provinces,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|string|max:20',
            'attendees_count' => 'integer|min:0',
            'price' => 'nullable|string|max:50',
            'organizer' => 'nullable|string|max:150',
            'featured' => 'boolean',
            'rating' => 'numeric|min:0|max:5',
            'image_url' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => ['required', Rule::in(['Upcoming', 'Ongoing', 'Completed', 'Cancelled', 'Scheduled', 'Auto'])],
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'Auto') {
            $validated['status'] = Event::autoStatusFor($validated['start_date'], $validated['end_date'] ?? null);
        }

        if (isset($validated['image']) && !isset($validated['image_url'])) {
            $validated['image_url'] = $validated['image'];
        }
        unset($validated['image']);

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $event = Event::create($validated);

        foreach ($tags as $tagName) {
            EventTag::create([
                'event_id' => $event->id,
                'tag_name' => $tagName,
            ]);
        }

        $event->load(['place', 'province', 'tags']);

        return $this->successResponse(new EventResource($event), 'Event created successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $event = Event::with(['place', 'province', 'tags'])->find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        return $this->successResponse(new EventResource($event), 'Event details retrieved successfully.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        if ($request->input('status') === 'Auto') {
            $startDate = $request->input('start_date') ?? $event->start_date?->format('Y-m-d');
            $endDate = $request->input('end_date') ?? $event->end_date?->format('Y-m-d');
            $request->merge(['status' => Event::autoStatusFor($startDate, $endDate)]);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:200',
            'category' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'sometimes|required|string|max:255',
            'place_id' => 'nullable|exists:places,id',
            'province_id' => 'nullable|exists:provinces,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|string|max:20',
            'attendees_count' => 'sometimes|integer|min:0',
            'price' => 'nullable|string|max:50',
            'organizer' => 'nullable|string|max:150',
            'featured' => 'sometimes|boolean',
            'rating' => 'sometimes|numeric|min:0|max:5',
            'image_url' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => ['sometimes', Rule::in(['Upcoming', 'Ongoing', 'Completed', 'Cancelled', 'Scheduled', 'Auto'])],
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'Auto') {
            $startDate = $validated['start_date'] ?? $event->start_date?->format('Y-m-d');
            $endDate = $validated['end_date'] ?? $event->end_date?->format('Y-m-d');
            $validated['status'] = Event::autoStatusFor($startDate, $endDate);
        }

        if (isset($validated['image']) && !isset($validated['image_url'])) {
            $validated['image_url'] = $validated['image'];
        }
        unset($validated['image']);

        if (array_key_exists('tags', $validated)) {
            $tags = $validated['tags'] ?? [];
            unset($validated['tags']);

            EventTag::where('event_id', $event->id)->delete();
            foreach ($tags as $tagName) {
                EventTag::create([
                    'event_id' => $event->id,
                    'tag_name' => $tagName,
                ]);
            }
        }

        $event->update($validated);
        $event->load(['place', 'province', 'tags']);

        return $this->successResponse(new EventResource($event), 'Event updated successfully.');
    }

    public function destroy(string $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        $event->delete();

        return $this->successResponse(null, 'Event deleted successfully.');
    }
}
