@extends('layouts.admin')

@section('title', $exercise->localized_name)

@section('header', $exercise->localized_name)

@section('header_actions')
<div class="flex space-x-2">
    @if($exercise->canEdit(auth()->user()))
        <a href="{{ route('exercises.edit', $exercise) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">تعديل</a>
        <form action="{{ route('exercises.destroy', $exercise) }}" method="POST" onsubmit="return confirm('حذف هذه الحركة؟')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">حذف</button>
        </form>
    @endif
    <a href="{{ route('exercises.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        العودة للمكتبة
    </a>
</div>
@endsection

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <div class="grid grid-cols-2 gap-3">
                @if($exercise->image_start_url)
                    <div>
                        <img src="{{ $exercise->image_start_url }}" alt="{{ $exercise->localized_name }} start" class="w-full rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-500 mt-1 text-center">بداية الحركة</p>
                    </div>
                @endif
                @if($exercise->image_peak_url)
                    <div>
                        <img src="{{ $exercise->image_peak_url }}" alt="{{ $exercise->localized_name }} peak" class="w-full rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-500 mt-1 text-center">ذروة الحركة</p>
                    </div>
                @elseif($exercise->image_url && !$exercise->image_start_url)
                    <div class="col-span-2">
                        <img src="{{ $exercise->image_url }}" alt="{{ $exercise->localized_name }}" class="w-full rounded-lg border border-gray-200">
                    </div>
                @endif
            </div>
            @if($exercise->video_url)
                <p class="mt-3 text-sm">
                    <a href="{{ $exercise->video_url }}" target="_blank" rel="noopener" class="text-indigo-600 underline">مشاهدة فيديو الحركة</a>
                </p>
            @endif
            @if($exercise->attribution_required && $exercise->image_url)
                <p class="mt-3 text-xs text-gray-500">
                    <a href="{{ $exercise->attribution_url }}" target="_blank" rel="noopener" class="underline hover:text-indigo-600">{{ $exercise->attribution_text }}</a>
                </p>
            @endif
        </div>

        <div>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-gray-500">المصدر</dt><dd class="font-medium">{{ $exercise->source_name }}</dd></div>
                <div><dt class="text-gray-500">المستوى</dt><dd class="font-medium">{{ $exercise->difficulty_name }}</dd></div>
                <div><dt class="text-gray-500">جزء الجسم</dt><dd class="font-medium">{{ $exercise->localized_body_part ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">المعدات</dt><dd class="font-medium">{{ $exercise->localized_equipment ?: 'وزن الجسم' }}</dd></div>
                <div><dt class="text-gray-500">التصنيف</dt><dd class="font-medium">{{ $exercise->category ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">المعرّف الخارجي</dt><dd class="font-mono text-xs">{{ $exercise->external_id }}</dd></div>
                @if($exercise->localized_name !== $exercise->name)
                    <div><dt class="text-gray-500">الاسم بالإنجليزية</dt><dd class="font-medium">{{ $exercise->name }}</dd></div>
                @endif
            </dl>

            @if($exercise->localized_description)
                <div class="mt-6">
                    <h3 class="font-medium text-gray-900 mb-2">الوصف</h3>
                    <p class="text-sm text-gray-600">{{ $exercise->localized_description }}</p>
                </div>
            @endif

            @if(count($exercise->localized_instructions))
                <div class="mt-6">
                    <h3 class="font-medium text-gray-900 mb-2">الخطوات</h3>
                    <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600">
                        @foreach($exercise->localized_instructions as $step)
                            <li>{{ is_string($step) ? $step : json_encode($step) }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
