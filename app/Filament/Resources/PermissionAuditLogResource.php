<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;
use App\Filament\Resources\PermissionAuditLogResource\Pages;
use App\Models\PermissionAuditLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PermissionAuditLogResource extends Resource
{
    use AdminOnlyResource;
    use Concerns\RequiresDatabaseTable;

    protected static ?string $model = PermissionAuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'سجل الصلاحيات';

    protected static ?string $modelLabel = 'سجل صلاحية';

    protected static ?string $pluralModelLabel = 'سجل الصلاحيات';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return (auth()->user()?->hasRole('admin') ?? false) && static::tablesReady();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('action')->label('الإجراء')->badge()->searchable(),
                Tables\Columns\TextColumn::make('permission_name')->label('الصلاحية')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('المنفّذ')->placeholder('—'),
                Tables\Columns\TextColumn::make('reason')->label('السبب')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('الإجراء')
                    ->options([
                        'override_grant' => 'منح تجاوز',
                        'override_deny' => 'منع تجاوز',
                        'override_revoked' => 'سحب تجاوز',
                        'override_used' => 'استخدام تجاوز',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionAuditLogs::route('/'),
        ];
    }
}
