<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\BlockedIp;
use App\Models\LoginAttempt;
use App\Models\Notification;
use App\Models\SecurityAlert;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request): JsonResponse
    {
        $ip = $request->ip();
        $throttleKey = 'admin_register:' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->errorResponse("Too many registration attempts. Please try again in {$seconds} seconds.", 429);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:30',
            'location' => 'nullable|string|max:100',
        ]);

        RateLimiter::hit($throttleKey, 60);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'role' => 'User', // Public registration is strictly restricted to regular User role
            'status' => 'Active',
            'location' => $validated['location'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        Notification::createNotification([
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

        $email = strtolower(trim($validated['email']));
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Check if source IP is blocked by administrators
        if ($ip && BlockedIp::isBlocked($ip)) {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => false,
                'failure_reason' => 'Access Denied: IP address blocked by administrator',
                'attempted_at' => now(),
            ]);

            SecurityAlert::create([
                'type' => 'blocked_ip_attempt',
                'email' => $email,
                'ip_address' => $ip,
                'attempts' => 1,
                'message' => "Access Denied: Blocked IP address ({$ip}) attempted login for account {$email}.",
                'is_read' => false,
                'data' => [
                    'email' => $email,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'attempted_at' => now()->toIso8601String(),
                ],
            ]);

            Notification::createNotification([
                'type' => 'alert',
                'category' => 'Security',
                'title' => 'Security Violation: Blocked IP Login Attempt',
                'description' => "Blocked IP address ({$ip}) attempted to log in using account {$email}.",
                'link' => '/notifications',
                'read' => false,
                'data' => [
                    'type' => 'blocked_ip_attempt',
                    'email' => $email,
                    'ip_address' => $ip,
                ],
            ]);

            return response()->json([
                'success' => false,
                'error' => 'IP_BLOCKED',
                'message' => "Access Denied: Your IP address ({$ip}) has been blocked by system administrators.",
                'ip_blocked' => true,
                'ip' => $ip,
            ], 403);
        }

        $throttleKey = 'admin_login:' . Str::lower($email) . '|' . $ip;

        // Rate limiting check (e.g., max 10 attempts in 5 minutes)
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->errorResponse("Too many login attempts. Please try again in {$seconds} seconds.", 429);
        }

        $user = User::where('email', $email)->first();

        // Failed credentials check
        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            RateLimiter::hit($throttleKey, 300);

            $failureReason = !$user ? 'User not found' : 'Invalid password';

            // Record failed attempt
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => false,
                'failure_reason' => $failureReason,
                'attempted_at' => now(),
            ]);

            // Count recent failed login attempts in last 30 minutes
            $recentFailedCount = LoginAttempt::where('email', $email)
                ->where('success', false)
                ->where('attempted_at', '>=', now()->subMinutes(30))
                ->count();

            $alertType = $recentFailedCount >= 6 ? 'failed_login_threshold' : 'failed_login';
            $alertMessage = $recentFailedCount >= 6
                ? "Multiple failed admin login attempts ({$recentFailedCount}) detected for account {$email} from IP {$ip}."
                : "Failed login attempt detected for account {$email} from IP {$ip} ({$failureReason}).";

            SecurityAlert::create([
                'type' => $alertType,
                'email' => $email,
                'ip_address' => $ip,
                'attempts' => $recentFailedCount,
                'message' => $alertMessage,
                'is_read' => false,
                'data' => [
                    'email' => $email,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'attempted_at' => now()->toIso8601String(),
                    'total_failures' => $recentFailedCount,
                    'reason' => $failureReason,
                ],
            ]);

            // At 6 or more failed attempts, create an urgent security notification
            if ($recentFailedCount >= 6) {
                Notification::createNotification([
                    'type' => 'alert',
                    'category' => 'Security',
                    'title' => 'Security Alert: Failed Login Threshold Exceeded',
                    'description' => "Account {$email} has {$recentFailedCount} failed login attempts from IP {$ip}.",
                    'link' => '/notifications',
                    'read' => false,
                    'data' => [
                        'type' => 'security_alert',
                        'email' => $email,
                        'ip_address' => $ip,
                        'attempts' => $recentFailedCount,
                    ],
                ]);
            }

            return $this->errorResponse('Invalid login credentials.', 401);
        }

        // Account status check
        if ($user->status !== 'Active') {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => false,
                'failure_reason' => 'Account ' . strtolower($user->status),
                'attempted_at' => now(),
            ]);

            SecurityAlert::create([
                'type' => 'inactive_account_login',
                'email' => $email,
                'ip_address' => $ip,
                'attempts' => 1,
                'message' => "Login attempt blocked: Account {$email} is " . strtolower($user->status) . ".",
                'is_read' => false,
                'data' => [
                    'email' => $email,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'status' => $user->status,
                ],
            ]);

            return $this->errorResponse('Account is ' . strtolower($user->status) . '. Please contact support.', 403);
        }

        // Admin Authorization check (Admin Panel is restricted to administrators)
        $allowedAdminRoles = ['Super Admin', 'Admin', 'Guide / Editor'];
        if (!in_array($user->role, $allowedAdminRoles, true)) {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => false,
                'failure_reason' => 'Unauthorized role for admin panel: ' . $user->role,
                'attempted_at' => now(),
            ]);

            SecurityAlert::create([
                'type' => 'unauthorized_access',
                'email' => $email,
                'ip_address' => $ip,
                'attempts' => 1,
                'message' => "Unauthorized Admin Access Attempt: User account {$email} with role '{$user->role}' attempted to log into web admin from IP {$ip}.",
                'is_read' => false,
                'data' => [
                    'email' => $email,
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'attempted_at' => now()->toIso8601String(),
                ],
            ]);

            Notification::createNotification([
                'type' => 'alert',
                'category' => 'Security',
                'title' => 'Security Alert: Unauthorized Admin Access Attempt',
                'description' => "User {$email} ({$user->role}) attempted to access Web Admin from IP {$ip}.",
                'link' => '/notifications',
                'read' => false,
                'data' => [
                    'type' => 'unauthorized_access',
                    'email' => $email,
                    'role' => $user->role,
                    'ip_address' => $ip,
                ],
            ]);

            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

        // Successful authentication
        LoginAttempt::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'success' => true,
            'attempted_at' => now(),
        ]);

        SecurityAlert::create([
            'type' => 'successful_login',
            'email' => $email,
            'ip_address' => $ip,
            'attempts' => 1,
            'message' => "Admin Login Event: User account {$email} ({$user->role}) successfully logged into web admin from IP {$ip}.",
            'is_read' => true,
            'data' => [
                'email' => $email,
                'user_id' => $user->id,
                'role' => $user->role,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'logged_in_at' => now()->toIso8601String(),
            ],
        ]);

        RateLimiter::clear($throttleKey);
        $user->update(['last_active_at' => now()]);

        $token = $user->createToken('admin_auth_token')->plainTextToken;

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

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            return $this->errorResponse('Current password does not match.', 422);
        }

        $user->update([
            'password_hash' => Hash::make($validated['new_password']),
        ]);

        return $this->successResponse(null, 'Password changed successfully.');
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
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return $this->successResponse(null, 'Logged out successfully.');
    }
}
