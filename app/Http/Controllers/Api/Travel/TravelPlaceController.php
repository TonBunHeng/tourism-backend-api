<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Resources\Travel\TravelPlaceResource;
use App\Models\Category;
use App\Models\Place;
use App\Models\Province;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelPlaceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Place::with(['category', 'province'])
            ->where('status', 'Active');

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
                    $query->where('id', 0);
                }
            }
        }

        if ($provinceId = $request->query('province_id')) {
            $query->where('province_id', $provinceId);
        } elseif ($province = $request->query('province')) {
            if ($province !== 'All') {
                $provModel = Province::where('name', $province)->first();
                if ($provModel) {
                    $query->where('province_id', $provModel->id);
                } else {
                    $query->where('id', 0);
                }
            }
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', (float) $request->query('min_rating'));
        } elseif ($request->has('rating')) {
            $rating = $request->query('rating');
            if (is_numeric($rating)) {
                $query->where('rating', '>=', (float) $rating);
            }
        }

        if ($price = $request->query('price')) {
            if ($price === 'Free' || $price === 'free') {
                $query->where('price', 'like', '%Free%');
            } elseif ($price !== 'All') {
                $query->where('price', 'like', "%{$price}%");
            }
        }

        if ($request->has('featured')) {
            $isFeatured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_featured', $isFeatured);
        }

        $sortBy = $request->query('sort_by', 'popular');
        switch ($sortBy) {
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'popular':
            case 'visitors':
                $query->orderBy('visitors_count', 'desc')->orderBy('rating', 'desc');
                break;
            case 'reviews':
                $query->orderBy('reviews_count', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
                $query->orderBy('id', 'desc');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $perPage = (int) $request->query('per_page', 12);
        $places = $query->paginate($perPage);

        return $this->successResponse(
            TravelPlaceResource::collection($places),
            'Destinations retrieved successfully.',
            200,
            [
                'total' => $places->total(),
                'per_page' => $places->perPage(),
                'current_page' => $places->currentPage(),
                'last_page' => $places->lastPage(),
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $place = Place::with([
            'category',
            'province',
            'galleryMedia',
            'reviews' => function ($q) {
                $q->where('status', 'Approved')->orderBy('id', 'desc');
            },
            'reviews.user',
            'reviews.images',
            'reviews.replies.user'
        ])
        ->where('status', 'Active')
        ->find($id);

        if (!$place) {
            return $this->errorResponse('Destination not found or not active.', 404);
        }

        // Increment visitors count safely
        $place->increment('visitors_count');

        return $this->successResponse(
            new TravelPlaceResource($place),
            'Destination details retrieved successfully.'
        );
    }
}
