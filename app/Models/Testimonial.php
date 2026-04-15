<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Services\TenantCache;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'story_content',
        'image',
        'is_visible',
        'sort_order',
        'user_id'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the user that created the testimonial
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include visible testimonials
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to order testimonials by sort order and then by id
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get all visible testimonials
     */
    public static function getVisibleTestimonials()
    {
        return Cache::remember(TenantCache::key('visible_testimonials'), 7200, function () {
            return self::visible()
                ->ordered()
                ->select(['id', 'name', 'story_content', 'image', 'sort_order'])
                ->get();
        });
    }

    /**
     * Clear the testimonials cache
     */
    public static function clearCache()
    {
        Cache::forget(TenantCache::key('visible_testimonials'));
        // Also clear the site settings cache to ensure fresh data
        Cache::forget(TenantCache::key('settings_group_homepage'));
    }

    /**
     * Get the status badge for admin display
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->is_visible) {
            return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">مرئي</span>';
        }
        
        return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">مخفي</span>';
    }
}