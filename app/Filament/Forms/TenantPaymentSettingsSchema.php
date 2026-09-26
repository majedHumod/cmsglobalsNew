<?php

namespace App\Filament\Forms;

use App\Services\Payments\TenantPaymentSettings;
use App\Support\TenantPaymentCatalog;
use Filament\Forms;
use Illuminate\Support\HtmlString;

class TenantPaymentSettingsSchema
{
    public static function tab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('إعدادات الدفع')
            ->icon('heroicon-o-banknotes')
            ->schema([
                Forms\Components\Placeholder::make('payment_scope_note')
                    ->label('نطاق هذه الإعدادات')
                    ->content('هذه القنوات خاصة بمدفوعات المدرب أو النادي: اشتراكات العملاء داخل هذا الموقع. دفع اشتراك المنصة الرئيسية منفصل ولا يُدار من هنا. شعارات المزوّدين أدناه مُجهّزة تلقائياً وتظهر للعميل دون الحاجة لرفعها.')
                    ->columnSpanFull(),
                Forms\Components\Section::make('قنوات الدفع داخل السعودية')
                    ->description('فعّل القنوات التي يريد النادي الربط معها، وأدخل بيانات كل قناة. الدفع المباشر يُفعّل الاشتراك فور تأكيد البوابة.')
                    ->schema(self::channelSections('saudi')),
                Forms\Components\Section::make('دفع عالمي')
                    ->description('سترايب للعملاء الذين يدفعون دولياً. التفعيل يتم مباشرة بعد اكتمال الدفع.')
                    ->schema(self::channelSections('global')),
                Forms\Components\Section::make('تحويل بنكي')
                    ->description('يمكن إضافة أكثر من حساب بنكي، ويختار العميل الحساب الذي حوّل له. يبقى الطلب معلقاً ولا يُفعَّل الاشتراك حتى يؤكد النادي استلام المبلغ.')
                    ->schema(self::channelSections('manual')),
            ]);
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function channelSections(string $region): array
    {
        $sections = [];

        foreach (TenantPaymentCatalog::channels() as $key => $definition) {
            if (($definition['region'] ?? '') !== $region) {
                continue;
            }

            $fields = [
                self::logoPlaceholder($key, $definition),
                Forms\Components\Toggle::make('payments.'.$key.'.enabled')
                    ->label('تفعيل '.$definition['label'])
                    ->live()
                    ->columnSpanFull(),
            ];

            if (TenantPaymentCatalog::isRepeatable($key)) {
                $fields[] = self::repeater($key, $definition);
            } else {
                foreach ($definition['fields'] ?? [] as $name => $field) {
                    if (! is_array($field)) {
                        continue;
                    }

                    $fields[] = self::field($key, (string) $name, $field);
                }
            }

            $sections[] = Forms\Components\Section::make($definition['label'])
                ->description($definition['description'] ?? null)
                ->schema($fields)
                ->columns(2);
        }

        return $sections;
    }

    /**
     * شعار المزوّد جاهز مسبقاً مع التطبيق (لا يُرفع من المدرب/النادي).
     *
     * @param  array<string, mixed>  $definition
     */
    private static function logoPlaceholder(string $key, array $definition): Forms\Components\Component
    {
        return Forms\Components\Placeholder::make('payments.'.$key.'.logo_preview')
            ->label('الشعار')
            ->content(function () use ($key, $definition): HtmlString {
                $url = TenantPaymentCatalog::logoUrl($key);

                if ($url === null) {
                    return new HtmlString('—');
                }

                $label = e((string) ($definition['label'] ?? $key));

                return new HtmlString(
                    '<img src="'.e($url).'" alt="'.$label.'" style="height:32px;max-width:180px;object-fit:contain;">'
                );
            })
            ->columnSpanFull();
    }

    /**
     * حقول الحسابات البنكية المتعددة لقناة التحويل البنكي.
     *
     * @param  array<string, mixed>  $definition
     */
    private static function repeater(string $key, array $definition): Forms\Components\Component
    {
        $repeaterKey = TenantPaymentCatalog::repeaterKey($key) ?? 'accounts';
        $schema = [];

        foreach ($definition['fields'] ?? [] as $name => $field) {
            if (! is_array($field)) {
                continue;
            }

            $type = $field['type'] ?? 'text';

            $component = match ($type) {
                'textarea' => Forms\Components\Textarea::make((string) $name)->rows(2)->columnSpanFull(),
                default => Forms\Components\TextInput::make((string) $name)->maxLength(255),
            };

            $schema[] = $component->label((string) ($field['label'] ?? $name));
        }

        return Forms\Components\Repeater::make('payments.'.$key.'.'.$repeaterKey)
            ->label('الحسابات البنكية')
            ->addActionLabel('إضافة حساب بنكي')
            ->schema($schema)
            ->columns(2)
            ->itemLabel(fn (array $state): ?string => trim((string) ($state['bank_name'] ?? '')) !== ''
                ? (string) $state['bank_name']
                : 'حساب بنكي جديد')
            ->collapsible()
            ->reorderable(true)
            ->defaultItems(0)
            ->columnSpanFull()
            ->helperText('أضف حساباً بنكياً واحداً على الأقل عند تفعيل هذه القناة. يختار العميل الحساب الذي حوّل له عند الدفع.')
            ->visible(fn (Forms\Get $get): bool => (bool) $get('payments.'.$key.'.enabled'));
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private static function field(string $channel, string $name, array $field): Forms\Components\Component
    {
        $path = 'payments.'.$channel.'.'.$name;
        $type = $field['type'] ?? 'text';
        $isSecret = (bool) ($field['secret'] ?? false);

        $component = match ($type) {
            'textarea' => Forms\Components\Textarea::make($path)->rows(3)->columnSpanFull(),
            'select' => Forms\Components\Select::make($path)
                ->options($field['options'] ?? [])
                ->native(false),
            default => Forms\Components\TextInput::make($path)->maxLength(255),
        };

        if ($isSecret && $component instanceof Forms\Components\TextInput) {
            $component = $component
                ->password()
                ->revealable()
                ->autocomplete('new-password')
                ->helperText(function () use ($channel, $name): ?string {
                    return app(TenantPaymentSettings::class)->hasStoredSecret($channel, $name)
                        ? 'محفوظ. اتركه فارغاً للإبقاء على القيمة الحالية.'
                        : null;
                });
        }

        return $component
            ->label((string) ($field['label'] ?? $name))
            ->visible(fn (Forms\Get $get): bool => (bool) $get('payments.'.$channel.'.enabled'))
            ->required(function (Forms\Get $get) use ($channel, $name, $field, $isSecret): bool {
                if (! ($field['required'] ?? false) || ! (bool) $get('payments.'.$channel.'.enabled')) {
                    return false;
                }

                if ($isSecret && app(TenantPaymentSettings::class)->hasStoredSecret($channel, $name)) {
                    return false;
                }

                return true;
            });
    }
}
