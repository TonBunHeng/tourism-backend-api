<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('places');

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($request->query('all') === 'true') {
            $categories = $query->orderBy('name', 'asc')->get();
            return $this->successResponse(CategoryResource::collection($categories), 'Categories list retrieved successfully.');
        }

        $perPage = (int) $request->query('per_page', 20);
        $categories = $query->orderBy('name', 'asc')->paginate($perPage);

        return $this->successResponse(CategoryResource::collection($categories), 'Categories retrieved successfully.', 200, [
            'total' => $categories->total(),
            'per_page' => $categories->perPage(),
            'current_page' => $categories->currentPage(),
            'last_page' => $categories->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        $category = Category::create($validated);

        return $this->successResponse(new CategoryResource($category), 'Category created successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::withCount('places')->find($id);

        if (!$category) {
            return $this->errorResponse('Category not found.', 404);
        }

        return $this->successResponse(new CategoryResource($category), 'Category details retrieved successfully.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->errorResponse('Category not found.', 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $category->update($validated);

        return $this->successResponse(new CategoryResource($category), 'Category updated successfully.');
    }

    public function destroy(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->errorResponse('Category not found.', 404);
        }

        $category->delete();

        return $this->successResponse(null, 'Category deleted successfully.');
    }
}
