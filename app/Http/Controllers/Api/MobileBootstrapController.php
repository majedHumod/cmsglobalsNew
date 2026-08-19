<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantDiscoveryService;
use App\Services\TenantService;
use Illuminate\Http\Request;

class MobileBootstrapController extends Controller
{
    public function __invoke(Request $request, TenantDiscoveryService $discovery)
    {
        $user = $request->user();
        $tenant = TenantService::getTenant();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->values(),
                'coach_id' => $user->coach_id ?? null,
            ],
            'organization' => $tenant ? $discovery->toPublicPayload($tenant) : null,
            'locale' => 'ar',
            'exercise_locale' => config('exercise_localization.default_locale', 'ar'),
            'direction' => 'rtl',
            'endpoints' => [
                'organizations_discover' => url('/api/v1/organizations/discover'),
                'organizations_search' => url('/api/v1/organizations/search'),
                'auth_me' => route('api.v1.auth.me'),
                'auth_logout' => route('api.v1.auth.logout'),
                'auth_logout_all' => route('api.v1.auth.logout-all'),
                'auth_forgot_password' => route('api.v1.auth.forgot-password'),
                'auth_reset_password' => route('api.v1.auth.reset-password'),
                'auth_whatsapp_request_otp' => route('api.v1.auth.whatsapp.request'),
                'auth_whatsapp_verify_otp' => route('api.v1.auth.whatsapp.verify'),
                'home' => route('api.v1.client.home'),
                'habits' => route('api.v1.habits.today'),
                'habits_log' => url('/api/v1/habits/{habit}/log'),
                'workouts_complete' => url('/api/v1/workouts/{workoutSchedule}/complete'),
                'workouts_skip' => url('/api/v1/workouts/{workoutSchedule}/skip'),
                'messages' => route('api.v1.messages.threads'),
                'messages_send' => route('api.v1.messages.send'),
                'messages_unread_count' => route('api.v1.messages.unread-count'),
                'messages_templates' => route('api.v1.messages.templates'),
                'messages_templates_render' => route('api.v1.messages.templates.render'),
                'messages_broadcast' => route('api.v1.messages.broadcast'),
                'messages_broadcasts' => route('api.v1.messages.broadcasts'),
                'communications_catalog' => route('api.v1.communications.catalog'),
                'communications_inbox_summary' => route('api.v1.communications.inbox-summary'),
                'notifications' => route('api.v1.notifications.index'),
                'notifications_unread_count' => route('api.v1.notifications.unread-count'),
                'notifications_preferences' => route('api.v1.notifications.preferences'),
                'notifications_read_all' => route('api.v1.notifications.read-all'),
                'push_subscriptions' => route('api.v1.push-subscriptions.store'),
                'community' => route('api.v1.community.index'),
                'community_store' => route('api.v1.community.store'),
                'nutrition' => route('api.v1.nutrition.index'),
                'nutrition_store' => route('api.v1.nutrition.store'),
                'nutrition_search' => route('api.v1.nutrition.search'),
                'check_ins' => route('api.v1.check-ins.index'),
                'check_ins_store' => route('api.v1.check-ins.store'),
                'bookings' => route('api.v1.bookings.index'),
                'bookings_sessions' => route('api.v1.bookings.sessions'),
                'bookings_slots' => url('/api/v1/bookings/sessions/{trainingSession}/slots'),
                'bookings_store' => route('api.v1.bookings.store'),
                'bookings_cancel' => url('/api/v1/bookings/{sessionBooking}/cancel'),
                'bookings_reschedule' => url('/api/v1/bookings/{sessionBooking}/reschedule'),
                'challenges' => route('api.v1.challenges.index'),
            ],
            'screens' => [
                'organization' => ['organizations_discover', 'organizations_search'],
                'login' => ['auth_login', 'bootstrap'],
                'home' => ['home'],
                'habits' => ['habits', 'habits_log', 'challenges'],
                'checkin' => ['check_ins', 'check_ins_store'],
                'bookings' => ['bookings', 'bookings_sessions', 'bookings_slots', 'bookings_store'],
                'workout' => ['workouts_complete', 'workouts_skip'],
                'messages' => ['messages', 'messages_send', 'messages_templates', 'messages_templates_render', 'messages_broadcast', 'messages_broadcasts', 'communications_inbox_summary'],
                'nutrition' => ['nutrition', 'nutrition_store', 'nutrition_search'],
                'community' => ['community', 'community_store'],
                'challenges' => ['challenges'],
                'notifications' => ['notifications', 'notifications_unread_count', 'notifications_preferences', 'communications_catalog', 'communications_inbox_summary'],
            ],
            'communication' => [
                'catalog_version' => 1,
                'channels' => ['dm', 'notification', 'broadcast', 'community', 'system'],
                'template_variables' => ['client_name', 'name', 'coach_name', 'org_name', 'date', 'membership_expires'],
            ],
        ]);
    }
}
