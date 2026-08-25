<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\CoachWorkspaceController;
use App\Http\Controllers\Api\ClientHomeController;
use App\Http\Controllers\Api\CommunicationController;
use App\Http\Controllers\Api\CommunityFeedController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\MobileBootstrapController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NutritionController;
use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Api\CommunicationWebhookController;
use App\Http\Controllers\Api\TenantDiscoveryController;
use App\Http\Controllers\Api\WorkoutLogController;
use App\Http\Controllers\Platform\SessionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/platform/session', [SessionApiController::class, 'show'])->name('api.platform.session');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Organization discovery (no tenant DB switch)
| Single mobile build → resolve join_code → store tenant_domain → auth
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:30,1'])->prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/organizations/discover', [TenantDiscoveryController::class, 'discover'])
        ->name('organizations.discover');
    Route::get('/organizations/search', [TenantDiscoveryController::class, 'search'])
        ->name('organizations.search');
});

// Public auth (tenant resolved via Host or X-Tenant-Domain)
Route::middleware(['tenants', 'throttle:10,1'])->prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');
    Route::post('/auth/whatsapp/request-otp', [AuthController::class, 'requestWhatsappOtp'])->name('auth.whatsapp.request');
    Route::post('/auth/whatsapp/verify-otp', [AuthController::class, 'verifyWhatsappOtp'])->name('auth.whatsapp.verify');
});

Route::middleware(['tenants', 'throttle:10,1'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.auth.forgot-password');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->name('api.auth.reset-password');
    Route::post('/auth/whatsapp/request-otp', [AuthController::class, 'requestWhatsappOtp'])->name('api.auth.whatsapp.request');
    Route::post('/auth/whatsapp/verify-otp', [AuthController::class, 'verifyWhatsappOtp'])->name('api.auth.whatsapp.verify');
});

Route::middleware(['tenants', 'auth:sanctum', 'throttle:120,1'])
    ->name('api.')
    ->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/auth/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

        Route::get('/coach/workspace', [CoachWorkspaceController::class, 'index'])->name('coach.workspace');
        Route::get('/coach/clients/{user}', [CoachWorkspaceController::class, 'client'])->name('coach.clients.show');

        Route::get('/client/home', ClientHomeController::class)->name('client.home');

        Route::get('/communications/catalog', [CommunicationController::class, 'catalog'])->name('communications.catalog');
        Route::get('/communications/inbox-summary', [CommunicationController::class, 'inboxSummary'])->name('communications.inbox-summary');

        Route::get('/messages/threads', [MessageController::class, 'threads'])->name('messages.threads');
        Route::get('/messages/threads/{conversation}', [MessageController::class, 'show'])->name('messages.show');
        Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread-count');
        Route::post('/messages', [MessageController::class, 'send'])->middleware('throttle:30,1')->name('messages.send');
        Route::get('/messages/templates', [MessageController::class, 'templates'])->name('messages.templates');
        Route::post('/messages/templates/render', [MessageController::class, 'renderTemplate'])->middleware('throttle:60,1')->name('messages.templates.render');
        Route::post('/messages/broadcast', [MessageController::class, 'broadcast'])->middleware('throttle:15,1')->name('messages.broadcast');
        Route::get('/messages/broadcasts', [MessageController::class, 'broadcasts'])->name('messages.broadcasts');
        Route::get('/messages/broadcasts/{broadcast}', [MessageController::class, 'broadcastShow'])->name('messages.broadcasts.show');
        Route::get('/messages/broadcasts/{broadcast}/recipients', [MessageController::class, 'broadcastRecipients'])->name('messages.broadcasts.recipients');

        Route::get('/habits/today', [HabitController::class, 'today'])->name('habits.today');
        Route::post('/habits/{habit}/log', [HabitController::class, 'log'])->middleware('throttle:40,1')->name('habits.log');

        Route::get('/workouts/today', [WorkoutLogController::class, 'today'])->name('workouts.today');
        Route::get('/workouts/{workoutSchedule}', [WorkoutLogController::class, 'show'])->name('workouts.show');
        Route::post('/workouts/{workoutSchedule}/complete', [WorkoutLogController::class, 'complete'])->middleware('throttle:40,1')->name('workouts.complete');
        Route::post('/workouts/{workoutSchedule}/skip', [WorkoutLogController::class, 'skip'])->middleware('throttle:40,1')->name('workouts.skip');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
        Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences.update');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->middleware('throttle:30,1')->name('push-subscriptions.store');
        Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');

        Route::get('/community/posts', [CommunityFeedController::class, 'index'])->name('community.index');
        Route::post('/community/posts', [CommunityFeedController::class, 'store'])->middleware('throttle:40,1')->name('community.store');
        Route::post('/community/posts/{post}/react', [CommunityFeedController::class, 'react'])->middleware('throttle:80,1')->name('community.react');
        Route::post('/community/posts/{post}/comment', [CommunityFeedController::class, 'comment'])->middleware('throttle:60,1')->name('community.comment');

        Route::get('/nutrition', [NutritionController::class, 'index'])->name('nutrition.index');
        Route::get('/nutrition/search', [NutritionController::class, 'search'])->name('nutrition.search');
        Route::post('/nutrition', [NutritionController::class, 'store'])->middleware('throttle:40,1')->name('nutrition.store');

        Route::get('/check-ins', [CheckInController::class, 'index'])->name('check-ins.index');
        Route::post('/check-ins', [CheckInController::class, 'store'])->middleware('throttle:20,1')->name('check-ins.store');
        Route::get('/check-ins/{checkIn}', [CheckInController::class, 'show'])->name('check-ins.show');

        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/sessions', [BookingController::class, 'sessions'])->name('bookings.sessions');
        Route::get('/bookings/sessions/{trainingSession}/slots', [BookingController::class, 'slots'])->name('bookings.slots');
        Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:20,1')->name('bookings.store');
        Route::post('/bookings/{sessionBooking}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:20,1')->name('bookings.cancel');
        Route::put('/bookings/{sessionBooking}/reschedule', [BookingController::class, 'reschedule'])->middleware('throttle:20,1')->name('bookings.reschedule');

        Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
        Route::post('/challenges/{challenge}/join', [ChallengeController::class, 'join'])->middleware('throttle:20,1')->name('challenges.join');
    });

