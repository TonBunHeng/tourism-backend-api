<?php

use App\Http\Controllers\Api\Travel\TravelOAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'AngkorVerses API Backend',
        'status' => 'active',
        'version' => '1.0.0',
        'endpoints' => [
            'api_documentation' => '/api/travel/settings',
            'health_check' => '/api/health',
            'google_oauth_redirect' => '/auth/google/redirect',
        ],
    ]);
});

// OAuth 2.0 Socialite Routes
Route::get('/auth/google/redirect', [TravelOAuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [TravelOAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
