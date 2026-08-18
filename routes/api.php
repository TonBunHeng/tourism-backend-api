<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeletionRequestController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SystemSettingController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserAchievementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Smart Tourism API is operational.',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Public Authentication
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

// Public App Settings & Analytics
Route::get('/settings', [SystemSettingController::class, 'index']);
Route::get('/dashboard/stats', [DashboardController::class, 'index']);
Route::get('/reports/analytics', [ReportController::class, 'analytics']);
Route::get('/deletion-requests/analytics', [DeletionRequestController::class, 'analytics']);
Route::get('/reviews/analytics', [ReviewController::class, 'analytics']);

// Public Read-Only Content APIs
Route::get('/places', [PlaceController::class, 'index']);
Route::get('/places/{id}', [PlaceController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/provinces', [ProvinceController::class, 'index']);
Route::get('/provinces/{id}', [ProvinceController::class, 'show']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::get('/galleries', [GalleryController::class, 'index']);
Route::get('/galleries/{id}', [GalleryController::class, 'show']);
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);

// File Upload endpoint
Route::post('/upload', [UploadController::class, 'upload']);

// Protected APIs (Sanctum Auth)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword']);

    // Admin Dashboard & Reports
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Content Management (CRUD)
    Route::post('/places', [PlaceController::class, 'store']);
    Route::put('/places/{id}', [PlaceController::class, 'update']);
    Route::delete('/places/{id}', [PlaceController::class, 'destroy']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::post('/provinces', [ProvinceController::class, 'store']);
    Route::put('/provinces/{id}', [ProvinceController::class, 'update']);
    Route::delete('/provinces/{id}', [ProvinceController::class, 'destroy']);

    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);

    Route::post('/galleries', [GalleryController::class, 'store']);
    Route::put('/galleries/{id}', [GalleryController::class, 'update']);
    Route::delete('/galleries/{id}', [GalleryController::class, 'destroy']);

    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::put('/reviews/{id}/status', [ReviewController::class, 'updateStatus']);

    // Users Management
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}/status', [UserController::class, 'updateStatus']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{placeId}', [FavoriteController::class, 'destroy']);
    Route::patch('/favorites/{id}/toggle-visited', [FavoriteController::class, 'toggleVisited']);

    // Chat / Messages
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::post('/chats/{id}/messages', [ChatController::class, 'sendMessage']);
    Route::put('/chats/{id}/status', [ChatController::class, 'updateStatus']);

    // Deletion Requests
    Route::get('/deletion-requests', [DeletionRequestController::class, 'index']);
    Route::post('/deletion-requests', [DeletionRequestController::class, 'store']);
    Route::get('/deletion-requests/{id}', [DeletionRequestController::class, 'show']);
    Route::put('/deletion-requests/{id}/status', [DeletionRequestController::class, 'updateStatus']);

    // Achievements
    Route::get('/achievements', [UserAchievementController::class, 'index']);
    Route::get('/users/{userId}/achievements', [UserAchievementController::class, 'userAchievements']);
    Route::put('/achievements/{id}/toggle', [UserAchievementController::class, 'toggleUnlocked']);

    // System Settings Write
    Route::put('/settings', [SystemSettingController::class, 'update']);
});
