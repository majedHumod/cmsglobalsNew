<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExerciseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach']);
    }

    public function index(Request $request)
    {
        try {
            $query = Exercise::query()->active();

            if ($request->filled('search')) {
                $query->searchLocalized($request->string('search')->toString());
            }

            if ($request->filled('body_part')) {
                $bodyPart = app(\App\Services\ExerciseTranslationService::class)
                    ->resolveLabelKey('body_part', $request->string('body_part')->toString());
                $query->where('body_part', $bodyPart);
            }

            if ($request->filled('equipment')) {
                $equipment = app(\App\Services\ExerciseTranslationService::class)
                    ->resolveLabelKey('equipment', $request->string('equipment')->toString());
                $query->where('equipment', $equipment);
            }

            if ($request->filled('difficulty')) {
                $query->where('difficulty', $request->difficulty);
            }

            if ($request->filled('source')) {
                $query->where('source', $request->source);
            }

            $exercises = $query->latest('id')->paginate(24)->withQueryString();

            $translationService = app(\App\Services\ExerciseTranslationService::class);
            $bodyParts = Exercise::query()->active()->whereNotNull('body_part')->distinct()->orderBy('body_part')->pluck('body_part');
            $equipments = Exercise::query()->active()->whereNotNull('equipment')->distinct()->orderBy('equipment')->pluck('equipment');
            $bodyPartLabels = collect($bodyParts)->mapWithKeys(
                fn ($part) => [$part => $translationService->label('body_part', $part)]
            )->all();
            $equipmentLabels = collect($equipments)->mapWithKeys(
                fn ($eq) => [$eq => $translationService->label('equipment', $eq)]
            )->all();

            return view('exercises.index', compact('exercises', 'bodyParts', 'equipments', 'bodyPartLabels', 'equipmentLabels'));
        } catch (\Exception $e) {
            Log::error('Error in ExerciseController@index', ['error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ أثناء تحميل مكتبة التمارين.');
        }
    }

    public function create()
    {
        return view('exercises.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validateCustomExercise($request);

            $instructions = $this->normalizeInstructions($validated['instructions'] ?? null);
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('exercise-library/custom', 'public');
            }

            Exercise::query()->create([
                'external_id' => Exercise::makeCustomExternalId($validated['name']),
                'source' => Exercise::SOURCE_CUSTOM,
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'instructions' => $instructions,
                'translations' => [
                    'name' => [app()->getLocale() => $validated['name']],
                    'description' => isset($validated['description'])
                        ? [app()->getLocale() => $validated['description']]
                        : [],
                    'instructions' => $instructions
                        ? [app()->getLocale() => $instructions]
                        : [],
                ],
                'category' => $validated['category'] ?? null,
                'difficulty' => $validated['difficulty'] ?? null,
                'equipment' => $validated['equipment'] ?? null,
                'body_part' => $validated['body_part'] ?? null,
                'image_start_path' => $imagePath,
                'image_peak_path' => null,
                'video_url' => $validated['video_url'] ?? null,
                'attribution_required' => false,
                'attribution_text' => null,
                'attribution_url' => null,
                'status' => $request->boolean('status', true),
            ]);

            return redirect()->route('exercises.index')->with('success', 'تم إضافة الحركة المخصصة بنجاح.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error creating custom exercise', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ الحركة.');
        }
    }

    public function show(Exercise $exercise)
    {
        return view('exercises.show', compact('exercise'));
    }

    public function edit(Exercise $exercise)
    {
        if (! $exercise->canEdit(auth()->user())) {
            abort(403, 'لا يمكن تعديل حركات مكتبة RepDB. أنشئ حركة مخصصة بدلاً منها.');
        }

        return view('exercises.edit', compact('exercise'));
    }

    public function update(Request $request, Exercise $exercise)
    {
        if (! $exercise->canEdit(auth()->user())) {
            abort(403, 'لا يمكن تعديل حركات مكتبة RepDB.');
        }

        try {
            $validated = $this->validateCustomExercise($request);

            $data = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'instructions' => $this->normalizeInstructions($validated['instructions'] ?? null),
                'category' => $validated['category'] ?? null,
                'difficulty' => $validated['difficulty'] ?? null,
                'equipment' => $validated['equipment'] ?? null,
                'body_part' => $validated['body_part'] ?? null,
                'video_url' => $validated['video_url'] ?? null,
                'status' => $request->boolean('status', true),
            ];

            $locale = app()->getLocale();
            $translations = $exercise->translations ?? [];
            $translations['name'][$locale] = $validated['name'];
            if (! empty($validated['description'])) {
                $translations['description'][$locale] = $validated['description'];
            }
            if (! empty($data['instructions'])) {
                $translations['instructions'][$locale] = $data['instructions'];
            }
            $data['translations'] = $translations;

            if ($request->hasFile('image')) {
                if ($exercise->image_start_path && Storage::disk('public')->exists($exercise->image_start_path)) {
                    Storage::disk('public')->delete($exercise->image_start_path);
                }
                $data['image_start_path'] = $request->file('image')->store('exercise-library/custom', 'public');
            }

            $exercise->update($data);

            return redirect()->route('exercises.show', $exercise)->with('success', 'تم تحديث الحركة بنجاح.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating custom exercise', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الحركة.');
        }
    }

    public function destroy(Exercise $exercise)
    {
        if (! $exercise->canDelete(auth()->user())) {
            abort(403, 'لا يمكن حذف حركات مكتبة RepDB.');
        }

        try {
            if ($exercise->image_start_path && str_starts_with((string) $exercise->image_start_path, 'exercise-library/custom/')) {
                Storage::disk('public')->delete($exercise->image_start_path);
            }

            $exercise->delete();

            return redirect()->route('exercises.index')->with('success', 'تم حذف الحركة.');
        } catch (\Exception $e) {
            Log::error('Error deleting custom exercise', ['error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ أثناء حذف الحركة.');
        }
    }

    /**
     * JSON search helper for workout create/edit picker.
     */
    public function search(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $limit = min(40, max(5, (int) $request->get('limit', 20)));

        $query = Exercise::query()->active()->orderBy('name');

        if ($search !== '') {
            $query->searchLocalized($search);
        }

        if ($request->filled('body_part')) {
            $bodyPart = app(\App\Services\ExerciseTranslationService::class)
                ->resolveLabelKey('body_part', $request->string('body_part')->toString());
            $query->where('body_part', $bodyPart);
        }

        $items = $query->limit($limit)->get()->map(fn (Exercise $exercise) => [
            'id' => $exercise->id,
            'name' => $exercise->localized_name,
            'name_en' => $exercise->name,
            'source' => $exercise->source,
            'body_part' => $exercise->body_part,
            'body_part_label' => $exercise->localized_body_part,
            'equipment' => $exercise->equipment,
            'equipment_label' => $exercise->localized_equipment,
            'difficulty' => $exercise->difficulty,
            'difficulty_name' => $exercise->difficulty_name,
            'image_url' => $exercise->image_url,
            'video_url' => $exercise->video_url,
            'attribution_required' => (bool) $exercise->attribution_required,
            'attribution_text' => $exercise->attribution_text,
            'attribution_url' => $exercise->attribution_url,
        ]);

        return response()->json(['exercises' => $items]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCustomExercise(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'instructions' => 'nullable|string|max:10000',
            'category' => 'nullable|string|max:100',
            'difficulty' => 'nullable|in:beginner,intermediate,advanced,easy,medium,hard',
            'equipment' => 'nullable|string|max:100',
            'body_part' => 'nullable|string|max:100',
            'video_url' => 'nullable|url|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'status' => 'nullable|boolean',
        ]);
    }

    /**
     * @return list<string>|null
     */
    private function normalizeInstructions(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));

        return $lines !== [] ? $lines : null;
    }
}
