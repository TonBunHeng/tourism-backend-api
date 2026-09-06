<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Travel\TravelAvatarUploadRequest;
use App\Http\Requests\Travel\TravelFacebookLoginRequest;
use App\Http\Requests\Travel\TravelGoogleLoginRequest;
use App\Http\Requests\Travel\TravelLoginRequest;
use App\Http\Requests\Travel\TravelPasswordUpdateRequest;
use App\Http\Requests\Travel\TravelProfileUpdateRequest;
use App\Http\Requests\Travel\TravelRegisterRequest;
use App\Http\Resources\Travel\TravelUserResource;
use App\Models\BlockedIp;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class TravelAuthController extends Controller
{
    use ApiResponse;

    public function register(TravelRegisterRequest $request): JsonResponse
    {
        $ip = $request->ip();
        $throttleKey = 'travel_register:' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->errorResponse("Too many registration attempts. Please try again in {$seconds} seconds.", 429);
        }

        $validated = $request->validated();
        RateLimiter::hit($throttleKey, 60);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'location' => $validated['location'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'role' => User::ROLE_USER,
            'status' => 'Active',
            'verified' => true,
            'email_verified_at' => now(),
            'last_active_at' => now(),
        ]);

        $token = $user->createToken('travel_auth_token')->plainTextToken;

        \App\Models\Notification::createNotification([
            'type' => 'user',
            'category' => 'Users',
            'title' => "New User Registered: {$user->name}",
            'description' => "{$user->name} ({$user->email}) joined AngkorVerses platform.",
            'link' => '/users',
            'read' => false,
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'location' => $user->location,
            ]
        ]);

        return $this->createdResponse([
            'user' => new TravelUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful. Welcome to AngkorVerses!');
    }

    public function login(TravelLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $email = strtolower(trim($validated['email']));
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        if ($ip && BlockedIp::isBlocked($ip)) {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => false,
                'failure_reason' => 'Access Denied: IP address blocked by administrator',
                'attempted_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'IP_BLOCKED',
                'message' => "Access Denied: Your IP address ({$ip}) has been blocked by system administrators.",
                'ip_blocked' => true,
                'ip' => $ip,
            ], 403);
        }

        $throttleKey = 'travel_login:' . Str::lower($email) . '|' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->errorResponse("Too many login attempts. Please try again in {$seconds} seconds.", 429);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            RateLimiter::hit($throttleKey, 300);

            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => false,
                'failure_reason' => !$user ? 'User not found' : 'Invalid password',
                'attempted_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if ($user->status !== 'Active') {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => false,
                'failure_reason' => 'Account ' . strtolower($user->status),
                'attempted_at' => now(),
            ]);

            return $this->errorResponse('Your account is currently ' . strtolower($user->status) . '. Please contact support.', 403);
        }

        $normalizedRole = User::normalizeRole($user->role);
        if (in_array($normalizedRole, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_GUIDE_EDITOR], true)) {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => false,
                'failure_reason' => 'Administrative account restricted from travel login: ' . $user->role,
                'attempted_at' => now(),
            ]);

            return $this->errorResponse('Access restricted. Administrative accounts (Super Admin, Admin, Tourism Content Editor) must sign in via the Admin Portal.', 403);
        }

        LoginAttempt::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'success' => true,
            'attempted_at' => now(),
        ]);

        RateLimiter::clear($throttleKey);
        $user->update(['last_active_at' => now()]);
        $token = $user->createToken('travel_auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new TravelUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            new TravelUserResource($request->user()),
            'Tourist profile retrieved successfully.'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully.');
    }

    public function updateProfile(TravelProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if (isset($validated['address']) && !isset($validated['location'])) {
            $validated['location'] = $validated['address'];
        }
        unset($validated['address']);

        $user->update($validated);

        return $this->successResponse(
            new TravelUserResource($user),
            'Profile updated successfully.'
        );
    }

    public function updatePassword(TravelPasswordUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided current password is incorrect.'],
            ]);
        }

        $user->update([
            'password_hash' => Hash::make($validated['password']),
        ]);

        return $this->successResponse(null, 'Password updated successfully.');
    }

    public function uploadAvatar(TravelAvatarUploadRequest $request): JsonResponse
    {
        $user = $request->user();
        $file = $request->file('avatar');

        if (!$file || !$file->isValid()) {
            return $this->errorResponse('Invalid avatar file provided.', 422);
        }

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = 'avatar_' . $user->id . '_' . time() . '_' . Str::random(6) . '.' . $extension;

        $path = $file->storeAs('avatars', $filename, 'public');
        $url = Storage::disk('public')->url($path);
        $fullUrl = url($url);

        $user->update(['avatar' => $fullUrl]);

        return $this->successResponse([
            'avatar' => $fullUrl,
            'relative_url' => $url,
            'user' => new TravelUserResource($user),
        ], 'Avatar uploaded and profile updated successfully.');
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['avatar' => null]);

        return $this->successResponse([
            'user' => new TravelUserResource($user),
        ], 'Avatar removed successfully.');
    }

    public function googleLogin(TravelGoogleLoginRequest $request): JsonResponse
    {
        $ip = $request->ip();
        $throttleKey = 'travel_oauth_google:' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->errorResponse("Too many authentication attempts. Please try again in {$seconds} seconds.", 429);
        }

        $validated = $request->validated();
        RateLimiter::hit($throttleKey, 60);

        $googleId = $validated['google_id'] ?? null;
        $email = $validated['email'] ?? null;
        $name = $validated['name'] ?? null;
        $avatar = $validated['avatar'] ?? null;

        // If an ID token or access token was provided, attempt verification with Google
        $idToken = $validated['id_token'] ?? $validated['token'] ?? null;
        $accessToken = $validated['access_token'] ?? null;

        if ($idToken && (!$googleId || !$email)) {
            try {
                $response = Http::get("https://oauth2.googleapis.com/tokeninfo", [
                    'id_token' => $idToken,
                ]);

                if ($response->successful()) {
                    $payload = $response->json();
                    $googleId = $payload['sub'] ?? $googleId;
                    $email = $payload['email'] ?? $email;
                    $name = $payload['name'] ?? $name;
                    $avatar = $payload['picture'] ?? $avatar;
                }
            } catch (\Throwable $e) {
                // proceed with client payload
            }
        }

        if ($accessToken && (!$googleId || !$email)) {
            try {
                $response = Http::withToken($accessToken)->get("https://www.googleapis.com/oauth2/v3/userinfo");
                if ($response->successful()) {
                    $payload = $response->json();
                    $googleId = $payload['sub'] ?? $googleId;
                    $email = $payload['email'] ?? $email;
                    $name = $payload['name'] ?? $name;
                    $avatar = $payload['picture'] ?? $avatar;
                }
            } catch (\Throwable $e) {
                // proceed with client payload
            }
        }

        if (!$email && !$googleId) {
            return $this->errorResponse('Unable to identify Google account. Please provide valid Google account details.', 422);
        }

        $user = null;
        if ($googleId) {
            $user = User::where('provider', 'google')->where('provider_id', $googleId)->first();
        }

        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $googleId ?: ('google_' . md5($email)),
                    'provider_email' => $email,
                    'avatar' => $user->avatar ?: $avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'last_active_at' => now(),
                ]);
            }
        }

        if (!$user) {
            $user = User::create([
                'name' => $name ?: 'Google Traveler',
                'email' => $email ?: (($googleId ?: ('google_' . time())) . '@google.oauth'),
                'avatar' => $avatar ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80',
                'role' => 'User',
                'status' => 'Active',
                'verified' => true,
                'provider' => 'google',
                'provider_id' => $googleId ?: ('google_' . md5($email)),
                'provider_email' => $email,
                'email_verified_at' => now(),
                'last_active_at' => now(),
            ]);
        } else {
            if ($user->status !== 'Active') {
                return $this->errorResponse('Account is ' . strtolower($user->status) . '. Please contact support.', 403);
            }
            $user->update([
                'last_active_at' => now(),
                'avatar' => $avatar ?: $user->avatar,
            ]);
        }

        RateLimiter::clear($throttleKey);
        $token = $user->createToken('travel_google_auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new TravelUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Google authentication successful.');
    }

    public function facebookLogin(TravelFacebookLoginRequest $request): JsonResponse
    {
        $ip = $request->ip();
        $throttleKey = 'travel_oauth_facebook:' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->errorResponse("Too many authentication attempts. Please try again in {$seconds} seconds.", 429);
        }

        $validated = $request->validated();
        RateLimiter::hit($throttleKey, 60);

        $fbId = $validated['facebook_id'] ?? null;
        $email = $validated['email'] ?? null;
        $name = $validated['name'] ?? null;
        $avatar = $validated['avatar'] ?? null;
        $accessToken = $validated['access_token'] ?? $validated['token'] ?? null;

        if ($accessToken && (!$fbId || !$email)) {
            try {
                $response = Http::get("https://graph.facebook.com/me", [
                    'fields' => 'id,name,email,picture.type(large)',
                    'access_token' => $accessToken,
                ]);

                if ($response->successful()) {
                    $payload = $response->json();
                    $fbId = $payload['id'] ?? $fbId;
                    $email = $payload['email'] ?? $email;
                    $name = $payload['name'] ?? $name;
                    $avatar = $payload['picture']['data']['url'] ?? $avatar;
                }
            } catch (\Throwable $e) {
                // proceed with client payload
            }
        }

        if (!$email && !$fbId) {
            return $this->errorResponse('Unable to identify Facebook account. Please provide valid Facebook account details.', 422);
        }

        $user = null;
        if ($fbId) {
            $user = User::where('provider', 'facebook')->where('provider_id', $fbId)->first();
        }

        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'provider' => 'facebook',
                    'provider_id' => $fbId ?: ('facebook_' . md5($email)),
                    'provider_email' => $email,
                    'avatar' => $user->avatar ?: $avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'last_active_at' => now(),
                ]);
            }
        }

        if (!$user) {
            $user = User::create([
                'name' => $name ?: 'Facebook Traveler',
                'email' => $email ?: (($fbId ?: ('facebook_' . time())) . '@facebook.oauth'),
                'avatar' => $avatar,
                'role' => 'User',
                'status' => 'Active',
                'verified' => true,
                'provider' => 'facebook',
                'provider_id' => $fbId ?: ('facebook_' . md5($email)),
                'provider_email' => $email,
                'email_verified_at' => now(),
                'last_active_at' => now(),
            ]);
        } else {
            if ($user->status !== 'Active') {
                return $this->errorResponse('Account is ' . strtolower($user->status) . '. Please contact support.', 403);
            }
            $user->update([
                'last_active_at' => now(),
                'avatar' => $avatar ?: $user->avatar,
            ]);
        }

        RateLimiter::clear($throttleKey);
        $token = $user->createToken('travel_facebook_auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new TravelUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Facebook authentication successful.');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5174');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return redirect($frontendUrl . '/login?error=' . urlencode('Google authentication failed.'));
        }

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName();
        $avatar = $googleUser->getAvatar();

        $user = User::where('provider', 'google')->where('provider_id', $googleId)->first();

        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $googleId,
                    'provider_email' => $email,
                    'avatar' => $user->avatar ?: $avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'last_active_at' => now(),
                ]);
            }
        }

        if (!$user) {
            $user = User::create([
                'name' => $name ?: 'Google Traveler',
                'email' => $email ?: ($googleId . '@google.oauth'),
                'avatar' => $avatar,
                'role' => User::ROLE_USER,
                'status' => 'Active',
                'verified' => true,
                'provider' => 'google',
                'provider_id' => $googleId,
                'provider_email' => $email,
                'email_verified_at' => now(),
                'last_active_at' => now(),
            ]);
        } else {
            if ($user->status !== 'Active') {
                return redirect($frontendUrl . '/login?error=' . urlencode('Account is ' . strtolower($user->status)));
            }
            $user->update([
                'last_active_at' => now(),
                'avatar' => $avatar ?: $user->avatar,
            ]);
        }

        $token = $user->createToken('travel_google_auth_token')->plainTextToken;

        return redirect($frontendUrl . '/auth/callback?token=' . urlencode($token) . '&user=' . urlencode(json_encode(new TravelUserResource($user))));
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function handleFacebookCallback()
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5174');

        try {
            $fbUser = Socialite::driver('facebook')->stateless()->user();
        } catch (\Throwable $e) {
            return redirect($frontendUrl . '/login?error=' . urlencode('Facebook authentication failed.'));
        }

        $fbId = $fbUser->getId();
        $email = $fbUser->getEmail();
        $name = $fbUser->getName();
        $avatar = $fbUser->getAvatar();

        $user = User::where('provider', 'facebook')->where('provider_id', $fbId)->first();

        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'provider' => 'facebook',
                    'provider_id' => $fbId,
                    'provider_email' => $email,
                    'avatar' => $user->avatar ?: $avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'last_active_at' => now(),
                ]);
            }
        }

        if (!$user) {
            $user = User::create([
                'name' => $name ?: 'Facebook Traveler',
                'email' => $email ?: ($fbId . '@facebook.oauth'),
                'avatar' => $avatar,
                'role' => User::ROLE_USER,
                'status' => 'Active',
                'verified' => true,
                'provider' => 'facebook',
                'provider_id' => $fbId,
                'provider_email' => $email,
                'email_verified_at' => now(),
                'last_active_at' => now(),
            ]);
        } else {
            if ($user->status !== 'Active') {
                return redirect($frontendUrl . '/login?error=' . urlencode('Account is ' . strtolower($user->status)));
            }
            $user->update([
                'last_active_at' => now(),
                'avatar' => $avatar ?: $user->avatar,
            ]);
        }

        $token = $user->createToken('travel_facebook_auth_token')->plainTextToken;

        return redirect($frontendUrl . '/auth/callback?token=' . urlencode($token) . '&user=' . urlencode(json_encode(new TravelUserResource($user))));
    }
}
