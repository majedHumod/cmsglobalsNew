<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habit extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_user_id',
        'created_by_user_id',
        'name',
        'unit',
        'target_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function logs()
    {
        return $this->hasMany(HabitLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
