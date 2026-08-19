<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CheckInResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $photoUrl = $this->progress_photo_path
            ? Storage::disk('public')->url($this->progress_photo_path)
            : null;

        return [
            'id' => $this->id,
            'checked_in_at' => optional($this->checked_in_at)->toIso8601String(),
            'date' => optional($this->checked_in_at)->toDateString(),
            'weight' => $this->weight !== null ? (float) $this->weight : null,
            'body_fat_percentage' => $this->body_fat_percentage !== null ? (float) $this->body_fat_percentage : null,
            'fat_percentage' => $this->body_fat_percentage !== null ? (float) $this->body_fat_percentage : null,
            'waist_cm' => $this->waist_cm !== null ? (float) $this->waist_cm : null,
            'waist_circumference' => $this->waist_cm !== null ? (float) $this->waist_cm : null,
            'chest_cm' => $this->chest_cm !== null ? (float) $this->chest_cm : null,
            'hips_cm' => $this->hips_cm !== null ? (float) $this->hips_cm : null,
            'arm_cm' => $this->arm_cm !== null ? (float) $this->arm_cm : null,
            'thigh_cm' => $this->thigh_cm !== null ? (float) $this->thigh_cm : null,
            'energy_level' => $this->energy_level,
            'training_adherence' => $this->training_adherence,
            'workout_commitment' => $this->training_adherence,
            'nutrition_adherence' => $this->nutrition_adherence,
            'nutrition_commitment' => $this->nutrition_adherence,
            'average_adherence' => $this->average_adherence,
            'notes' => $this->notes,
            'coach_feedback' => $this->coach_feedback,
            'next_steps' => $this->next_steps,
            'progress_photo_url' => $photoUrl,
            'photo_url' => $photoUrl,
            'labels' => [
                'energy_level' => $this->scaleLabel($this->energy_level),
                'training_adherence' => $this->scaleLabel($this->training_adherence),
                'nutrition_adherence' => $this->scaleLabel($this->nutrition_adherence),
            ],
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }

    private function scaleLabel(?int $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $labels = [
            1 => '1 — ضعيف جداً',
            2 => '2 — ضعيف',
            3 => '3 — أقل من المتوسط',
            4 => '4 — مقبول',
            5 => '5 — متوسط',
            6 => '6 — جيد',
            7 => '7 — جيد جداً',
            8 => '8 — ممتاز',
            9 => '9 — رائع',
            10 => '10 — استثنائي',
        ];

        return $labels[$value] ?? (string) $value;
    }
}