Route::middleware(['tenants', 'auth:sanctum', 'throttle:120,1'])
    ->prefix('v1')
    ->name('api.v1.')
    ->group(function () {
        Route::get('/mobile/bootstrap', MobileBootstrapController::class)->name('mobile.bootstrap');

        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/auth/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

        Route::get('/coach/workspace', [CoachWorkspaceController::class, 'index'])->name('coach.workspace');
        Route::get('/coach/clients/{user}', [CoachWorkspaceController::class, 'client'])->name('coach.clients.show');

        Route::get('/client/home', ClientHomeController::class)->name('client.home');

        Route::get('/communications/catalog', [CommunicationController::class, 'catalog'])->name('communications.catalog');
        Route::get('/communications/inbox-summary', [CommunicationController::class, 'inboxSummary'])->name('communications.inbox-summary');

        Route::get('/messages/threads', [MessageController::class, 'threads'])->name('messages.threads');
        Route::get('/messages/threads/{conversation}', [MessageController::class, 'show'])->name('messages.show');
        Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread-count');
        Route::post('/messages', [MessageController::class, 'send'])->middleware('throttle:30,1')->name('messages.send');
        Route::get('/messages/templates', [MessageController::class, 'templates'])->name('messages.templates');
        Route::post('/messages/templates/render', [MessageController::class, 'renderTemplate'])->middleware('throttle:60,1')->name('messages.templates.render');
        Route::post('/messages/broadcast', [MessageController::class, 'broadcast'])->middleware('throttle:15,1')->name('messages.broadcast');
        Route::get('/messages/broadcasts', [MessageController::class, 'broadcasts'])->name('messages.broadcasts');
        Route::get('/messages/broadcasts/{broadcast}', [MessageController::class, 'broadcastShow'])->name('messages.broadcasts.show');
        Route::get('/messages/broadcasts/{broadcast}/recipients', [MessageController::class, 'broadcastRecipients'])->name('messages.broadcasts.recipients');

        Route::get('/habits/today', [HabitController::class, 'today'])->name('habits.today');
        Route::post('/habits/{habit}/log', [HabitController::class, 'log'])->middleware('throttle:40,1')->name('habits.log');

        Route::get('/workouts/today', [WorkoutLogController::class, 'today'])->name('workouts.today');
        Route::get('/workouts/{workoutSchedule}', [WorkoutLogController::class, 'show'])->name('workouts.show');
        Route::post('/workouts/{workoutSchedule}/complete', [WorkoutLogController::class, 'complete'])->middleware('throttle:40,1')->name('workouts.complete');
        Route::post('/workouts/{workoutSchedule}/skip', [WorkoutLogController::class, 'skip'])->middleware('throttle:40,1')->name('workouts.skip');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
        Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences.update');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->middleware('throttle:30,1')->name('push-subscriptions.store');
        Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');

        Route::get('/community/posts', [CommunityFeedController::class, 'index'])->name('community.index');
        Route::post('/community/posts', [CommunityFeedController::class, 'store'])->middleware('throttle:40,1')->name('community.store');
        Route::post('/community/posts/{post}/react', [CommunityFeedController::class, 'react'])->middleware('throttle:80,1')->name('community.react');
        Route::post('/community/posts/{post}/comment', [CommunityFeedController::class, 'comment'])->middleware('throttle:60,1')->name('community.comment');

        Route::get('/nutrition', [NutritionController::class, 'index'])->name('nutrition.index');
        Route::get('/nutrition/search', [NutritionController::class, 'search'])->name('nutrition.search');
        Route::post('/nutrition', [NutritionController::class, 'store'])->middleware('throttle:40,1')->name('nutrition.store');

        Route::get('/check-ins', [CheckInController::class, 'index'])->name('check-ins.index');
        Route::post('/check-ins', [CheckInController::class, 'store'])->middleware('throttle:20,1')->name('check-ins.store');
        Route::get('/check-ins/{checkIn}', [CheckInController::class, 'show'])->name('check-ins.show');

        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/sessions', [BookingController::class, 'sessions'])->name('bookings.sessions');
        Route::get('/bookings/sessions/{trainingSession}/slots', [BookingController::class, 'slots'])->name('bookings.slots');
        Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:20,1')->name('bookings.store');
        Route::post('/bookings/{sessionBooking}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:20,1')->name('bookings.cancel');
        Route::put('/bookings/{sessionBooking}/reschedule', [BookingController::class, 'reschedule'])->middleware('throttle:20,1')->name('bookings.reschedule');

        Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
        Route::post('/challenges/{challenge}/join', [ChallengeController::class, 'join'])->middleware('throttle:20,1')->name('challenges.join');
    });

Route::post('/webhooks/communication/{provider}', CommunicationWebhookController::class)
    ->middleware(['tenants', 'verify_webhook_signature', 'throttle:120,1'])
    ->name('api.webhooks.communication');
