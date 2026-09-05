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

    protected static array $allowedRoleInputs = [
        User::ROLE_SUPER_ADMIN,
        User::ROLE_ADMIN,
        User::ROLE_GUIDE_EDITOR,
        User::ROLE_BUSINESS_OWNER,
        User::ROLE_USER,
        'Super Admin',
        'Admin',
        'Guide / Editor',
        'Business Owner',
        'User',
    ];

    public function index(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        if (!$currentUser || !$currentUser->isAdmin()) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $normalizedRole = User::normalizeRole($role);
            $query->where(function ($q) use ($normalizedRole, $role) {
                $q->where('role', $normalizedRole)
                  ->orWhere('role', $role);
            });
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
        if (!$currentUser || !$currentUser->isAdmin()) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:30',
            'avatar' => 'nullable|string',
            'role' => ['required', Rule::in(self::$allowedRoleInputs)],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'location' => 'nullable|string|max:100',
            'subscription' => ['nullable', Rule::in(['Free', 'Basic', 'Premium'])],
            'bio' => 'nullable|string',
        ]);

        $normalizedRole = User::normalizeRole($validated['role']);

        if ($normalizedRole === User::ROLE_SUPER_ADMIN && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return $this->errorResponse('Only Super Administrators can create Super Admin accounts.', 403);
        }

        $validated['role'] = $normalizedRole;
        $validated['password_hash'] = Hash::make($validated['password']);
        unset($validated['password']);

        $user = User::create($validated);

        return $this->successResponse(new UserResource($user), 'User created successfully.', 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $currentUser = $request->user();
        if (!$currentUser || (!$currentUser->isAdmin() && (int)$currentUser->id !== (int)$id)) {
            return $this->errorResponse('Access denied.', 403);
        }

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

        if (!$currentUser || (!$currentUser->isAdmin() && (int)$currentUser->id !== (int)$user->id)) {
            return $this->errorResponse('Access denied.', 403);
        }

        if ($user->isSuperAdmin() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return $this->errorResponse('Only Super Administrators can modify Super Admin accounts.', 403);
        }

        if ($user->isAdmin() && (!$currentUser || !$currentUser->isSuperAdmin()) && (int)$user->id !== (int)$currentUser->id) {
            return $this->errorResponse('Administrators cannot modify other Administrator accounts.', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:30',
            'avatar' => 'nullable|string',
            'role' => ['sometimes', Rule::in(self::$allowedRoleInputs)],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'location' => 'nullable|string|max:100',
            'verified' => 'sometimes|boolean',
            'subscription' => ['sometimes', Rule::in(['Free', 'Basic', 'Premium'])],
            'activity_level' => ['sometimes', Rule::in(['Low', 'Medium', 'High'])],
            'bio' => 'nullable|string',
        ]);

        if (isset($validated['role'])) {
            $normalizedRole = User::normalizeRole($validated['role']);
            if ($normalizedRole === User::ROLE_SUPER_ADMIN && (!$currentUser || !$currentUser->isSuperAdmin())) {
                return $this->errorResponse('Only Super Administrators can assign the Super Admin role.', 403);
            }
            $validated['role'] = $normalizedRole;
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

        if (!$currentUser || !$currentUser->isAdmin()) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

        if ($currentUser && (int)$user->id === (int)$currentUser->id) {
            return $this->errorResponse('You cannot delete your own account.', 422);
        }

        if ($user->isSuperAdmin() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return $this->errorResponse('Only Super Administrators can delete Super Admin accounts.', 403);
        }

        if ($user->isAdmin() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return $this->errorResponse('Administrators cannot delete other Administrator accounts.', 403);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully.');
    }

    /**
     * Update user account status (Active, Inactive, Suspended).
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $currentUser = $request->user();
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if (!$currentUser || !$currentUser->isAdmin()) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

        if ($user->isSuperAdmin() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return $this->errorResponse('Only Super Administrators can modify Super Admin account status.', 403);
        }

        if ($user->isAdmin() && (!$currentUser || !$currentUser->isSuperAdmin()) && (int)$user->id !== (int)$currentUser->id) {
            return $this->errorResponse('Administrators cannot modify other Administrator account status.', 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
        ]);

        $user->update(['status' => $validated['status']]);

        return $this->successResponse(new UserResource($user), 'User status updated successfully.');
    }

    /**
     * Get real-time user online and active tracking metrics.
     */
    public function activeStatus(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        if (!$currentUser || !$currentUser->isAdmin()) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

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
