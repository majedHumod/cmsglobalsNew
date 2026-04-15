<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Services\TenantCache;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'category',
        'is_active',
        'sort_order',
        'user_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the user that created the FAQ
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active FAQs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order FAQs by sort order and then by id
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get all active FAQs
     */
    public static function getActive()
    {
        return Cache::remember(TenantCache::key('active_faqs'), 7200, function () {
            return self::active()
                ->ordered()
                ->select(['id', 'question', 'answer', 'category', 'sort_order'])
                ->get();
        });
    }

    /**
     * Get FAQs grouped by category
     */
    public static function getGroupedByCategory()
    {
        return Cache::remember(TenantCache::key('faqs_by_category'), 7200, function () {
            return self::active()
                ->ordered()
                ->select(['id', 'question', 'answer', 'category', 'sort_order'])
                ->get()
                ->groupBy('category');
        });
    }

    /**
     * Clear the FAQ cache
     */
    public static function clearCache()
    {
        Cache::forget(TenantCache::key('active_faqs'));
        Cache::forget(TenantCache::key('faqs_by_category'));
    }
}