<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Filament\Widgets\ClientFollowUpStats;
use App\Models\User;
use App\Services\MessagingService;
use App\Services\NotificationFeedService;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل الملف')
                ->icon('heroicon-o-pencil-square'),
            Actions\Action::make('remind')
                ->label('تذكير')
                ->icon('heroicon-o-bell-alert')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('إرسال تذكير')
                ->modalDescription('سيتم إرسال تذكير ورسالة متابعة لهذا المتدرب.')
                ->action(function (): void {
                    /** @var User $client */
                    $client = $this->record;
                    $coach = auth()->user();
                    $messageBody = 'مدربك يطلب منك متابعة برنامجك اليومي وإرسال تحديث.';

                    $messaging = app(MessagingService::class);
                    $conversation = $messaging->findOrCreateDirectConversation($coach, $client, 'تذكير من المدرب');
                    $messaging->sendMessage($conversation, $coach, $messageBody);

                    app(NotificationFeedService::class)->pushToUser(
                        $client->id,
                        'coach.reminder',
                        'تذكير من المدرب',
                        $messageBody,
                        [
                            'coach_id' => $coach->id,
                            'messages_url' => route('client.messages.index'),
                        ]
                    );

                    Notification::make()
                        ->title('تم إرسال التذكير')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ClientFollowUpStats::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'record' => $this->record,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('البيانات الأساسية')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')->label('الاسم'),
                        Infolists\Components\TextEntry::make('email')->label('البريد'),
                        Infolists\Components\TextEntry::make('phone')->label('الجوال')->placeholder('—'),
                        Infolists\Components\TextEntry::make('coach.name')->label('المدرب')->placeholder('غير معيّن'),
                        Infolists\Components\TextEntry::make('membershipType.name')->label('نوع العضوية')->placeholder('غير محدد'),
                        Infolists\Components\TextEntry::make('membership_expires_at')
                            ->label('انتهاء العضوية')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ التسجيل')
                            ->date('d/m/Y'),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('الملف الرياضي')
                    ->schema([
                        Infolists\Components\TextEntry::make('clientProfile.fitness_goal')
                            ->label('الهدف')
                            ->placeholder('غير محدد'),
                        Infolists\Components\TextEntry::make('clientProfile.target_weight')
                            ->label('الوزن المستهدف')
                            ->formatStateUsing(fn ($state) => $state ? $state.' كجم' : 'غير محدد'),
                        Infolists\Components\TextEntry::make('clientProfile.activity_level')
                            ->label('مستوى النشاط')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'intermediate' => 'متوسط',
                                'advanced' => 'متقدم',
                                default => 'مبتدئ',
                            }),
                        Infolists\Components\TextEntry::make('clientProfile.current_program_week')
                            ->label('أسبوع البرنامج')
                            ->placeholder('1'),
                        Infolists\Components\TextEntry::make('clientProfile.week_advance_mode')
                            ->label('انتقال الأسابيع')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'auto' => 'تلقائي',
                                'manual' => 'يدوي',
                                default => 'حسب الإعدادات العامة',
                            }),
                        Infolists\Components\TextEntry::make('clientProfile.program_started_at')
                            ->label('بداية الخطة')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('لم تُفعَّل بعد'),
                        Infolists\Components\TextEntry::make('clientProfile.preferred_contact_method')
                            ->label('وسيلة التواصل')
                            ->placeholder('whatsapp'),
                        Infolists\Components\TextEntry::make('clientProfile.injuries')
                            ->label('إصابات / قيود')
                            ->placeholder('لا توجد بيانات')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('clientProfile.medical_notes')
                            ->label('ملاحظات إضافية')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
