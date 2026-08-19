<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Resources\Travel\TravelProvinceResource;
use App\Models\Province;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelProvinceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Province::where('status', 'Active')
            ->withCount([
                'places' => fn($q) => $q->where('status', 'Active'),
                'events' => fn($q) => $q->where('status', '!=', 'Cancelled'),
            ]);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $provinces = $query->orderBy('name', 'asc')->get();

        return $this->successResponse(
            TravelProvinceResource::collection($provinces),
            'Provinces retrieved successfully.'
        );
    }

    public function show(string $id): JsonResponse
    {
        $province = Province::where('status', 'Active')
            ->withCount([
                'places' => fn($q) => $q->where('status', 'Active'),
                'events' => fn($q) => $q->where('status', '!=', 'Cancelled'),
            ])
            ->with([
                'places' => fn($q) => $q->where('status', 'Active')->with(['category', 'province'])->orderBy('rating', 'desc'),
                'events' => fn($q) => $q->where('status', '!=', 'Cancelled')->orderBy('start_date', 'asc'),
            ])
            ->find($id);

        if (!$province) {
            return $this->errorResponse('Province not found.', 404);
        }

        return $this->successResponse(
            new TravelProvinceResource($province),
            'Province details retrieved successfully.'
        );
    }
}
