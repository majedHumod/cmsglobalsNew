@extends('layouts.admin')

@section('title', 'إضافة حركة مخصصة')

@section('header', 'إضافة حركة مخصصة')

@section('header_actions')
<a href="{{ route('exercises.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">العودة للمكتبة</a>
@endsection

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <p class="text-sm text-gray-500 mb-6">أضف حركة من خارج مكتبة RepDB (صورة أو فيديو خاص بكم). لن يُطلب نسب RepDB لهذه الحركة.</p>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('exercises.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('exercises._form')
            <div class="flex justify-end gap-3">
                <a href="{{ route('exercises.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700">إلغاء</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">حفظ الحركة</button>
            </div>
        </form>
    </div>
</div>
@endsection
