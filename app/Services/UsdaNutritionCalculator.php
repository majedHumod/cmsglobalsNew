<?php

namespace App\Services;

class UsdaNutritionCalculator
{
    /**
     * @param  array<int, array{key: string, grams: float|int}>  $ingredients
     * @return array{calories: int, protein: int, carbs: int, fats: int, lines_ar: list<string>, lines_en: list<string>, breakdown: list<array>}
     */
    public function calculate(array $ingredients): array
    {
        $catalog = config('usda_ingredients', []);
        $kcal = 0.0;
        $protein = 0.0;
        $carbs = 0.0;
        $fats = 0.0;
        $linesAr = [];
        $linesEn = [];
        $breakdown = [];

        foreach ($ingredients as $row) {
            $key = (string) ($row['key'] ?? '');
            $grams = (float) ($row['grams'] ?? 0);
            if ($key === '' || $grams <= 0 || ! isset($catalog[$key])) {
                continue;
            }

            $item = $catalog[$key];
            $factor = $grams / 100.0;
            $itemKcal = $item['kcal'] * $factor;
            $itemProtein = $item['protein'] * $factor;
            $itemCarbs = $item['carbs'] * $factor;
            $itemFats = $item['fats'] * $factor;

            $kcal += $itemKcal;
            $protein += $itemProtein;
            $carbs += $itemCarbs;
            $fats += $itemFats;

            $nameAr = $item['name_ar'] ?? $key;
            $nameEn = $item['name_en'] ?? $key;
            $linesAr[] = sprintf('%s — %sg', $nameAr, rtrim(rtrim(number_format($grams, 1, '.', ''), '0'), '.'));
            $linesEn[] = sprintf('%s — %sg', $nameEn, rtrim(rtrim(number_format($grams, 1, '.', ''), '0'), '.'));

            $breakdown[] = [
                'key' => $key,
                'grams' => $grams,
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'calories' => round($itemKcal, 1),
                'protein' => round($itemProtein, 1),
                'carbs' => round($itemCarbs, 1),
                'fats' => round($itemFats, 1),
            ];
        }

        return [
            'calories' => (int) round($kcal),
            'protein' => (int) round($protein),
            'carbs' => (int) round($carbs),
            'fats' => (int) round($fats),
            'lines_ar' => $linesAr,
            'lines_en' => $linesEn,
            'breakdown' => $breakdown,
        ];
    }
}
