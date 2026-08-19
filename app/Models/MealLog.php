<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'meal_plan_id',
        'logged_on',
        'meal_slot',
        'adherence_score',
        'notes',
    ];

    protected $casts = [
        'logged_on' => 'date',
        'adherence_score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class);
    }

    public static function mealSlotLabels(): array
    {
        return [
            'breakfast' => 'إفطار',
            'lunch' => 'غداء',
            'dinner' => 'عشاء',
            'snack' => 'وجبة خفيفة',
        ];
    }
}
