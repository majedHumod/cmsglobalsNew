@php
    $exercise = $exercise ?? null;
    $instructionsText = old('instructions');
    if ($instructionsText === null && $exercise && is_array($exercise->instructions)) {
        $instructionsText = implode("\n", $exercise->instructions);
    }
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">اسم الحركة *</label>
        <input type="text" name="name" id="name" required value="{{ old('name', $exercise->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label for="difficulty" class="block text-sm font-medium text-gray-700">المستوى</label>
        <select name="difficulty" id="difficulty" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">—</option>
            @foreach(['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'] as $value => $label)
                <option value="{{ $value }}" @selected(old('difficulty', $exercise->difficulty ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="body_part" class="block text-sm font-medium text-gray-700">جزء الجسم</label>
        <input type="text" name="body_part" id="body_part" value="{{ old('body_part', $exercise->body_part ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="chest / core / …">
    </div>
    <div>
        <label for="equipment" class="block text-sm font-medium text-gray-700">المعدات</label>
        <input type="text" name="equipment" id="equipment" value="{{ old('equipment', $exercise->equipment ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="dumbbell / bodyweight / …">
    </div>
    <div>
        <label for="category" class="block text-sm font-medium text-gray-700">التصنيف</label>
        <input type="text" name="category" id="category" value="{{ old('category', $exercise->category ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="strength / stretching / …">
    </div>
    <div>
        <label for="video_url" class="block text-sm font-medium text-gray-700">رابط فيديو توضيحي</label>
        <input type="url" name="video_url" id="video_url" value="{{ old('video_url', $exercise->video_url ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://…">
    </div>
</div>

<div>
    <label for="description" class="block text-sm font-medium text-gray-700">الوصف</label>
    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $exercise->description ?? '') }}</textarea>
</div>

<div>
    <label for="instructions" class="block text-sm font-medium text-gray-700">خطوات الأداء (سطر لكل خطوة)</label>
    <textarea name="instructions" id="instructions" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $instructionsText }}</textarea>
</div>

<div>
    <label for="image" class="block text-sm font-medium text-gray-700">صورة توضيحية</label>
    @if($exercise?->image_url)
        <img src="{{ $exercise->image_url }}" alt="" class="mt-2 w-28 h-28 object-cover rounded border">
    @endif
    <input type="file" name="image" id="image" accept="image/*" class="mt-2 block w-full text-sm text-gray-500">
</div>

<div class="flex items-start">
    <input type="checkbox" name="status" id="status" value="1" class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded" @checked(old('status', $exercise->status ?? true))>
    <label for="status" class="mr-2 text-sm text-gray-700">تفعيل الحركة في المكتبة</label>
</div>
