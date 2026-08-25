<?php

use App\Http\Controllers\Billing\CheckoutController;
use App\Http\Controllers\Billing\PaylinkCallbackController;
use App\Http\Controllers\Billing\PaylinkWebhookController;
use App\Http\Controllers\Billing\StripeWebhookController;
use App\Http\Controllers\Billing\PlanController;
use App\Http\Controllers\Billing\SubscribePageController;
use App\Http\Controllers\Platform\AccountController;
use App\Http\Controllers\Platform\CustomerDirectoryController;
use App\Http\Controllers\Platform\SessionApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MembershipTypeController;
use App\Http\Controllers\AdvancedPermissionController;
use App\Http\Controllers\ClientProgressController;
use App\Http\Controllers\CoachAvailabilityController;
use App\Http\Controllers\CoachClientController;
use App\Http\Controllers\CoachWorkspaceWebController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\SessionBookingController;
use App\Http\Controllers\NutritionDiscountController;
use App\Http\Controllers\SupplementPlanController;
use App\Http\Controllers\MessageThreadController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\CommunityFeedController;
use App\Http\Controllers\BookingCalendarController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserMembershipListController;
use App\Http\Controllers\ClientHomeWebController;
use App\Http\Controllers\ClientMoreWebController;
use App\Http\Controllers\ClientPageWebController;
use App\Http\Controllers\ClientNotificationWebController;
use App\Http\Controllers\ClientBookingWebController;
use App\Http\Controllers\ClientHabitWebController;
use App\Http\Controllers\ClientProgressWebController;
use App\Http\Controllers\ClientMessageWebController;
use App\Http\Controllers\ClientNutritionWebController;
use App\Http\Controllers\ClientCommunityWebController;
use App\Http\Controllers\ClientChallengeWebController;
use App\Http\Controllers\Api\ClientHomeController as ApiClientHomeController;
use App\Http\Controllers\Api\WorkoutLogController as ApiWorkoutLogController;

// Landing Page Route
Route::get('/', function() {
    try {
        return app()->make(LandingPageController::class)->show();
    } catch (\Exception $e) {
        return view('welcome');
    }
})->name('home');

Route::get('/meal-plans/search', [MealPlanController::class, 'search'])
    ->middleware(['auth', 'role:user|client|admin|coach'])
    ->name('meal-plans.search');
Route::get('/meal-plans/{mealPlan}', [MealPlanController::class, 'showPublic'])
    ->whereNumber('mealPlan')
    ->name('meal-plans.show-public');
// Public FAQs Route
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');

// Public Testimonials Route
Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.all');

// Public Training Sessions Routes
Route::get('/training-sessions', function() {
    try {
        $trainingSessions = \App\Models\TrainingSession::getAllVisibleSessions(auth()->user());
        return view('training-sessions.all', compact('trainingSessions'));
    } catch (\Exception $e) {
        return redirect()->route('home')->with('error', 'حدث خطأ أثناء تحميل جلسات التدريب.');
    }
})->name('training-sessions.all');

Route::get('/training-sessions/{trainingSession}', [TrainingSessionController::class, 'show'])->name('training-sessions.show');
Route::get('/subscription-plans', [SubscriptionPlanController::class, 'publicIndex'])->name('subscription-plans.public');

// Public Nutrition Discounts Route
Route::get('/nutrition-discounts', [NutritionDiscountController::class, 'frontend'])->name('nutrition-discounts.frontend');

// Public Supplement Plans for clients
Route::middleware('auth')->get('/supplement-plans-public', [SupplementPlanController::class, 'publicIndex'])->name('supplement-plans.public');
Route::get('/articles-public', [ArticleController::class, 'publicIndex'])->name('articles.public.index');
Route::get('/articles-public/{article}', [ArticleController::class, 'publicShow'])->name('articles.public.show');

