<?php

use App\Http\Controllers\Api\Admin\AdminBusinessController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Business\BusinessOwnerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeletionRequestController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SecurityAlertController;
use App\Http\Controllers\Api\SystemSettingController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserAchievementController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Travel\TravelAchievementController;
use App\Http\Controllers\Api\Travel\TravelAiChatController;
use App\Http\Controllers\Api\Travel\TravelAuthController;
use App\Http\Controllers\Api\Travel\TravelBusinessController;
use App\Http\Controllers\Api\Travel\TravelCategoryController;
use App\Http\Controllers\Api\Travel\TravelDeletionRequestController;
use App\Http\Controllers\Api\Travel\TravelEventController;
use App\Http\Controllers\Api\Travel\TravelFavoriteController;
use App\Http\Controllers\Api\Travel\TravelGalleryController;
use App\Http\Controllers\Api\Travel\TravelPlaceController;
use App\Http\Controllers\Api\Travel\TravelProvinceController;
use App\Http\Controllers\Api\Travel\TravelReviewController;
use App\Http\Controllers\Api\Travel\TravelSettingController;
use App\Http\Controllers\Api\Travel\TravelTripController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'AngkorVerses API is operational.',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::post('/chat', [TravelAiChatController::class, 'chat']);
Route::post('/ai/chat', [TravelAiChatController::class, 'chat']);
Route::get('/ai/status', [TravelAiChatController::class, 'status']);


