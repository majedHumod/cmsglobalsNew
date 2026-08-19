<?php

namespace App\Services;

use App\Models\Exercise;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ExerciseTranslationService
{
    /**
     * @return list<string>
     */
    public function supportedLocales(): array
    {
        return array_values(config('exercise_localization.supported_locales', ['ar', 'en']));
    }

    public function resolveLocale(?string $locale = null): string
    {
        $candidate = $locale
            ?? config('exercise_localization.runtime_locale')
            ?? config('exercise_localization.default_locale', 'ar');

        $candidate = strtolower((string) $candidate);
        $candidate = strtok($candidate, '_-') ?: $candidate;

        $supported = $this->supportedLocales();
        if (in_array($candidate, $supported, true)) {
            return $candidate;
        }

        return (string) config('exercise_localization.fallback_locale', 'en');
    }

    /**
     * @return mixed
     */
    public function localizedValue(Exercise $exercise, string $field, ?string $locale = null)
    {
        $locale = $this->resolveLocale($locale);
        $fallback = (string) config('exercise_localization.fallback_locale', 'en');
        $translations = $exercise->translations ?? [];
        $localized = $translations[$field][$locale] ?? null;

        if ($this->hasContent($localized)) {
            return $localized;
        }

        if ($locale !== $fallback) {
            $fallbackValue = $translations[$field][$fallback] ?? null;
            if ($this->hasContent($fallbackValue)) {
                return $fallbackValue;
            }
        }

        return $exercise->getAttribute($field);
    }

    /**
     * @return array{updated:int,skipped:int,missing:int}
     */
    public function applyOverlay(string $locale, bool $force = false): array
    {
        $locale = $this->resolveLocale($locale);
        $overlay = $this->loadOverlay($locale);
        $exercises = $overlay['exercises'] ?? [];

        if (! is_array($exercises) || $exercises === []) {
            throw new RuntimeException("Overlay for [{$locale}] has no exercises.");
        }

        $updated = 0;
        $skipped = 0;
        $missing = 0;

        foreach ($exercises as $externalId => $payload) {
            if (! is_array($payload)) {
                $skipped++;
                continue;
            }

            $exercise = Exercise::query()->where('external_id', (string) $externalId)->first();
            if (! $exercise) {
                $missing++;
                continue;
            }

            $translations = $exercise->translations ?? [];
            $changed = false;

            foreach (config('exercise_localization.translatable_fields', ['name', 'description', 'instructions', 'tips']) as $field) {
                if (! array_key_exists($field, $payload) || ! $this->hasContent($payload[$field])) {
                    continue;
                }

                $current = $translations[$field][$locale] ?? null;
                if (! $force && $this->hasContent($current)) {
                    continue;
                }

                if ($current === $payload[$field]) {
                    continue;
                }

                $translations[$field] ??= [];
                $translations[$field][$locale] = $payload[$field];
                $changed = true;
            }

            if (! $changed) {
                $skipped++;
                continue;
            }

            $exercise->translations = $translations;
            $exercise->save();
            $updated++;
        }

        return compact('updated', 'skipped', 'missing');
    }

    /**
     * Merge imported RepDB translations while preserving overlay locales.
     *
     * @param  array<string, mixed>|null  $existing
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    public function mergePreservingOverlays(?array $existing, array $incoming): array
    {
        $merged = $incoming;
        $existing = $existing ?? [];
        $overlayLocales = config('exercise_localization.overlay_locales', ['ar']);

        foreach (config('exercise_localization.translatable_fields', []) as $field) {
            $merged[$field] ??= [];
            if (! is_array($merged[$field])) {
                $merged[$field] = [];
            }

            foreach ($overlayLocales as $locale) {
                $value = $existing[$field][$locale] ?? null;
                if ($this->hasContent($value)) {
                    $merged[$field][$locale] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    public function loadOverlay(string $locale): array
    {
        $path = $this->overlayPath($locale);
        if (! File::exists($path)) {
            throw new RuntimeException("Translation overlay not found: {$path}");
        }

        $payload = json_decode(File::get($path), true);
        if (! is_array($payload)) {
            throw new RuntimeException("Invalid translation overlay JSON: {$path}");
        }

        return $payload;
    }

    public function overlayPath(string $locale): string
    {
        $base = rtrim((string) config('exercise_localization.overlay_path'), DIRECTORY_SEPARATOR);

        return $base.DIRECTORY_SEPARATOR.strtolower($locale).'.json';
    }

    /**
     * Map a UI label (Arabic or English key) back to the stored body_part/equipment value.
     */
    public function resolveLabelKey(string $group, string $input, ?string $locale = null): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        $normalized = mb_strtolower($input);
        $labels = $this->labels($group, $locale);

        if (array_key_exists($normalized, $labels) || array_key_exists($input, $labels)) {
            return $input;
        }

        foreach ($labels as $key => $label) {
            if (mb_strtolower((string) $label) === $normalized || (string) $label === $input) {
                return (string) $key;
            }
        }

        return $input;
    }

    /**
     * @return array<string, string>
     */
    public function labels(string $group, ?string $locale = null): array
    {
        $locale = $this->resolveLocale($locale);

        try {
            $overlay = $this->loadOverlay($locale);
        } catch (RuntimeException) {
            return [];
        }

        $labels = $overlay['labels'][$group] ?? [];

        return is_array($labels) ? $labels : [];
    }

    public function label(string $group, ?string $key, ?string $locale = null): ?string
    {
        if ($key === null || $key === '') {
            return $key;
        }

        $labels = $this->labels($group, $locale);

        return $labels[$key] ?? $key;
    }

    private function hasContent(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }
}