// Booking routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::post('/training-sessions/{trainingSession}/book', [TrainingSessionController::class, 'book'])->name('training-sessions.book');
    Route::get('/training-sessions/booking/{sessionBooking}/payment', [TrainingSessionController::class, 'processPayment'])->name('training-sessions.payment');
    Route::get('/training-sessions/booking/{sessionBooking}/success', [TrainingSessionController::class, 'paymentSuccess'])->name('training-sessions.booking-success');
    Route::get('/training-sessions/booking/{sessionBooking}/reschedule', [TrainingSessionController::class, 'rescheduleForm'])->name('training-sessions.reschedule-form');
    Route::put('/training-sessions/booking/{sessionBooking}/reschedule', [TrainingSessionController::class, 'reschedule'])->name('training-sessions.reschedule');
    Route::post('/training-sessions/booking/{sessionBooking}/cancel', [TrainingSessionController::class, 'cancel'])->name('training-sessions.cancel');
    Route::post('/subscription-plans/{subscriptionPlan}/subscribe', [SubscriptionPlanController::class, 'subscribe'])->name('subscription-plans.subscribe');
    Route::get('/subscription-memberships/{userMembership}/payment', [SubscriptionPlanController::class, 'payment'])->name('subscription-plans.payment');
    Route::get('/subscription-memberships/{userMembership}/renew', [SubscriptionPlanController::class, 'renew'])->name('subscription-plans.renew');
    Route::get('/subscription-memberships/{userMembership}/success', [SubscriptionPlanController::class, 'success'])->name('subscription-plans.success');
});