/*
|--------------------------------------------------------------------------
| 1. TRAVEL / USER REST API (/api/travel/*)
| Consumed by Tourist Web (tourism-travel) & Android Mobile App
|--------------------------------------------------------------------------
*/
Route::prefix('travel')->group(function () {

    // 1.1 Public Authentication
    Route::post('/auth/register', [TravelAuthController::class, 'register']);
    Route::post('/auth/login', [TravelAuthController::class, 'login']);
    Route::post('/auth/google', [TravelAuthController::class, 'googleLogin']);
    Route::post('/auth/facebook', [TravelAuthController::class, 'facebookLogin']);
    Route::get('/auth/google/redirect', [TravelAuthController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [TravelAuthController::class, 'handleGoogleCallback']);
    Route::get('/auth/facebook/redirect', [TravelAuthController::class, 'redirectToFacebook']);
    Route::get('/auth/facebook/callback', [TravelAuthController::class, 'handleFacebookCallback']);

    // 1.2 Public Destinations / Places
    Route::get('/places', [TravelPlaceController::class, 'index']);
    Route::get('/places/{id}', [TravelPlaceController::class, 'show']);

    // 1.3 Public Businesses & Directory
    Route::get('/businesses', [TravelBusinessController::class, 'index']);
    Route::get('/businesses/{id}', [TravelBusinessController::class, 'show']);
    Route::get('/businesses/{id}/services', [TravelBusinessController::class, 'services']);
    Route::get('/businesses/{id}/hours', [TravelBusinessController::class, 'hours']);
    Route::get('/businesses/{id}/gallery', [TravelBusinessController::class, 'gallery']);
    Route::get('/businesses/{id}/promotions', [TravelBusinessController::class, 'promotions']);
    Route::get('/businesses/{id}/events', [TravelBusinessController::class, 'events']);
    Route::get('/businesses/{id}/reviews', [TravelBusinessController::class, 'reviews']);

    // 1.4 Public Provinces
    Route::get('/provinces', [TravelProvinceController::class, 'index']);
    Route::get('/provinces/{id}', [TravelProvinceController::class, 'show']);

    // 1.5 Public Categories
    Route::get('/categories', [TravelCategoryController::class, 'index']);
    Route::get('/categories/{id}', [TravelCategoryController::class, 'show']);

    // 1.6 Public Events & Festivals
    Route::get('/events', [TravelEventController::class, 'index']);
    Route::get('/events/{id}', [TravelEventController::class, 'show']);

    // 1.7 Public Gallery / Media
    Route::get('/galleries', [TravelGalleryController::class, 'index']);
    Route::get('/galleries/{id}', [TravelGalleryController::class, 'show']);
    Route::post('/galleries/{id}/view', [TravelGalleryController::class, 'recordView']);
    Route::post('/galleries/{id}/views', [TravelGalleryController::class, 'recordView']);
    Route::get('/galleries/{id}/comments', [TravelGalleryController::class, 'comments']);
    Route::get('/galleries/{id}/stream', [TravelGalleryController::class, 'stream']);

    // 1.8 Public Reviews
    Route::get('/reviews', [TravelReviewController::class, 'index']);
    Route::get('/reviews/{id}', [TravelReviewController::class, 'show']);

    // 1.9 Public Achievements / Badges List
    Route::get('/achievements', [TravelAchievementController::class, 'index']);

    // 1.10 Public Safe System Settings
    Route::get('/settings', [TravelSettingController::class, 'index']);

    // 1.11 AI Assistant & Tourism Intelligence (Powered by Angkor Verse AI)
    Route::post('/ai/chat', [TravelAiChatController::class, 'chat']);
    Route::post('/ai-chat', [TravelAiChatController::class, 'chat']);
    Route::post('/ai/recommendations', [TravelAiChatController::class, 'recommendations']);
    Route::post('/recommendations', [TravelAiChatController::class, 'recommendations']);
    Route::post('/ai/itineraries', [TravelAiChatController::class, 'itineraries']);
    Route::post('/itineraries', [TravelAiChatController::class, 'itineraries']);
    Route::get('/ai/weather', [TravelAiChatController::class, 'weather']);
    Route::get('/weather', [TravelAiChatController::class, 'weather']);
    Route::get('/ai/events', [TravelAiChatController::class, 'events']);
    Route::get('/ai/currency', [TravelAiChatController::class, 'currency']);
    Route::get('/currency', [TravelAiChatController::class, 'currency']);
    Route::post('/ai/currency/convert', [TravelAiChatController::class, 'convertCurrency']);
    Route::post('/currency/convert', [TravelAiChatController::class, 'convertCurrency']);
    Route::get('/ai/transport', [TravelAiChatController::class, 'transport']);
    Route::get('/transport', [TravelAiChatController::class, 'transport']);
    Route::post('/ai/search', [TravelAiChatController::class, 'search']);
    Route::post('/ai/summary', [TravelAiChatController::class, 'summary']);
    Route::get('/ai/status', [TravelAiChatController::class, 'status']);

    // 1.12 Authenticated Tourist Endpoints (Sanctum Auth)
    Route::middleware('auth:sanctum')->group(function () {

        // Tourist Profile & Password Management
        Route::get('/auth/me', [TravelAuthController::class, 'me']);
        Route::post('/auth/logout', [TravelAuthController::class, 'logout']);
        Route::put('/auth/profile', [TravelAuthController::class, 'updateProfile']);
        Route::put('/auth/password', [TravelAuthController::class, 'updatePassword']);
        Route::post('/auth/avatar', [TravelAuthController::class, 'uploadAvatar']);
        Route::delete('/auth/avatar', [TravelAuthController::class, 'deleteAvatar']);

        // Tourist Business Review
        Route::post('/businesses/{id}/reviews', [TravelBusinessController::class, 'storeReview']);

        // Tourist Gallery Interactions (Requires Authentication)
        Route::post('/galleries/{id}/comments', [TravelGalleryController::class, 'storeComment']);
        Route::post('/galleries/{id}/replies', [TravelGalleryController::class, 'storeComment']);
        Route::delete('/galleries/comments/{commentId}', [TravelGalleryController::class, 'deleteComment']);
        Route::delete('/galleries/{id}/comments/{commentId}', [TravelGalleryController::class, 'deleteComment']);
        Route::post('/galleries/{id}/like', [TravelGalleryController::class, 'toggleLike']);
        Route::post('/galleries/{id}/likes', [TravelGalleryController::class, 'toggleLike']);

        // Tourist Reviews
        Route::post('/reviews', [TravelReviewController::class, 'store']);
        Route::put('/reviews/{id}', [TravelReviewController::class, 'update']);
        Route::delete('/reviews/{id}', [TravelReviewController::class, 'destroy']);

        // Tourist Favorites / Wishlist
        Route::get('/favorites', [TravelFavoriteController::class, 'index']);
        Route::post('/favorites', [TravelFavoriteController::class, 'store']);
        Route::post('/favorites/toggle', [TravelFavoriteController::class, 'toggle']);
        Route::post('/favorites/toggle/{placeId}', [TravelFavoriteController::class, 'toggle']);
        Route::delete('/favorites/{placeId}', [TravelFavoriteController::class, 'destroy']);
        Route::patch('/favorites/{id}/toggle-visited', [TravelFavoriteController::class, 'toggleVisited']);

        // Tourist My Achievements
        Route::get('/achievements/my', [TravelAchievementController::class, 'myAchievements']);

        // Tourist Trip Planner / Itineraries
        Route::get('/trips', [TravelTripController::class, 'index']);
        Route::post('/trips', [TravelTripController::class, 'store']);
        Route::get('/trips/{id}', [TravelTripController::class, 'show']);
        Route::put('/trips/{id}', [TravelTripController::class, 'update']);
        Route::delete('/trips/{id}', [TravelTripController::class, 'destroy']);
        Route::post('/trips/{id}/duplicate', [TravelTripController::class, 'duplicate']);
        Route::post('/trips/{id}/itineraries', [TravelTripController::class, 'addItinerary']);
        Route::delete('/trips/{id}/itineraries/{itineraryId}', [TravelTripController::class, 'deleteItinerary']);
        Route::post('/trips/{id}/reorder', [TravelTripController::class, 'reorderItineraries']);

        // Tourist AI Chat History
        Route::get('/ai/conversations', [TravelAiChatController::class, 'conversations']);
        Route::get('/ai/conversations/{sessionId}/messages', [TravelAiChatController::class, 'getMessages']);
        Route::delete('/ai/conversations/{sessionId}', [TravelAiChatController::class, 'clearConversation']);

        // Tourist Deletion & Privacy Requests
        Route::get('/deletion-requests', [TravelDeletionRequestController::class, 'index']);
        Route::post('/deletion-requests', [TravelDeletionRequestController::class, 'store']);

        // Tourist Notifications & Web Push
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('/notifications/subscribe', [NotificationController::class, 'subscribePush']);
        Route::delete('/notifications/subscribe', [NotificationController::class, 'unsubscribePush']);
        Route::get('/notifications/settings', [NotificationController::class, 'getSettings']);
        Route::put('/notifications/settings', [NotificationController::class, 'updateSettings']);
        Route::get('/notifications/vapid-key', [NotificationController::class, 'vapidPublicKey']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/notifications', [NotificationController::class, 'clearAll']);
    });
});

/*
|--------------------------------------------------------------------------
| 2. BUSINESS OWNER REST API (/api/business/*)
| Consumed by Business Owners via tourism-travel frontend
| Strictly protected with Sanctum Auth & Business Owner role / Admin privilege
|--------------------------------------------------------------------------
*/
Route::prefix('business')->middleware(['auth:sanctum', 'role:business_owner,admin,super_admin'])->group(function () {
    Route::get('/profile', [BusinessOwnerController::class, 'profile']);
    Route::get('/businesses', [BusinessOwnerController::class, 'index']);
    Route::post('/businesses', [BusinessOwnerController::class, 'store']);
    Route::get('/businesses/{id}', [BusinessOwnerController::class, 'show']);
    Route::put('/businesses/{id}', [BusinessOwnerController::class, 'update']);
    Route::delete('/businesses/{id}', [BusinessOwnerController::class, 'destroy']);

    // Images
    Route::get('/businesses/{id}/images', [BusinessOwnerController::class, 'images']);
    Route::post('/businesses/{id}/images', [BusinessOwnerController::class, 'storeImage']);
    Route::delete('/businesses/{id}/images/{imageId}', [BusinessOwnerController::class, 'destroyImage']);

    // Services
    Route::get('/businesses/{id}/services', [BusinessOwnerController::class, 'services']);
    Route::post('/businesses/{id}/services', [BusinessOwnerController::class, 'storeService']);
    Route::put('/businesses/{id}/services/{serviceId}', [BusinessOwnerController::class, 'updateService']);
    Route::delete('/businesses/{id}/services/{serviceId}', [BusinessOwnerController::class, 'destroyService']);

    // Opening Hours
    Route::get('/businesses/{id}/hours', [BusinessOwnerController::class, 'hours']);
    Route::put('/businesses/{id}/hours', [BusinessOwnerController::class, 'updateHours']);

    // Promotions
    Route::get('/businesses/{id}/promotions', [BusinessOwnerController::class, 'promotions']);
    Route::post('/businesses/{id}/promotions', [BusinessOwnerController::class, 'storePromotion']);
    Route::put('/businesses/{id}/promotions/{promoId}', [BusinessOwnerController::class, 'updatePromotion']);
    Route::delete('/businesses/{id}/promotions/{promoId}', [BusinessOwnerController::class, 'destroyPromotion']);

    // Reviews & Replies
    Route::get('/businesses/{id}/reviews', [BusinessOwnerController::class, 'reviews']);
    Route::post('/businesses/{id}/reviews/{reviewId}/reply', [BusinessOwnerController::class, 'replyReview']);

    // Statistics & Events
    Route::get('/businesses/{id}/statistics', [BusinessOwnerController::class, 'statistics']);
    Route::get('/businesses/{id}/events', [BusinessOwnerController::class, 'events']);
    Route::post('/businesses/{id}/events', [BusinessOwnerController::class, 'storeEvent']);
});

/*
|--------------------------------------------------------------------------
| 2.5 GUIDE / EDITOR REST API (/api/guide/*)
| Consumed by Guide / Editor users via tourism-travel frontend
| Protected with Sanctum Auth & guide_editor role / Admin privilege
|--------------------------------------------------------------------------
*/
Route::prefix('guide')->middleware(['auth:sanctum', 'role:guide_editor,admin,super_admin'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\Guide\GuideController::class, 'dashboard']);

    // Places
    Route::get('/places', [\App\Http\Controllers\Api\Guide\GuideController::class, 'places']);
    Route::post('/places', [\App\Http\Controllers\Api\Guide\GuideController::class, 'storePlace']);
    Route::get('/places/{id}', [\App\Http\Controllers\Api\Guide\GuideController::class, 'showPlace']);
    Route::put('/places/{id}', [\App\Http\Controllers\Api\Guide\GuideController::class, 'updatePlace']);
    Route::delete('/places/{id}', [\App\Http\Controllers\Api\Guide\GuideController::class, 'destroyPlace']);

    // Events
    Route::get('/events', [\App\Http\Controllers\Api\Guide\GuideController::class, 'events']);
    Route::post('/events', [\App\Http\Controllers\Api\Guide\GuideController::class, 'storeEvent']);
    Route::get('/events/{id}', [\App\Http\Controllers\Api\Guide\GuideController::class, 'showEvent']);
    Route::put('/events/{id}', [\App\Http\Controllers\Api\Guide\GuideController::class, 'updateEvent']);
    Route::delete('/events/{id}', [\App\Http\Controllers\Api\Guide\GuideController::class, 'destroyEvent']);

    // Galleries / Media
    Route::get('/galleries', [\App\Http\Controllers\Api\Guide\GuideController::class, 'galleries']);
    Route::post('/galleries', [\App\Http\Controllers\Api\Guide\GuideController::class, 'storeGallery']);
    Route::delete('/galleries/{id}', [\App\Http\Controllers\Api\Guide\GuideController::class, 'destroyGallery']);

    // Reviews & Assistance
    Route::get('/reviews', [\App\Http\Controllers\Api\Guide\GuideController::class, 'reviews']);
    Route::post('/reviews/{id}/reply', [\App\Http\Controllers\Api\Guide\GuideController::class, 'replyReview']);
});

