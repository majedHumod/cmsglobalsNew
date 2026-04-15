@extends('layouts.admin')

@section('title', 'اشتراكات العضوية')

@section('header', 'اشتراكات العضوية')

@section('header_actions')
<div class="flex space-x-2 space-x-reverse">
    <a href="{{ route('membership-types.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        أنواع العضوية
    </a>
</div>
@endsection

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المشترك</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع العضوية</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">بداية</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">انتهاء</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدفع</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($memberships as $m)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $m->user->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                            @if($m->membershipType)
                                <a href="{{ route('membership-types.show', $m->membershipType) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $m->membershipType->name }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $m->starts_at?->format('Y-m-d') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $m->expires_at?->format('Y-m-d') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $m->payment_status ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $m->status_text }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">لا توجد اشتراكات مسجّلة بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($memberships->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $memberships->links() }}
        </div>
    @endif
</div>
@endsection
