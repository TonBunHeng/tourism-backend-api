<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class TravelOAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse|JsonResponse
    {
        $clientId = config('services.google.client_id');
        if (empty($clientId) || $clientId === 'your_google_client_id_here') {
            return response()->json([
                'success' => false,
                'message' => 'Google Client ID is not configured yet in backend .env. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET.',
            ], 422);
        }

        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Obtain the user information from Google callback.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse|JsonResponse
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return redirect($frontendUrl . '/login?error=' . urlencode('Google authentication failed: ' . $e->getMessage()));
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();
        $name = $googleUser->getName() ?: 'Google Traveler';
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
                'name' => $name,
                'email' => $email,
                'avatar' => $avatar,
                'role' => 'User',
                'status' => 'Active',
                'verified' => true,
                'provider' => 'google',
                'provider_id' => $googleId,
                'provider_email' => $email,
                'email_verified_at' => now(),
                'last_active_at' => now(),
            ]);
        }

        $token = $user->createToken('travel_google_auth_token')->plainTextToken;

        // Redirect back to frontend with token and user details in query or fragment
        return redirect($frontendUrl . '/?token=' . urlencode($token) . '&user=' . urlencode(json_encode([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'role' => $user->role,
        ])));
    }
}