/*
|--------------------------------------------------------------------------
| 3. ADMIN & MANAGEMENT REST API
| Consumed by tourism-admin (Admin Web Panel)
| Strictly protected with Sanctum Authentication and Admin Role Authorization
|--------------------------------------------------------------------------
*/

// Public Authentication Endpoints
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

// Protected APIs (Sanctum Auth)
Route::middleware('auth:sanctum')->group(function () {

    // Authenticated User Profile
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::put('/auth/password', [TravelAuthController::class, 'updatePassword']);
    Route::post('/auth/avatar', [AuthController::class, 'updateAvatar']);

    // Admin & Management Routes (Guarded by admin.role middleware)
    Route::middleware('admin.role')->group(function () {

        // Analytics & Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/stats', [DashboardController::class, 'index']);
        Route::get('/reports/analytics', [ReportController::class, 'analytics']);
        Route::get('/deletion-requests/analytics', [DeletionRequestController::class, 'analytics']);
        Route::get('/reviews/analytics', [ReviewController::class, 'analytics']);
        Route::get('/favorites/analytics', [FavoriteController::class, 'analytics']);

        // File Upload
        Route::post('/upload', [UploadController::class, 'upload']);

        // Businesses Management & Verification Workflow
        Route::get('/businesses', [AdminBusinessController::class, 'index']);
        Route::get('/businesses/{id}', [AdminBusinessController::class, 'show']);
        Route::put('/businesses/{id}', [AdminBusinessController::class, 'update']);
        Route::delete('/businesses/{id}', [AdminBusinessController::class, 'destroy']);
        Route::post('/businesses/{id}/approve', [AdminBusinessController::class, 'approve']);
        Route::post('/businesses/{id}/reject', [AdminBusinessController::class, 'reject']);
        Route::post('/businesses/{id}/suspend', [AdminBusinessController::class, 'suspend']);
        Route::post('/businesses/{id}/activate', [AdminBusinessController::class, 'activate']);

        // Admin explicit alias (/api/admin/businesses/*)
        Route::prefix('admin')->group(function () {
            Route::get('/businesses', [AdminBusinessController::class, 'index']);
            Route::get('/businesses/{id}', [AdminBusinessController::class, 'show']);
            Route::put('/businesses/{id}', [AdminBusinessController::class, 'update']);
            Route::delete('/businesses/{id}', [AdminBusinessController::class, 'destroy']);
            Route::post('/businesses/{id}/approve', [AdminBusinessController::class, 'approve']);
            Route::post('/businesses/{id}/reject', [AdminBusinessController::class, 'reject']);
            Route::post('/businesses/{id}/suspend', [AdminBusinessController::class, 'suspend']);
            Route::post('/businesses/{id}/activate', [AdminBusinessController::class, 'activate']);
        });

        // Places Management
        Route::get('/places', [PlaceController::class, 'index']);
        Route::get('/places/{id}', [PlaceController::class, 'show']);
        Route::post('/places', [PlaceController::class, 'store']);
        Route::put('/places/{id}', [PlaceController::class, 'update']);
        Route::delete('/places/{id}', [PlaceController::class, 'destroy']);
        Route::post('/places/{id}/approve', [PlaceController::class, 'approve']);
        Route::post('/places/{id}/reject', [PlaceController::class, 'reject']);

        // Categories Management
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Provinces Management
        Route::get('/provinces', [ProvinceController::class, 'index']);
        Route::get('/provinces/{id}', [ProvinceController::class, 'show']);
        Route::post('/provinces', [ProvinceController::class, 'store']);
        Route::put('/provinces/{id}', [ProvinceController::class, 'update']);
        Route::delete('/provinces/{id}', [ProvinceController::class, 'destroy']);

        // Events Management
        Route::get('/events', [EventController::class, 'index']);
        Route::get('/events/{id}', [EventController::class, 'show']);
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);

        // Galleries Management
        Route::get('/galleries', [GalleryController::class, 'index']);
        Route::get('/galleries/{id}', [GalleryController::class, 'show']);
        Route::post('/galleries', [GalleryController::class, 'store']);
        Route::put('/galleries/{id}', [GalleryController::class, 'update']);
        Route::delete('/galleries/{id}', [GalleryController::class, 'destroy']);

        // Reviews Moderation
        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::get('/reviews/{id}', [ReviewController::class, 'show']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::put('/reviews/{id}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
        Route::put('/reviews/{id}/status', [ReviewController::class, 'updateStatus']);
        Route::post('/reviews/{id}/replies', [ReviewController::class, 'reply']);

        // User Management & Activity Tracking
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/active-status', [UserController::class, 'activeStatus']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::put('/users/{id}/status', [UserController::class, 'updateStatus']);

        // Favorites Admin Inspection
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{placeId}', [FavoriteController::class, 'destroy']);
        Route::patch('/favorites/{id}/toggle-visited', [FavoriteController::class, 'toggleVisited']);

        // Deletion Requests Admin Moderation
        Route::get('/deletion-requests', [DeletionRequestController::class, 'index']);
        Route::post('/deletion-requests', [DeletionRequestController::class, 'store']);
        Route::get('/deletion-requests/{id}', [DeletionRequestController::class, 'show']);
        Route::put('/deletion-requests/{id}/status', [DeletionRequestController::class, 'updateStatus']);

        // Achievements Admin Management
        Route::get('/achievements', [UserAchievementController::class, 'index']);
        Route::get('/users/{userId}/achievements', [UserAchievementController::class, 'userAchievements']);
        Route::put('/achievements/{id}/toggle', [UserAchievementController::class, 'toggleUnlocked']);

        // System Settings Admin Management
        Route::get('/settings', [SystemSettingController::class, 'index']);
        Route::put('/settings', [SystemSettingController::class, 'update']);

        // Notifications Admin APIs & Web Push
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('/notifications/subscribe', [NotificationController::class, 'subscribePush']);
        Route::delete('/notifications/subscribe', [NotificationController::class, 'unsubscribePush']);
        Route::get('/notifications/settings', [NotificationController::class, 'getSettings']);
        Route::put('/notifications/settings', [NotificationController::class, 'updateSettings']);
        Route::get('/notifications/vapid-key', [NotificationController::class, 'vapidPublicKey']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/notifications', [NotificationController::class, 'clearAll']);

        // Security Alerts Management
        Route::get('/security-alerts', [SecurityAlertController::class, 'index']);
        Route::get('/security-alerts/export', [SecurityAlertController::class, 'exportData']);
        Route::get('/security-alerts/login-attempts', [SecurityAlertController::class, 'loginAttempts']);
        Route::delete('/security-alerts/login-attempts/{id}', [SecurityAlertController::class, 'destroyLoginAttempt']);
        Route::delete('/security-alerts/login-attempts', [SecurityAlertController::class, 'clearLoginAttempts']);
        Route::get('/security-alerts/blocked-ips', [SecurityAlertController::class, 'blockedIps']);
        Route::post('/security-alerts/block-ip', [SecurityAlertController::class, 'blockIp']);
        Route::post('/security-alerts/unblock-ip', [SecurityAlertController::class, 'unblockIp']);
        Route::put('/security-alerts/{id}/read', [SecurityAlertController::class, 'markAsRead']);
        Route::post('/security-alerts/mark-all-read', [SecurityAlertController::class, 'markAllRead']);
        Route::delete('/security-alerts/{id}', [SecurityAlertController::class, 'destroy']);
        Route::delete('/security-alerts', [SecurityAlertController::class, 'clearAll']);
    });
});
