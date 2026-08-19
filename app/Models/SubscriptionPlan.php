<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_type_id',
        'name',
        'slug',
        'description',
        'duration_days',
        'price',
        'compare_at_price',
        'gender_scope',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function membershipType()
    {
        return $this->belongsTo(MembershipType::class);
    }

    public function memberships()
    {
        return $this->hasMany(UserMembership::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * True when a higher list price is set to communicate savings.
     */
    public function hasDiscount(): bool
    {
        return $this->compare_at_price !== null
            && (float) $this->compare_at_price > (float) $this->price;
    }

    public function discountPercent(): ?int
    {
        if (! $this->hasDiscount() || (float) $this->compare_at_price <= 0) {
            return null;
        }

        return (int) round((1 - ((float) $this->price / (float) $this->compare_at_price)) * 100);
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->formatMoney((float) $this->price);
    }

    public function getFormattedCompareAtPriceAttribute(): ?string
    {
        if ($this->compare_at_price === null) {
            return null;
        }

        return $this->formatMoney((float) $this->compare_at_price);
    }

    public function getDurationTextAttribute(): string
    {
        return match ((int) $this->duration_days) {
            7 => 'أسبوع',
            30 => 'شهر',
            90 => '3 أشهر',
            180 => '6 أشهر',
            365 => 'سنة',
            default => $this->duration_days . ' يوم',
        };
    }

    public function getGenderScopeLabelAttribute(): string
    {
        return match ($this->gender_scope) {
            'male' => 'رجال',
            'female' => 'نساء',
            default => 'الجميع',
        };
    }

    private function formatMoney(float $amount): string
    {
        if ($amount === 0.0) {
            return 'مجاني';
        }

        return number_format($amount, 2) . ' ريال';
    }
}
