<?php

namespace App\Services\Billing;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaylinkService
{
    public function createInvoice(array $payload): array
    {
        $response = $this->request()
            ->post('/api/addInvoice', $payload);

        return $this->handleResponse($response);
    }

    public function getInvoice(string $transactionNo): array
    {
        $response = $this->request()
            ->get('/api/getInvoice/' . urlencode($transactionNo));

        return $this->handleResponse($response);
    }

    public function normalizeInvoiceStatus(?string $status): string
    {
        return Str::upper((string) $status);
    }

    public function isPaid(array $invoice): bool
    {
        return $this->normalizeInvoiceStatus(Arr::get($invoice, 'orderStatus')) === 'PAID';
    }

    public function authenticate(): string
    {
        $cacheKey = 'paylink:id_token';

        return Cache::remember($cacheKey, $this->tokenTtlSeconds(), function (): string {
            $response = Http::baseUrl($this->baseUrl())
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post('/api/auth', [
                    'apiId' => config('services.paylink.api_id'),
                    'secretKey' => config('services.paylink.secret_key'),
                    'persistToken' => config('services.paylink.persist_token', true),
                ]);

            if ($response->failed()) {
                throw new \RuntimeException($this->extractErrorMessage($response));
            }

            $token = (string) $response->json('id_token');

            if ($token === '') {
                throw new \RuntimeException('Paylink authentication succeeded but no token was returned.');
            }

            return $token;
        });
    }

    private function request()
    {
        return Http::baseUrl($this->baseUrl())
            ->withToken($this->authenticate())
            ->acceptJson()
            ->asJson()
            ->timeout(30);
    }

    private function handleResponse(Response $response): array
    {
        if ($response->failed()) {
            throw new \RuntimeException($this->extractErrorMessage($response));
        }

        $payload = $response->json();

        if (!is_array($payload)) {
            throw new \RuntimeException('Paylink returned an unexpected response.');
        }

        if (array_key_exists('success', $payload) && !$payload['success']) {
            $message = $payload['paymentErrors'] ?? $payload['detail'] ?? 'Paylink request failed.';
            if (is_array($message)) {
                $message = collect($message)
                    ->map(fn ($item) => is_array($item) ? ($item['errorMessage'] ?? $item['errorTitle'] ?? json_encode($item)) : (string) $item)
                    ->implode(' | ');
            }

            throw new \RuntimeException((string) $message);
        }

        return $payload;
    }

    private function extractErrorMessage(Response $response): string
    {
        $payload = $response->json();

        if (is_array($payload)) {
            foreach (['detail', 'title', 'message'] as $key) {
                if (!empty($payload[$key])) {
                    return (string) $payload[$key];
                }
            }
        }

        return 'Paylink request failed with HTTP ' . $response->status() . '.';
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.paylink.base_url'), '/');
    }

    private function tokenTtlSeconds(): int
    {
        return config('services.paylink.persist_token', true) ? (29 * 60 * 60) : (25 * 60);
    }
}
