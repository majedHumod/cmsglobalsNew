<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workout_schedule_id',
        'workout_id',
        'scheduled_on',
        'status',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_on' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workoutSchedule()
    {
        return $this->belongsTo(WorkoutSchedule::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
