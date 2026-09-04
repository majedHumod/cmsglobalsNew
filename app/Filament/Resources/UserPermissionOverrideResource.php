<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;
use App\Filament\Resources\UserPermissionOverrideResource\Pages;
use App\Models\User;
use App\Models\UserPermissionOverride;
use App\Services\AdvancedPermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class UserPermissionOverrideResource extends Resource
{
    use AdminOnlyResource;

    protected static ?string $model = UserPermissionOverride::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'تجاوزات الصلاحيات';

    protected static ?string $modelLabel = 'تجاوز صلاحية';

    protected static ?string $pluralModelLabel = 'تجاوزات الصلاحيات';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('المستخدم')
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('permission_name')
                    ->label('الصلاحية')
                    ->options(fn () => Permission::query()->orderBy('name')->pluck('name', 'name'))
                    ->searchable()
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('type')
                    ->label('النوع')
                    ->options([
                        'grant' => 'منح',
                        'deny' => 'منع',
                    ])
                    ->required()
                    ->native(false)
                    ->default('grant'),
                Forms\Components\Textarea::make('reason')
                    ->label('السبب')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->native(false)
                    ->seconds(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('المستخدم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('permission.name')->label('الصلاحية')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'grant' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === 'grant' ? 'منح' : 'منع'),
                Tables\Columns\TextColumn::make('reason')->label('السبب')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('expires_at')->label('ينتهي')->dateTime('Y-m-d H:i')->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
                Tables\Columns\TextColumn::make('grantedBy.name')->label('بواسطة')->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options(['grant' => 'منح', 'deny' => 'منع']),
                Tables\Filters\TernaryFilter::make('is_active')->label('نشط'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cleanupExpired')
                    ->label('تنظيف المنتهية')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        $count = app(AdvancedPermissionService::class)->cleanupExpiredOverrides();
                        Notification::make()
                            ->title("تم تعطيل {$count} تجاوز منتهي")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('revoke')
                    ->label('سحب')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (UserPermissionOverride $record): bool => (bool) $record->is_active)
                    ->action(function (UserPermissionOverride $record): void {
                        $permissionName = $record->permission?->name;
                        if (! $permissionName || ! $record->user) {
                            return;
                        }
                        app(AdvancedPermissionService::class)->revokePermissionOverride(
                            $record->user,
                            $permissionName,
                            'سحب من لوحة Filament'
                        );
                        Notification::make()->title('تم سحب التجاوز')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserPermissionOverrides::route('/'),
            'create' => Pages\CreateUserPermissionOverride::route('/create'),
        ];
    }
}
