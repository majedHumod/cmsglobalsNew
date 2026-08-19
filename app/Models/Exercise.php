<?php

namespace App\Models;

use App\Services\ExerciseTranslationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Exercise extends Model
{
    use HasFactory;

    public const SOURCE_REPDB = 'repdb';

    public const SOURCE_CUSTOM = 'custom';

    public const DEFAULT_ATTRIBUTION_TEXT = 'Exercise data by RepDB (repdb.co)';

    public const DEFAULT_ATTRIBUTION_URL = 'https://repdb.co';

    protected $fillable = [
        'external_id',
        'source',
        'user_id',
        'name',
        'description',
        'instructions',
        'translations',
        'category',
        'difficulty',
        'equipment',
        'body_part',
        'primary_muscles',
        'secondary_muscles',
        'tags',
        'met',
        'image_start_path',
        'image_peak_path',
        'video_url',
        'attribution_required',
        'attribution_text',
        'attribution_url',
        'status',
    ];

    protected $casts = [
        'instructions' => 'array',
        'translations' => 'array',
        'primary_muscles' => 'array',
        'secondary_muscles' => 'array',
        'tags' => 'array',
        'met' => 'float',
        'attribution_required' => 'boolean',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workouts()
    {
        return $this->belongsToMany(Workout::class, 'workout_exercises')
            ->withPivot(['sort_order', 'sets', 'reps', 'rest_seconds', 'coach_cue'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeFromRepdb($query)
    {
        return $query->where('source', self::SOURCE_REPDB);
    }

    public function scopeCustom($query)
    {
        return $query->where('source', self::SOURCE_CUSTOM);
    }

    /**
     * Search English columns plus localized translation JSON for the active/fallback locales.
     */
    public function scopeSearchLocalized(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';
        $service = app(ExerciseTranslationService::class);
        $locale = $service->resolveLocale();
        $fallback = (string) config('exercise_localization.fallback_locale', 'en');

        return $query->where(function (Builder $q) use ($like, $locale, $fallback) {
            $q->where('name', 'like', $like)
                ->orWhere('external_id', 'like', $like)
                ->orWhere('body_part', 'like', $like)
                ->orWhere('equipment', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('translations', 'like', $like)
                ->orWhere("translations->name->{$locale}", 'like', $like)
                ->orWhere("translations->description->{$locale}", 'like', $like);

            if ($fallback !== $locale) {
                $q->orWhere("translations->name->{$fallback}", 'like', $like);
            }
        });
    }

    public function localized(?string $field = 'name', ?string $locale = null): mixed
    {
        return app(ExerciseTranslationService::class)->localizedValue($this, $field ?: 'name', $locale);
    }

    public function getLocalizedNameAttribute(): string
    {
        return (string) ($this->localized('name') ?: $this->name);
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        $value = $this->localized('description');

        return $value !== null && $value !== '' ? (string) $value : null;
    }

    /**
     * @return list<string>
     */
    public function getLocalizedInstructionsAttribute(): array
    {
        $value = $this->localized('instructions');
        if (is_string($value) && trim($value) !== '') {
            return [trim($value)];
        }

        if (! is_array($value)) {
            return array_values($this->instructions ?? []);
        }

        return array_values(array_filter(array_map(
            fn ($line) => is_string($line) ? trim($line) : '',
            $value
        ), fn ($line) => $line !== ''));
    }

    public function getLocalizedBodyPartAttribute(): ?string
    {
        return app(ExerciseTranslationService::class)->label('body_part', $this->body_part);
    }

    public function getLocalizedEquipmentAttribute(): ?string
    {
        return app(ExerciseTranslationService::class)->label('equipment', $this->equipment);
    }

    public function isCustom(): bool
    {
        return $this->source === self::SOURCE_CUSTOM;
    }

    public function isRepdb(): bool
    {
        return $this->source === self::SOURCE_REPDB;
    }

    public function canEdit($user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $this->isCustom() && (int) $this->user_id === (int) $user->id;
    }

    public function canDelete($user): bool
    {
        if ($this->isRepdb()) {
            return false;
        }

        return $this->canEdit($user);
    }

    public function getPrimaryImagePathAttribute(): ?string
    {
        return $this->image_peak_path ?: $this->image_start_path;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->resolveStorageUrl($this->primary_image_path);
    }

    public function getImageStartUrlAttribute(): ?string
    {
        return $this->resolveStorageUrl($this->image_start_path);
    }

    public function getImagePeakUrlAttribute(): ?string
    {
        return $this->resolveStorageUrl($this->image_peak_path);
    }

    public function getDifficultyNameAttribute(): string
    {
        return match ($this->difficulty) {
            'beginner', 'easy' => 'مبتدئ',
            'intermediate', 'medium' => 'متوسط',
            'advanced', 'hard' => 'متقدم',
            default => $this->difficulty ?: '—',
        };
    }

    public function getSourceNameAttribute(): string
    {
        return match ($this->source) {
            self::SOURCE_REPDB => 'مكتبة RepDB',
            self::SOURCE_CUSTOM => 'مخصص',
            default => $this->source ?: '—',
        };
    }

    public function attributionPayload(): ?array
    {
        if (! $this->attribution_required || ! $this->primary_image_path) {
            return null;
        }

        return [
            'required' => true,
            'text' => $this->attribution_text ?: self::DEFAULT_ATTRIBUTION_TEXT,
            'url' => $this->attribution_url ?: self::DEFAULT_ATTRIBUTION_URL,
        ];
    }

    public static function makeCustomExternalId(string $name): string
    {
        $slug = Str::slug($name) ?: 'exercise';

        return 'custom-'.$slug.'-'.Str::lower(Str::random(6));
    }

    private function resolveStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return url('/storage/'.ltrim($path, '/'));
    }
}
