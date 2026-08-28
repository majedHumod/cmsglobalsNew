@php
    $renewal = $renewal ?? ($pageMode ?? '') === 'renewal';
    $defaultPlan = $selectedPlan ?? ($plans->first()?->code);
@endphp

@if($plans->isEmpty())
    <div class="billing-panel">
        <p class="billing-lead">لا توجد خطط متاحة حالياً. يرجى المحاولة لاحقاً.</p>
        <div class="form-actions">
            <a href="{{ $marketingUrl }}" class="btn">العودة للموقع الرئيسي</a>
        </div>
    </div>
@else
    <div class="billing-panel" id="subscribe-form">
        <h2>{{ $renewal ? 'اختر خطة التجديد' : 'اختر خطتك' }}</h2>

        <form action="{{ route('billing.checkout.session') }}" method="POST" class="subscribe-checkout-form">
            @csrf

            <div class="plan-grid">
                @foreach($plans as $plan)
                    @php $checked = ($defaultPlan === $plan->code) || ($loop->first && ! $defaultPlan); @endphp
                    <label class="plan-option {{ $checked ? 'selected' : '' }}" data-plan-option>
                        <input type="radio" name="plan_code" value="{{ $plan->code }}" {{ $checked ? 'checked' : '' }}>
                        <div class="plan-name">{{ $plan->name }}</div>
                        <div class="plan-price">{{ number_format($plan->price, 0) }} <span style="font-size:16px">{{ $plan->currency }}</span></div>
                        <div class="plan-interval">{{ $plan->interval === 'monthly' ? 'فوترة شهرية' : 'فوترة سنوية' }}</div>
                        @if(!empty($plan->features))
                            <ul>
                                @foreach($plan->features as $feat)
                                    <li>{{ $feat }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </label>
                @endforeach
            </div>

            <div class="form-grid" style="margin-top:18px">
                <div class="form-field">
                    <label for="subdomain">السب-دومين المطلوب</label>
                    <input type="text" id="subdomain" name="subdomain" placeholder="example"
                           value="{{ old('subdomain', $prefill['subdomain'] ?? '') }}"
                           required @if($renewal) readonly @endif>
                    <span class="hint">
                        @if($renewal)
                            لا يمكن تغيير سب-دومين النادي الحالي عند التجديد.
                        @else
                            أحرف صغيرة وأرقام وشرطة (-) فقط. مثال: {{ $prefill['subdomain'] ?: 'example' }}.{{ $domain }}
                        @endif
                    </span>
                </div>
                <div class="form-field">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com"
                           value="{{ old('email', $prefill['email'] ?? '') }}"
                           required @if($renewal) readonly @endif>
                </div>
                <div class="form-field">
                    <label for="name">الاسم (اختياري)</label>
                    <input type="text" id="name" name="name" placeholder="اسم المالك/المسؤول"
                           value="{{ old('name', $prefill['name'] ?? '') }}">
                </div>
                <div class="form-field">
                    <label for="mobile">رقم الجوال</label>
                    <input type="text" id="mobile" name="mobile" placeholder="05xxxxxxxx"
                           value="{{ old('mobile', $prefill['mobile'] ?? '') }}" required>
                    <span class="hint">مطلوب لدى Paylink لإصدار رابط الدفع.</span>
                </div>
            </div>

            <div class="subscribe-errors alert error hidden" role="alert"></div>
            <div class="subscribe-success alert success hidden" role="status"></div>

            <div class="form-actions">
                <button type="submit" class="btn primary">
                    {{ $renewal ? 'تجديد الاشتراك والدفع' : 'متابعة إلى صفحة الدفع' }}
                </button>
                <a href="{{ $marketingUrl }}" class="btn">العودة للموقع الرئيسي</a>
            </div>
        </form>
    </div>
@endif

@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-plan-option]').forEach(function (option) {
        option.addEventListener('click', function () {
            document.querySelectorAll('[data-plan-option]').forEach(function (el) {
                el.classList.remove('selected');
            });
            option.classList.add('selected');
            var input = option.querySelector('input[type="radio"]');
            if (input) input.checked = true;
        });
    });

    document.querySelectorAll('.subscribe-checkout-form').forEach(function (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            var errorsBox = form.querySelector('.subscribe-errors');
            var successBox = form.querySelector('.subscribe-success');
            errorsBox.classList.add('hidden');
            successBox.classList.add('hidden');
            errorsBox.textContent = '';
            successBox.textContent = '';

            try {
                var resp = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: new FormData(form)
                });
                var contentType = resp.headers.get('Content-Type') || '';
                if (!resp.ok) {
                    var message = '';
                    if (contentType.includes('application/json')) {
                        var j = await resp.json();
                        if (j.errors) {
                            message = Object.values(j.errors).flat().join('، ');
                        } else {
                            message = j.error || j.message || '';
                        }
                    } else {
                        message = await resp.text();
                    }
                    errorsBox.textContent = message || ('خطأ غير متوقع (الحالة ' + resp.status + ')');
                    errorsBox.classList.remove('hidden');
                    return;
                }
                var payload = contentType.includes('application/json') ? await resp.json() : {};
                successBox.textContent = payload.message || 'تم إنشاء الفاتورة بنجاح. سيتم تحويلك إلى Paylink.';
                successBox.classList.remove('hidden');
                if (payload.redirect_url) {
                    setTimeout(function () {
                        window.location.href = payload.redirect_url;
                    }, 800);
                }
            } catch (err) {
                errorsBox.textContent = (err && err.message) ? err.message : 'تعذر الاتصال بالخادم. حاول لاحقاً.';
                errorsBox.classList.remove('hidden');
            }
        });
    });
})();
</script>
@endpush
