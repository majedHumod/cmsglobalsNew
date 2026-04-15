@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6 space-y-8">
    <h1 class="text-2xl font-bold">الفوترة والاشتراك</h1>

    <div class="bg-white shadow rounded p-5">
        <h2 class="text-xl font-semibold mb-3">بيانات الاشتراك الحالي</h2>
        @if($subscription && $plan)
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-gray-500">الخطة</dt>
                    <dd class="font-medium">{{ $plan->name }} ({{ $plan->code }})</dd>
                </div>
                <div>
                    <dt class="text-gray-500">الدورية</dt>
                    <dd class="font-medium">{{ $plan->interval === 'yearly' ? 'سنوي' : 'شهري' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">السعر</dt>
                    <dd class="font-medium">{{ number_format((float)$plan->price, 2) }} {{ $plan->currency }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">الحالة</dt>
                    <dd class="font-medium">{{ $subscription->status }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">بداية الفترة</dt>
                    <dd class="font-medium">{{ optional($subscription->current_period_start)->format('Y-m-d H:i') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">نهاية الفترة</dt>
                    <dd class="font-medium">{{ optional($subscription->current_period_end)->format('Y-m-d H:i') ?? '-' }}</dd>
                </div>
            </dl>
        @else
            <p class="text-gray-600">لا توجد بيانات اشتراك بعد.</p>
        @endif
    </div>

    <div class="bg-white shadow rounded p-5">
        <h2 class="text-xl font-semibold mb-3">الفواتير الأخيرة</h2>
        @if($invoices->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pe-4">رقم الفاتورة</th>
                            <th class="py-2 pe-4">المبلغ</th>
                            <th class="py-2 pe-4">الحالة</th>
                            <th class="py-2 pe-4">الفترة</th>
                            <th class="py-2 pe-4">روابط</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                            <tr class="border-b">
                                <td class="py-2 pe-4">{{ $inv->number ?? '-' }}</td>
                                <td class="py-2 pe-4">{{ number_format((float)$inv->amount_due, 2) }} {{ $inv->currency }}</td>
                                <td class="py-2 pe-4">{{ $inv->status }}</td>
                                <td class="py-2 pe-4">
                                    {{ optional($inv->period_start)->format('Y-m-d') ?? '-' }}
                                    —
                                    {{ optional($inv->period_end)->format('Y-m-d') ?? '-' }}
                                </td>
                                <td class="py-2 pe-4">
                                    @if($inv->hosted_invoice_url)
                                        <a class="text-cyan-700 hover:underline" href="{{ $inv->hosted_invoice_url }}" target="_blank">عرض</a>
                                    @endif
                                    @if($inv->invoice_pdf_url)
                                        <span class="mx-1">|</span>
                                        <a class="text-cyan-700 hover:underline" href="{{ $inv->invoice_pdf_url }}" target="_blank">PDF</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">لا توجد فواتير بعد.</p>
        @endif
    </div>

    <div class="bg-white shadow rounded p-5">
        <h2 class="text-xl font-semibold mb-3">آخر المدفوعات</h2>
        @if($payments->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pe-4">المبلغ</th>
                            <th class="py-2 pe-4">الحالة</th>
                            <th class="py-2 pe-4">تاريخ الدفع</th>
                            <th class="py-2 pe-4">الإيصال</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $pay)
                            <tr class="border-b">
                                <td class="py-2 pe-4">{{ number_format((float)$pay->amount, 2) }} {{ $pay->currency }}</td>
                                <td class="py-2 pe-4">{{ $pay->status }}</td>
                                <td class="py-2 pe-4">{{ optional($pay->paid_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="py-2 pe-4">
                                    @if($pay->receipt_url)
                                        <a class="text-cyan-700 hover:underline" href="{{ $pay->receipt_url }}" target="_blank">إيصال</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">لا توجد مدفوعات بعد.</p>
        @endif
    </div>

    <div class="bg-white shadow rounded p-5">
        <h2 class="text-xl font-semibold mb-3">إجراءات</h2>
        <div class="flex flex-wrap gap-3">
            <button disabled class="px-4 py-2 rounded bg-gray-200 text-gray-500 cursor-not-allowed">تغيير الخطة (قريباً)</button>
            <button disabled class="px-4 py-2 rounded bg-gray-200 text-gray-500 cursor-not-allowed">تحديث وسيلة الدفع (قريباً)</button>
        </div>
    </div>
</div>
@endsection

