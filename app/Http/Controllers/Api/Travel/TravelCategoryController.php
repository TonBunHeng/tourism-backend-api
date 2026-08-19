<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Resources\Travel\TravelCategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Category::where('status', 'Active')
            ->withCount(['places' => fn($q) => $q->where('status', 'Active')]);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('name', 'asc')->get();

        return $this->successResponse(
            TravelCategoryResource::collection($categories),
            'Categories retrieved successfully.'
        );
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::where('status', 'Active')
            ->withCount(['places' => fn($q) => $q->where('status', 'Active')])
            ->with([
                'places' => fn($q) => $q->where('status', 'Active')->with(['category', 'province'])->orderBy('rating', 'desc'),
            ])
            ->find($id);

        if (!$category) {
            return $this->errorResponse('Category not found.', 404);
        }

        return $this->successResponse(
            new TravelCategoryResource($category),
            'Category details retrieved successfully.'
        );
    }
}
