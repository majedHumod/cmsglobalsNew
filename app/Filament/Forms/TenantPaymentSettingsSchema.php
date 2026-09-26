<?php

namespace App\Filament\Forms;

use App\Services\Payments\TenantPaymentSettings;
use App\Support\TenantPaymentCatalog;
use Filament\Forms;

class TenantPaymentSettingsSchema
{
    public static function tab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('إعدادات الدفع')
            ->icon('heroicon-o-banknotes')
            ->schema([
                Forms\Components\Placeholder::make('payment_scope_note')
                    ->label('نطاق هذه الإعدادات')
                    ->content('هذه القنوات خاصة بمدفوعات المدرب أو النادي: اشتراكات العملاء داخل هذا الموقع. دفع اشتراك المنصة الرئيسية منفصل ولا يُدار من هنا.')
                    ->columnSpanFull(),
                Forms\Components\Section::make('قنوات الدفع داخل السعودية')
                    ->description('فعّل القنوات التي يريد النادي الربط معها، وأدخل بيانات كل قناة. الدفع المباشر يُفعّل الاشتراك فور تأكيد البوابة.')
                    ->schema(self::channelSections('saudi')),
                Forms\Components\Section::make('دفع عالمي')
                    ->description('سترايب للعملاء الذين يدفعون دولياً. التفعيل يتم مباشرة بعد اكتمال الدفع.')
                    ->schema(self::channelSections('global')),
                Forms\Components\Section::make('تحويل بنكي')
                    ->description('إذا اختار العميل التحويل البنكي يبقى الطلب معلقاً ولا يُفعَّل الاشتراك حتى يؤكد النادي استلام المبلغ من اشتراكات الأعضاء.')
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
                Forms\Components\Toggle::make('payments.'.$key.'.enabled')
                    ->label('تفعيل '.$definition['label'])
                    ->live()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('payments.'.$key.'.logo')
                    ->label('شعار '.$definition['label'])
                    ->helperText('اختياري. ارفع الشعار الرسمي للمزوّد (من موقعه الرسمي) ليظهر للعميل عند اختيار وسيلة الدفع.')
                    ->image()
                    ->directory('payment-logos')
                    ->disk('public')
                    ->imageEditor()
                    ->maxSize(1024)
                    ->columnSpanFull(),
            ];

            foreach ($definition['fields'] ?? [] as $name => $field) {
                if (! is_array($field)) {
                    continue;
                }

                $fields[] = self::field($key, (string) $name, $field);
            }

            $sections[] = Forms\Components\Section::make($definition['label'])
                ->description($definition['description'] ?? null)
                ->schema($fields)
                ->columns(2);
        }

        return $sections;
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
