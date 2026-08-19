@extends('layouts.admin')

@section('title', 'إدارة قصص النجاح')

@section('header', 'إدارة قصص النجاح')

@section('header_actions')
    <x-admin.button :href="route('admin.testimonials.create')" variant="primary">إضافة قصة نجاح جديدة</x-admin.button>
@endsection

@section('content')
<x-admin.card :padding="false">
    <x-slot:header>
        <x-admin.section-heading
            class="mb-0"
            title="قائمة قصص النجاح"
            description="إدارة وتنظيم قصص النجاح والشهادات من هنا."
        />
    </x-slot:header>

    @if($testimonials->isEmpty())
        <x-admin.empty-state
            title="لا توجد قصص نجاح"
            description="ابدأ بإضافة قصة نجاح جديدة لعرضها للزوار."
        >
            <x-slot:actions>
                <x-admin.button :href="route('admin.testimonials.create')" variant="primary">إضافة قصة نجاح جديدة</x-admin.button>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-tremor-border">
                <thead class="bg-tremor-background-muted">
                    <tr>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الاسم</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">محتوى القصة</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الصورة</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الترتيب</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الحالة</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">تاريخ الإنشاء</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-tremor-border bg-white">
                    @foreach($testimonials as $testimonial)
                        <tr class="hover:bg-tremor-background-muted/70">
                            <td class="px-5 py-4 text-sm font-medium text-tremor-content-strong">{{ $testimonial->name }}</td>
                            <td class="px-5 py-4 text-sm text-tremor-content">{{ Str::limit($testimonial->story_content, 100) }}</td>
                            <td class="px-5 py-4">
                                @if($testimonial->image)
                                    <img src="{{ Storage::url($testimonial->image) }}" alt="{{ $testimonial->name }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <span class="text-sm text-tremor-content-subtle">لا توجد صورة</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-tremor-content">{{ $testimonial->sort_order }}</td>
                            <td class="px-5 py-4">
                                <x-admin.badge :tone="$testimonial->is_visible ? 'success' : 'neutral'">
                                    {{ $testimonial->is_visible ? 'مرئي' : 'مخفي' }}
                                </x-admin.badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-tremor-content">{{ $testimonial->created_at->format('Y-m-d') }}</td>
                            <td class="px-5 py-4">
                                <x-admin.actions>
                                    <x-admin.action :href="route('admin.testimonials.edit', $testimonial)">تعديل</x-admin.action>

                                    <form action="{{ route('admin.testimonials.toggle-visibility', $testimonial) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <x-admin.action type="submit">
                                            {{ $testimonial->is_visible ? 'إخفاء' : 'إظهار' }}
                                        </x-admin.action>
                                    </form>

                                    <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.action type="submit" tone="danger" confirm="هل أنت متأكد من حذف هذه القصة؟">حذف</x-admin.action>
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
