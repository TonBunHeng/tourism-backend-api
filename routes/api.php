<?php

use App\Http\Controllers\Api\Admin\AdminBookingController;
use App\Http\Controllers\Api\Admin\AdminBusinessController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Business\BusinessBookingController;
use App\Http\Controllers\Api\Business\BusinessOwnerController;
use App\Http\Controllers\Api\Business\BusinessReportController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeletionRequestController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\Guide\GuideController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SecurityAlertController;
use App\Http\Controllers\Api\SystemSettingController;
use App\Http\Controllers\Api\AdminGuideController;
use App\Http\Controllers\Api\AdminTourController;
use App\Http\Controllers\Api\Travel\TravelAchievementController;
use App\Http\Controllers\Api\Travel\TravelAiChatController;
use App\Http\Controllers\Api\Travel\TravelAuthController;
use App\Http\Controllers\Api\Travel\TravelBookingController;
use App\Http\Controllers\Api\Travel\TravelBusinessController;
use App\Http\Controllers\Api\Travel\TravelCategoryController;
use App\Http\Controllers\Api\Travel\TravelContactController;
use App\Http\Controllers\Api\Travel\TravelDeletionRequestController;
use App\Http\Controllers\Api\Travel\TravelEventController;
use App\Http\Controllers\Api\Travel\TravelFavoriteController;
use App\Http\Controllers\Api\Travel\TravelGalleryController;
use App\Http\Controllers\Api\Travel\TravelGuideController;
use App\Http\Controllers\Api\Travel\TravelPlaceController;
use App\Http\Controllers\Api\Travel\TravelProvinceController;
use App\Http\Controllers\Api\Travel\TravelReviewController;
use App\Http\Controllers\Api\Travel\TravelSettingController;
use App\Http\Controllers\Api\Travel\TravelTourController;
use App\Http\Controllers\Api\Travel\TravelTripController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserAchievementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Global Route Parameter Constraints (Prevents SQL cast errors & injection)
|--------------------------------------------------------------------------
*/
Route::pattern('id', '[0-9]+');
Route::pattern('placeId', '[0-9]+');
Route::pattern('commentId', '[0-9]+');
Route::pattern('itineraryId', '[0-9]+');
Route::pattern('imageId', '[0-9]+');
Route::pattern('serviceId', '[0-9]+');
Route::pattern('promoId', '[0-9]+');
Route::pattern('reviewId', '[0-9]+');
Route::pattern('userId', '[0-9]+');

