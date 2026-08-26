<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($request->boolean('online') || $request->query('filter') === 'online') {
            $query->where('last_active_at', '>=', now()->subMinutes(5));
        }

        $perPage = (int) $request->query('per_page', 15);
        $users = $query->orderBy('id', 'desc')->paginate($perPage);

        $onlineUsersCount = User::where('last_active_at', '>=', now()->subMinutes(5))->count();

        return $this->successResponse(UserResource::collection($users), 'Users retrieved successfully.', 200, [
            'total' => $users->total(),
            'online_users' => $onlineUsersCount,
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:30',
            'avatar' => 'nullable|string',
            'role' => ['required', Rule::in(['Super Admin', 'Admin', 'Guide / Editor', 'User'])],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'location' => 'nullable|string|max:100',
            'subscription' => ['nullable', Rule::in(['Free', 'Basic', 'Premium'])],
            'bio' => 'nullable|string',
        ]);

        if ($validated['role'] === 'Super Admin' && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return $this->errorResponse('Only Super Administrators can create Super Admin accounts.', 403);
        }

        $validated['password_hash'] = Hash::make($validated['password']);
        unset($validated['password']);

        $user = User::create($validated);

        return $this->successResponse(new UserResource($user), 'User created successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        return $this->successResponse(new UserResource($user), 'User details retrieved successfully.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $currentUser = $request->user();
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if ($user->isSuperAdmin() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return $this->errorResponse('Only Super Administrators can modify Super Admin accounts.', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:30',
            'avatar' => 'nullable|string',
            'role' => ['sometimes', Rule::in(['Super Admin', 'Admin', 'Guide / Editor', 'User'])],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'location' => 'nullable|string|max:100',
            'verified' => 'sometimes|boolean',
            'subscription' => ['sometimes', Rule::in(['Free', 'Basic', 'Premium'])],
            'activity_level' => ['sometimes', Rule::in(['Low', 'Medium', 'High'])],
            'bio' => 'nullable|string',
        ]);

        if (isset($validated['role']) && $validated['role'] === 'Super Admin' && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return $this->errorResponse('Only Super Administrators can assign the Super Admin role.', 403);
        }

        if (!empty($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
        }

        $user->update($validated);

        return $this->successResponse(new UserResource($user), 'User updated successfully.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $currentUser = $request->user();
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if ($currentUser && $user->id === $currentUser->id) {
            return $this->errorResponse('You cannot delete your own account.', 422);
        }

        if ($user->isSuperAdmin() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return $this->errorResponse('Only Super Administrators can delete Super Admin accounts.', 403);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully.');
    }

    /**
     * Get real-time user online and active tracking metrics.
     */
    public function activeStatus(): JsonResponse
    {
        $totalUsers = User::count();
        $onlineUsers = User::where('last_active_at', '>=', now()->subMinutes(5))->count();
        $activeUsers = User::where('status', 'Active')->count();
        $inactiveUsers = User::where('status', 'Inactive')->count();
        $suspendedUsers = User::where('status', 'Suspended')->count();

        $recentOnline = User::where('last_active_at', '>=', now()->subMinutes(60))
            ->orderBy('last_active_at', 'desc')
            ->limit(10)
            ->get();

        return $this->successResponse([
            'total_users' => $totalUsers,
            'online_users' => $onlineUsers,
            'offline_users' => max(0, $totalUsers - $onlineUsers),
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'suspended_users' => $suspendedUsers,
            'recent_online_users' => UserResource::collection($recentOnline),
        ], 'User active status metrics retrieved successfully.');
    }
}
