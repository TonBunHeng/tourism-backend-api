<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Travel\TravelFacebookLoginRequest;
use App\Http\Requests\Travel\TravelGoogleLoginRequest;
use App\Http\Requests\Travel\TravelLoginRequest;
use App\Http\Requests\Travel\TravelRegisterRequest;
use App\Http\Resources\Travel\TravelUserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class TravelAuthController extends Controller
{
    use ApiResponseTrait;

    public function register(TravelRegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'User',
            'status' => 'Active',
            'verified' => true,
            'email_verified_at' => now(),
            'last_active_at' => now(),
        ]);

        $token = $user->createToken('travel_auth_token')->plainTextToken;

        return $this->createdResponse([
            'user' => new TravelUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful. Welcome to AngkorVerses!');
    }

    public function login(TravelLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if ($user->status !== 'Active') {
            return $this->errorResponse('Your account is currently ' . strtolower($user->status) . '. Please contact support.', 403);
        }

        $user->update(['last_active_at' => now()]);
        $token = $user->createToken('travel_auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new TravelUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }

    public function googleLogin(TravelGoogleLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
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

        $token = $user->createToken('travel_google_auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new TravelUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Google authentication successful.');
    }

    public function facebookLogin(TravelFacebookLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $fbId = $validated['facebook_id'] ?? null;
        $email = $validated['email'] ?? null;
        $name = $validated['name'] ?? null;
        $avatar = $validated['avatar'] ?? null;

        $user = User::where('provider', 'facebook')->where('provider_id', $fbId)->first();

        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'provider' => 'facebook',
                    'provider_id' => $fbId,
                    'provider_email' => $email,
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
                'role' => 'User',
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
                return $this->errorResponse('Account is ' . strtolower($user->status) . '. Please contact support.', 403);
            }
            $user->update(['last_active_at' => now()]);
        }

        $token = $user->createToken('travel_facebook_auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new TravelUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Facebook authentication successful.');
    }
}