/*
|--------------------------------------------------------------------------
| Root & Health Check (Minimal metadata to prevent reconnaissance)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'service' => 'AngkorVerses API Backend',
        'status'  => 'active',
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'success'   => true,
        'message'   => 'AngkorVerses API is operational.',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Root AI aliases strictly throttled to prevent quota abuse
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/chat', [TravelAiChatController::class, 'chat']);
    Route::post('/ai/chat', [TravelAiChatController::class, 'chat']);
    Route::get('/ai/status', [TravelAiChatController::class, 'status']);
});

/*
|--------------------------------------------------------------------------
| 1. TRAVEL / USER REST API (/api/travel/*)
| Consumed by Tourist Web (tourism-travel) & Android Mobile App
|--------------------------------------------------------------------------
*/
Route::prefix('travel')->group(function () {

    // 1.1 Public Authentication (Strictly Rate-Limited against Brute Force)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/register', [TravelAuthController::class, 'register']);
        Route::post('/auth/login', [TravelAuthController::class, 'login']);
        Route::post('/auth/google', [TravelAuthController::class, 'googleLogin']);
        Route::post('/auth/facebook', [TravelAuthController::class, 'facebookLogin']);
    });

    // OAuth Redirect/Callbacks (Throttled; ensure stateless PKCE or web session middleware is used)
    Route::middleware('throttle:10,1')->group(function () {
        Route::get('/auth/google/redirect', [TravelAuthController::class, 'redirectToGoogle']);
        Route::get('/auth/google/callback', [TravelAuthController::class, 'handleGoogleCallback']);
        Route::get('/auth/facebook/redirect', [TravelAuthController::class, 'redirectToFacebook']);
        Route::get('/auth/facebook/callback', [TravelAuthController::class, 'handleFacebookCallback']);
    });

    // 1.2 Public Read-Only Directory & Content (Standard API Throttling)
    Route::middleware('throttle:60,1')->group(function () {
        // Destinations / Places
        Route::get('/places', [TravelPlaceController::class, 'index']);
        Route::get('/places/{id}', [TravelPlaceController::class, 'show']);

        // Businesses & Directory
        Route::get('/businesses', [TravelBusinessController::class, 'index']);
        Route::get('/businesses/{id}', [TravelBusinessController::class, 'show']);
        Route::get('/businesses/{id}/services', [TravelBusinessController::class, 'services']);
        Route::get('/businesses/{id}/hours', [TravelBusinessController::class, 'hours']);
        Route::get('/businesses/{id}/gallery', [TravelBusinessController::class, 'gallery']);
        Route::get('/businesses/{id}/promotions', [TravelBusinessController::class, 'promotions']);
        Route::get('/businesses/{id}/events', [TravelBusinessController::class, 'events']);
        Route::get('/businesses/{id}/reviews', [TravelBusinessController::class, 'reviews']);

        // Provinces
        Route::get('/provinces', [TravelProvinceController::class, 'index']);
        Route::get('/provinces/{id}', [TravelProvinceController::class, 'show']);

        // Categories
        Route::get('/categories', [TravelCategoryController::class, 'index']);
        Route::get('/categories/{id}', [TravelCategoryController::class, 'show']);

        // Events & Festivals
        Route::get('/events', [TravelEventController::class, 'index']);
        Route::get('/events/{id}', [TravelEventController::class, 'show']);

        // Gallery / Media (Read)
        Route::get('/galleries', [TravelGalleryController::class, 'index']);
        Route::get('/galleries/{id}', [TravelGalleryController::class, 'show']);
        Route::get('/galleries/{id}/comments', [TravelGalleryController::class, 'comments']);
        Route::get('/galleries/{id}/stream', [TravelGalleryController::class, 'stream']);

        // Reviews
        Route::get('/reviews', [TravelReviewController::class, 'index']);
        Route::get('/reviews/{id}', [TravelReviewController::class, 'show']);

        // Achievements / Badges List
        Route::get('/achievements', [TravelAchievementController::class, 'index']);

        // Accredited Tour Guides Directory
        Route::get('/guides', [TravelGuideController::class, 'index']);
        Route::get('/guides/{id}', [TravelGuideController::class, 'show']);

        // Safe System Settings
        Route::get('/settings', [TravelSettingController::class, 'index']);

        // Low-cost AI / Utility Read Endpoints
        Route::get('/ai/weather', [TravelAiChatController::class, 'weather']);
        Route::get('/weather', [TravelAiChatController::class, 'weather']);
        Route::get('/ai/events', [TravelAiChatController::class, 'events']);
        Route::get('/ai/currency', [TravelAiChatController::class, 'currency']);
        Route::get('/currency', [TravelAiChatController::class, 'currency']);
        Route::get('/ai/transport', [TravelAiChatController::class, 'transport']);
        Route::get('/transport', [TravelAiChatController::class, 'transport']);
        Route::get('/ai/status', [TravelAiChatController::class, 'status']);
    });

    // Public Booking Submissions (For both authenticated tourists and guest travelers)
    Route::middleware('throttle:15,1')->group(function () {
        Route::post('/bookings', [TravelBookingController::class, 'store']);
        Route::post('/businesses/{id}/bookings', [TravelBookingController::class, 'store']);
    });

    // Public Gallery View Recording (Strictly throttled to prevent view spoofing)
    Route::middleware('throttle:15,1')->group(function () {
        Route::post('/galleries/{id}/view', [TravelGalleryController::class, 'recordView']);
        Route::post('/galleries/{id}/views', [TravelGalleryController::class, 'recordView']);
    });

    // 1.11 AI Assistant & Compute-Heavy Endpoints (Strictly Throttled: 10 req/min per IP)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/ai/chat', [TravelAiChatController::class, 'chat']);
        Route::post('/ai-chat', [TravelAiChatController::class, 'chat']);
        Route::post('/ai/recommendations', [TravelAiChatController::class, 'recommendations']);
        Route::post('/recommendations', [TravelAiChatController::class, 'recommendations']);
        Route::post('/ai/itineraries', [TravelAiChatController::class, 'itineraries']);
        Route::post('/itineraries', [TravelAiChatController::class, 'itineraries']);
        Route::post('/ai/currency/convert', [TravelAiChatController::class, 'convertCurrency']);
        Route::post('/currency/convert', [TravelAiChatController::class, 'convertCurrency']);
        Route::post('/ai/search', [TravelAiChatController::class, 'search']);
        Route::post('/ai/summary', [TravelAiChatController::class, 'summary']);
    });

    // 1.12 Authenticated Tourist Endpoints (Sanctum Auth)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

        // Tourist Profile & Password Management
        Route::get('/auth/me', [TravelAuthController::class, 'me']);
        Route::post('/auth/logout', [TravelAuthController::class, 'logout']);
        Route::put('/auth/profile', [TravelAuthController::class, 'updateProfile']);
        Route::put('/auth/password', [TravelAuthController::class, 'updatePassword'])->middleware('throttle:5,1');
        Route::post('/auth/avatar', [TravelAuthController::class, 'uploadAvatar'])->middleware('throttle:10,1');
        Route::delete('/auth/avatar', [TravelAuthController::class, 'deleteAvatar']);

        // Tourist Business Review
        Route::post('/businesses/{id}/reviews', [TravelBusinessController::class, 'storeReview'])->middleware('throttle:10,1');

        // Tourist Gallery Interactions
        Route::post('/galleries/{id}/comments', [TravelGalleryController::class, 'storeComment'])->middleware('throttle:15,1');
        Route::post('/galleries/{id}/replies', [TravelGalleryController::class, 'storeComment'])->middleware('throttle:15,1');
        Route::delete('/galleries/comments/{commentId}', [TravelGalleryController::class, 'deleteComment']);
        Route::delete('/galleries/{id}/comments/{commentId}', [TravelGalleryController::class, 'deleteComment']);
        Route::post('/galleries/{id}/like', [TravelGalleryController::class, 'toggleLike']);
        Route::post('/galleries/{id}/likes', [TravelGalleryController::class, 'toggleLike']);

        // Tourist Reviews
        Route::post('/reviews', [TravelReviewController::class, 'store'])->middleware('throttle:10,1');
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

        // Tourist Bookings with Businesses
        Route::get('/bookings', [TravelBookingController::class, 'index']);
        Route::post('/bookings', [TravelBookingController::class, 'store'])->middleware('throttle:10,1');
        Route::post('/businesses/{id}/bookings', [TravelBookingController::class, 'store'])->middleware('throttle:10,1');
        Route::get('/bookings/{id}', [TravelBookingController::class, 'show']);
        Route::post('/bookings/{id}/cancel', [TravelBookingController::class, 'cancel']);
        Route::put('/bookings/{id}/cancel', [TravelBookingController::class, 'cancel']);

        // Tourist AI Chat History (Constrained sessionId to alphanumeric/UUID/dash)
        Route::get('/ai/conversations', [TravelAiChatController::class, 'conversations']);
        Route::get('/ai/conversations/{sessionId}/messages', [TravelAiChatController::class, 'getMessages'])
            ->where('sessionId', '[a-zA-Z0-9\-_]+');
        Route::delete('/ai/conversations/{sessionId}', [TravelAiChatController::class, 'clearConversation'])
            ->where('sessionId', '[a-zA-Z0-9\-_]+');

        // Tourist Deletion & Privacy Requests
        Route::get('/deletion-requests', [TravelDeletionRequestController::class, 'index']);
        Route::post('/deletion-requests', [TravelDeletionRequestController::class, 'store'])->middleware('throttle:5,1');

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
| Strictly protected with Sanctum Auth & Business Owner / Admin roles
|--------------------------------------------------------------------------
*/
Route::prefix('business')
    ->middleware(['auth:sanctum', 'role:business_owner,admin,super_admin', 'throttle:60,1'])
    ->group(function () {
        Route::get('/profile', [BusinessOwnerController::class, 'profile']);
        Route::get('/businesses', [BusinessOwnerController::class, 'index']);
        Route::post('/businesses', [BusinessOwnerController::class, 'store']);
        Route::get('/businesses/{id}', [BusinessOwnerController::class, 'show']);
        Route::put('/businesses/{id}', [BusinessOwnerController::class, 'update']);
        Route::delete('/businesses/{id}', [BusinessOwnerController::class, 'destroy']);

        // Images
        Route::get('/businesses/{id}/images', [BusinessOwnerController::class, 'images']);
        Route::post('/businesses/{id}/images', [BusinessOwnerController::class, 'storeImage'])->middleware('throttle:20,1');
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

        // Bookings Management
        Route::get('/bookings', [BusinessBookingController::class, 'index']);
        Route::get('/bookings/statistics', [BusinessBookingController::class, 'statistics']);
        Route::get('/businesses/{id}/bookings', [BusinessBookingController::class, 'businessBookings']);
        Route::get('/bookings/{id}', [BusinessBookingController::class, 'show']);
        Route::post('/bookings/{id}/confirm', [BusinessBookingController::class, 'confirm']);
        Route::put('/bookings/{id}/confirm', [BusinessBookingController::class, 'confirm']);
        Route::post('/bookings/{id}/reject', [BusinessBookingController::class, 'reject']);
        Route::put('/bookings/{id}/reject', [BusinessBookingController::class, 'reject']);
        Route::post('/bookings/{id}/complete', [BusinessBookingController::class, 'complete']);
        Route::put('/bookings/{id}/complete', [BusinessBookingController::class, 'complete']);
        Route::patch('/bookings/{id}/status', [BusinessBookingController::class, 'updateStatus']);
        Route::put('/bookings/{id}/status', [BusinessBookingController::class, 'updateStatus']);

        // Reports & Export Analytics
        Route::get('/reports', [BusinessReportController::class, 'index']);
        Route::get('/reports/export', [BusinessReportController::class, 'export'])->middleware('throttle:10,1');
        Route::get('/businesses/{id}/reports', [BusinessReportController::class, 'businessReport']);
        Route::get('/businesses/{id}/reports/export', [BusinessReportController::class, 'exportBusiness'])->middleware('throttle:10,1');
    });

