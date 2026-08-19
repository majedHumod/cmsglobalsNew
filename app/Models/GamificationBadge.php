<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamificationBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'points',
    ];

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class, 'badge_id');
    }
}
