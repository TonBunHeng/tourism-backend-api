<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProvinceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Province::withCount(['places', 'events']);

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $perPage = (int) $request->query('per_page', 25);
        
        if ($request->query('all') === 'true') {
            $provinces = $query->orderBy('name', 'asc')->get();
            return $this->successResponse(ProvinceResource::collection($provinces), 'Provinces list retrieved successfully.');
        }

        $provinces = $query->orderBy('name', 'asc')->paginate($perPage);

        return $this->successResponse(ProvinceResource::collection($provinces), 'Provinces retrieved successfully.', 200, [
            'total' => $provinces->total(),
            'per_page' => $provinces->perPage(),
            'current_page' => $provinces->currentPage(),
            'last_page' => $provinces->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:provinces,name',
            'type' => ['required', Rule::in(['Capital City', 'Province', 'Municipality'])],
            'population' => 'nullable|string|max:50',
            'area' => 'nullable|string|max:50',
            'districts_count' => 'integer|min:0',
            'communes_count' => 'integer|min:0',
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'rating' => 'numeric|min:0|max:5',
        ]);

        $province = Province::create($validated);

        return $this->successResponse(new ProvinceResource($province), 'Province created successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $province = Province::withCount(['places', 'events'])->find($id);

        if (!$province) {
            return $this->errorResponse('Province not found.', 404);
        }

        return $this->successResponse(new ProvinceResource($province), 'Province details retrieved successfully.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $province = Province::find($id);

        if (!$province) {
            return $this->errorResponse('Province not found.', 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('provinces', 'name')->ignore($province->id)],
            'type' => ['sometimes', Rule::in(['Capital City', 'Province', 'Municipality'])],
            'population' => 'nullable|string|max:50',
            'area' => 'nullable|string|max:50',
            'districts_count' => 'sometimes|integer|min:0',
            'communes_count' => 'sometimes|integer|min:0',
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'rating' => 'sometimes|numeric|min:0|max:5',
        ]);

        $province->update($validated);

        return $this->successResponse(new ProvinceResource($province), 'Province updated successfully.');
    }

    public function destroy(string $id): JsonResponse
    {
        $province = Province::find($id);

        if (!$province) {
            return $this->errorResponse('Province not found.', 404);
        }

        $province->delete();

        return $this->successResponse(null, 'Province deleted successfully.');
    }
}
