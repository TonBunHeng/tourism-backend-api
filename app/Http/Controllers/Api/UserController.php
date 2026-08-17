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

        $perPage = (int) $request->query('per_page', 15);
        $users = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->successResponse(UserResource::collection($users), 'Users retrieved successfully.', 200, [
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
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
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:30',
            'avatar' => 'nullable|string|max:255',
            'role' => ['sometimes', Rule::in(['Super Admin', 'Admin', 'Guide / Editor', 'User'])],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'location' => 'nullable|string|max:100',
            'verified' => 'sometimes|boolean',
            'subscription' => ['sometimes', Rule::in(['Free', 'Basic', 'Premium'])],
            'activity_level' => ['sometimes', Rule::in(['Low', 'Medium', 'High'])],
            'bio' => 'nullable|string',
        ]);

        if (!empty($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
        }

        $user->update($validated);

        return $this->successResponse(new UserResource($user), 'User updated successfully.');
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully.');
    }
}
