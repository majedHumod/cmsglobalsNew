<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressCheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coach_id',
        'submitted_by_user_id',
        'checked_in_at',
        'weight',
        'body_fat_percentage',
        'waist_cm',
        'chest_cm',
        'hips_cm',
        'arm_cm',
        'thigh_cm',
        'progress_photo_path',
        'energy_level',
        'training_adherence',
        'nutrition_adherence',
        'notes',
        'coach_feedback',
        'next_steps',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'weight' => 'decimal:2',
        'body_fat_percentage' => 'decimal:2',
        'waist_cm' => 'decimal:2',
        'chest_cm' => 'decimal:2',
        'hips_cm' => 'decimal:2',
        'arm_cm' => 'decimal:2',
        'thigh_cm' => 'decimal:2',
        'energy_level' => 'integer',
        'training_adherence' => 'integer',
        'nutrition_adherence' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function getAverageAdherenceAttribute(): ?float
    {
        $scores = array_filter([
            $this->training_adherence,
            $this->nutrition_adherence,
        ], static fn ($value) => $value !== null);

        if ($scores === []) {
            return null;
        }

        return round(array_sum($scores) / count($scores), 1);
    }
}
