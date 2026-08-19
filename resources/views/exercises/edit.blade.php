@extends('layouts.admin')

@section('title', 'تعديل الحركة')

@section('header', 'تعديل: '.$exercise->localized_name)

@section('header_actions')
<a href="{{ route('exercises.show', $exercise) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">عرض</a>
@endsection

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('exercises.update', $exercise) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('exercises._form', ['exercise' => $exercise])
            <div class="flex justify-end gap-3">
                <a href="{{ route('exercises.show', $exercise) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700">إلغاء</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">تحديث</button>
            </div>
        </form>
    </div>
</div>
@endsection
