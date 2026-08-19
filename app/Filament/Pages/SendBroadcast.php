<?php

namespace App\Filament\Pages;

use App\Models\MessageTemplate;
use App\Services\BroadcastSegmentService;
use App\Services\MessagingService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SendBroadcast extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static string $view = 'filament.pages.send-broadcast';

    protected static ?string $navigationGroup = 'التواصل والمتابعة';

    protected static ?string $navigationLabel = 'بث جماعي';

    protected static ?string $title = 'إرسال بث جماعي';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'segment_type' => auth()->user()?->hasRole('admin') ? 'all_clients' : 'coach_clients',
            'title' => '',
            'body' => '',
            'template_id' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        $isAdmin = auth()->user()?->hasRole('admin') ?? false;

        return $form
            ->schema([
                Forms\Components\Section::make('محتوى البث')
                    ->schema([
                        Forms\Components\Select::make('segment_type')
                            ->label('الشريحة')
                            ->options([
                                'all_clients' => 'كل العملاء',
                                'coach_clients' => 'عملاء المدرب الحالي',
                                'inactive_clients' => 'عملاء غير نشطين',
                                'membership_expiring' => 'عضوية قاربت على الانتهاء',
                            ])
                            ->required()
                            ->native(false)
                            ->disabled(fn () => ! $isAdmin)
                            ->dehydrated(),
                        Forms\Components\Select::make('template_id')
                            ->label('قالب جاهز (اختياري)')
                            ->options(fn () => MessageTemplate::query()
                                ->where('is_active', true)
                                ->where(function ($query) {
                                    $query->where('created_by_user_id', auth()->id())
                                        ->orWhere('category', 'global');
                                })
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                if (! $state) {
                                    return;
                                }
                                $template = MessageTemplate::query()->find($state);
                                if ($template) {
                                    $set('body', $template->body);
                                }
                            }),
                        Forms\Components\TextInput::make('title')
                            ->label('العنوان (اختياري)')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('body')
                            ->label('نص الرسالة')
                            ->required()
                            ->rows(6)
                            ->maxLength(5000)
                            ->helperText('يمكن استخدام {{client_name}} وسيُخصص لكل مستلم عند التسليم.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $sender = auth()->user();
        $segmentType = $data['segment_type'] ?? 'coach_clients';

        if ($sender->hasRole('coach') && ! $sender->hasRole('admin')) {
            $segmentType = 'coach_clients';
        }

        $recipients = app(BroadcastSegmentService::class)->resolveRecipients($sender, $segmentType);

        if ($recipients->isEmpty()) {
            Notification::make()
                ->title('لا يوجد مستلمون في هذه الشريحة')
                ->warning()
                ->send();

            return;
        }

        $templateId = ! empty($data['template_id']) ? (int) $data['template_id'] : null;
        $body = (string) $data['body'];
        if ($templateId) {
            $template = MessageTemplate::query()->find($templateId);
            if ($template) {
                $body = $template->body;
            }
        }

        $broadcast = app(MessagingService::class)->queueBroadcast(
            $sender,
            $recipients,
            $body,
            $data['title'] ?: null,
            $segmentType,
            [],
            $templateId
        );

        Notification::make()
            ->title('تمت جدولة البث لـ '.$broadcast->recipients_count.' عميل')
            ->body('رقم البث #'.$broadcast->id.' — الحالة: '.$broadcast->status.' (التسليم يتم في الخلفية)')
            ->success()
            ->send();

        $this->form->fill([
            'segment_type' => $segmentType,
            'title' => '',
            'body' => '',
            'template_id' => null,
        ]);
    }
}
