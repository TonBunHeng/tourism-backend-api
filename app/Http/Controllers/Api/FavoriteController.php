<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $favorites = Favorite::with('place.category', 'place.province')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

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
}
