<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Events\CheckInSubmitted;
use App\Models\ProgressCheckIn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProgressCheckInsRelationManager extends RelationManager
{
    protected static string $relationship = 'progressCheckIns';

    protected static ?string $title = 'متابعة التقدم (Check-ins)';

    protected static ?string $modelLabel = 'تحديث تقدم';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('القياسات')
                    ->schema([
                        Forms\Components\DateTimePicker::make('checked_in_at')
                            ->label('تاريخ التحديث')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\TextInput::make('weight')
                            ->label('الوزن (كجم)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000),
                        Forms\Components\TextInput::make('body_fat_percentage')
                            ->label('نسبة الدهون %')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('waist_cm')->label('الخصر (سم)')->numeric()->minValue(0),
                        Forms\Components\TextInput::make('chest_cm')->label('الصدر (سم)')->numeric()->minValue(0),
                        Forms\Components\TextInput::make('hips_cm')->label('الوركين (سم)')->numeric()->minValue(0),
                        Forms\Components\TextInput::make('arm_cm')->label('الذراع (سم)')->numeric()->minValue(0),
                        Forms\Components\TextInput::make('thigh_cm')->label('الفخذ (سم)')->numeric()->minValue(0),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('الالتزام والملاحظات')
                    ->schema([
                        Forms\Components\TextInput::make('energy_level')
                            ->label('مستوى الطاقة (1-10)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10),
                        Forms\Components\TextInput::make('training_adherence')
                            ->label('التزام التمرين (1-10)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10),
                        Forms\Components\TextInput::make('nutrition_adherence')
                            ->label('التزام التغذية (1-10)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10),
                        Forms\Components\FileUpload::make('progress_photo_path')
                            ->label('صورة التقدم')
                            ->image()
                            ->directory('progress-check-ins')
                            ->disk('public')
                            ->visibility('public')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات العميل')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('coach_feedback')
                            ->label('ملاحظات المدرب')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('next_steps')
                            ->label('الخطوات القادمة')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('checked_in_at')
            ->columns([
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label('الوزن')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('training_adherence')
                    ->label('تمرين')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('nutrition_adherence')
                    ->label('تغذية')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('average_adherence')
                    ->label('متوسط الالتزام')
                    ->placeholder('—'),
                Tables\Columns\ImageColumn::make('progress_photo_path')
                    ->label('صورة')
                    ->disk('public')
                    ->square()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('submittedBy.name')
                    ->label('أضافه')
                    ->toggleable(),
            ])
            ->defaultSort('checked_in_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة Check-in')
                    ->mutateFormDataUsing(function (array $data): array {
                        $client = $this->getOwnerRecord();
                        $data['user_id'] = $client->id;
                        $data['coach_id'] = $client->coach_id ?: auth()->id();
                        $data['submitted_by_user_id'] = auth()->id();

                        return $data;
                    })
                    ->after(function (ProgressCheckIn $record): void {
                        event(new CheckInSubmitted($record));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
            ]);
    }
}
