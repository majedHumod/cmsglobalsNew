<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\TrainingSettings;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageTrainingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $slug = 'manage-training-settings';

    protected static string $view = 'filament.pages.manage-training-settings';

    protected static ?string $navigationGroup = 'التمارين';

    protected static ?string $navigationLabel = 'إعدادات التدريب';

    protected static ?string $title = 'إعدادات التدريب';

    protected static ?int $navigationSort = 0;

    protected static bool $shouldRegisterNavigation = true;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->form->fill([
            'training_week_start_day' => TrainingSettings::weekStartDay(),
            'training_week_advance_mode' => TrainingSettings::weekAdvanceMode(),
            'training_auto_activate_plan_on_subscription' => TrainingSettings::autoActivatePlanOnSubscription(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفعيل الخطة')
                    ->description('أفضل ممارسة: يبدأ البرنامج تلقائياً عند اعتماد الاشتراك المدفوع دون انتظار تدخل يدوي.')
                    ->schema([
                        Forms\Components\Toggle::make('training_auto_activate_plan_on_subscription')
                            ->label('تفعيل خطة التمرين تلقائياً بعد اعتماد الاشتراك')
                            ->helperText('عند التفعيل: يُضبط تاريخ بداية البرنامج وأسبوع 1 للمتدرب بعد دفع/اعتماد الاشتراك.')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('بداية الأسبوع')
                    ->description('حدد أول يوم في أسبوع التمرين. الشائع عالمياً الأحد أو الاثنين؛ في المنطقة العربية يُستخدم أيضاً السبت.')
                    ->schema([
                        Forms\Components\Select::make('training_week_start_day')
                            ->label('يوم بداية الأسبوع')
                            ->options(TrainingSettings::weekStartDayOptions())
                            ->required()
                            ->native(false)
                            ->helperText('يُستخدم لترتيب أيام الجدول الأسبوعي (الجلسة 1 = يوم البداية).'),
                    ]),

                Forms\Components\Section::make('الانتقال بين أسابيع البرنامج')
                    ->description('التلقائي: يحسب أسبوع البرنامج من تاريخ البداية وفق حدود الأسبوع. اليدوي: المدرب يحدّث رقم الأسبوع من ملف المتدرب.')
                    ->schema([
                        Forms\Components\Select::make('training_week_advance_mode')
                            ->label('وضع الانتقال الافتراضي')
                            ->options(TrainingSettings::weekAdvanceModeOptions())
                            ->required()
                            ->native(false)
                            ->helperText('يمكن تجاوز هذا الإعداد لكل متدرب من ملفه (تلقائي / يدوي).'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();

        SiteSetting::set(
            'training_week_start_day',
            $data['training_week_start_day'] ?? TrainingSettings::WEEK_START_SUNDAY,
            'training',
            'string',
            'Training week start day (sunday|saturday)'
        );

        SiteSetting::set(
            'training_week_advance_mode',
            $data['training_week_advance_mode'] ?? TrainingSettings::ADVANCE_AUTO,
            'training',
            'string',
            'Program week advance mode (auto|manual)'
        );

        SiteSetting::set(
            'training_auto_activate_plan_on_subscription',
            (bool) ($data['training_auto_activate_plan_on_subscription'] ?? true),
            'training',
            'boolean',
            'Auto-activate training plan after subscription approval'
        );

        SiteSetting::clearGroupCache('training');

        Notification::make()
            ->title('تم حفظ إعدادات التدريب')
            ->success()
            ->send();
    }
}
