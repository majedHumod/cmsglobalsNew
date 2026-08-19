<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $labels = \App\Models\MealLog::mealSlotLabels();
        $score = (int) ($this->adherence_score ?? 0);

        return [
            'id' => $this->id,
            'logged_on' => optional($this->logged_on)->toDateString(),
            'meal_slot' => $this->meal_slot,
            'meal_slot_label' => $labels[$this->meal_slot] ?? $this->meal_slot,
            'meal_plan_id' => $this->meal_plan_id,
            'meal_plan_name' => $this->mealPlan?->localized_name,
            'meal_plan_name_en' => $this->mealPlan?->name_en,
            'nutrition_is_estimated' => (bool) ($this->mealPlan?->nutrition_is_estimated),
            'adherence_score' => $score,
            'adherence_label' => "التزام: {$score}/10",
            'title' => ($labels[$this->meal_slot] ?? $this->meal_slot)
                .($this->mealPlan?->localized_name ? ' — '.$this->mealPlan->localized_name : ''),
            'notes' => $this->notes,
        ];
    }
}
