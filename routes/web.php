<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MembershipTypeController;
use App\Http\Controllers\AdvancedPermissionController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\SessionBookingController;
use App\Http\Controllers\NutritionDiscountController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserMembershipListController;

// Landing Page Route
Route::get('/', function() {
    try {
        return app()->make(LandingPageController::class)->show();
    } catch (\Exception $e) {
        return view('welcome');
    }
})->name('home');

Route::get('/meal-plans/{mealPlan}', [MealPlanController::class, 'showPublic'])->name('meal-plans.show-public');
// Public FAQs Route
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');

// Public Testimonials Route
Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.all');

// Public Training Sessions Routes
Route::get('/training-sessions', function() {
    try {
        $trainingSessions = \App\Models\TrainingSession::getAllVisibleSessions();
        return view('training-sessions.all', compact('trainingSessions'));
    } catch (\Exception $e) {
        return redirect()->route('home')->with('error', 'حدث خطأ أثناء تحميل جلسات التدريب.');
    }
})->name('training-sessions.all');

Route::get('/training-sessions/{trainingSession}', [TrainingSessionController::class, 'show'])->name('training-sessions.show');

// Public Nutrition Discounts Route
Route::get('/nutrition-discounts', [NutritionDiscountController::class, 'frontend'])->name('nutrition-discounts.frontend');
Route::get('/articles-public', [ArticleController::class, 'publicIndex'])->name('articles.public.index');
Route::get('/articles-public/{article}', [ArticleController::class, 'publicShow'])->name('articles.public.show');

// Booking routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::post('/training-sessions/{trainingSession}/book', [TrainingSessionController::class, 'book'])->name('training-sessions.book');
    Route::get('/training-sessions/booking/{sessionBooking}/payment', [TrainingSessionController::class, 'processPayment'])->name('training-sessions.payment');
    Route::get('/training-sessions/booking/{sessionBooking}/success', [TrainingSessionController::class, 'paymentSuccess'])->name('training-sessions.booking-success');
});

Route::middleware([
    'auth:sanctum',config('jetstream.auth_session'),'verified','tenants'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware(['auth', 'role:admin'])->prefix('admin/user-memberships')->name('admin.user-memberships.')->group(function () {
        Route::get('/', [UserMembershipListController::class, 'index'])->name('index');
    });

    // Notes routes with admin role middleware
    Route::middleware(['auth', 'role:user|admin'])->group(function () {
        Route::resource('/notes', NoteController::class);
    });

    // Articles routes - admin only
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::resource('/articles', ArticleController::class)->except('show');
    });

    // Meal Plans routes - accessible to both admin and user
    Route::middleware(['auth', 'role:user|admin'])->group(function () {
        Route::resource('/meal-plans', MealPlanController::class);
    });

    // Public meal plans route (accessible to all authenticated users)
    Route::get('/meal-plans-public', [MealPlanController::class, 'publicIndex'])->name('meal-plans.public');

    // Pages routes - accessible to admin and page_manager
    Route::middleware(['auth', 'role:admin|page_manager'])->group(function () {
        Route::resource('/pages', PageController::class);
    });

    // Public pages route (accessible to all authenticated users)
    Route::get('/pages-public', [PageController::class, 'publicIndex'])->name('pages.public');

    // Membership Types routes - admin only
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::resource('/membership-types', MembershipTypeController::class);
        Route::patch('/membership-types/{membershipType}/toggle-status', [MembershipTypeController::class, 'toggleStatus'])->name('membership-types.toggle-status');
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
    Route::middleware(['auth', 'role:admin'])->prefix('admin/training-sessions')->name('admin.training-sessions.')->group(function () {
        Route::get('/', [TrainingSessionController::class, 'index'])->name('index');
        Route::get('/create', [TrainingSessionController::class, 'create'])->name('create');
        Route::post('/', [TrainingSessionController::class, 'store'])->name('store');
        Route::get('/{trainingSession}/edit', [TrainingSessionController::class, 'edit'])->name('edit');
        Route::put('/{trainingSession}', [TrainingSessionController::class, 'update'])->name('update');
        Route::delete('/{trainingSession}', [TrainingSessionController::class, 'destroy'])->name('destroy');
        Route::patch('/{trainingSession}/toggle-visibility', [TrainingSessionController::class, 'toggleVisibility'])->name('toggle-visibility');
    });
    
    // Session Bookings routes - admin only
    Route::middleware(['auth', 'role:admin'])->prefix('admin/session-bookings')->name('admin.session-bookings.')->group(function () {
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

    // Workouts routes - accessible to admin, coach, and client with different permissions
    Route::middleware(['auth', 'role:admin|coach|client'])->group(function () {
        Route::resource('/workouts', \App\Http\Controllers\WorkoutController::class);
    });

    // Workout Schedules routes - accessible to admin, coach, and client with different permissions
    Route::middleware(['auth', 'role:admin|coach|client'])->group(function () {
        Route::resource('/workout-schedules', \App\Http\Controllers\WorkoutScheduleController::class);
        Route::get('/workout-schedules-weekly', [\App\Http\Controllers\WorkoutScheduleController::class, 'weeklyView'])->name('workout-schedules.weekly');
    });
});

// Public page view route (accessible to everyone)
Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');

Route::get('/send-sms', [SmsController::class, 'sendTestSms']);

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        Session::put('locale', $locale);
        App::setLocale($locale);
    }
    return redirect()->back();
});

// ------------------------------
// Billing (system-level) endpoints
// ------------------------------
use App\Http\Controllers\Billing\PlanController;
use App\Http\Controllers\Billing\CheckoutController;
use App\Http\Controllers\Billing\PaylinkCallbackController;
use App\Http\Controllers\Billing\PaylinkWebhookController;
use App\Http\Controllers\Billing\SubscribePageController;

// Public endpoints to read plans and start checkout (initial stub)
Route::get('/plans', [PlanController::class, 'index'])->name('billing.plans');
Route::post('/checkout/session', [CheckoutController::class, 'create'])->name('billing.checkout.session');

// Paylink payment callbacks
Route::get('/billing/paylink/callback', PaylinkCallbackController::class)->name('billing.paylink.callback');
Route::post('/webhooks/paylink', PaylinkWebhookController::class)->name('billing.webhooks.paylink');

// Subscribe landing/form
Route::get('/subscribe', [SubscribePageController::class, 'index'])->name('subscribe');

// ------------------------------
// Tenant Admin - Billing page (read-only for now)
// ------------------------------
Route::middleware([
    'auth:sanctum', config('jetstream.auth_session'), 'verified', 'tenants', 'role:admin'
])->group(function () {
    Route::get('/admin/billing', [\App\Http\Controllers\Tenant\BillingController::class, 'index'])
        ->name('tenant.billing');
});