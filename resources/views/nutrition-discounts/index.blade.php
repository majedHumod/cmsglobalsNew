@extends('layouts.admin')

@section('header')
خصومات المراكز الغذائية
@endsection

@section('header_actions')
<a href="{{ route('nutrition-discounts.create') }}" 
   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
    إضافة خصم جديد
</a>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    @if($discounts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($discounts as $discount)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200">
                    @if($discount->image)
                        <img src="{{ Storage::url($discount->image) }}" alt="صورة مركز {{ $discount->name }}" 
                             class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">لا توجد صورة</span>
                        </div>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 text-right">{{ $discount->name }}</h3>
                        <div class="flex items-center mb-2">
                            <span class="text-2xl font-bold text-green-600">{{ $discount->discount_percentage }}%</span>
                            <span class="text-gray-600 mr-2">خصم</span>
                        </div>
                        
                        <div class="text-sm text-gray-600 mb-4 text-right">
                            <p><strong>تاريخ البداية:</strong> {{ $discount->start_date->format('Y/m/d') }}</p>
                            <p><strong>تاريخ النهاية:</strong> {{ $discount->end_date->format('Y/m/d') }}</p>
                        </div>

                        <div class="flex items-center mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $discount->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $discount->is_active ? 'نشط' : 'غير نشط' }}
                            </span>
                        </div>

                        <div class="flex space-x-2">
                            <a href="{{ route('nutrition-discounts.edit', $discount) }}" 
                               class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-3 rounded text-sm transition duration-200">
                                تعديل
                            </a>
                            <form action="{{ route('nutrition-discounts.toggle-status', $discount) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full {{ $discount->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white py-2 px-3 rounded text-sm transition duration-200">
                                    {{ $discount->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}
                                </button>
                            </form>
                            <form action="{{ route('nutrition-discounts.destroy', $discount) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('هل أنت متأكد من حذف هذا الخصم؟')"
                                        class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded text-sm transition duration-200">
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $discounts->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-500 text-lg mb-4">لا توجد خصومات غذائية.</div>
            <a href="{{ route('nutrition-discounts.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                إنشاء أول خصم
            </a>
        </div>
    @endif
</div>
@endsection
