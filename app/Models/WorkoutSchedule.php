<?php

namespace App\Models;

use App\Models\Concerns\HasAudience;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutSchedule extends Model
{
    use HasFactory;
    use HasAudience;

    protected $fillable = [
        'workout_id',
        'week_number',
        'session_number',
        'notes',
        'status',
        'user_id',
        'audience_gender',
        'required_membership_types',
    ];

    protected $casts = [
        'status' => 'boolean',
        'week_number' => 'integer',
        'session_number' => 'integer',
        'audience_gender' => 'string',
    ];

    /**
     * العلاقة مع التمرين
     */
    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function logs()
    {
        return $this->hasMany(WorkoutLog::class);
    }

    /**
     * العلاقة مع المستخدم (المدرب)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope للجدولة النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope للجدولة حسب الأسبوع
     */
    public function scopeByWeek($query, $weekNumber)
    {
        return $query->where('week_number', $weekNumber);
    }

    /**
     * Scope للجدولة حسب الجلسة
     */
    public function scopeBySession($query, $sessionNumber)
    {
        return $query->where('session_number', $sessionNumber);
    }

    /**
     * Scope للجدولة الخاصة بالمستخدم
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * الحصول على اسم الجلسة
     */
    public function getSessionNameAttribute()
    {
        $sessions = [
            1 => 'الجلسة الأولى',
            2 => 'الجلسة الثانية',
            3 => 'الجلسة الثالثة',
            4 => 'الجلسة الرابعة',
            5 => 'الجلسة الخامسة',
            6 => 'الجلسة السادسة',
            7 => 'الجلسة السابعة'
        ];

        return $sessions[$this->session_number] ?? "الجلسة {$this->session_number}";
    }

    /**
     * الحصول على اسم الأسبوع
     */
    public function getWeekNameAttribute()
    {
        return "الأسبوع {$this->week_number}";
    }

    /**
     * الحصول على حالة الجدولة بالعربية
     */
    public function getStatusNameAttribute()
    {
        return $this->status ? 'نشط' : 'غير نشط';
    }

    /**
     * التحقق من إمكانية التعديل
     */
    public function canEdit($user)
    {
        return $user->hasRole('admin') || $this->user_id === $user->id;
    }

    /**
     * التحقق من إمكانية الحذف
     */
    public function canDelete($user)
    {
        return $user->hasRole('admin') || $this->user_id === $user->id;
    }
}