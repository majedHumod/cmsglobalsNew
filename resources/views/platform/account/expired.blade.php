<x-guest-layout>
    <div class="max-w-lg mx-auto px-4 py-12">
        <div class="bg-white shadow rounded-2xl p-6 space-y-4">
            <h1 class="text-2xl font-extrabold">الاشتراك غير نشط</h1>
            <p class="text-gray-700 leading-7">{{ $message }}</p>
            <p class="text-sm text-gray-600">
                لا يتم حذف بيانات النادي أو المتدربين تلقائياً. بعد التجديد يعود الوصول كما كان.
            </p>
            @if(!empty($accountName) || !empty($accountEmail))
                <p class="text-sm">
                    <strong>الحساب:</strong>
                    {{ $accountName ?: $accountEmail }}
                    @if($accountEmail && $accountName)
                        <span class="text-gray-500">({{ $accountEmail }})</span>
                    @endif
                </p>
            @endif
            @if($tenant)
                <p class="text-sm"><strong>النادي:</strong> {{ $tenant->name }}</p>
            @endif
            <div class="flex flex-wrap gap-3 pt-2">
                <a href="{{ $subscribeUrl }}" class="inline-flex px-4 py-2 rounded-lg bg-teal-700 text-white font-bold">تجديد الاشتراك</a>
                <a href="{{ $marketingUrl }}" class="inline-flex px-4 py-2 rounded-lg border">الموقع الرئيسي</a>
            </div>
        </div>
    </div>
</x-guest-layout>
