@extends('layouts.public-page')

@section('title', 'دفع الاشتراك')

@section('content')
<section class="py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">إتمام الدفع</h1>
            <p class="text-gray-600 mb-2">
                الخطة: <strong>{{ $membership->subscriptionPlan->name }}</strong>
            </p>
            <div class="mb-6">
                <x-subscription-plan-price :plan="$membership->subscriptionPlan" size="sm" />
                <p class="mt-1 text-xs text-gray-500">المبلغ المستحق هو سعر البيع. اختر وسيلة الدفع المتاحة لدى النادي.</p>
            </div>

            @if(session('success'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @if($membership->payment_channel === 'bank_transfer' && $membership->payment_status === 'pending' && $membership->transfer_reference)
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900 mb-6">
                    تم استلام بيانات التحويل برقم <strong>{{ $membership->transfer_reference }}</strong>.
                    الاشتراك معلق ولن يتم تفعيله إلا بعد تأكيد النادي لاستلام المبلغ.
                </div>
            @endif

            @if($methods === [])
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 mb-6">
                    لم يضبط النادي وسائل الدفع بعد. لا يمكن إتمام الاشتراك حتى تُفعَّل قناة واحدة على الأقل من إعدادات الدفع.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($methods as $method)
                        @if($method['key'] === 'bank_transfer')
                            <form method="POST" action="{{ route('subscription-plans.payment.bank', $membership) }}" enctype="multipart/form-data" class="rounded-lg border border-gray-200 p-4 space-y-3">
                                @csrf
                                <div class="flex items-center gap-3">
                                    @if($method['logo_url'])
                                        <img src="{{ $method['logo_url'] }}" alt="{{ $method['label'] }}" class="h-8 w-auto object-contain">
                                    @endif
                                    <div>
                                        <h2 class="font-semibold text-gray-900">{{ $method['label'] }}</h2>
                                        <p class="text-sm text-gray-600 mt-1">يبقى الطلب معلقاً حتى يؤكد النادي وصول التحويل. لا يتم التفعيل تلقائياً.</p>
                                    </div>
                                </div>
                                <dl class="text-sm text-gray-700 space-y-1">
                                    <div>البنك: <strong>{{ $method['bank']['bank_name'] }}</strong></div>
                                    <div>صاحب الحساب: <strong>{{ $method['bank']['account_name'] }}</strong></div>
                                    <div>الآيبان: <strong dir="ltr">{{ $method['bank']['iban'] }}</strong></div>
                                    @if($method['bank']['account_number'] !== '')
                                        <div>رقم الحساب: <strong dir="ltr">{{ $method['bank']['account_number'] }}</strong></div>
                                    @endif
                                </dl>
                                @if($method['bank']['instructions'] !== '')
                                    <p class="text-sm text-gray-600">{{ $method['bank']['instructions'] }}</p>
                                @endif
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="transfer_reference">رقم الحوالة</label>
                                    <input id="transfer_reference" name="transfer_reference" value="{{ old('transfer_reference', $membership->transfer_reference) }}" required maxlength="100" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                    @error('transfer_reference')
                                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="transfer_receipt">إيصال التحويل (اختياري)</label>
                                    <input id="transfer_receipt" name="transfer_receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-sm text-gray-600">
                                    @error('transfer_receipt')
                                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="rounded-md bg-slate-800 px-4 py-3 text-white hover:bg-slate-900">إرسال بيانات التحويل</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('subscription-plans.payment.start', $membership) }}" class="rounded-lg border border-gray-200 p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                @csrf
                                <input type="hidden" name="channel" value="{{ $method['key'] }}">
                                <div class="flex items-center gap-3">
                                    @if($method['logo_url'])
                                        <img src="{{ $method['logo_url'] }}" alt="{{ $method['label'] }}" class="h-8 w-auto object-contain">
                                    @endif
                                    <div>
                                        <h2 class="font-semibold text-gray-900">{{ $method['label'] }}</h2>
                                        <p class="text-sm text-gray-600 mt-1">دفع مباشر، ويُفعَّل الاشتراك فور تأكيد اكتمال العملية.</p>
                                    </div>
                                </div>
                                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-3 text-white hover:bg-indigo-700">الدفع عبر {{ $method['label'] }}</button>
                            </form>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($membership->gateway_reference && $membership->payment_status !== 'paid' && $membership->payment_channel !== 'bank_transfer')
                <form method="POST" action="{{ route('subscription-plans.payment.return', $membership) }}" class="mt-6">
                    @csrf
                    <button type="submit" class="rounded-md border border-gray-300 px-4 py-3 text-gray-700">التحقق من حالة الدفع</button>
                </form>
            @endif

            <div class="mt-6">
                <a href="{{ route('subscription-plans.public') }}" class="text-sm text-gray-600 hover:text-gray-900">العودة للخطط</a>
            </div>
        </div>
    </div>
</section>
@endsection
