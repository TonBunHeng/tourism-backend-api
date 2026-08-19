<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Travel\TravelFavoriteRequest;
use App\Http\Resources\Travel\TravelFavoriteResource;
use App\Models\Favorite;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelFavoriteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $favorites = Favorite::with(['place.category', 'place.province'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return $this->successResponse(
            TravelFavoriteResource::collection($favorites),
            'Favorites retrieved successfully.'
        );
    }

    public function store(TravelFavoriteRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $favorite = Favorite::firstOrCreate(
            [
                'user_id' => $user->id,
                'place_id' => $validated['place_id'],
            ],
            [
                'visited' => $validated['visited'] ?? false,
                'saved_date' => now()->toDateString(),
            ]
        );

        $favorite->load(['place.category', 'place.province']);

        return $this->successResponse(
            new TravelFavoriteResource($favorite),
            'Destination saved to favorites successfully.',
            201
        );
    }

    public function destroy(Request $request, string $placeId): JsonResponse
    {
        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where(function ($q) use ($placeId) {
                $q->where('place_id', $placeId)
                  ->orWhere('id', $placeId);
            })
            ->first();

        if (!$favorite) {
            return $this->errorResponse('Favorite not found in your wishlist.', 404);
        }

        $favorite->delete();

        return $this->successResponse(null, 'Removed from favorites successfully.');
    }

    public function toggleVisited(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('place_id', $id);
            })
            ->first();

        if (!$favorite) {
            return $this->errorResponse('Favorite item not found.', 404);
        }

        $favorite->update([
            'visited' => !$favorite->visited,
        ]);

        $favorite->load(['place.category', 'place.province']);

        return $this->successResponse(
            new TravelFavoriteResource($favorite),
            'Visited status updated successfully.'
        );
    }
}
