@extends('layouts.admin')

@section('title', 'إدارة الأسئلة الشائعة')

@section('header', 'إدارة الأسئلة الشائعة')

@section('header_actions')
    <x-admin.button :href="route('admin.faqs.create')" variant="primary">إضافة سؤال جديد</x-admin.button>
@endsection

@section('content')
<x-admin.card :padding="false">
    <x-slot:header>
        <x-admin.section-heading
            class="mb-0"
            title="الأسئلة الشائعة"
            description="إدارة الأسئلة المعروضة للجمهور في صفحة المساعدة."
        />
    </x-slot:header>

    @if($faqs->isEmpty())
        <x-admin.empty-state
            title="لا توجد أسئلة شائعة"
            description="ابدأ بإضافة أسئلة شائعة لمساعدة المستخدمين."
        >
            <x-slot:actions>
                <x-admin.button :href="route('admin.faqs.create')" variant="primary">إضافة سؤال جديد</x-admin.button>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-tremor-border">
                <thead class="bg-tremor-background-muted">
                    <tr>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">السؤال</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">التصنيف</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الترتيب</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الحالة</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">تاريخ الإنشاء</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-tremor-border bg-white">
                    @foreach($faqs as $faq)
                        <tr class="hover:bg-tremor-background-muted/70">
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-tremor-content-strong">{{ Str::limit($faq->question, 50) }}</div>
                                <div class="mt-1 text-xs text-tremor-content">{{ Str::limit(strip_tags($faq->answer), 60) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <x-admin.badge tone="brand">{{ $faq->category }}</x-admin.badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-tremor-content">{{ $faq->sort_order }}</td>
                            <td class="px-5 py-4">
                                <x-admin.badge :tone="$faq->is_active ? 'success' : 'danger'">
                                    {{ $faq->is_active ? 'نشط' : 'غير نشط' }}
                                </x-admin.badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-tremor-content">{{ $faq->created_at->format('Y-m-d') }}</td>
                            <td class="px-5 py-4">
                                <x-admin.actions>
                                    <x-admin.action :href="route('admin.faqs.edit', $faq)">تعديل</x-admin.action>

                                    <form action="{{ route('admin.faqs.toggle-status', $faq) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <x-admin.action type="submit">
                                            {{ $faq->is_active ? 'إيقاف' : 'تفعيل' }}
                                        </x-admin.action>
                                    </form>

                                    <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.action type="submit" tone="danger" confirm="هل أنت متأكد من حذف هذا السؤال؟">حذف</x-admin.action>
                                    </form>
                                </x-admin.actions>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin.card>
@endsection
