<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Downloads unique food-tagged images (LoremFlickr / Flickr CC) locked per meal id.
 * Safer relevance than hard-coded Pexels IDs that can map to unrelated photos.
 */
class PexelsMealImageService
{
    public const STORAGE_DIR = 'meal-plans/library';

    /** @var array<string, true> */
    private array $usedLocks = [];

    public function resetUsage(): void
    {
        $this->usedLocks = [];
    }

    public function resolveCategory(string $mealType, string $nameEn, string $nameAr, array $ingredientKeys = []): string
    {
        $hay = Str::lower($nameEn.' '.$nameAr);
        $keys = collect($ingredientKeys)->map(fn ($k) => Str::lower((string) $k))->implode(' ');
        $blob = $hay.' '.$keys;

        return match (true) {
            str_contains($blob, 'shrimp') || str_contains($hay, 'روبيان') || str_contains($hay, 'ربيان') => 'shrimp',
            str_contains($blob, 'salmon') || str_contains($hay, 'سلمون') => 'salmon',
            str_contains($blob, 'tuna') || str_contains($hay, 'تونة') => 'tuna',
            str_contains($blob, 'fish') || str_contains($hay, 'سمك') || str_contains($hay, 'صيادية') => 'fish',
            str_contains($blob, 'chicken') || str_contains($hay, 'دجاج') || str_contains($hay, 'طاووق') => 'chicken',
            str_contains($blob, 'turkey') || str_contains($hay, 'ديك رومي') => 'turkey',
            str_contains($blob, 'beef') || str_contains($blob, 'lamb') || str_contains($hay, 'لحم') || str_contains($hay, 'كفتة') => 'beef',
            str_contains($blob, 'oats') || str_contains($hay, 'شوفان') => 'oats',
            str_contains($blob, 'egg') || str_contains($hay, 'بيض') || str_contains($hay, 'عجة') => 'eggs',
            str_contains($blob, 'labneh') || str_contains($hay, 'لبنة') => 'labneh',
            str_contains($blob, 'yogurt') || str_contains($hay, 'زبادي') => 'yogurt',
            str_contains($blob, 'hummus') || str_contains($hay, 'حمص') => 'hummus',
            str_contains($blob, 'lentil') || str_contains($hay, 'عدس') || str_contains($hay, 'حريرة') => 'lentils',
            str_contains($blob, 'okra') || str_contains($hay, 'بامية') => 'okra',
            str_contains($blob, 'date') || str_contains($hay, 'تمر') => 'dates',
            str_contains($hay, 'apple') || str_contains($hay, 'banana') || str_contains($hay, 'orange')
                || str_contains($hay, 'تفاح') || str_contains($hay, 'موز') || str_contains($hay, 'برتقال') => 'fruit',
            str_contains($blob, 'kabsa') || str_contains($blob, 'machboos') || str_contains($blob, 'mandi')
                || str_contains($hay, 'كبسة') || str_contains($hay, 'مجبوس') || str_contains($hay, 'مندي')
                || str_contains($hay, 'مقلوبة') => 'rice',
            str_contains($blob, 'rice') || str_contains($blob, 'freekeh') || str_contains($blob, 'bulgur') || str_contains($blob, 'quinoa')
                || str_contains($hay, 'أرز') || str_contains($hay, 'برغل') || str_contains($hay, 'فريكة') || str_contains($hay, 'كينوا') => 'rice',
            str_contains($blob, 'soup') || str_contains($hay, 'شوربة') => 'soup',
            str_contains($blob, 'stew') || str_contains($hay, 'مرق') || str_contains($hay, 'فول') => 'stew',
            (str_contains($hay, 'salad') || str_contains($hay, 'سلطة') || str_contains($hay, 'تبولة') || str_contains($hay, 'فتوش'))
                && ! preg_match('/shrimp|chicken|fish|beef|salmon|tuna|turkey|روبيان|دجاج|سمك|لحم|سلمون|تونة|ديك رومي/u', $blob) => 'salad',
            str_contains($hay, 'grill') || str_contains($hay, 'مشوي') => 'grilled',
            $mealType === 'breakfast' => 'breakfast',
            $mealType === 'snack' => 'snack',
            default => 'healthy,meal,food',
        };
    }

