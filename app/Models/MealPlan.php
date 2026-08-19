<?php

namespace App\Models;

use App\Models\Concerns\HasAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class MealPlan extends Model
{
    use HasFactory;
    use HasAudience;

    public const SOURCE_CUSTOM = 'custom';

    public const SOURCE_ARABIC_LIBRARY = 'arabic_library';

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'meal_type',
        'calories',
        'protein',
        'carbs',
        'fats',
        'nutrition_is_estimated',
        'nutrition_source',
        'ingredients',
        'ingredients_en',
        'ingredients_json',
        'instructions',
        'instructions_en',
        'image',
        'image_attribution',
        'image_attribution_url',
        'prep_time',
        'cook_time',
        'servings',
        'difficulty',
        'is_active',
        'user_id',
        'source',
        'external_id',
        'audience_gender',
        'required_membership_types',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'nutrition_is_estimated' => 'boolean',
        'calories' => 'integer',
        'protein' => 'integer',
        'carbs' => 'integer',
        'fats' => 'integer',
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'servings' => 'integer',
        'audience_gender' => 'string',
        'ingredients_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canManage(User $user): bool
    {
        // Library meals are read-only for everyone (including admins).
        if ($this->isFromLibrary()) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return (int) $this->user_id === (int) $user->id
            && $this->source === self::SOURCE_CUSTOM;
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user ? $this->canManage($user) : false;
    }

    public function isFromLibrary(): bool
    {
        return $this->source === self::SOURCE_ARABIC_LIBRARY;
    }

    public function canReplaceImage(User $user): bool
    {
        return $this->canManage($user);
    }

    public function scopeFromLibrary(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_ARABIC_LIBRARY);
    }

    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_CUSTOM);
    }

    public function scopeSearchLibrary(Builder $query, ?string $term = null, ?string $mealType = null): Builder
    {
        if ($mealType) {
            $query->where('meal_type', $mealType);
        }

        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('name_en', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('description_en', 'like', $like)
                ->orWhere('ingredients', 'like', $like)
                ->orWhere('ingredients_en', 'like', $like)
                ->orWhere('external_id', 'like', $like);
        });
    }

    public function getLocalizedNameAttribute(): string
    {
        return $this->localized('name');
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        return $this->localized('description');
    }

    public function getLocalizedIngredientsAttribute(): ?string
    {
        return $this->localized('ingredients');
    }

    public function getLocalizedInstructionsAttribute(): ?string
    {
        return $this->localized('instructions');
    }

    public function localized(string $field = 'name', ?string $locale = null): ?string
    {
        $locale = $locale ?: App::getLocale();
        $enField = $field.'_en';

        if (str_starts_with($locale, 'en') && ! empty($this->{$enField})) {
            return $this->{$enField};
        }

        return $this->{$field} ?? $this->{$enField};
    }

    public function getImageUrlAttribute(): ?string
    {
        $image = $this->image;
        if (! $image) {
            return null;
        }

        $url = null;

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            $path = parse_url($image, PHP_URL_PATH);
            // Re-host absolute /storage/... URLs on the current tenant host.
            if (is_string($path) && str_contains($path, '/storage/')) {
                $url = $this->absolutePublicUrl($path);
            } else {
                return $image;
            }
        } elseif (str_starts_with($image, '/')) {
            $url = $this->absolutePublicUrl($image);
        } else {
            $url = $this->absolutePublicUrl('/storage/'.ltrim($image, '/'));
        }

        return $this->withImageCacheBuster($url, $image);
    }

    private function absolutePublicUrl(string $path): string
    {
        $path = '/'.ltrim($path, '/');
        $root = request()?->getSchemeAndHttpHost();

        if (filled($root)) {
            return $root.$path;
        }

        return url($path);
    }

    private function withImageCacheBuster(string $url, string $relativeOrAbsolute): string
    {
        $version = null;
        $relative = $relativeOrAbsolute;

        if (str_starts_with($relative, 'http://') || str_starts_with($relative, 'https://')) {
            $path = parse_url($relative, PHP_URL_PATH) ?: '';
            $relative = ltrim(preg_replace('#^.*?/storage/#', '', $path) ?? '', '/');
        } else {
            $relative = ltrim(preg_replace('#^/?storage/#', '', $relative) ?? $relative, '/');
        }

        if ($relative !== '' && Storage::disk('public')->exists($relative)) {
            $version = (string) Storage::disk('public')->lastModified($relative);
        } elseif ($this->updated_at) {
            $version = (string) $this->updated_at->timestamp;
        }

        if (! $version) {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').'v='.$version;
    }

    public function getNutritionDisclaimerAttribute(): string
    {
        $locale = App::getLocale();
        if (str_starts_with($locale, 'en')) {
            return (string) config('meal_library.nutrition_disclaimer_en');
        }

        return (string) config('meal_library.nutrition_disclaimer_ar');
    }

    public function getMealTypeNameAttribute()
    {
        $types = [
            'breakfast' => 'إفطار',
            'lunch' => 'غداء',
            'dinner' => 'عشاء',
            'snack' => 'وجبة خفيفة',
        ];

        return $types[$this->meal_type] ?? $this->meal_type;
    }

    public function getDifficultyNameAttribute()
    {
        $difficulties = [
            'easy' => 'سهل',
            'medium' => 'متوسط',
            'hard' => 'صعب',
        ];

        return $difficulties[$this->difficulty] ?? $this->difficulty;
    }

    public function getTotalTimeAttribute()
    {
        return ($this->prep_time ?? 0) + ($this->cook_time ?? 0);
    }

    public function getTotalMacrosAttribute()
    {
        return ($this->protein ?? 0) + ($this->carbs ?? 0) + ($this->fats ?? 0);
    }

    public function getMacroPercentagesAttribute()
    {
        $total = $this->total_macros;

        if ($total == 0) {
            return ['protein' => 0, 'carbs' => 0, 'fats' => 0];
        }

        return [
            'protein' => round((($this->protein ?? 0) / $total) * 100, 1),
            'carbs' => round((($this->carbs ?? 0) / $total) * 100, 1),
            'fats' => round((($this->fats ?? 0) / $total) * 100, 1),
        ];
    }
}
