<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class NutritionDiscount extends Model
{
    use HasFactory;

    /** Relative path on the public disk. */
    public const DEFAULT_IMAGE = 'nutrition-discounts/default.svg';

    /** @deprecated use DEFAULT_IMAGE */
    public const DEFAULT_IMAGE_PATH = 'images/nutrition-discount-placeholder.svg';

    protected $fillable = [
        'name',
        'discount_percentage',
        'start_date',
        'end_date',
        'image',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->hasCustomImage()) {
            if (str_starts_with((string) $this->image, 'http://') || str_starts_with((string) $this->image, 'https://')) {
                return (string) $this->image;
            }

            return $this->publicUrl('/storage/'.ltrim((string) $this->image, '/'));
        }

        return static::defaultImageUrl();
    }

    public function getHasCustomImageAttribute(): bool
    {
        return $this->hasCustomImage();
    }

    public function hasCustomImage(): bool
    {
        $image = $this->image;

        if (! filled($image) || $image === self::DEFAULT_IMAGE) {
            return false;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return true;
        }

        return Storage::disk('public')->exists($image);
    }

    public static function defaultImageUrl(): string
    {
        static::ensureDefaultImageExists();

        return static::publicUrl('/storage/'.self::DEFAULT_IMAGE);
    }

    /**
     * Inline SVG fallback that never depends on HTTP/static files.
     */
    public static function defaultImageDataUri(): string
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#0f766e"/>
      <stop offset="100%" stop-color="#134e4a"/>
    </linearGradient>
  </defs>
  <rect width="640" height="480" fill="url(#bg)"/>
  <circle cx="520" cy="80" r="90" fill="#14b8a6" opacity="0.18"/>
  <circle cx="80" cy="400" r="120" fill="#99f6e4" opacity="0.12"/>
  <g transform="translate(220 145)">
    <path d="M40 20h110a20 20 0 0 1 20 20v90a20 20 0 0 1-20 20H90l-30 30v-30H40a20 20 0 0 1-20-20V40a20 20 0 0 1 20-20z" fill="#ecfdf5" opacity="0.95"/>
    <text x="95" y="95" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="42" font-weight="700" fill="#0f766e">%</text>
  </g>
  <text x="320" y="360" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="28" font-weight="600" fill="#ecfdf5">خصم غذائي</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public static function ensureDefaultImageExists(): void
    {
        if (Storage::disk('public')->exists(self::DEFAULT_IMAGE)) {
            return;
        }

        $source = public_path(self::DEFAULT_IMAGE_PATH);
        if (is_file($source)) {
            Storage::disk('public')->put(self::DEFAULT_IMAGE, file_get_contents($source));

            return;
        }

        Storage::disk('public')->put(
            self::DEFAULT_IMAGE,
            <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#0f766e"/>
      <stop offset="100%" stop-color="#134e4a"/>
    </linearGradient>
  </defs>
  <rect width="640" height="480" fill="url(#bg)"/>
  <circle cx="520" cy="80" r="90" fill="#14b8a6" opacity="0.18"/>
  <circle cx="80" cy="400" r="120" fill="#99f6e4" opacity="0.12"/>
  <g transform="translate(220 145)">
    <path d="M40 20h110a20 20 0 0 1 20 20v90a20 20 0 0 1-20 20H90l-30 30v-30H40a20 20 0 0 1-20-20V40a20 20 0 0 1 20-20z" fill="#ecfdf5" opacity="0.95"/>
    <text x="95" y="95" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="42" font-weight="700" fill="#0f766e">%</text>
  </g>
  <text x="320" y="360" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="28" font-weight="600" fill="#ecfdf5">خصم غذائي</text>
</svg>
SVG
        );
    }

    public static function publicUrl(string $path): string
    {
        $path = '/'.ltrim($path, '/');
        $root = request()?->getSchemeAndHttpHost();

        if (filled($root)) {
            return $root.$path;
        }

        return url($path);
    }

    public function getIsValidAttribute(): bool
    {
        return $this->is_active
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    public function getValidityStatusAttribute(): string
    {
        $today = now()->startOfDay();

        if ($this->end_date->lt($today)) {
            return 'expired';
        }

        if ($this->start_date->gt($today)) {
            return 'upcoming';
        }

        return 'current';
    }

    public function getValidityStatusLabelAttribute(): string
    {
        return match ($this->validity_status) {
            'upcoming' => 'قادم',
            'expired' => 'منتهي',
            default => 'ساري',
        };
    }
}
