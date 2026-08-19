<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutTodayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $schedule = $this['schedule'];
        $workout = $this['workout'];
        $difficulty = $workout?->difficulty;
        $difficultyLabel = $workout?->difficulty_name ?? $difficulty;
        $duration = (int) ($workout?->duration ?? 0);
        $exerciseCount = (int) ($workout?->exercise_count ?? 0);
        $equipment = $workout?->equipment_label ?: 'معدات';
        $sessionNumber = (int) ($this['session_number'] ?? $schedule->session_number);
        $programWeek = (int) ($this['program_week'] ?? $schedule->week_number);
        $videoSeconds = (int) ($workout?->video_duration_seconds ?? 0);

        $workout?->loadMissing('exercises');
        $resolved = $workout?->resolvedMedia() ?? [
            'video_url' => null,
            'image' => null,
            'image_url' => null,
            'media_type' => null,
            'media_url' => null,
            'media_source' => null,
            'media_attribution' => null,
        ];

        $videoUrl = $resolved['video_url'];
        $image = $resolved['image'];
        $imageUrl = $resolved['image_url'];
        $mediaType = $resolved['media_type'];
        $mediaUrl = $resolved['media_url'];
        $mediaSource = $resolved['media_source'];

        $metaParts = [];
        if ($duration > 0) {
            $metaParts[] = "{$duration} دقيقة";
        }
        if ($difficultyLabel) {
            $metaParts[] = $difficultyLabel;
        }

        $volumeParts = [];
        if ($exerciseCount > 0) {
            $volumeParts[] = "{$exerciseCount} تمارين";
        }
        if ($equipment) {
            $volumeParts[] = $equipment;
        }

        $exerciseItems = collect($workout?->exercises ?? [])->map(function ($exercise) {
            $attribution = $exercise->attributionPayload();

            return [
                'id' => $exercise->id,
                'external_id' => $exercise->external_id,
                'source' => $exercise->source,
                'name' => $exercise->localized_name,
                'name_en' => $exercise->name,
                'description' => $exercise->localized_description,
                'instructions' => $exercise->localized_instructions,
                'body_part' => $exercise->body_part,
                'body_part_label' => $exercise->localized_body_part,
                'equipment' => $exercise->equipment,
                'equipment_label' => $exercise->localized_equipment,
                'difficulty' => $exercise->difficulty,
                'difficulty_label' => $exercise->difficulty_name,
                'image_url' => $exercise->image_url,
                'image_start_url' => $exercise->image_start_url,
                'image_peak_url' => $exercise->image_peak_url,
                'video_url' => $exercise->video_url,
                'sets' => $exercise->pivot?->sets,
                'reps' => $exercise->pivot?->reps,
                'rest_seconds' => $exercise->pivot?->rest_seconds,
                'coach_cue' => $exercise->pivot?->coach_cue,
                'sort_order' => $exercise->pivot?->sort_order,
                'attribution' => $attribution,
            ];
        })->values()->all();

        $mediaAttribution = $resolved['media_attribution'];
        if (! $mediaAttribution) {
            foreach ($exerciseItems as $item) {
                if (! empty($item['attribution']['required']) && ! empty($item['image_url'])) {
                    $mediaAttribution = $item['attribution'];
                    break;
                }
            }
        }

        return [
            'workout_schedule_id' => $schedule->id,
            'workout_id' => $workout?->id,
            'card_title' => 'تمرين اليوم',
            'name' => $workout?->name,
            'description' => $workout?->description,
            'about_title' => 'نبذة عن التمرين',
            'coach_notes_title' => 'ملاحظات المدرب',
            'coach_notes' => array_values($workout?->coach_notes ?? []),
            'duration' => $duration,
            'duration_label' => $duration > 0 ? "{$duration} دقيقة" : null,
            'difficulty' => $difficulty,
            'difficulty_label' => $difficultyLabel,
            'exercise_count' => $exerciseCount ?: count($exerciseItems),
            'equipment_label' => $equipment,
            'meta_line' => implode(' • ', $metaParts),
            'volume_line' => implode(' • ', $volumeParts),
            'video_url' => $videoUrl,
            'video_duration_seconds' => $videoSeconds > 0 ? $videoSeconds : null,
            'video_duration_label' => $videoSeconds > 0 ? $this->formatDuration($videoSeconds) : null,
            'image' => $image,
            'image_url' => $imageUrl,
            'media_type' => $mediaType,
            'media_url' => $mediaUrl,
            'media_source' => $mediaSource,
            'exercises' => $exerciseItems,
            'content_locale' => app(\App\Services\ExerciseTranslationService::class)->resolveLocale(),
            'media_attribution' => $mediaAttribution,
            'scheduled_on' => $this['scheduled_on'],
            'scheduled_on_label' => $this->formatDateLabel($this['scheduled_on'] ?? null),
            'session_number' => $sessionNumber,
            'session_label' => $this['session_label'] ?? "الجلسة {$sessionNumber}",
            'session_badge' => "الجلسة {$sessionNumber}",
            'program_week' => $programWeek,
            'week_badge' => "الأسبوع {$programWeek}",
            'notes' => $schedule->notes,
            'status' => $this['status'],
            'is_completed' => (bool) $this['is_completed'],
            'is_skipped' => (bool) $this['is_skipped'],
            'completed_at' => optional($this['log']?->completed_at)->toIso8601String(),
            'actions' => [
                'complete_url' => url("/api/v1/workouts/{$schedule->id}/complete"),
                'skip_url' => url("/api/v1/workouts/{$schedule->id}/skip"),
                'detail_url' => url("/api/v1/workouts/{$schedule->id}"),
                'can_complete' => ! (bool) $this['is_completed'] && ! (bool) $this['is_skipped'],
                'can_skip' => ! (bool) $this['is_completed'] && ! (bool) $this['is_skipped'],
                'start_label' => (bool) $this['is_completed'] ? 'مكتمل' : 'ابدأ التمرين',
                'skip_label' => 'تخطي',
                'watch_video_label' => $mediaType === 'animated_image' ? 'عرض التمرين' : 'مشاهدة الفيديو',
            ],
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remain = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remain);
    }

    private function formatDateLabel(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    }
}
