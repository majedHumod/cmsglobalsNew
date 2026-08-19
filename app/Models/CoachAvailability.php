<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'buffer_minutes',
        'location',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'slot_duration_minutes' => 'integer',
        'buffer_minutes' => 'integer',
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supportsSlot(string $date, string $time, int $durationHours): bool
    {
        $slotStart = Carbon::parse($date . ' ' . $time);
        $slotEnd = (clone $slotStart)->addHours($durationHours);
        $windowStart = Carbon::parse($date . ' ' . $this->start_time);
        $windowEnd = Carbon::parse($date . ' ' . $this->end_time);

        return $this->is_active
            && $slotStart->dayOfWeek === $this->day_of_week
            && $slotStart->greaterThanOrEqualTo($windowStart)
            && $slotEnd->lessThanOrEqualTo($windowEnd);
    }

    public function getDayNameAttribute(): string
    {
        return [
            0 => 'الأحد',
            1 => 'الاثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
        ][$this->day_of_week] ?? 'غير محدد';
    }
}
