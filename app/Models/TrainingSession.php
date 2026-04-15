<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Services\TenantCache;

class TrainingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'duration_hours',
        'image',
        'is_visible',
        'sort_order',
        'user_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_hours' => 'integer',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
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
    public static function getHomepageSessions()
    {
        return Cache::remember(TenantCache::key('homepage_training_sessions'), 7200, function () {
            try {
                $count = \App\Models\SiteSetting::get('training_sessions_count', 4);
                return self::visible()
                    ->ordered()
                    ->limit($count)
                    ->select(['id', 'title', 'description', 'price', 'duration_hours', 'image', 'sort_order'])
                    ->get();
            } catch (\Exception $e) {
                // Fallback to 4 sessions if there's an error
                return self::visible()
                    ->ordered()
                    ->limit(4)
                    ->select(['id', 'title', 'description', 'price', 'duration_hours', 'image', 'sort_order'])
                    ->get();
            }
        });
    }

    /**
     * Get all visible sessions
     */
    public static function getAllVisibleSessions()
    {
        return self::visible()->ordered()->get();
    }

    /**
     * Clear the training sessions cache
     */
    public static function clearCache()
    {
        Cache::forget(TenantCache::key('homepage_training_sessions'));
        // Also clear the site settings cache to ensure fresh data
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
        return !$this->bookings()
            ->where('booking_date', $date)
            ->where('booking_time', $time)
            ->whereIn('status', ['confirmed', 'pending'])
            ->exists();
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
}