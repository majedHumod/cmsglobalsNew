<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class NutritionDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'discount_percentage',
        'start_date',
        'end_date',
        'image',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active discounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get discounts that are currently valid (within date range)
     */
    public function scopeValid($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return null;
    }

    /**
     * Check if discount is currently valid
     */
    public function getIsValidAttribute()
    {
        return $this->is_active && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }
}
