<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTourController extends Controller
{
    use ApiResponse;

    /**
     * List all tours for admin management with filters and search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tour::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($destination = $request->query('destination')) {
            if ($destination !== 'All') {
                $query->where(function ($q) use ($destination) {
                    $q->where('destination', $destination)
                      ->orWhere('province', $destination);
                });
            }
        }

        if ($status = $request->query('status')) {
            if ($status !== 'all' && $status !== 'All') {
                $query->where('status', $status);
            }
        }

        $perPage = min((int) $request->query('per_page', 50), 100);
        $tours = $query->latest('id')->paginate($perPage);

        return $this->successResponse([
            'tours' => $tours->items(),
            'pagination' => [
                'total' => $tours->total(),
                'per_page' => $tours->perPage(),
                'current_page' => $tours->currentPage(),
                'last_page' => $tours->lastPage(),
            ],
            'meta' => [
                'total' => Tour::count(),
                'active' => Tour::where('status', 'active')->count(),
                'avg_price' => round((float) Tour::avg('price'), 2),
                'avg_rating' => round((float) Tour::avg('rating'), 2),
            ],
        ], 'Tour packages retrieved successfully.');
    }

    /**
     * Create a new tour package.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'destination' => 'required|string|max:150',
            'province' => 'nullable|string|max:150',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'duration' => 'nullable|string|max:100',
            'duration_days' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'max_travelers' => 'nullable|integer|min:1',
            'group_size' => 'nullable|string|max:100',
            'difficulty' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'badge' => 'nullable|string|max:100',
            'image_url' => 'nullable|string',
            'description' => 'nullable|string',
            'highlights' => 'nullable',
            'itinerary' => 'nullable',
            'included' => 'nullable',
            'status' => 'nullable|in:active,draft,archived',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }

        if (isset($validated['highlights']) && is_string($validated['highlights'])) {
            $validated['highlights'] = array_map('trim', explode("\n", $validated['highlights']));
        }
        if (isset($validated['included']) && is_string($validated['included'])) {
            $validated['included'] = array_map('trim', explode(',', $validated['included']));
        }

        $tour = Tour::create($validated);

        AuditLogger::log(
            'tour_created',
            'Tour',
            $tour->id,
            "Created tour package: {$tour->title}",
            null,
            ['title' => $tour->title, 'destination' => $tour->destination, 'price' => $tour->price]
        );

        return $this->successResponse($tour, 'Tour package created successfully.', 201);
    }

    /**
     * Show single tour package.
     */
    public function show(int $id): JsonResponse
    {
        $tour = Tour::find($id);
        if (!$tour) {
            return $this->errorResponse('Tour package not found.', 404);
        }
        return $this->successResponse($tour, 'Tour package details retrieved.');
    }

    /**
     * Update an existing tour package.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tour = Tour::find($id);
        if (!$tour) {
            return $this->errorResponse('Tour package not found.', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'destination' => 'sometimes|required|string|max:150',
            'province' => 'nullable|string|max:150',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'duration' => 'nullable|string|max:100',
            'duration_days' => 'nullable|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'max_travelers' => 'nullable|integer|min:1',
            'group_size' => 'nullable|string|max:100',
            'difficulty' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'badge' => 'nullable|string|max:100',
            'image_url' => 'nullable|string',
            'description' => 'nullable|string',
            'highlights' => 'nullable',
            'itinerary' => 'nullable',
            'included' => 'nullable',
            'status' => 'nullable|in:active,draft,archived',
        ]);

        if (isset($validated['highlights']) && is_string($validated['highlights'])) {
            $validated['highlights'] = array_map('trim', explode("\n", $validated['highlights']));
        }
        if (isset($validated['included']) && is_string($validated['included'])) {
            $validated['included'] = array_map('trim', explode(',', $validated['included']));
        }

        $tour->update($validated);

        AuditLogger::log(
            'tour_updated',
            'Tour',
            $tour->id,
            "Updated tour package: {$tour->title}",
            null,
            ['title' => $tour->title, 'destination' => $tour->destination, 'price' => $tour->price]
        );

        return $this->successResponse($tour, 'Tour package updated successfully.');
    }

    /**
     * Delete a tour package.
     */
    public function destroy(int $id): JsonResponse
    {
        $tour = Tour::find($id);
        if (!$tour) {
            return $this->errorResponse('Tour package not found.', 404);
        }

        $title = $tour->title;
        $tour->delete();

        AuditLogger::log(
            'tour_deleted',
            'Tour',
            (int) $id,
            "Deleted tour package \"{$title}\" (ID: {$id})"
        );

        return $this->successResponse(null, 'Tour package deleted successfully.');
    }

    /**
     * Toggle status between active and draft.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $tour = Tour::find($id);
        if (!$tour) {
            return $this->errorResponse('Tour package not found.', 404);
        }

        $tour->status = $tour->status === 'active' ? 'draft' : 'active';
        $tour->save();

        AuditLogger::log(
            'tour_status_changed',
            'Tour',
            $tour->id,
            "Changed status of tour \"{$tour->title}\" to {$tour->status}"
        );

        return $this->successResponse($tour, "Tour status updated to {$tour->status}.");
    }
}