    /**
     * @param  list<string>  $ingredientKeys
     */
    public function downloadForMeal(
        string $externalId,
        string $mealType,
        string $nameEn,
        string $nameAr,
        bool $force = false,
        array $ingredientKeys = []
    ): ?string {
        $relative = self::STORAGE_DIR.'/'.$externalId.'.jpg';
        if (! $force && Storage::disk('public')->exists($relative)) {
            return $relative;
        }

        $category = $this->resolveCategory($mealType, $nameEn, $nameAr, $ingredientKeys);
        $tags = $this->tagsForCategory($category);
        $lock = $this->uniqueLock($externalId, $category);
        $url = "https://loremflickr.com/800/600/{$tags}/all?lock={$lock}";

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'User-Agent' => 'cmsglobals-meal-library/1.1',
                    'Accept' => 'image/jpeg,image/*;q=0.8',
                ])
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if (! $response->successful() || strlen($response->body()) < 2000) {
                // Fallback: generic healthy food with a different lock.
                $fallbackLock = $lock + 917;
                $response = Http::timeout(60)
                    ->withHeaders(['User-Agent' => 'cmsglobals-meal-library/1.1'])
                    ->get("https://loremflickr.com/800/600/healthy,food,meal/all?lock={$fallbackLock}");
            }

            if (! $response->successful() || strlen($response->body()) < 2000) {
                return null;
            }

            // Reject obvious non-jpeg HTML error pages.
            $body = $response->body();
            if (str_starts_with(ltrim($body), '<')) {
                return null;
            }

            Storage::disk('public')->put($relative, $body);
            $this->usedLocks[(string) $lock] = true;

            return $relative;
        } catch (\Throwable) {
            return null;
        }
    }

    public function tagsForCategory(string $category): string
    {
        return match ($category) {
            'shrimp' => 'shrimp,seafood,grilled',
            'salmon' => 'salmon,fish,grilled',
            'tuna' => 'tuna,fish,salad',
            'fish' => 'fish,grilled,seafood',
            'chicken' => 'chicken,grilled,breast',
            'turkey' => 'turkey,breast,poultry',
            'beef' => 'beef,grilled,steak',
            'oats' => 'oatmeal,breakfast,bowl',
            'eggs' => 'eggs,omelette,breakfast',
            'labneh' => 'yogurt,mediterranean,food',
            'yogurt' => 'yogurt,breakfast,bowl',
            'hummus' => 'hummus,chickpeas,mediterranean',
            'lentils' => 'lentil,soup,stew',
            'okra' => 'okra,stew,vegetable',
            'dates' => 'dates,fruit,middleeast',
            'fruit' => 'fruit,healthy,snack',
            'rice' => 'rice,biryani,middleeastern',
            'soup' => 'soup,bowl,healthy',
            'stew' => 'stew,vegetables,healthy',
            'salad' => 'salad,vegetables,healthy',
            'grilled' => 'grilled,meat,healthy',
            'breakfast' => 'breakfast,healthy,food',
            'snack' => 'healthy,snack,food',
            default => 'healthy,meal,food',
        };
    }

    public function uniqueLock(string $externalId, string $category): int
    {
        $base = abs(crc32($externalId.'|'.$category));
        $lock = ($base % 900000) + 1;
        $guard = 0;
        while (isset($this->usedLocks[(string) $lock]) && $guard < 50) {
            $lock++;
            $guard++;
        }

        return $lock;
    }

    /** @deprecated kept for compatibility */
    public function pickUniquePhotoId(string $category, string $seed): int
    {
        return $this->uniqueLock($seed, $category);
    }

    public function pexelsImageUrl(int $photoId): string
    {
        return "https://loremflickr.com/800/600/healthy,food/all?lock={$photoId}";
    }

    public function ensureStorageDir(): void
    {
        Storage::disk('public')->makeDirectory(self::STORAGE_DIR);
        File::ensureDirectoryExists(storage_path('app/public/'.self::STORAGE_DIR));
    }
}
