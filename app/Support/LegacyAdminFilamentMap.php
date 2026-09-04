<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Maps legacy Blade admin route names to Filament /admin-cms paths.
 */
class LegacyAdminFilamentMap
{
    public const PANEL = '/admin-cms';

    /**
     * Resolve Filament URL for a named legacy route, or null if none.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function urlForRoute(?string $routeName, array $parameters = []): ?string
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        $id = static fn (string $key): string => (string) self::paramId($parameters, $key);

        return match ($routeName) {
            'dashboard' => self::PANEL,

            'notes.index' => self::PANEL.'/notes',
            'notes.create' => self::PANEL.'/notes/create',
            'notes.edit' => self::PANEL.'/notes/'.$id('note').'/edit',

            'pages.index' => self::PANEL.'/pages',
            'pages.create' => self::PANEL.'/pages/create',
            'pages.edit' => self::PANEL.'/pages/'.$id('page').'/edit',

            'articles.index' => self::PANEL.'/articles',
            'articles.create' => self::PANEL.'/articles/create',
            'articles.edit' => self::PANEL.'/articles/'.$id('article').'/edit',

            'admin.landing-pages.index' => self::PANEL.'/landing-pages',
            'admin.landing-pages.create' => self::PANEL.'/landing-pages/create',
            'admin.landing-pages.edit' => self::PANEL.'/landing-pages/'.$id('landingPage').'/edit',

            'admin.faqs.index' => self::PANEL.'/faqs',
            'admin.faqs.create' => self::PANEL.'/faqs/create',
            'admin.faqs.edit' => self::PANEL.'/faqs/'.$id('faq').'/edit',

            'admin.testimonials.index' => self::PANEL.'/testimonials',
            'admin.testimonials.create' => self::PANEL.'/testimonials/create',
            'admin.testimonials.edit' => self::PANEL.'/testimonials/'.$id('testimonial').'/edit',

            'coach.workspace' => self::PANEL.'/coach-workspace',
            'coach.clients.index' => self::PANEL.'/clients',
            'coach.clients.show' => self::PANEL.'/clients/'.$id('user'),

            'clients.progress.index',
            'clients.progress.create' => self::PANEL.'/clients/'.$id('user'),

            'coach-availabilities.index' => self::PANEL.'/coach-availabilities',
            'coach-availabilities.create' => self::PANEL.'/coach-availabilities/create',
            'coach-availabilities.edit' => self::PANEL.'/coach-availabilities/'.$id('coach_availability').'/edit',

            'admin.training-sessions.index' => self::PANEL.'/training-sessions',
            'admin.training-sessions.create' => self::PANEL.'/training-sessions/create',
            'admin.training-sessions.edit' => self::PANEL.'/training-sessions/'.$id('trainingSession').'/edit',

            'admin.session-bookings.index' => self::PANEL.'/session-bookings',
            'admin.session-bookings.edit' => self::PANEL.'/session-bookings/'.$id('sessionBooking').'/edit',

            'exercises.index' => self::PANEL.'/exercises',
            'exercises.create' => self::PANEL.'/exercises/create',
            'exercises.show' => self::PANEL.'/exercises/'.$id('exercise'),
            'exercises.edit' => self::PANEL.'/exercises/'.$id('exercise').'/edit',

            'workouts.index' => self::PANEL.'/workouts',
            'workouts.create' => self::PANEL.'/workouts/create',
            'workouts.show' => self::PANEL.'/workouts/'.$id('workout'),
            'workouts.edit' => self::PANEL.'/workouts/'.$id('workout').'/edit',

            'workout-schedules.weekly',
            'workout-schedules.index' => self::PANEL.'/workout-schedules',
            'workout-schedules.create' => self::PANEL.'/workout-schedules/create',
            'workout-schedules.edit' => self::PANEL.'/workout-schedules/'.$id('workout_schedule').'/edit',
            'workout-schedules.show' => self::PANEL.'/workout-schedules/list',

            'meal-plans.index' => self::PANEL.'/meal-plans',
            'meal-plans.create' => self::PANEL.'/meal-plans/create',
            'meal-plans.edit' => self::PANEL.'/meal-plans/'.$id('mealPlan').'/edit',
            'meal-plans.library-review' => self::PANEL.'/meal-plans/library-review',

            'supplement-plans.index' => self::PANEL.'/supplement-plans',
            'supplement-plans.create' => self::PANEL.'/supplement-plans/create',
            'supplement-plans.edit' => self::PANEL.'/supplement-plans/'.$id('supplementPlan').'/edit',

            'nutrition-discounts.index' => self::PANEL.'/nutrition-discounts',
            'nutrition-discounts.create' => self::PANEL.'/nutrition-discounts/create',
            'nutrition-discounts.edit' => self::PANEL.'/nutrition-discounts/'.$id('nutritionDiscount').'/edit',

            'messages.index' => self::PANEL.'/conversations',
            'messages.show' => self::PANEL.'/conversations/'.$id('conversation').'/edit',

            'notifications.index' => self::PANEL.'/notification-feeds',

            'habits.index' => self::PANEL.'/habits',
            'habits.create' => self::PANEL.'/habits/create',
            'habits.edit' => self::PANEL.'/habits/'.$id('habit').'/edit',

            'community.index' => self::PANEL.'/community-posts',

            'membership-types.index' => self::PANEL.'/membership-types',
            'membership-types.create' => self::PANEL.'/membership-types/create',
            'membership-types.edit' => self::PANEL.'/membership-types/'.$id('membershipType').'/edit',
            'membership-types.show' => self::PANEL.'/membership-types/'.$id('membershipType').'/edit',

            'subscription-plans.index' => self::PANEL.'/subscription-plans',
            'subscription-plans.create' => self::PANEL.'/subscription-plans/create',
            'subscription-plans.edit' => self::PANEL.'/subscription-plans/'.$id('subscriptionPlan').'/edit',

            'admin.user-memberships.index' => self::PANEL.'/user-memberships',

            'tenant.billing' => self::PANEL.'/tenant-billing',

            'admin.permissions.index' => self::PANEL.'/roles',
            'admin.permissions.groups' => self::PANEL.'/permission-groups',
            'admin.permissions.manage-user' => self::PANEL.'/user-permission-overrides',
            'admin.permissions.report' => self::PANEL.'/permission-audit-logs',

            'admin.settings.index' => self::PANEL.'/manage-site-settings',

            default => null,
        };
    }

    public static function urlForRequest(Request $request): ?string
    {
        $route = $request->route();
        if (! $route) {
            return null;
        }

        return self::urlForRoute($route->getName(), $route->parameters());
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private static function paramId(array $parameters, string $key): string|int
    {
        $value = $parameters[$key] ?? null;

        if ($value instanceof Model) {
            return $value->getKey();
        }

        // Resource route param names sometimes differ (snake vs camel).
        if ($value === null) {
            foreach ($parameters as $param) {
                if ($param instanceof Model) {
                    return $param->getKey();
                }
            }
        }

        return $value ?? '';
    }
}
