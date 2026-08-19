@extends('layouts.admin')

@section('title', 'إدارة جلسات التدريب الخاصة')

@section('header', 'إدارة جلسات التدريب الخاصة')

@section('header_actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.session-bookings.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
        </svg>
        إدارة الحجوزات
    </a>
    <a href="{{ route('admin.training-sessions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        إضافة جلسة جديدة
    </a>
</div>
@endsection

@section('content')
@php
    $sessionTypeLabels = [
        'in_person' => 'حضوري',
        'online' => 'أونلاين',
        'hybrid' => 'هجين',
    ];
@endphp
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">قائمة جلسات التدريب الخاصة</h2>
            <p class="mt-1 text-sm text-gray-500">إدارة وتنظيم جلسات التدريب الخاصة والحجوزات.</p>
        </div>

        @if($sessions->isEmpty())
            <div class="text-center py-12">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد جلسات تدريب</h3>
                <p class="text-sm text-gray-500 mb-6">ابدأ بإنشاء جلسة تدريب خاصة جديدة.</p>
                <a href="{{ route('admin.training-sessions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    إضافة جلسة جديدة
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($sessions as $session)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        @if($session->image)
                            <img src="{{ Storage::url($session->image) }}" alt="{{ $session->title }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        @endif
                        
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ $session->title }}</h3>
                                <div class="flex space-x-1 ml-2">
                                    <a href="{{ route('admin.training-sessions.edit', $session) }}" class="text-indigo-600 hover:text-indigo-900 p-1" title="تعديل">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.training-sessions.destroy', $session) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 p-1" onclick="return confirm('هل أنت متأكد من حذف هذه الجلسة؟')" title="حذف">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ Str::limit($session->description, 150) }}</p>
                            
                            <!-- Session Details -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">السعر:</span>
                                    <span class="font-medium text-green-600">{{ $session->formatted_price }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">المدة:</span>
                                    <span class="font-medium">{{ $session->duration_text }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">النوع:</span>
                                    <span class="font-medium">{{ $sessionTypeLabels[$session->session_type ?? 'in_person'] ?? ($session->session_type ?? 'in_person') }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">السعة:</span>
                                    <span class="font-medium">{{ $session->capacity ?? 1 }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">الحالة:</span>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $session->is_visible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $session->is_visible ? 'مرئي' : 'مخفي' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">الحجوزات:</span>
                                    <span class="font-medium">{{ $session->total_bookings }} حجز</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $session->user->name }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $session->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                            
                            <div class="flex space-x-2">
                                <a href="{{ route('training-sessions.show', $session) }}" class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 px-3 rounded-md transition-colors" target="_blank">
                                    عرض
                                </a>
                                <a href="{{ route('admin.training-sessions.edit', $session) }}" class="flex-1 text-center bg-yellow-600 hover:bg-yellow-700 text-white text-sm py-2 px-3 rounded-md transition-colors">
                                    تعديل
                                </a>
                                <form action="{{ route('admin.training-sessions.toggle-visibility', $session) }}" method="POST" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full bg-{{ $session->is_visible ? 'red' : 'green' }}-600 hover:bg-{{ $session->is_visible ? 'red' : 'green' }}-700 text-white text-sm py-2 px-3 rounded-md transition-colors">
                                        {{ $session->is_visible ? 'إخفاء' : 'إظهار' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection