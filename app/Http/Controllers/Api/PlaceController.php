<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Category;
use App\Models\Place;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlaceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Place::with(['category', 'province']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        } elseif ($category = $request->query('category')) {
            if ($category !== 'All') {
                $catModel = Category::where('name', $category)->first();
                if ($catModel) {
                    $query->where('category_id', $catModel->id);
                } else {
                    $query->where('id', 0); // No match
                }
            }
        }

        if ($provinceId = $request->query('province_id')) {
            $query->where('province_id', $provinceId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($request->has('featured')) {
            $isFeatured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_featured', $isFeatured);
        }

        $perPage = (int) $request->query('per_page', 10);
        $places = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->successResponse(PlaceResource::collection($places), 'Places retrieved successfully.', 200, [
            'total' => $places->total(),
            'per_page' => $places->perPage(),
            'current_page' => $places->currentPage(),
            'last_page' => $places->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'province_id' => 'nullable|exists:provinces,id',
            'address' => 'required|string|max:255',
            'coordinates' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'best_time' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:50',
            'price' => 'nullable|string|max:50',
            'rating' => 'numeric|min:0|max:5',
            'image_url' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Pending'])],
        ]);

        $place = Place::create($validated);
        $place->load(['category', 'province']);

        return $this->successResponse(new PlaceResource($place), 'Place created successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $place = Place::with(['category', 'province', 'reviews.user', 'reviews.images', 'reviews.replies.user'])->find($id);

        if (!$place) {
            return $this->errorResponse('Place not found.', 404);
        }

        return $this->successResponse(new PlaceResource($place), 'Place details retrieved successfully.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $place = Place::find($id);

        if (!$place) {
            return $this->errorResponse('Place not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'category_id' => 'sometimes|required|exists:categories,id',
            'province_id' => 'nullable|exists:provinces,id',
            'address' => 'sometimes|required|string|max:255',
            'coordinates' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'best_time' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:50',
            'price' => 'nullable|string|max:50',
            'rating' => 'sometimes|numeric|min:0|max:5',
            'image_url' => 'nullable|string|max:255',
            'is_featured' => 'sometimes|boolean',
            'status' => ['sometimes', Rule::in(['Active', 'Inactive', 'Pending'])],
        ]);

        $place->update($validated);
        $place->load(['category', 'province']);

        return $this->successResponse(new PlaceResource($place), 'Place updated successfully.');
    }

    public function destroy(string $id): JsonResponse
    {
        $place = Place::find($id);

        if (!$place) {
            return $this->errorResponse('Place not found.', 404);
        }

        $place->delete();

        return $this->successResponse(null, 'Place deleted successfully.');
    }
}
