<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MealPlan;
use App\Models\SessionBooking;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'users' => $this->safeCount('users', User::class),
            'meal_plans' => $this->safeCount('meal_plans', MealPlan::class),
            'session_bookings' => $this->safeCount('session_bookings', SessionBooking::class),
            'active_memberships' => $this->safeActiveMembershipsCount(),
        ];

        $recentMemberships = collect();
        if (Schema::hasTable('user_memberships')) {
            try {
                $recentMemberships = UserMembership::query()
                    ->with(['user', 'membershipType'])
                    ->latest()
                    ->take(8)
                    ->get();
            } catch (\Throwable) {
            }
        }

        $recentBookings = collect();
        if (Schema::hasTable('session_bookings')) {
            try {
                $recentBookings = SessionBooking::query()
                    ->with(['trainingSession', 'user'])
                    ->latest()
                    ->take(8)
                    ->get();
            } catch (\Throwable) {
            }
        }

        return view('admin.dashboard', compact('stats', 'recentMemberships', 'recentBookings'));
    }

    private function safeCount(string $table, string $modelClass): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }
        try {
            return $modelClass::query()->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function safeActiveMembershipsCount(): int
    {
        if (! Schema::hasTable('user_memberships')) {
            return 0;
        }
        try {
            return UserMembership::query()
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
