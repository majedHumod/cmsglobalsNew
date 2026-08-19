@extends('layouts.admin')

@section('title', 'مكتبة التمارين')

@section('header', 'مكتبة التمارين')

@section('header_actions')
<div class="flex space-x-2">
    <a href="{{ route('exercises.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
        إضافة حركة مخصصة
    </a>
    <a href="{{ route('workouts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        التمارين الرياضية
    </a>
</div>
@endsection

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">بحث</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="اسم الحركة…">
            </div>
            <div>
                <label for="body_part" class="block text-sm font-medium text-gray-700 mb-2">جزء الجسم</label>
                <select name="body_part" id="body_part" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">الكل</option>
                    @foreach($bodyParts as $part)
                        <option value="{{ $part }}" @selected(request('body_part') === $part)>{{ $bodyPartLabels[$part] ?? $part }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="equipment" class="block text-sm font-medium text-gray-700 mb-2">المعدات</label>
                <select name="equipment" id="equipment" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">الكل</option>
                    @foreach($equipments as $eq)
                        <option value="{{ $eq }}" @selected(request('equipment') === $eq)>{{ $equipmentLabels[$eq] ?? $eq }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">المستوى</label>
                <select name="difficulty" id="difficulty" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">الكل</option>
                    <option value="beginner" @selected(request('difficulty') === 'beginner')>مبتدئ</option>
                    <option value="intermediate" @selected(request('difficulty') === 'intermediate')>متوسط</option>
                    <option value="advanced" @selected(request('difficulty') === 'advanced')>متقدم</option>
                </select>
            </div>
            <div>
                <label for="source" class="block text-sm font-medium text-gray-700 mb-2">المصدر</label>
                <select name="source" id="source" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">الكل</option>
                    <option value="repdb" @selected(request('source') === 'repdb')>مكتبة RepDB</option>
                    <option value="custom" @selected(request('source') === 'custom')>مخصص</option>
                </select>
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">تصفية</button>
                <a href="{{ route('exercises.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse($exercises as $exercise)
        <a href="{{ route('exercises.show', $exercise) }}" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="aspect-square bg-gray-100">
                @if($exercise->image_url)
                    <img src="{{ $exercise->image_url }}" alt="{{ $exercise->localized_name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">بدون صورة</div>
                @endif
            </div>
            <div class="p-3">
                <h3 class="font-medium text-gray-900 text-sm line-clamp-2">{{ $exercise->localized_name }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $exercise->localized_body_part ?: $exercise->body_part }} · {{ $exercise->difficulty_name }}</p>
                <p class="text-[10px] mt-1 {{ $exercise->isCustom() ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $exercise->source_name }}</p>
                @if($exercise->attribution_required && $exercise->image_url)
                    <p class="text-[10px] text-gray-400 mt-2">
                        <a href="{{ $exercise->attribution_url }}" target="_blank" rel="noopener" class="underline hover:text-indigo-600" onclick="event.stopPropagation()">{{ $exercise->attribution_text }}</a>
                    </p>
                @endif
            </div>
        </a>
    @empty
        <div class="col-span-full bg-white rounded-lg shadow p-8 text-center text-gray-500">
            لا توجد تمارين في المكتبة بعد. شغّل أمر الاستيراد:
            <code class="block mt-2 text-xs bg-gray-50 p-2 rounded">php artisan exercises:import-repdb --tenant=your-domain.test</code>
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $exercises->links() }}
</div>
@endsection
