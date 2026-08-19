<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckInSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CheckInResource;
use App\Models\ProgressCheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $perPage = min(50, max(1, (int) $request->integer('per_page', 10)));
        $checkIns = ProgressCheckIn::query()
            ->where('user_id', $user->id)
            ->latest('checked_in_at')
            ->paginate($perPage);

        $latest = $checkIns->getCollection()->first();

        return response()->json([
            'screen' => [
                'title' => 'تسجيل المتابعة',
                'subtitle' => 'سجّل بياناتك لمتابعة تقدمك',
                'submit_label' => 'إرسال المتابعة',
                'photo_section_label' => 'إضافة صورة (اختياري)',
                'photo_hint' => 'اضغط لإضافة صورة',
                'photo_rules' => 'JPG أو PNG – حتى 5MB',
                'nav_label' => 'المتابعة',
            ],
            'form' => $this->formSchema(),
            'latest_check_in' => $latest ? new CheckInResource($latest) : null,
            'check_ins' => CheckInResource::collection($checkIns->getCollection()),
            'meta' => [
                'current_page' => $checkIns->currentPage(),
                'last_page' => $checkIns->lastPage(),
                'per_page' => $checkIns->perPage(),
                'total' => $checkIns->total(),
            ],
            'actions' => [
                'submit_url' => url('/api/v1/check-ins'),
                'list_url' => url('/api/v1/check-ins'),
                'home_url' => url('/api/v1/client/home'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $this->normalizeAliases($request);

        $validated = $request->validate([
            'checked_in_at' => 'required|date',
            'weight' => 'nullable|numeric|min:0|max:1000',
            'body_fat_percentage' => 'nullable|numeric|min:0|max:100',
            'waist_cm' => 'nullable|numeric|min:0|max:500',
            'chest_cm' => 'nullable|numeric|min:0|max:500',
            'hips_cm' => 'nullable|numeric|min:0|max:500',
            'arm_cm' => 'nullable|numeric|min:0|max:500',
            'thigh_cm' => 'nullable|numeric|min:0|max:500',
            'energy_level' => 'nullable|integer|min:1|max:10',
            'training_adherence' => 'nullable|integer|min:1|max:10',
            'nutrition_adherence' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:300',
            'progress_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        if ($request->hasFile('progress_photo')) {
            $validated['progress_photo_path'] = $request->file('progress_photo')->store('progress-check-ins', 'public');
        }

        unset($validated['progress_photo']);

        // خزّن التاريخ كيوم كامل إن أُرسل تاريخ فقط
        $checkedInAt = Carbon::parse($validated['checked_in_at']);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('checked_in_at'))
            || preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('date'))) {
            $checkedInAt = $checkedInAt->startOfDay()->setTimeFrom(now());
        }
        $validated['checked_in_at'] = $checkedInAt;

        $checkIn = ProgressCheckIn::create([
            ...$validated,
            'user_id' => $user->id,
            'coach_id' => $user->coach_id ?: $user->id,
            'submitted_by_user_id' => $user->id,
        ]);

        event(new CheckInSubmitted($checkIn));

        return response()->json([
            'status' => 'ok',
            'message' => 'تم إرسال المتابعة بنجاح.',
            'check_in' => new CheckInResource($checkIn),
        ], 201);
    }

    public function show(Request $request, ProgressCheckIn $checkIn)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);
        abort_unless((int) $checkIn->user_id === (int) $user->id, 403);

        return response()->json([
            'screen' => [
                'title' => 'تفاصيل المتابعة',
            ],
            'check_in' => new CheckInResource($checkIn),
        ]);
    }

    /**
     * Map UI-friendly aliases onto backend column names.
     */
    private function normalizeAliases(Request $request): void
    {
        $map = [
            'date' => 'checked_in_at',
            'fat_percentage' => 'body_fat_percentage',
            'body_fat' => 'body_fat_percentage',
            'waist_circumference' => 'waist_cm',
            'waist' => 'waist_cm',
            'workout_commitment' => 'training_adherence',
            'training_commitment' => 'training_adherence',
            'nutrition_commitment' => 'nutrition_adherence',
            'photo' => 'progress_photo',
            'image' => 'progress_photo',
            'progress_image' => 'progress_photo',
        ];

        foreach ($map as $alias => $canonical) {
            if ($request->has($alias) && ! $request->filled($canonical)) {
                $request->merge([$canonical => $request->input($alias)]);
            }
        }

        // multipart: file aliases
        foreach (['photo', 'image', 'progress_image'] as $fileAlias) {
            if ($request->hasFile($fileAlias) && ! $request->hasFile('progress_photo')) {
                $request->files->set('progress_photo', $request->file($fileAlias));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formSchema(): array
    {
        $scaleOptions = $this->scaleOptions();

        return [
            'method' => 'POST',
            'endpoint' => '/api/v1/check-ins',
            'content_type' => 'multipart/form-data',
            'defaults' => [
                'checked_in_at' => now()->toDateString(),
                'date' => now()->toDateString(),
            ],
            'fields' => [
                [
                    'key' => 'checked_in_at',
                    'aliases' => ['date'],
                    'label' => 'التاريخ',
                    'type' => 'date',
                    'required' => true,
                    'placeholder' => null,
                    'icon_key' => 'calendar',
                ],
                [
                    'key' => 'weight',
                    'aliases' => [],
                    'label' => 'الوزن (كجم)',
                    'type' => 'number',
                    'required' => false,
                    'placeholder' => 'أدخل وزنك',
                    'icon_key' => 'scale',
                    'min' => 0,
                    'max' => 1000,
                    'step' => 0.1,
                ],
                [
                    'key' => 'body_fat_percentage',
                    'aliases' => ['fat_percentage'],
                    'label' => 'نسبة الدهون (%)',
                    'type' => 'number',
                    'required' => false,
                    'placeholder' => 'أدخل نسبة الدهون %',
                    'icon_key' => 'percent',
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.1,
                ],
                [
                    'key' => 'waist_cm',
                    'aliases' => ['waist_circumference'],
                    'label' => 'محيط الخصر (سم)',
                    'type' => 'number',
                    'required' => false,
                    'placeholder' => 'أدخل محيط الخصر',
                    'icon_key' => 'tape',
                    'min' => 0,
                    'max' => 500,
                    'step' => 0.1,
                ],
                [
                    'key' => 'energy_level',
                    'aliases' => [],
                    'label' => 'مستوى الطاقة',
                    'type' => 'select',
                    'required' => false,
                    'placeholder' => 'اختر مستوى الطاقة',
                    'icon_key' => 'bolt',
                    'options' => $scaleOptions,
                ],
                [
                    'key' => 'training_adherence',
                    'aliases' => ['workout_commitment'],
                    'label' => 'الالتزام بالتمرين',
                    'type' => 'select',
                    'required' => false,
                    'placeholder' => 'اختر مستوى الالتزام',
                    'icon_key' => 'dumbbell',
                    'options' => $scaleOptions,
                ],
                [
                    'key' => 'nutrition_adherence',
                    'aliases' => ['nutrition_commitment'],
                    'label' => 'الالتزام بالتغذية',
                    'type' => 'select',
                    'required' => false,
                    'placeholder' => 'اختر مستوى الالتزام',
                    'icon_key' => 'utensils',
                    'options' => $scaleOptions,
                ],
                [
                    'key' => 'notes',
                    'aliases' => [],
                    'label' => 'ملاحظات',
                    'type' => 'textarea',
                    'required' => false,
                    'placeholder' => 'اكتب ملاحظاتك هنا...',
                    'max_length' => 300,
                    'counter' => true,
                ],
                [
                    'key' => 'progress_photo',
                    'aliases' => ['photo', 'image'],
                    'label' => 'إضافة صورة (اختياري)',
                    'type' => 'image',
                    'required' => false,
                    'accept' => ['image/jpeg', 'image/png'],
                    'max_mb' => 5,
                    'hint' => 'اضغط لإضافة صورة',
                    'rules_hint' => 'JPG أو PNG – حتى 5MB',
                    'icon_key' => 'camera',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{value:int,label:string}>
     */
    private function scaleOptions(): array
    {
        $labels = [
            1 => '1 — ضعيف جداً',
            2 => '2 — ضعيف',
            3 => '3 — أقل من المتوسط',
            4 => '4 — مقبول',
            5 => '5 — متوسط',
            6 => '6 — جيد',
            7 => '7 — جيد جداً',
            8 => '8 — ممتاز',
            9 => '9 — رائع',
            10 => '10 — استثنائي',
        ];

        return collect($labels)->map(fn ($label, $value) => [
            'value' => (int) $value,
            'label' => $label,
        ])->values()->all();
    }
}
