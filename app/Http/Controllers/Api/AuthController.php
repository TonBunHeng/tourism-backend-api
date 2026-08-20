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
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:30',
            'role' => ['nullable', Rule::in(['Super Admin', 'Admin', 'Guide / Editor', 'User'])],
            'location' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'User',
            'status' => 'Active',
            'location' => $validated['location'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        \App\Models\Notification::createNotification([
            'type' => 'user',
            'category' => 'Users',
            'title' => "New User Registered: {$user->name}",
            'description' => "{$user->name} ({$user->email}) registered as {$user->role}.",
            'link' => '/users',
            'read' => false,
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'User registered successfully.', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            return $this->errorResponse('Invalid login credentials.', 401);
        }

        if ($user->status !== 'Active') {
            return $this->errorResponse('Account is ' . strtolower($user->status) . '. Please contact support.', 403);
        }

        $user->update(['last_active_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Login successful.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(new UserResource($request->user()), 'User profile retrieved successfully.');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'avatar' => 'nullable|string',
            'image' => 'nullable|string',
            'location' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'password' => 'nullable|string|min:6',
        ]);

        if (isset($validated['image']) && !isset($validated['avatar'])) {
            $validated['avatar'] = $validated['image'];
        }
        unset($validated['image']);

        if (isset($validated['address']) && !isset($validated['location'])) {
            $validated['location'] = $validated['address'];
        }
        unset($validated['address']);

        if (!empty($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
        }

        $user->update($validated);

        return $this->successResponse(new UserResource($user), 'Profile updated successfully.');
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'avatar' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $avatar = $validated['avatar'] ?? $validated['image'] ?? null;
        $user->update(['avatar' => $avatar]);

        return $this->successResponse(new UserResource($user), 'Avatar updated successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully.');
    }
}
