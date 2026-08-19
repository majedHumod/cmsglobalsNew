<?php

namespace App\Services;

use App\Models\Exercise;
use App\Services\ExerciseTranslationService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class RepdbExerciseImportService
{
    public const STORAGE_DIR = 'exercise-library/repdb';

    public const RELEASE_ZIP_URL = 'https://github.com/sergei-argutin/exercise-dataset/releases/latest/download/repdb-free.zip';

    /**
     * Prepare source directory (local path or downloaded zip) and return absolute path to extracted dataset root.
     */
    public function resolveDatasetRoot(?string $localPath = null): string
    {
        if ($localPath) {
            $absolute = $this->normalizePath($localPath);
            if (! is_dir($absolute) && ! is_file($absolute)) {
                throw new RuntimeException("Path not found: {$localPath}");
            }

            if (is_file($absolute) && Str::endsWith(strtolower($absolute), '.zip')) {
                return $this->extractZip($absolute);
            }

            return $this->findDatasetRoot($absolute);
        }

        $zipPath = storage_path('app/tmp/repdb-free.zip');
        File::ensureDirectoryExists(dirname($zipPath));

        $response = Http::timeout(180)->get(self::RELEASE_ZIP_URL);
        if (! $response->successful()) {
            throw new RuntimeException('Failed to download RepDB free zip (HTTP '.$response->status().').');
        }

        File::put($zipPath, $response->body());

        if (! file_exists($zipPath) || filesize($zipPath) < 1000) {
            throw new RuntimeException('Downloaded RepDB zip is empty or invalid.');
        }

        return $this->extractZip($zipPath);
    }

    /**
     * Copy flat images once to shared public storage. Skips upgrade/premium sample folders.
     *
     * @return int Number of image files copied or already present
     */
    public function syncImages(string $datasetRoot, bool $force = false): int
    {
        $imagesDir = $this->locateImagesFlatDir($datasetRoot);
        if (! $imagesDir) {
            throw new RuntimeException('Could not find images/flat directory in RepDB dataset.');
        }

        Storage::disk('public')->makeDirectory(self::STORAGE_DIR);

        $copied = 0;
        foreach (File::files($imagesDir) as $file) {
            $name = $file->getFilename();
            if (! Str::endsWith(strtolower($name), '.webp')) {
                continue;
            }

            $relative = self::STORAGE_DIR.'/'.$name;
            if ($force || ! Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->put($relative, File::get($file->getPathname()));
            }
            $copied++;
        }

        return $copied;
    }

    /**
     * Upsert exercises into the current tenant database connection.
     *
     * @return array{created:int,updated:int,skipped:int}
     */
    public function importExercises(string $datasetRoot, bool $force = false): array
    {
        $jsonPath = $this->locateExercisesJson($datasetRoot);
        if (! $jsonPath) {
            throw new RuntimeException('Could not find exercises.json in RepDB dataset.');
        }

        $payload = json_decode(File::get($jsonPath), true);
        if (! is_array($payload)) {
            throw new RuntimeException('Invalid exercises.json payload.');
        }

        $items = $payload['exercises'] ?? $payload;
        if (! is_array($items)) {
            throw new RuntimeException('exercises.json does not contain an exercises list.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                $skipped++;
                continue;
            }

            $externalId = (string) ($item['id'] ?? '');
            if ($externalId === '') {
                $skipped++;
                continue;
            }

            $attributes = $this->mapExerciseAttributes($item);
            $existing = Exercise::query()->where('external_id', $externalId)->first();

            if ($existing) {
                if ($force) {
                    $attributes['translations'] = app(ExerciseTranslationService::class)
                        ->mergePreservingOverlays($existing->translations, $attributes['translations'] ?? []);
                    $existing->update($attributes);
                    $updated++;
                } else {
                    $skipped++;
                }
                continue;
            }

            Exercise::query()->create($attributes);
            $created++;
        }

        return compact('created', 'updated', 'skipped');
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function mapExerciseAttributes(array $item): array
    {
        $externalId = (string) $item['id'];
        [$startPath, $peakPath] = $this->resolveImagePaths($externalId, $item['images']['flat'] ?? null);

        return [
            'external_id' => $externalId,
            'source' => Exercise::SOURCE_REPDB,
            'name' => (string) ($item['name_en'] ?? $item['name'] ?? $externalId),
            'description' => $item['description_en'] ?? $item['description'] ?? null,
            'instructions' => $item['instructions_en'] ?? $item['instructions'] ?? null,
            'translations' => [
                'name' => [
                    'en' => $item['name_en'] ?? null,
                    'de' => $item['name_de'] ?? null,
                    'es' => $item['name_es'] ?? null,
                ],
                'description' => [
                    'en' => $item['description_en'] ?? null,
                    'de' => $item['description_de'] ?? null,
                    'es' => $item['description_es'] ?? null,
                ],
                'instructions' => [
                    'en' => $item['instructions_en'] ?? null,
                    'de' => $item['instructions_de'] ?? null,
                    'es' => $item['instructions_es'] ?? null,
                ],
                'tips' => [
                    'en' => $item['tips_en'] ?? null,
                    'de' => $item['tips_de'] ?? null,
                    'es' => $item['tips_es'] ?? null,
                ],
            ],
            'category' => $item['category'] ?? null,
            'difficulty' => $item['difficulty'] ?? null,
            'equipment' => $item['equipment'] ?? null,
            'body_part' => $item['body_part'] ?? null,
            'primary_muscles' => $item['primary_muscles'] ?? [],
            'secondary_muscles' => $item['secondary_muscles'] ?? [],
            'tags' => $item['tags'] ?? [],
            'met' => isset($item['met']) ? (float) $item['met'] : null,
            'image_start_path' => $startPath,
            'image_peak_path' => $peakPath,
            'attribution_required' => true,
            'attribution_text' => Exercise::DEFAULT_ATTRIBUTION_TEXT,
            'attribution_url' => Exercise::DEFAULT_ATTRIBUTION_URL,
            'status' => true,
        ];
    }

    /**
     * Supports both release-zip pose lists ["start","peak"] and repo path maps {start: "...", peak: "..."}.
     *
     * @return array{0:?string,1:?string}
     */
    private function resolveImagePaths(string $externalId, mixed $flat): array
    {
        $startPath = null;
        $peakPath = null;

        if (is_array($flat) && array_is_list($flat)) {
            $poses = $flat;
            if (in_array('start', $poses, true)) {
                $startPath = self::STORAGE_DIR.'/'.$externalId.'-start.webp';
            }
            if (in_array('peak', $poses, true)) {
                $peakPath = self::STORAGE_DIR.'/'.$externalId.'-peak.webp';
            }
            if (in_array('main', $poses, true)) {
                $startPath = $startPath ?: self::STORAGE_DIR.'/'.$externalId.'-main.webp';
            }

            return [$startPath, $peakPath];
        }

        if (is_array($flat)) {
            $startRel = $flat['start'] ?? $flat['main'] ?? null;
            $peakRel = $flat['peak'] ?? null;

            return [
                $this->storagePathFromDatasetRelative($startRel) ?? (
                    isset($flat['main']) ? self::STORAGE_DIR.'/'.$externalId.'-main.webp' : null
                ),
                $this->storagePathFromDatasetRelative($peakRel),
            ];
        }

        return [null, null];
    }

    private function storagePathFromDatasetRelative(?string $relative): ?string
    {
        if (! $relative) {
            return null;
        }

        // Skip paid preview / sample folders entirely.
        $normalized = str_replace('\\', '/', $relative);
        if (
            str_contains($normalized, 'upgrade-samples/')
            || str_contains($normalized, 'premium-samples/')
        ) {
            return null;
        }

        $filename = basename($normalized);

        return self::STORAGE_DIR.'/'.$filename;
    }

    private function extractZip(string $zipPath): string
    {
        $target = storage_path('app/tmp/repdb-extract-'.md5($zipPath));
        if (is_dir($target)) {
            File::deleteDirectory($target);
        }
        File::ensureDirectoryExists($target);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException("Unable to open zip: {$zipPath}");
        }
        $zip->extractTo($target);
        $zip->close();

        return $this->findDatasetRoot($target);
    }

    private function findDatasetRoot(string $path): string
    {
        foreach (['free.json', 'exercises.json', 'free.en.json'] as $candidate) {
            if (File::exists($path.DIRECTORY_SEPARATOR.$candidate)) {
                return $path;
            }
        }

        // Zip may unwrap into a single top-level folder.
        $children = File::directories($path);
        foreach ($children as $child) {
            foreach (['free.json', 'exercises.json', 'free.en.json'] as $candidate) {
                if (File::exists($child.DIRECTORY_SEPARATOR.$candidate)) {
                    return $child;
                }
            }
            $nested = $this->locateExercisesJson($child);
            if ($nested) {
                return dirname($nested);
            }
        }

        $found = $this->locateExercisesJson($path);
        if ($found) {
            return dirname($found);
        }

        return $path;
    }

    private function locateExercisesJson(string $root): ?string
    {
        foreach (['free.json', 'exercises.json', 'free.en.json'] as $candidate) {
            $direct = $root.DIRECTORY_SEPARATOR.$candidate;
            if (File::exists($direct)) {
                return $direct;
            }
        }

        foreach (File::allFiles($root) as $file) {
            $name = $file->getFilename();
            if (! in_array($name, ['free.json', 'exercises.json', 'free.en.json'], true)) {
                continue;
            }

            $pathname = $file->getPathname();
            if (
                str_contains($pathname, 'upgrade-samples')
                || str_contains($pathname, 'premium-samples')
            ) {
                continue;
            }

            return $pathname;
        }

        return null;
    }

    private function locateImagesFlatDir(string $root): ?string
    {
        $direct = $root.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'flat';
        if (is_dir($direct)) {
            return $direct;
        }

        foreach (File::directories($root) as $dir) {
            $candidate = $dir.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'flat';
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        if (Str::startsWith($path, ['/', '\\']) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            return $path;
        }

        return base_path($path);
    }
}
