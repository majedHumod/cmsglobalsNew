<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fitness_goal',
        'target_weight',
        'activity_level',
        'preferred_contact_method',
        'injuries',
        'medical_notes',
        'onboarding_notes',
        'current_program_week',
        'program_started_at',
        'week_advance_mode',
    ];

    protected $casts = [
        'target_weight' => 'decimal:2',
        'current_program_week' => 'integer',
        'program_started_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