/*
|--------------------------------------------------------------------------
| 2.5 GUIDE / EDITOR REST API (/api/guide/*)
| Protected with Sanctum Auth & guide_editor / Admin roles
|--------------------------------------------------------------------------
*/
Route::prefix('guide')
    ->middleware(['auth:sanctum', 'role:guide_editor,admin,super_admin', 'throttle:60,1'])
    ->group(function () {
        Route::get('/dashboard', [GuideController::class, 'dashboard']);

        // Places
        Route::get('/places', [GuideController::class, 'places']);
        Route::post('/places', [GuideController::class, 'storePlace']);
        Route::get('/places/{id}', [GuideController::class, 'showPlace']);
        Route::put('/places/{id}', [GuideController::class, 'updatePlace']);
        Route::delete('/places/{id}', [GuideController::class, 'destroyPlace']);

        // Events
        Route::get('/events', [GuideController::class, 'events']);
        Route::post('/events', [GuideController::class, 'storeEvent']);
        Route::get('/events/{id}', [GuideController::class, 'showEvent']);
        Route::put('/events/{id}', [GuideController::class, 'updateEvent']);
        Route::delete('/events/{id}', [GuideController::class, 'destroyEvent']);

        // Galleries / Media
        Route::get('/galleries', [GuideController::class, 'galleries']);
        Route::post('/galleries', [GuideController::class, 'storeGallery']);
        Route::delete('/galleries/{id}', [GuideController::class, 'destroyGallery']);

        // Reviews & Assistance
        Route::get('/reviews', [GuideController::class, 'reviews']);
        Route::post('/reviews/{id}/reply', [GuideController::class, 'replyReview']);
    });

