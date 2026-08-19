@extends('layouts.admin')

@section('title', 'خطط المكملات الغذائية')

@section('header', 'خطط المكملات الغذائية')

@section('header_actions')
<a href="{{ route('supplement-plans.create') }}"
   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm">
    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    إضافة مكمل جديد
</a>
@endsection

@section('content')
{{-- فلاتر البحث --}}
<div class="bg-white shadow-md rounded-lg p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">النوع</label>
            <select name="type" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">الكل</option>
                <option value="protein"      @selected(request('type')=='protein')>بروتين</option>
                <option value="vitamins"     @selected(request('type')=='vitamins')>فيتامينات</option>
                <option value="minerals"     @selected(request('type')=='minerals')>معادن</option>
                <option value="pre_workout"  @selected(request('type')=='pre_workout')>ما قبل التمرين</option>
                <option value="post_workout" @selected(request('type')=='post_workout')>ما بعد التمرين</option>
                <option value="omega"        @selected(request('type')=='omega')>أوميغا</option>
                <option value="general"      @selected(request('type')=='general')>عام</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">بحث</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="اسم أو علامة تجارية..."
                   class="border border-gray-300 rounded-md px-3 py-2 text-sm w-52 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <button type="submit"
                class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium rounded-md">
            بحث
        </button>
        <a href="{{ route('supplement-plans.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2">
            إعادة تعيين
        </a>
    </form>
</div>

{{-- الجدول --}}
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المكمل</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الجرعة / التوقيت</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الجمهور</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجراءات</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($supplementPlans as $plan)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        @if($plan->image)
                            <img src="{{ Storage::url($plan->image) }}" alt="{{ $plan->name }}"
                                 class="w-12 h-12 object-cover rounded-lg border border-gray-200 flex-shrink-0">
                        @else
                            <div class="w-12 h-12 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $plan->name }}</p>
                            @if($plan->brand)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $plan->brand }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                        {{ $plan->supplement_type_name }}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    <p class="font-medium">{{ $plan->dosage ?? '—' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $plan->timing_name }}</p>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $plan->audience_gender_label }}</td>
                <td class="px-4 py-3">
                    @if($plan->is_active)
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">نشط</span>
                    @else
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">معطّل</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('supplement-plans.show', $plan) }}"
                           class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">عرض</a>
                        @if($plan->canManage(auth()->user()))
                        <a href="{{ route('supplement-plans.edit', $plan) }}"
                           class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">تعديل</a>
                        <form method="POST" action="{{ route('supplement-plans.destroy', $plan) }}"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المكمل؟')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">حذف</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    لا توجد خطط مكملات حتى الآن
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($supplementPlans->hasPages())
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $supplementPlans->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
