<?php

namespace App\Models;

use App\Models\Concerns\HasAudience;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\TenantCache;
use Illuminate\Support\Facades\Cache;

class TrainingSession extends Model
{
    use HasFactory;
    use HasAudience;

    protected $fillable = [
        'title',
        'description',
        'price',
        'duration_hours',
        'session_type',
        'capacity',
        'location',
        'video_meeting_url',
        'image',
        'is_visible',
        'sort_order',
        'user_id',
        'audience_gender',
        'required_membership_types',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_hours' => 'integer',
        'capacity' => 'integer',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
        'audience_gender' => 'string',
    ];

    /**
     * Get the user that created the training session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all bookings for this session
     */
    public function bookings()
    {
        return $this->hasMany(SessionBooking::class);
    }

    /**
     * Get confirmed bookings for this session
     */
    public function confirmedBookings()
    {
        return $this->hasMany(SessionBooking::class)->where('status', 'confirmed');
    }

    public function coachAvailabilities()
    {
        return $this->user?->coachAvailabilities();
    }

    /**
     * Scope a query to only include visible sessions
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to order sessions by sort order and then by id
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get visible sessions for homepage (4 sessions)
     */
    public static function getHomepageSessions(?User $user = null)
    {
        try {
            $count = \App\Models\SiteSetting::get('training_sessions_count', 4);

            return self::visible()
                ->visibleTo($user)
                ->ordered()
                ->limit($count)
                ->select([
                    'id', 'title', 'description', 'price', 'duration_hours', 'image',
                    'sort_order', 'audience_gender', 'required_membership_types',
                ])
                ->get();
        } catch (\Exception $e) {
            return self::visible()
                ->visibleTo($user)
                ->ordered()
                ->limit(4)
                ->select([
                    'id', 'title', 'description', 'price', 'duration_hours', 'image',
                    'sort_order', 'audience_gender', 'required_membership_types',
                ])
                ->get();
        }
    }

    /**
     * Get all visible sessions
     */
    public static function getAllVisibleSessions(?User $user = null)
    {
        return self::visible()->visibleTo($user)->ordered()->get();
    }

    /**
     * Clear the training sessions cache
     */
    public static function clearCache()
    {
        Cache::forget(TenantCache::key('settings_group_homepage'));
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        if ($this->price == 0) {
            return 'مجاني';
        }
        return number_format($this->price, 2) . ' ريال';
    }

    /**
     * Get duration text
     */
    public function getDurationTextAttribute()
    {
        if ($this->duration_hours == 1) {
            return 'ساعة واحدة';
        } elseif ($this->duration_hours == 2) {
            return 'ساعتان';
        } elseif ($this->duration_hours <= 10) {
            return $this->duration_hours . ' ساعات';
        } else {
            return $this->duration_hours . ' ساعة';
        }
    }

    /**
     * Get status badge for admin display
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->is_visible) {
            return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">مرئي</span>';
        }
        
        return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">مخفي</span>';
    }

    /**
     * Check if session is available for booking on specific date/time
     */
    public function isAvailableAt($date, $time)
    {
        $requestedStart = Carbon::parse($date . ' ' . $time);

        $availableWindows = CoachAvailability::query()
            ->where('user_id', $this->user_id)
            ->where('is_active', true)
            ->get();

        if ($availableWindows->isNotEmpty() && ! $availableWindows->contains(fn ($availability) => $availability->supportsSlot($date, $time, $this->duration_hours))) {
            return false;
        }

        $existingBookings = $this->bookings()
            ->where('booking_date', $date)
            ->where('booking_time', $requestedStart->format('H:i:s'))
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();

        return $existingBookings < max(1, (int) $this->capacity);
    }

    /**
     * Get total bookings count
     */
    public function getTotalBookingsAttribute()
    {
        return $this->bookings()->count();
    }

    /**
     * Get confirmed bookings count
     */
    public function getConfirmedBookingsAttribute()
    {
        return $this->bookings()->where('status', 'confirmed')->count();
    }

    public function canManage(User $user): bool
    {
        return $user->hasRole('admin') || (int) $this->user_id === (int) $user->id;
    }
}