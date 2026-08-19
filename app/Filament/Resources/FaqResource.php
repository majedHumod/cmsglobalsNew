<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    use AdminOnlyResource;
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'إدارة المحتوى';

    protected static ?string $navigationLabel = 'الأسئلة الشائعة';

    protected static ?string $modelLabel = 'سؤال شائع';

    protected static ?string $pluralModelLabel = 'الأسئلة الشائعة';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('question')
                    ->label('السؤال')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('answer')
                    ->label('الإجابة')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->label('التصنيف')
                    ->options([
                        'عام' => 'عام',
                        'العضويات' => 'العضويات',
                        'الدفع' => 'الدفع',
                        'الحساب' => 'الحساب',
                        'المحتوى' => 'المحتوى',
                        'الدعم الفني' => 'الدعم الفني',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('مفعّل')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->label('السؤال')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('category')
                    ->label('التصنيف')
                    ->badge(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('مفعّل'),
                Tables\Filters\SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options([
                        'عام' => 'عام',
                        'العضويات' => 'العضويات',
                        'الدفع' => 'الدفع',
                        'الحساب' => 'الحساب',
                        'المحتوى' => 'المحتوى',
                        'الدعم الفني' => 'الدعم الفني',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\Action::make('toggle')
                    ->label(fn (Faq $record) => $record->is_active ? 'إيقاف' : 'تفعيل')
                    ->action(function (Faq $record) {
                        $record->update(['is_active' => ! $record->is_active]);
                        Faq::clearCache();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->after(fn () => Faq::clearCache()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->after(fn () => Faq::clearCache()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
