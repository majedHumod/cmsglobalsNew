<?php

namespace App\Filament\Resources\Concerns;

use App\Models\MembershipType;
use Filament\Forms;

trait HasAudienceFields
{
    public static function audienceSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('الجمهور المستهدف')
            ->schema([
                Forms\Components\Select::make('audience_gender')
                    ->label('نطاق الجنس')
                    ->options([
                        'all' => 'الجميع',
                        'male' => 'رجال',
                        'female' => 'نساء',
                    ])
                    ->default('all')
                    ->native(false),
                Forms\Components\Select::make('required_membership_types')
                    ->label('أنواع العضوية المطلوبة')
                    ->multiple()
                    ->options(fn () => MembershipType::query()->active()->ordered()->pluck('name', 'id'))
                    ->native(false)
                    ->helperText('اتركه فارغاً ليكون متاحاً لجميع المسارات.'),
            ])
            ->columns(2);
    }

    public static function mutateAudienceData(array $data): array
    {
        $data['audience_gender'] = $data['audience_gender'] ?? 'all';
        $data['required_membership_types'] = array_values(array_filter(array_map(
            'intval',
            $data['required_membership_types'] ?? []
        )));

        return $data;
    }
}
