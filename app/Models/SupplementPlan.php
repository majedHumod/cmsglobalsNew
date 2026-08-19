<?php

namespace App\Models;

use App\Models\Concerns\HasAudience;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SupplementPlan extends Model
{
    use HasFactory;
    use HasAudience;

    protected $fillable = [
        'name',
        'description',
        'supplement_type',
        'dosage',
        'timing',
        'brand',
        'image',
        'instructions',
        'warnings',
        'is_active',
        'user_id',
        'audience_gender',
        'required_membership_types',
        'sort_order',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
        'audience_gender' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canManage(User $user): bool
    {
        return $user->hasRole('admin') || (int) $this->user_id === (int) $user->id;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return null;
    }

    public function getSupplementTypeNameAttribute(): string
    {
        return match ($this->supplement_type) {
            'protein'      => 'بروتين',
            'vitamins'     => 'فيتامينات',
            'minerals'     => 'معادن',
            'pre_workout'  => 'ما قبل التمرين',
            'post_workout' => 'ما بعد التمرين',
            'omega'        => 'أوميغا',
            default        => 'عام',
        };
    }

    public function getTimingNameAttribute(): string
    {
        return match ($this->timing) {
            'morning'      => 'الصباح',
            'pre_workout'  => 'قبل التمرين',
            'post_workout' => 'بعد التمرين',
            'night'        => 'قبل النوم',
            'with_meal'    => 'مع الوجبة',
            default        => $this->timing,
        };
    }

    public function getSupplementTypeColorAttribute(): string
    {
        return match ($this->supplement_type) {
            'protein'      => 'blue',
            'vitamins'     => 'yellow',
            'minerals'     => 'gray',
            'pre_workout'  => 'red',
            'post_workout' => 'green',
            'omega'        => 'indigo',
            default        => 'gray',
        };
    }
}
