<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'challenge_type',
        'target_value',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_active' => 'boolean',
    ];

    public function participants()
    {
        return $this->hasMany(ChallengeParticipant::class, 'challenge_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)
            ->whereDate('starts_on', '<=', now()->toDateString())
            ->whereDate('ends_on', '>=', now()->toDateString());
    }
}
