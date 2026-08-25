<x-guest-layout>
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-extrabold mb-3">{{ $renewal ? 'تجديد الاشتراك' : 'الاشتراك عبر سب-دومين' }}</h1>
    <p class="text-gray-600 mb-8">
        @if($renewal)
            بيانات ناديك جاهزة أدناه. اختر الخطة ثم أكمل الدفع لتجديد نفس الحساب دون إنشاء مركز جديد.
        @else
            اختر الخطة، ثم أدخل بياناتك الأساسية. سيتم تحويلك إلى Paylink لإتمام الدفع، ولن يتم تفعيل نسختك إلا بعد تأكيد السداد.
        @endif
    </p>

    @if($renewal && $tenant)
        <div class="p-4 mb-6 rounded bg-teal-50 text-teal-900 text-sm">
            <div><strong>النادي:</strong> {{ $tenant->name }}</div>
            @if(!empty($prefill['email']))
                <div><strong>البريد:</strong> {{ $prefill['email'] }}</div>
            @endif
            @if(!empty($prefill['subdomain']))
                <div><strong>الرابط:</strong> {{ $prefill['subdomain'] }}.{{ config('app.domain', 'etoscoach.com') }}</div>
            @endif
        </div>
    @endif

    @if (session('status'))
        <div class="p-4 mb-6 rounded bg-green-100 text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white shadow rounded p-5 mb-6">
        <h2 class="font-bold mb-3">الخطط المتاحة</h2>
        @if($plans->isEmpty())
            <p class="text-gray-600">لا توجد خطط متاحة حالياً. يرجى المحاولة لاحقاً.</p>
        @else
            <form id="subscribe-form" action="{{ route('billing.checkout.session') }}" method="POST" class="space-y-5">
                @csrf
                <div class="space-y-2">
                    @foreach($plans as $idx => $plan)
                        <label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="plan_code" value="{{ $plan->code }}" {{ $idx===0 ? 'checked' : '' }}>
                            <div>
                                <div class="font-semibold">
                                    {{ $plan->name }} — {{ number_format($plan->price, 2) }} {{ $plan->currency }}
                                    ({{ $plan->interval === 'monthly' ? 'شهري' : 'سنوي' }})
                                </div>
                                @if(!empty($plan->features))
                                    <ul class="list-disc ps-5 text-gray-600">
                                        @foreach($plan->features as $feat)
                                            <li>{{ $feat }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">السب-دومين المطلوب</label>
                        <input type="text" name="subdomain" class="w-full border rounded px-3 py-2" placeholder="example" value="{{ old('subdomain', $prefill['subdomain'] ?? '') }}" required @if($renewal) readonly @endif>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($renewal)
                                لا يمكن تغيير سب-دومين النادي الحالي عند التجديد.
                            @else
                                أحرف صغيرة/أرقام/— فقط. مثال: example. سيصبح رابطك example.{{ config('app.domain', 'yourdomain.com') }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">البريد الإلكتروني للتواصل</label>
                        <input type="email" name="email" class="w-full border rounded px-3 py-2" placeholder="you@example.com" value="{{ old('email', $prefill['email'] ?? '') }}" required @if($renewal) readonly @endif>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">الاسم (اختياري)</label>
                        <input type="text" name="name" class="w-full border rounded px-3 py-2" placeholder="اسم المالك/المسؤول" value="{{ old('name', $prefill['name'] ?? '') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">رقم الجوال</label>
                        <input type="text" name="mobile" class="w-full border rounded px-3 py-2" placeholder="05xxxxxxxx" value="{{ old('mobile', $prefill['mobile'] ?? '') }}" required>
                        <p class="text-xs text-gray-500 mt-1">مطلوب لدى Paylink لإصدار رابط الدفع.</p>
                    </div>
                </div>

                <div id="subscribe-errors" class="hidden p-3 rounded bg-red-100 text-red-800"></div>
                <div id="subscribe-success" class="hidden p-3 rounded bg-green-100 text-green-800"></div>

                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 rounded bg-emerald-600 text-white font-bold hover:bg-emerald-700">
                        {{ $renewal ? 'تجديد الاشتراك والدفع' : 'متابعة إلى صفحة الدفع' }}
                    </button>
                    <a href="/" class="px-4 py-2 rounded border">العودة</a>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
document.getElementById('subscribe-form')?.addEventListener('submit', async (e) => {
    // استخدم AJAX لعرض رسائل مريحة دون مغادرة الصفحة
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const errorsBox = document.getElementById('subscribe-errors');
    const successBox = document.getElementById('subscribe-success');
    errorsBox.classList.add('hidden');
    successBox.classList.add('hidden');
    errorsBox.textContent = '';
    successBox.textContent = '';
    try {
        const resp = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: data
        });
        const contentType = resp.headers.get('Content-Type') || '';
        if (!resp.ok) {
            let message = '';
            if (contentType.includes('application/json')) {
                const j = await resp.json();
                if (j.errors) {
                    message = Object.values(j.errors).flat().join('، ');
                } else if (j.error) {
                    message = j.error;
                } else if (j.message) {
                    message = j.message;
                }
            } else {
                message = await resp.text();
            }
            errorsBox.textContent = message || `خطأ غير متوقع (الحالة ${resp.status})`;
            errorsBox.classList.remove('hidden');
            return;
        }
        const payload = contentType.includes('application/json') ? await resp.json() : {};
        successBox.textContent = payload.message || 'تم إنشاء الفاتورة بنجاح. سيتم تحويلك إلى Paylink.';
        successBox.classList.remove('hidden');
        if (payload.redirect_url) {
            setTimeout(() => {
                window.location.href = payload.redirect_url;
            }, 800);
            return;
        }
        form.reset();
    } catch (err) {
        errorsBox.textContent = (err && err.message) ? err.message : 'تعذر الاتصال بالخادم. حاول لاحقاً.';
        errorsBox.classList.remove('hidden');
    }
});
</script>
{{-- Blade component layout expects slot, so we wrap content inside <x-guest-layout> --}}
</x-guest-layout>