Route::middleware([
    'auth:sanctum',config('jetstream.auth_session'),'verified','tenants'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/client/home', ClientHomeWebController::class)->name('client.home');

    Route::middleware(['trainee'])->prefix('client')->name('client.')->group(function () {
        Route::get('/home/data', ApiClientHomeController::class)->name('home.data');
        Route::post('/workouts/{workoutSchedule}/complete', [ApiWorkoutLogController::class, 'complete'])->name('workouts.complete');
        Route::post('/workouts/{workoutSchedule}/skip', [ApiWorkoutLogController::class, 'skip'])->name('workouts.skip');

        Route::get('/habits', [ClientHabitWebController::class, 'index'])->name('habits.index');
        Route::post('/habits/{habit}/log', [ClientHabitWebController::class, 'log'])->name('habits.log');

        Route::get('/progress', [ClientProgressWebController::class, 'index'])->name('progress.index');
        Route::get('/progress/create', [ClientProgressWebController::class, 'create'])->name('progress.create');
        Route::post('/progress', [ClientProgressWebController::class, 'store'])->name('progress.store');

        Route::get('/messages', [ClientMessageWebController::class, 'index'])->name('messages.index');
        Route::get('/messages/{conversation}', [ClientMessageWebController::class, 'show'])->name('messages.show');
        Route::post('/messages', [ClientMessageWebController::class, 'store'])->name('messages.store');
        Route::post('/messages/{conversation}/send', [ClientMessageWebController::class, 'send'])->name('messages.send');

        Route::get('/nutrition', [ClientNutritionWebController::class, 'index'])->name('nutrition.index');
        Route::post('/nutrition', [ClientNutritionWebController::class, 'store'])->name('nutrition.store');

        Route::get('/community', [ClientCommunityWebController::class, 'index'])->name('community.index');
        Route::post('/community', [ClientCommunityWebController::class, 'store'])->name('community.store');
        Route::post('/community/{post}/react', [ClientCommunityWebController::class, 'react'])->name('community.react');
        Route::post('/community/{post}/comment', [ClientCommunityWebController::class, 'comment'])->name('community.comment');

        Route::get('/challenges', [ClientChallengeWebController::class, 'index'])->name('challenges.index');

        Route::get('/notifications', [ClientNotificationWebController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [ClientNotificationWebController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/notifications/read-all', [ClientNotificationWebController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [ClientNotificationWebController::class, 'markAsRead'])->name('notifications.read');

        Route::get('/bookings', [ClientBookingWebController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [ClientBookingWebController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [ClientBookingWebController::class, 'store'])->name('bookings.store');
        Route::post('/bookings/{sessionBooking}/cancel', [ClientBookingWebController::class, 'cancel'])->name('bookings.cancel');
        Route::put('/bookings/{sessionBooking}/reschedule', [ClientBookingWebController::class, 'reschedule'])->name('bookings.reschedule');
        Route::get('/training-sessions/{trainingSession}/slots', [ClientBookingWebController::class, 'slots'])->name('training-sessions.slots');

        Route::get('/more', ClientMoreWebController::class)->name('more');
        Route::get('/pages', [ClientPageWebController::class, 'index'])->name('pages.index');
        Route::get('/pages/{slug}', [ClientPageWebController::class, 'show'])->name('pages.show');
    });

    Route::middleware(['auth', 'role:admin'])->prefix('admin/user-memberships')->name('admin.user-memberships.')->group(function () {
        Route::get('/', [UserMembershipListController::class, 'index'])->name('index');
    });

    // Notes routes with admin role middleware
    Route::middleware(['auth', 'role:user|client|admin'])->group(function () {
        Route::resource('/notes', NoteController::class);
    });

    // Articles routes - admin only
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::resource('/articles', ArticleController::class)->except('show');
    });

    // Meal Plans routes - accessible to both admin and user
    Route::middleware(['auth', 'role:user|client|admin|coach'])->group(function () {
        Route::get('/meal-plans/library-review', [MealPlanController::class, 'libraryReview'])->name('meal-plans.library-review');
        Route::get('/meal-plans/stock-images/search', [MealPlanController::class, 'searchStockImages'])->name('meal-plans.stock-images.search');
        Route::put('/meal-plans/{mealPlan}/image', [MealPlanController::class, 'updateImage'])->whereNumber('mealPlan')->name('meal-plans.update-image');
        Route::post('/meal-plans/{mealPlan}/stock-image', [MealPlanController::class, 'applyStockImage'])->whereNumber('mealPlan')->name('meal-plans.stock-image.apply');
        Route::resource('/meal-plans', MealPlanController::class)->whereNumber('mealPlan');
    });

    // Public meal plans route (accessible to all authenticated users)
    Route::get('/meal-plans-public', [MealPlanController::class, 'publicIndex'])->name('meal-plans.public');

    // Pages: Spatie permissions (PagePolicy) + أدوار مُزامَنة في PermissionsSeeder
    Route::resource('/pages', PageController::class);

    // Public pages route (accessible to all authenticated users)
    Route::get('/pages-public', [PageController::class, 'publicIndex'])->name('pages.public');

    Route::get('/clients/{user}/progress', [ClientProgressController::class, 'index'])->name('clients.progress.index');
    Route::get('/clients/{user}/progress/create', [ClientProgressController::class, 'create'])->name('clients.progress.create');
    Route::post('/clients/{user}/progress', [ClientProgressController::class, 'store'])->name('clients.progress.store');
    Route::put('/clients/{user}/profile', [ClientProgressController::class, 'updateProfile'])->name('clients.profile.update');
    Route::get('/progress-check-ins/{progressCheckIn}', [ClientProgressController::class, 'show'])->name('progress-check-ins.show');
    Route::delete('/progress-check-ins/{progressCheckIn}', [ClientProgressController::class, 'destroy'])->name('progress-check-ins.destroy');

    Route::middleware(['auth', 'role:admin|coach'])->prefix('coach')->name('coach.')->group(function () {
        Route::get('/workspace', CoachWorkspaceWebController::class)->name('workspace');
        Route::get('/clients', [CoachClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/{user}', [CoachClientController::class, 'show'])->name('clients.show');
        Route::post('/clients/{user}/remind', [CoachClientController::class, 'remind'])->name('clients.remind');
        Route::put('/clients/{user}/assignment', [CoachClientController::class, 'updateAssignment'])->name('clients.assignment');
        Route::post('/clients/{user}/meals', [CoachClientController::class, 'assignMeals'])->name('clients.meals.assign');
        Route::delete('/clients/{user}/meals/{assignment}', [CoachClientController::class, 'unassignMeal'])->name('clients.meals.unassign');
    });

    Route::middleware(['auth', 'role:admin|coach'])->group(function () {
        Route::resource('/coach-availabilities', CoachAvailabilityController::class)->except('show');
    });

    Route::middleware(['auth', 'role:admin|coach|user|client'])->group(function () {
        Route::get('/messages', [MessageThreadController::class, 'index'])->name('messages.index');
        Route::post('/messages', [MessageThreadController::class, 'store'])->name('messages.store');
        Route::get('/messages/{conversation}', [MessageThreadController::class, 'show'])->name('messages.show');
        Route::post('/messages/{conversation}/send', [MessageThreadController::class, 'send'])->name('messages.send');
        Route::post('/messages/templates', [MessageThreadController::class, 'templatesStore'])->name('messages.templates.store');
        Route::post('/messages/broadcast', [MessageThreadController::class, 'broadcast'])->name('messages.broadcast');
    });

    Route::middleware(['auth', 'role:admin|coach|user|client'])->group(function () {
        Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.read');
    });

    Route::middleware(['auth', 'role:admin|coach|user|client'])->group(function () {
        Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
        Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
        Route::post('/habits/{habit}/toggle', [HabitController::class, 'toggle'])->name('habits.toggle');
        Route::post('/habits/{habit}/log', [HabitController::class, 'log'])->name('habits.log');
    });

    Route::middleware(['auth', 'role:admin|coach|user|client'])->group(function () {
        Route::get('/community', [CommunityFeedController::class, 'index'])->name('community.index');
        Route::post('/community', [CommunityFeedController::class, 'store'])->name('community.store');
        Route::post('/community/{post}/react', [CommunityFeedController::class, 'react'])->name('community.react');
        Route::post('/community/{post}/comment', [CommunityFeedController::class, 'comment'])->name('community.comment');
    });

    Route::middleware('auth')->get('/session-bookings/{sessionBooking}/calendar', [BookingCalendarController::class, 'download'])
        ->name('session-bookings.calendar');

    // Membership Types routes - admin only
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::resource('/membership-types', MembershipTypeController::class);
        Route::patch('/membership-types/{membershipType}/toggle-status', [MembershipTypeController::class, 'toggleStatus'])->name('membership-types.toggle-status');
        Route::resource('/admin/subscription-plans', SubscriptionPlanController::class)
            ->except('show')
            ->names('subscription-plans');
    });

    // Advanced Permissions routes - admin only
    Route::middleware(['auth', 'role:admin'])->prefix('admin/permissions')->name('admin.permissions.')->group(function () {
        Route::get('/', [AdvancedPermissionController::class, 'index'])->name('index');
        Route::get('/users/{user}/manage', [AdvancedPermissionController::class, 'manageUser'])->name('manage-user');
        Route::post('/users/{user}/grant-override', [AdvancedPermissionController::class, 'grantOverride'])->name('grant-override');
        Route::delete('/users/{user}/overrides/{override}/revoke', [AdvancedPermissionController::class, 'revokeOverride'])->name('revoke-override');
        Route::get('/groups', [AdvancedPermissionController::class, 'manageGroups'])->name('groups');
        Route::post('/groups', [AdvancedPermissionController::class, 'storeGroup'])->name('store-group');
        Route::get('/report', [AdvancedPermissionController::class, 'report'])->name('report');
        Route::post('/cleanup-expired', [AdvancedPermissionController::class, 'cleanupExpired'])->name('cleanup-expired');
        Route::get('/users/{user}/check-dependencies', [AdvancedPermissionController::class, 'checkDependencies'])->name('check-dependencies');
    });
    
    // Site Settings routes - admin only
    Route::middleware(['auth', 'role:admin'])->prefix('admin/settings')->name('admin.settings.')->group(function () {
        Route::get('/', [SiteSettingController::class, 'index'])->name('index');
        Route::post('/update-general', [SiteSettingController::class, 'updateGeneral'])->name('update-general');
        Route::post('/update-contact', [SiteSettingController::class, 'updateContact'])->name('update-contact');
        Route::post('/update-social', [SiteSettingController::class, 'updateSocial'])->name('update-social');
        Route::post('/update-app', [SiteSettingController::class, 'updateApp'])->name('update-app');
        Route::post('/update-homepage', [SiteSettingController::class, 'updateHomepage'])->name('update-homepage');
    });
    
    // Landing Page routes - admin only
    Route::middleware(['auth', 'role:admin'])->prefix('admin/landing-pages')->name('admin.landing-pages.')->group(function () {
        Route::get('/', [LandingPageController::class, 'index'])->name('index');
        Route::get('/create', [LandingPageController::class, 'create'])->name('create');
        Route::post('/', [LandingPageController::class, 'store'])->name('store');
        Route::get('/{landingPage}/edit', [LandingPageController::class, 'edit'])->name('edit');
        Route::put('/{landingPage}', [LandingPageController::class, 'update'])->name('update');
        Route::delete('/{landingPage}', [LandingPageController::class, 'destroy'])->name('destroy');
        Route::patch('/{landingPage}/set-active', [LandingPageController::class, 'setActive'])->name('set-active');
    });
    
    // FAQs routes - admin only
    Route::middleware(['auth', 'role:admin'])->prefix('admin/faqs')->name('admin.faqs.')->group(function () {
        Route::get('/', [FaqController::class, 'adminIndex'])->name('index');
        Route::get('/create', [FaqController::class, 'create'])->name('create');
        Route::post('/', [FaqController::class, 'store'])->name('store');
        Route::get('/{faq}/edit', [FaqController::class, 'edit'])->name('edit');
        Route::put('/{faq}', [FaqController::class, 'update'])->name('update');
        Route::delete('/{faq}', [FaqController::class, 'destroy'])->name('destroy');
        Route::patch('/{faq}/toggle-status', [FaqController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // Testimonials routes - admin only
    Route::middleware(['auth', 'role:admin'])->prefix('admin/testimonials')->name('admin.testimonials.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TestimonialController::class, 'adminIndex'])->name('index');
        Route::get('/create', [\App\Http\Controllers\TestimonialController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\TestimonialController::class, 'store'])->name('store');
        Route::get('/{testimonial}/edit', [\App\Http\Controllers\TestimonialController::class, 'edit'])->name('edit');
        Route::put('/{testimonial}', [\App\Http\Controllers\TestimonialController::class, 'update'])->name('update');
        Route::delete('/{testimonial}', [\App\Http\Controllers\TestimonialController::class, 'destroy'])->name('destroy');
        Route::patch('/{testimonial}/toggle-visibility', [\App\Http\Controllers\TestimonialController::class, 'toggleVisibility'])->name('toggle-visibility');
    });

    // Training Sessions routes - admin only
    Route::middleware(['auth', 'role:admin|coach'])->prefix('admin/training-sessions')->name('admin.training-sessions.')->group(function () {
        Route::get('/', [TrainingSessionController::class, 'index'])->name('index');
        Route::get('/create', [TrainingSessionController::class, 'create'])->name('create');
        Route::post('/', [TrainingSessionController::class, 'store'])->name('store');
        Route::get('/{trainingSession}/edit', [TrainingSessionController::class, 'edit'])->name('edit');
        Route::put('/{trainingSession}', [TrainingSessionController::class, 'update'])->name('update');
        Route::delete('/{trainingSession}', [TrainingSessionController::class, 'destroy'])->name('destroy');
        Route::patch('/{trainingSession}/toggle-visibility', [TrainingSessionController::class, 'toggleVisibility'])->name('toggle-visibility');
    });
    
    // Session Bookings routes - admin only
    Route::middleware(['auth', 'role:admin|coach'])->prefix('admin/session-bookings')->name('admin.session-bookings.')->group(function () {
        Route::get('/', [SessionBookingController::class, 'index'])->name('index');
        Route::get('/{sessionBooking}/edit', [SessionBookingController::class, 'edit'])->name('edit');
        Route::put('/{sessionBooking}', [SessionBookingController::class, 'update'])->name('update');
        Route::delete('/{sessionBooking}', [SessionBookingController::class, 'destroy'])->name('destroy');
        Route::patch('/{sessionBooking}/update-status', [SessionBookingController::class, 'updateStatus'])->name('update-status');
    });

    // Nutrition Discounts routes - admin only
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::resource('/nutrition-discounts', NutritionDiscountController::class);
        Route::patch('/nutrition-discounts/{nutritionDiscount}/toggle-status', [NutritionDiscountController::class, 'toggleStatus'])->name('nutrition-discounts.toggle-status');
    });

    // Supplement Plans CRUD - admin and coach
    Route::middleware(['auth', 'role:admin|coach'])->group(function () {
        Route::resource('/supplement-plans', SupplementPlanController::class)->except(['show']);
    });

    // Supplement Plans show - any authenticated user (access control handled in controller)
    Route::middleware('auth')->get('/supplement-plans/{supplementPlan}', [SupplementPlanController::class, 'show'])->name('supplement-plans.show');

    // Workouts routes - accessible to admin, coach, and trainee roles
    Route::middleware(['auth', 'role:admin|coach'])->group(function () {
        Route::get('/exercises/search', [\App\Http\Controllers\ExerciseController::class, 'search'])->name('exercises.search');
        Route::resource('/exercises', \App\Http\Controllers\ExerciseController::class);
    });

    Route::middleware(['auth', 'role:admin|coach|user|client'])->group(function () {
        Route::resource('/workouts', \App\Http\Controllers\WorkoutController::class);
    });

    // Workout Schedules routes - accessible to admin, coach, and trainee roles
    Route::middleware(['auth', 'role:admin|coach|user|client'])->group(function () {
        Route::resource('/workout-schedules', \App\Http\Controllers\WorkoutScheduleController::class);
        Route::get('/workout-schedules-weekly', [\App\Http\Controllers\WorkoutScheduleController::class, 'weeklyView'])->name('workout-schedules.weekly');
    });
});

// Public page view route (accessible to everyone)
Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');

Route::get('/send-sms', [SmsController::class, 'sendTestSms']);
Route::view('/offline', 'offline')->name('offline');

Route::get('/lang/{locale}', function ($locale) {
    $supported = array_values(array_unique(array_merge(
        ['en', 'ar'],
        config('exercise_localization.supported_locales', [])
    )));

    if (in_array($locale, $supported, true)) {
        Session::put('locale', $locale);
        App::setLocale($locale);
        config(['exercise_localization.runtime_locale' => $locale]);
    }
    return redirect()->back();
});

// Public endpoints to read plans and start checkout (initial stub)
Route::get('/plans', [PlanController::class, 'index'])->name('billing.plans');
Route::post('/checkout/session', [CheckoutController::class, 'create'])->name('billing.checkout.session');

// Paylink payment callbacks
Route::get('/billing/paylink/callback', PaylinkCallbackController::class)->name('billing.paylink.callback');
Route::post('/webhooks/paylink', PaylinkWebhookController::class)->name('billing.webhooks.paylink');
Route::post('/webhooks/stripe', StripeWebhookController::class)->middleware('tenants')->name('billing.webhooks.stripe');

// Subscribe landing/form (coach/club purchase — not trainee register)
Route::get('/subscribe', [SubscribePageController::class, 'index'])->name('subscribe');

Route::prefix('account')->name('platform.account.')->group(function () {
    Route::get('/login', [AccountController::class, 'loginForm'])->name('login');
    Route::post('/login', [AccountController::class, 'login'])->name('login.store');
    Route::match(['get', 'post'], '/logout', [AccountController::class, 'logout'])->name('logout');
    Route::get('/forgot', [AccountController::class, 'forgotForm'])->name('forgot');
    Route::post('/forgot', [AccountController::class, 'forgot'])->name('forgot.store');
    Route::get('/expired', [AccountController::class, 'expired'])->name('expired');
    Route::get('/session', [SessionApiController::class, 'show'])->name('session');
});

Route::get('/platform/customers', [CustomerDirectoryController::class, 'index'])->name('platform.customers');

// ------------------------------
// Tenant Admin - Billing page (read-only for now)
// ------------------------------
Route::middleware([
    'auth:sanctum', config('jetstream.auth_session'), 'verified', 'tenants', 'role:admin'
])->group(function () {
    Route::get('/admin/billing', [\App\Http\Controllers\Tenant\BillingController::class, 'index'])
        ->name('tenant.billing');
});