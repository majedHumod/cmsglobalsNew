<?php

namespace App\Models;

use App\Models\Concerns\HasAudience;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory;
    use HasAudience;

    protected $fillable = [
        'name',
        'description',
        'duration',
        'exercise_count',
        'equipment_label',
        'difficulty',
        'video_url',
        'video_duration_seconds',
        'coach_notes',
        'image',
        'status',
        'user_id',
        'audience_gender',
        'required_membership_types',
    ];

    protected $casts = [
        'status' => 'boolean',
        'duration' => 'integer',
        'exercise_count' => 'integer',
        'video_duration_seconds' => 'integer',
        'coach_notes' => 'array',
        'audience_gender' => 'string',
    ];

    /**
     * العلاقة مع المستخدم (المدرب)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع جداول التمارين
     */
    public function schedules()
    {
        return $this->hasMany(WorkoutSchedule::class);
    }

    /**
     * حركات المكتبة المرتبطة بهذه الجلسة
     */
    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'workout_exercises')
            ->withPivot(['sort_order', 'sets', 'reps', 'rest_seconds', 'coach_cue'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function syncExerciseCountFromLibrary(): void
    {
        $count = $this->exercises()->count();
        if ($count > 0) {
            $this->forceFill(['exercise_count' => $count])->saveQuietly();
        }
    }

    public function mediaAttribution(): ?array
    {
        $this->loadMissing('exercises');

        foreach ($this->exercises as $exercise) {
            $attribution = $exercise->attributionPayload();
            if ($attribution) {
                return $attribution;
            }
        }

        return null;
    }

    /**
     * Resolve session media with fallback to the first linked exercise.
     *
     * @return array{video_url:?string,image:?string,image_url:?string,media_type:?string,media_url:?string,media_source:?string,media_attribution:?array}
     */
    public function resolvedMedia(): array
    {
        $this->loadMissing('exercises');

        $image = $this->image;
        $imageUrl = $this->resolveWorkoutImageUrl($image);
        $videoUrl = $this->video_url ?: null;
        $mediaSource = null;

        if ($videoUrl || $imageUrl) {
            $mediaSource = 'workout';
        } else {
            $first = $this->exercises->first();
            if ($first) {
                if ($first->video_url) {
                    $videoUrl = $first->video_url;
                    $mediaSource = 'first_exercise';
                } elseif ($first->image_url) {
                    $image = $first->primary_image_path;
                    $imageUrl = $first->image_url;
                    $mediaSource = 'first_exercise';
                }
            }
        }

        $mediaType = $videoUrl ? 'video' : ($imageUrl ? 'animated_image' : null);
        $mediaUrl = $videoUrl ?: $imageUrl;

        $mediaAttribution = null;
        if ($mediaSource === 'first_exercise') {
            $first = $this->exercises->first();
            $mediaAttribution = $first?->attributionPayload();
        } elseif ($imageUrl || $videoUrl) {
            // Workout-level media has no RepDB attribution by default.
            $mediaAttribution = null;
        }

        return [
            'video_url' => $videoUrl,
            'image' => $image,
            'image_url' => $imageUrl,
            'media_type' => $mediaType,
            'media_url' => $mediaUrl,
            'media_source' => $mediaSource,
            'media_attribution' => $mediaAttribution,
        ];
    }

    private function resolveWorkoutImageUrl(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        if (str_starts_with($image, '/')) {
            return url($image);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($image)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($image);
        }

        return url('/'.ltrim($image, '/'));
    }

    /**
     * Scope للتمارين النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope للتمارين حسب المستوى
     */
    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope للتمارين الخاصة بالمستخدم
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * الحصول على اسم مستوى الصعوبة بالعربية
     */
    public function getDifficultyNameAttribute()
    {
        $difficulties = [
            'easy' => 'سهل',
            'medium' => 'متوسط',
            'hard' => 'صعب'
        ];

        return $difficulties[$this->difficulty] ?? $this->difficulty;
    }

    /**
     * الحصول على حالة التمرين بالعربية
     */
    public function getStatusNameAttribute()
    {
        return $this->status ? 'نشط' : 'غير نشط';
    }

    /**
     * الحصول على لون مستوى الصعوبة
     */
    public function getDifficultyColorAttribute()
    {
        $colors = [
            'easy' => 'green',
            'medium' => 'yellow',
            'hard' => 'red'
        ];

        return $colors[$this->difficulty] ?? 'gray';
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