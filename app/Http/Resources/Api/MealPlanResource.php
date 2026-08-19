<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source,
            'external_id' => $this->external_id,
            'name' => $this->localized_name,
            'name_ar' => $this->name,
            'name_en' => $this->name_en,
            'description' => $this->localized_description,
            'description_ar' => $this->description,
            'description_en' => $this->description_en,
            'meal_type' => $this->meal_type,
            'meal_type_label' => $this->meal_type_name,
            'calories' => $this->calories,
            'protein' => $this->protein,
            'carbs' => $this->carbs,
            'fats' => $this->fats,
            'macros_label' => $this->formatMacrosLabel(),
            'nutrition_is_estimated' => (bool) $this->nutrition_is_estimated,
            'nutrition_source' => $this->nutrition_source,
            'nutrition_disclaimer' => $this->nutrition_is_estimated ? $this->nutrition_disclaimer : null,
            'ingredients' => $this->localized_ingredients,
            'ingredients_ar' => $this->ingredients,
            'ingredients_en' => $this->ingredients_en,
            'instructions' => $this->localized_instructions,
            'instructions_ar' => $this->instructions,
            'instructions_en' => $this->instructions_en,
            'prep_time' => $this->prep_time,
            'cook_time' => $this->cook_time,
            'servings' => $this->servings,
            'difficulty' => $this->difficulty,
            'difficulty_label' => $this->difficulty_name,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'image_attribution' => $this->image_attribution,
            'image_attribution_url' => $this->image_attribution_url,
            'is_active' => (bool) $this->is_active,
        ];
    }

    private function formatMacrosLabel(): ?string
    {
        if ($this->calories === null && $this->protein === null) {
            return null;
        }

        $parts = [];
        if ($this->calories !== null) {
            $parts[] = $this->calories.' سعرة';
        }
        if ($this->protein !== null) {
            $parts[] = 'بروتين '.$this->protein.'غ';
        }
        if ($this->carbs !== null) {
            $parts[] = 'كارب '.$this->carbs.'غ';
        }
        if ($this->fats !== null) {
            $parts[] = 'دهون '.$this->fats.'غ';
        }

        $label = implode(' • ', $parts);
        if ($this->nutrition_is_estimated) {
            $label .= ' • تقديري';
        }

        return $label;
    }
}
