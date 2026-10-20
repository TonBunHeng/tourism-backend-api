<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\NormalizesMediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminGuideController extends Controller
{
    use ApiResponse, NormalizesMediaUrl;

    /**
     * List all guides for admin panel with search and language filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where(function ($q) {
            $q->where('role', User::ROLE_GUIDE_EDITOR)
              ->orWhere('role', 'like', '%guide%')
              ->orWhere('role', 'like', '%editor%');
        });

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('license_no', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('specialties', 'like', "%{$search}%");
            });
        }

        if ($language = $request->query('language')) {
            if ($language !== 'All' && $language !== 'all') {
                $query->where('languages', 'like', "%{$language}%");
            }
        }

        if ($province = $request->query('province')) {
            if ($province !== 'All' && $province !== 'all') {
                $query->where('location', 'like', "%{$province}%");
            }
        }

        $perPage = min((int) $request->query('per_page', 50), 100);
        $users = $query->latest('id')->paginate($perPage);

        $guides = collect($users->items())->map(function ($u) {
            return $this->formatAdminGuide($u);
        });

        $totalGuides = User::where(function ($q) {
            $q->where('role', User::ROLE_GUIDE_EDITOR)
              ->orWhere('role', 'like', '%guide%');
        })->count();

        $activeGuides = User::where(function ($q) {
            $q->where('role', User::ROLE_GUIDE_EDITOR)
              ->orWhere('role', 'like', '%guide%');
        })->where('status', 'Active')->count();

        return $this->successResponse([
            'guides' => $guides,
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
            'meta' => [
                'total' => $totalGuides,
                'verified' => $activeGuides,
            ],
        ], 'Guides retrieved successfully.');
    }

    /**
     * Create a new guide user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'license_no' => 'nullable|string|max:50',
            'province' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'languages' => 'nullable',
            'daily_rate' => 'nullable|numeric|min:0',
            'experience_years' => 'nullable|integer|min:0',
            'specialties' => 'nullable|string',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',
            'status' => 'nullable|in:Active,Inactive,Suspended,verified,pending',
        ]);

        $languages = $validated['languages'] ?? ['Khmer', 'English'];
        if (is_string($languages)) {
            $languages = array_map('trim', explode(',', $languages));
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password_hash' => Hash::make($validated['password'] ?? 'password123'),
            'role' => User::ROLE_GUIDE_EDITOR,
            'status' => in_array($validated['status'] ?? 'Active', ['Active', 'verified']) ? 'Active' : 'Inactive',
            'location' => $validated['location'] ?? $validated['province'] ?? 'Siem Reap',
            'license_no' => $validated['license_no'] ?? ('MOT-KH-2026-' . rand(100, 999)),
            'languages' => $languages,
            'daily_rate' => $validated['daily_rate'] ?? 45.00,
            'experience_years' => $validated['experience_years'] ?? 5,
            'specialties' => $validated['specialties'] ?? $validated['bio'] ?? 'Ministry of Tourism Certified Tour Guide.',
            'bio' => $validated['bio'] ?? $validated['specialties'] ?? 'Professional tour guide.',
            'avatar' => $validated['avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
            'rating' => 5.00,
            'reviews_count' => 0,
            'verified' => true,
        ]);

        return $this->successResponse($this->formatAdminGuide($user), 'Guide created successfully.', 201);
    }

    /**
     * Show single guide details.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('Guide not found.', 404);
        }

        return $this->successResponse($this->formatAdminGuide($user), 'Guide details retrieved.');
    }

    /**
     * Update guide user details.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('Guide not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => "sometimes|required|email|unique:users,email,{$id}",
            'phone' => 'nullable|string|max:30',
            'license_no' => 'nullable|string|max:50',
            'province' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'languages' => 'nullable',
            'daily_rate' => 'nullable|numeric|min:0',
            'experience_years' => 'nullable|integer|min:0',
            'specialties' => 'nullable|string',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',
            'status' => 'nullable|in:Active,Inactive,Suspended,verified,pending',
        ]);

        if (isset($validated['languages']) && is_string($validated['languages'])) {
            $validated['languages'] = array_map('trim', explode(',', $validated['languages']));
        }

        if (isset($validated['province']) && !isset($validated['location'])) {
            $validated['location'] = $validated['province'];
        }

        if (isset($validated['status'])) {
            $validated['status'] = in_array($validated['status'], ['Active', 'verified']) ? 'Active' : 'Inactive';
        }

        $user->update($validated);

        return $this->successResponse($this->formatAdminGuide($user), 'Guide updated successfully.');
    }

    /**
     * Delete guide user.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('Guide not found.', 404);
        }

        $user->delete();
        return $this->successResponse(null, 'Guide deleted successfully.');
    }

    /**
     * Helper to format guide data for admin responses.
     */
    protected function formatAdminGuide(User $u): array
    {
        $avatarFallbacks = [
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=400&q=80',
        ];

        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone ?: '+855 12 345 678',
            'license_no' => $u->license_no ?: "MOT-KH-2022-{$u->id}",
            'province' => $u->location ?: 'Siem Reap & Angkor',
            'location' => $u->location ?: 'Siem Reap & Angkor',
            'languages' => !empty($u->languages) ? $u->languages : ['Khmer', 'English'],
            'experience_years' => (int) ($u->experience_years ?: 5),
            'daily_rate' => (float) ($u->daily_rate ?: 45.00),
            'rating' => (float) ($u->rating ?: 4.95),
            'reviews_count' => (int) ($u->reviews_count ?: 50),
            'status' => in_array($u->status, ['Active', 'verified']) ? 'verified' : 'pending',
            'avatar' => $this->normalizeUrl($u->avatar) ?: $avatarFallbacks[$u->id % count($avatarFallbacks)],
            'specialties' => $u->specialties ?: ($u->bio ?: 'Ministry of Tourism Certified Tour Guide & Cultural Docent.'),
            'bio' => $u->bio ?: $u->specialties,
        ];
    }
}