/*
|--------------------------------------------------------------------------
| 3. ADMIN & MANAGEMENT REST API
| Consumed by tourism-admin (Admin Web Panel)
|--------------------------------------------------------------------------
*/

// Admin Login (Strictly Rate-Limited; public /auth/register removed for security)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
});

// Protected Admin & Management APIs
Route::middleware(['auth:sanctum', 'admin.role'])->group(function () {

    // Authenticated Admin/Staff Profile (Fixed controller consistency & protected by admin.role)
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:5,1');
    Route::put('/auth/password', [AuthController::class, 'changePassword'])->middleware('throttle:5,1');
    Route::post('/auth/avatar', [AuthController::class, 'updateAvatar'])->middleware('throttle:10,1');

    // Analytics & Dashboard (Accessible to Admin, Super Admin, and Content Editors)
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);
    Route::get('/reports/analytics', [ReportController::class, 'analytics']);
    Route::get('/reviews/analytics', [ReviewController::class, 'analytics']);
    Route::get('/favorites/analytics', [FavoriteController::class, 'analytics']);

    // File Upload (Throttled to prevent storage exhaustion)
    Route::post('/upload', [UploadController::class, 'upload'])->middleware('throttle:20,1');

    // Places Management (Create/Update; Approve/Reject restricted to Admin+ below)
    Route::get('/places', [PlaceController::class, 'index']);
    Route::get('/places/{id}', [PlaceController::class, 'show']);
    Route::post('/places', [PlaceController::class, 'store']);
    Route::put('/places/{id}', [PlaceController::class, 'update']);

    // Categories Management
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);

    // Provinces Management
    Route::get('/provinces', [ProvinceController::class, 'index']);
    Route::get('/provinces/{id}', [ProvinceController::class, 'show']);
    Route::post('/provinces', [ProvinceController::class, 'store']);
    Route::put('/provinces/{id}', [ProvinceController::class, 'update']);

    // Events Management
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);

    // Galleries Management
    Route::get('/galleries', [GalleryController::class, 'index']);
    Route::get('/galleries/{id}', [GalleryController::class, 'show']);
    Route::post('/galleries', [GalleryController::class, 'store']);
    Route::put('/galleries/{id}', [GalleryController::class, 'update']);

    // Reviews Viewing & Replies
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::post('/reviews/{id}/replies', [ReviewController::class, 'reply']);

    // Favorites Admin Inspection (Accessible across all staff roles)
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{placeId}', [FavoriteController::class, 'destroy']);
    Route::patch('/favorites/{id}/toggle-visited', [FavoriteController::class, 'toggleVisited']);

    // Deletion Requests Submission (Super Admin, Admin, and Content Editor can submit)
    Route::post('/deletion-requests', [DeletionRequestController::class, 'store']);

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

    /*
    |--------------------------------------------------------------------------
    | 3.1 PRIVILEGED ADMIN OPERATIONS (Restricted to Admin & Super Admin Only)
    | Prevents Content Editors from managing users, approving businesses, or altering settings
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,super_admin')->group(function () {

        // Place Approval Workflow
        Route::post('/places/{id}/approve', [PlaceController::class, 'approve']);
        Route::post('/places/{id}/reject', [PlaceController::class, 'reject']);

        // Review Status Moderation
        Route::put('/reviews/{id}/status', [ReviewController::class, 'updateStatus']);

        // Businesses Management & Verification Workflow
        Route::get('/businesses', [AdminBusinessController::class, 'index']);
        Route::post('/businesses', [AdminBusinessController::class, 'store']);
        Route::get('/businesses/{id}', [AdminBusinessController::class, 'show']);
        Route::put('/businesses/{id}', [AdminBusinessController::class, 'update']);
        Route::post('/businesses/{id}/approve', [AdminBusinessController::class, 'approve']);
        Route::post('/businesses/{id}/reject', [AdminBusinessController::class, 'reject']);
        Route::post('/businesses/{id}/suspend', [AdminBusinessController::class, 'suspend']);
        Route::post('/businesses/{id}/activate', [AdminBusinessController::class, 'activate']);

        // Bookings Management
        Route::get('/bookings', [AdminBookingController::class, 'index']);
        Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
        Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
        Route::patch('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);

        // Admin explicit alias (/api/admin/*)
        Route::prefix('admin')->group(function () {
            Route::get('/businesses', [AdminBusinessController::class, 'index']);
            Route::post('/businesses', [AdminBusinessController::class, 'store']);
            Route::get('/businesses/{id}', [AdminBusinessController::class, 'show']);
            Route::put('/businesses/{id}', [AdminBusinessController::class, 'update']);
            Route::post('/businesses/{id}/approve', [AdminBusinessController::class, 'approve']);
            Route::post('/businesses/{id}/reject', [AdminBusinessController::class, 'reject']);
            Route::post('/businesses/{id}/suspend', [AdminBusinessController::class, 'suspend']);
            Route::post('/businesses/{id}/activate', [AdminBusinessController::class, 'activate']);

            Route::get('/bookings', [AdminBookingController::class, 'index']);
            Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
            Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
            Route::patch('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
        });

        // User Management & Activity Tracking (Strictly Admin / Super Admin)
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/active-status', [UserController::class, 'activeStatus']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::put('/users/{id}/status', [UserController::class, 'updateStatus']);

        // Achievements Admin Management
        Route::get('/achievements', [UserAchievementController::class, 'index']);
        Route::get('/users/{userId}/achievements', [UserAchievementController::class, 'userAchievements']);
        Route::put('/achievements/{id}/toggle', [UserAchievementController::class, 'toggleUnlocked']);

        // System Settings Management
        Route::get('/settings', [SystemSettingController::class, 'index']);
        Route::put('/settings', [SystemSettingController::class, 'update']);

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

    /*
    |--------------------------------------------------------------------------
    | 3.2 DESTRUCTIVE OPERATIONS (Restricted to Super Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:super_admin')->group(function () {
        // Direct permanent deletions
        Route::delete('/places/{id}', [PlaceController::class, 'destroy']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
        Route::delete('/provinces/{id}', [ProvinceController::class, 'destroy']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);
        Route::delete('/galleries/{id}', [GalleryController::class, 'destroy']);
        Route::delete('/businesses/{id}', [AdminBusinessController::class, 'destroy']);
        Route::delete('/admin/businesses/{id}', [AdminBusinessController::class, 'destroy']);
        Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy']);
        Route::delete('/admin/bookings/{id}', [AdminBookingController::class, 'destroy']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Deletion requests moderation, inspection & status approval/rejection
        Route::get('/deletion-requests/analytics', [DeletionRequestController::class, 'analytics']);
        Route::get('/deletion-requests', [DeletionRequestController::class, 'index']);
        Route::get('/deletion-requests/{id}', [DeletionRequestController::class, 'show']);
        Route::put('/deletion-requests/{id}/status', [DeletionRequestController::class, 'updateStatus']);
    });
});