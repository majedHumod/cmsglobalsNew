@extends('layouts.admin')

@section('header')
تعديل الخصم
@endsection

@section('breadcrumbs')
<li class="inline-flex items-center">
    <a href="{{ route('nutrition-discounts.index') }}" class="text-gray-700 hover:text-indigo-600 text-sm font-medium">
        خصومات المراكز الغذائية
    </a>
</li>
<li>
    <div class="flex items-center">
        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-500 text-sm font-medium mr-1">تعديل الخصم</span>
    </div>
</li>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            @include('nutrition-discounts.form', ['discount' => $nutritionDiscount])
        </div>
    </div>
</div>
@endsection
