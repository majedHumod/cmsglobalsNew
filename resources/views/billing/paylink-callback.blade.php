<x-guest-layout>
<div class="max-w-3xl mx-auto px-4 py-10 space-y-6">
    <div class="bg-white shadow rounded p-6 space-y-4">
        <h1 class="text-3xl font-extrabold">حالة الدفع</h1>

        @php
            $statusClasses = [
                'paid' => 'bg-green-100 text-green-800',
                'canceled' => 'bg-red-100 text-red-800',
                'pending' => 'bg-amber-100 text-amber-800',
            ];
        @endphp

        <div class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $statusClasses[$status] ?? $statusClasses['pending'] }}">
            {{ $status === 'paid' ? 'تم الدفع' : ($status === 'canceled' ? 'تم الإلغاء' : 'قيد التحقق') }}
        </div>

        <p class="text-gray-700">{{ $message }}</p>
        <p class="text-sm text-gray-500">إذا تأخر التفعيل، فسيتم التحقق آليًا من الفاتورة خلال دقيقة تقريبًا حتى لو لم يصل webhook من Paylink.</p>

        @if($invoice)
            <div class="border rounded-lg p-4 bg-gray-50 space-y-2 text-sm">
                <div><strong>رقم الطلب:</strong> {{ $invoice->number ?? '-' }}</div>
                <div><strong>المبلغ:</strong> {{ number_format((float) $invoice->amount_due, 2) }} {{ $invoice->currency }}</div>
                <div><strong>الحالة الحالية:</strong> {{ $invoice->status }}</div>
            </div>
        @endif

        <div class="flex flex-wrap gap-3 pt-2">
            <a href="{{ route('subscribe') }}" class="px-4 py-2 rounded border">العودة إلى الاشتراك</a>
            <a href="{{ url('/') }}" class="px-4 py-2 rounded bg-emerald-600 text-white">الصفحة الرئيسية</a>
        </div>
    </div>
</div>
</x-guest-layout>
