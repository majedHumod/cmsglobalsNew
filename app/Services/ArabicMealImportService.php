<?php

namespace App\Services;

use App\Models\MealPlan;
use App\Models\User;

class ArabicMealImportService
{
    public function __construct(
        private ArabicMealLibraryGenerator $generator,
        private PexelsMealImageService $images,
    ) {
    }

    /**
     * @return array{created: int, updated: int, skipped: int, images: int}
     */
    public function import(int $count = 300, bool $force = false, bool $withImages = true, ?int $ownerUserId = null): array
    {
        $source = (string) config('meal_library.source', 'arabic_library');
        $ownerUserId ??= User::query()->role(['admin', 'coach'])->orderBy('id')->value('id')
            ?? User::query()->orderBy('id')->value('id');

        if (! $ownerUserId) {
            throw new \RuntimeException('No tenant users found to own imported meal plans.');
        }

        $this->images->ensureStorageDir();
        $this->images->resetUsage();

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'images' => 0];
        $meals = $this->generator->generate($count);

        foreach ($meals as $meal) {
            $existing = MealPlan::query()
                ->where('source', $source)
                ->where('external_id', $meal['external_id'])
                ->first();

            if ($existing && ! $force) {
                $stats['skipped']++;
                continue;
            }

            $imagePath = $existing?->image;
            if ($withImages) {
                $downloaded = $this->images->downloadForMeal(
                    $meal['external_id'],
                    $meal['meal_type'],
                    $meal['name_en'],
                    $meal['name'],
                    $force,
                    $meal['ingredient_keys'] ?? []
                );
                if ($downloaded) {
                    $imagePath = $downloaded;
                    $stats['images']++;
                }
            }

            $payload = [
                'source' => $source,
                'external_id' => $meal['external_id'],
                'user_id' => $ownerUserId,
                'name' => $meal['name'],
                'name_en' => $meal['name_en'],
                'description' => $meal['description'],
                'description_en' => $meal['description_en'],
                'meal_type' => $meal['meal_type'],
                'calories' => $meal['calories'],
                'protein' => $meal['protein'],
                'carbs' => $meal['carbs'],
                'fats' => $meal['fats'],
                'nutrition_is_estimated' => true,
                'nutrition_source' => $meal['nutrition_source'],
                'ingredients' => $meal['ingredients'],
                'ingredients_en' => $meal['ingredients_en'],
                'ingredients_json' => $meal['ingredients_json'],
                'instructions' => $meal['instructions'],
                'instructions_en' => $meal['instructions_en'],
                'prep_time' => $meal['prep_time'],
                'cook_time' => $meal['cook_time'],
                'servings' => $meal['servings'],
                'difficulty' => $meal['difficulty'],
                'is_active' => true,
                'audience_gender' => 'all',
                'required_membership_types' => [],
                'image' => $imagePath,
                'image_attribution' => 'Photo via LoremFlickr (Flickr Creative Commons)',
                'image_attribution_url' => 'https://loremflickr.com',
            ];

            if ($existing) {
                $existing->update($payload);
                $stats['updated']++;
            } else {
                MealPlan::create($payload);
                $stats['created']++;
            }
        }

        return $stats;
    }
}
