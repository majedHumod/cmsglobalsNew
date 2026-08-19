<?php

namespace App\Services;

use App\Models\MessageTemplate;
use App\Models\User;
use App\Services\TenantService;

class MessageTemplateService
{
    /**
     * @return array<string, array{label_ar: string, example: string}>
     */
    public function availableVariables(): array
    {
        return [
            'client_name' => ['label_ar' => 'اسم العميل', 'example' => 'أحمد'],
            'name' => ['label_ar' => 'الاسم (مرادف)', 'example' => 'أحمد'],
            'coach_name' => ['label_ar' => 'اسم المدرب', 'example' => 'سلمان'],
            'org_name' => ['label_ar' => 'اسم المنظمة', 'example' => 'وقت اللياقة'],
            'date' => ['label_ar' => 'تاريخ اليوم', 'example' => now()->toDateString()],
            'membership_expires' => ['label_ar' => 'تاريخ انتهاء العضوية', 'example' => '2026-09-01'],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function render(string $body, array $context = []): string
    {
        $replacements = [];
        foreach ($this->availableVariables() as $key => $meta) {
            $value = $context[$key] ?? '';
            if ($value === null || $value === '') {
                $value = '';
            }
            $replacements['{{'.$key.'}}'] = (string) $value;
            $replacements['{'.$key.'}'] = (string) $value;
        }

        return strtr($body, $replacements);
    }

    /**
     * @return list<string>
     */
    public function detectVariables(string $body): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $body, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public function contextFor(?User $recipient = null, ?User $sender = null): array
    {
        $tenant = TenantService::getTenant();

        return [
            'client_name' => $recipient?->name ?? '',
            'name' => $recipient?->name ?? '',
            'coach_name' => $sender?->name ?? '',
            'org_name' => $tenant?->name ?? (string) config('app.name'),
            'date' => now()->toDateString(),
            'membership_expires' => optional($recipient?->membership_expires_at)->toDateString() ?? '',
        ];
    }

    public function renderTemplate(MessageTemplate $template, ?User $recipient = null, ?User $sender = null, array $extra = []): string
    {
        return $this->render(
            $template->body,
            array_merge($this->contextFor($recipient, $sender), $extra)
        );
    }

    public function present(MessageTemplate $template): array
    {
        return [
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'category' => $template->category,
            'body' => $template->body,
            'variables_used' => $this->detectVariables($template->body),
            'is_active' => (bool) $template->is_active,
        ];
    }
}
