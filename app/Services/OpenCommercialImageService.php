<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Search commercially-usable open stock photos and download them locally.
 *
 * Providers:
 * - Openverse (default, no key) with license_type=commercial
 * - Pexels (optional API key) — free commercial use
 * - Unsplash (optional API key) — free commercial use
 */
class OpenCommercialImageService
{
    public const STORAGE_DIR = 'meal-plans/reviewed';

    /**
     * @return list<array{
     *   id: string,
     *   provider: string,
     *   thumb_url: string,
     *   full_url: string,
     *   photographer: ?string,
     *   attribution: string,
     *   attribution_url: ?string,
     *   license: string
     * }>
     */
    public function search(string $query, string $provider = 'openverse', int $perPage = 12): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        return match ($provider) {
            'pexels' => $this->searchPexels($query, $perPage),
            'unsplash' => $this->searchUnsplash($query, $perPage),
            default => $this->searchOpenverse($query, $perPage),
        };
    }

    /**
     * @return array{path: string, attribution: string, attribution_url: ?string}|null
     */
    public function downloadAndStore(string $imageUrl, string $filenameStem, string $attribution, ?string $attributionUrl = null): ?array
    {
        $imageUrl = trim($imageUrl);
        if ($imageUrl === '' || ! Str::startsWith($imageUrl, ['http://', 'https://'])) {
            return null;
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders(['User-Agent' => 'cmsglobals-meal-library/1.2'])
                ->withOptions(['allow_redirects' => true])
                ->get($imageUrl);

            if (! $response->successful() || strlen($response->body()) < 1500) {
                return null;
            }

            $body = $response->body();
            if (str_starts_with(ltrim($body), '<')) {
                return null;
            }

            $ext = 'jpg';
            $contentType = strtolower((string) $response->header('Content-Type'));
            if (str_contains($contentType, 'png')) {
                $ext = 'png';
            } elseif (str_contains($contentType, 'webp')) {
                $ext = 'webp';
            }

            $relative = self::STORAGE_DIR.'/'.Str::slug($filenameStem).'-'.Str::random(6).'.'.$ext;
            Storage::disk('public')->makeDirectory(self::STORAGE_DIR);
            Storage::disk('public')->put($relative, $body);

            return [
                'path' => $relative,
                'attribution' => $attribution,
                'attribution_url' => $attributionUrl,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return list<array>
     */
    private function searchOpenverse(string $query, int $perPage): array
    {
        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => 'cmsglobals-meal-library/1.2'])
            ->get('https://api.openverse.org/v1/images/', [
                'q' => $query,
                'page_size' => min(20, max(1, $perPage)),
                'license_type' => 'commercial',
                'category' => 'photograph',
            ]);

        if (! $response->successful()) {
            return [];
        }

        $results = [];
        foreach (($response->json('results') ?? []) as $row) {
            $thumb = $row['thumbnail'] ?? $row['url'] ?? null;
            $full = $row['url'] ?? null;
            if (! $thumb || ! $full) {
                continue;
            }

            $creator = $row['creator'] ?? null;
            $provider = $row['provider'] ?? 'Openverse';
            $license = strtoupper((string) ($row['license'] ?? 'CC'));
            $foreign = $row['foreign_landing_url'] ?? ($row['license_url'] ?? 'https://openverse.org');

            $results[] = [
                'id' => 'openverse:'.($row['id'] ?? md5($full)),
                'provider' => 'openverse',
                'thumb_url' => $thumb,
                'full_url' => $full,
                'photographer' => $creator,
                'attribution' => trim(($creator ? $creator.' / ' : '').$provider.' ('.$license.')'),
                'attribution_url' => $foreign,
                'license' => $license.' — commercial use allowed via Openverse filter',
            ];
        }

        return $results;
    }

    /**
     * @return list<array>
     */
    private function searchPexels(string $query, int $perPage): array
    {
        $apiKey = (string) config('meal_library.pexels.api_key');
        if ($apiKey === '') {
            return [];
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => $apiKey,
                'User-Agent' => 'cmsglobals-meal-library/1.2',
            ])
            ->get('https://api.pexels.com/v1/search', [
                'query' => $query,
                'per_page' => min(20, max(1, $perPage)),
                'orientation' => 'landscape',
            ]);

        if (! $response->successful()) {
            return [];
        }

        $results = [];
        foreach (($response->json('photos') ?? []) as $row) {
            $full = $row['src']['large2x'] ?? $row['src']['large'] ?? $row['src']['original'] ?? null;
            $thumb = $row['src']['medium'] ?? $row['src']['small'] ?? $full;
            if (! $full || ! $thumb) {
                continue;
            }

            $photographer = $row['photographer'] ?? 'Pexels';
            $page = $row['url'] ?? 'https://www.pexels.com';

            $results[] = [
                'id' => 'pexels:'.($row['id'] ?? md5($full)),
                'provider' => 'pexels',
                'thumb_url' => $thumb,
                'full_url' => $full,
                'photographer' => $photographer,
                'attribution' => 'Photo by '.$photographer.' on Pexels',
                'attribution_url' => $page,
                'license' => 'Pexels License — free for commercial use',
            ];
        }

        return $results;
    }

    /**
     * @return list<array>
     */
    private function searchUnsplash(string $query, int $perPage): array
    {
        $accessKey = (string) config('meal_library.unsplash.access_key');
        if ($accessKey === '') {
            return [];
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Client-ID '.$accessKey,
                'User-Agent' => 'cmsglobals-meal-library/1.2',
            ])
            ->get('https://api.unsplash.com/search/photos', [
                'query' => $query,
                'per_page' => min(20, max(1, $perPage)),
                'orientation' => 'landscape',
                'content_filter' => 'high',
            ]);

        if (! $response->successful()) {
            return [];
        }

        $results = [];
        foreach (($response->json('results') ?? []) as $row) {
            $full = $row['urls']['regular'] ?? $row['urls']['full'] ?? null;
            $thumb = $row['urls']['small'] ?? $row['urls']['thumb'] ?? $full;
            if (! $full || ! $thumb) {
                continue;
            }

            $name = $row['user']['name'] ?? 'Unsplash';
            $page = $row['links']['html'] ?? 'https://unsplash.com';

            $results[] = [
                'id' => 'unsplash:'.($row['id'] ?? md5($full)),
                'provider' => 'unsplash',
                'thumb_url' => $thumb,
                'full_url' => $full,
                'photographer' => $name,
                'attribution' => 'Photo by '.$name.' on Unsplash',
                'attribution_url' => $page,
                'license' => 'Unsplash License — free for commercial use',
            ];
        }

        return $results;
    }

    /**
     * @return array{openverse: bool, pexels: bool, unsplash: bool}
     */
    public function availableProviders(): array
    {
        return [
            'openverse' => true,
            'pexels' => filled(config('meal_library.pexels.api_key')),
            'unsplash' => filled(config('meal_library.unsplash.access_key')),
        ];
    }
}
