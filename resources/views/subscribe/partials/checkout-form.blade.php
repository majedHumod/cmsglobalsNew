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

        <form action="{{ route('billing.checkout.session') }}" method="POST" class="subscribe-checkout-form"
              data-email-code-url="{{ route('subscribe.email-code') }}"
              data-email-verify-url="{{ route('subscribe.email-verify') }}">
            @csrf
            <input type="hidden" name="intent" value="{{ $renewal ? 'renew' : 'new' }}">
            {{-- Honeypot: invisible to people, bots fill it in. --}}
            <div style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden" aria-hidden="true">
                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off" value=""></label>
            </div>

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
                           autocomplete="off" spellcheck="false" inputmode="lowercase"
                           required @if($renewal) readonly @endif
                           @if(!$renewal) data-subdomain-check="{{ route('subscribe.check-subdomain') }}" @endif>
                    <span class="hint">
                        @if($renewal)
                            لا يمكن تغيير سب-دومين النادي الحالي عند التجديد.
                        @else
                            أحرف صغيرة وأرقام وشرطة (-) فقط. مثال: {{ $prefill['subdomain'] ?: 'example' }}.{{ $domain }}
                        @endif
                    </span>
                    @if(!$renewal)
                        <span class="field-status checking hidden" id="subdomain-status" aria-live="polite"></span>
                    @endif
                </div>
                <div class="form-field">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com"
                           value="{{ old('email', $prefill['email'] ?? '') }}"
                           required @if($renewal) readonly @endif>
                </div>
                @if(! $renewal && ($verifyEmail ?? false))
                    <div class="form-field" id="email-verify-box" style="grid-column:1/-1">
                        <label for="email_code">رمز التحقق من البريد</label>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                            <input type="text" id="email_code" inputmode="numeric" maxlength="6" autocomplete="one-time-code"
                                   placeholder="6 أرقام" style="flex:1;min-width:140px">
                            <button type="button" class="btn" id="email-code-send">إرسال الرمز</button>
                            <button type="button" class="btn primary" id="email-code-verify">تأكيد</button>
                        </div>
                        <span class="hint" id="email-verify-status" aria-live="polite">سنرسل رمزاً من 6 أرقام إلى بريدك للتأكد من ملكيته قبل الدفع.</span>
                    </div>
                @endif
                <div class="form-field">
                    <label for="name">الاسم (اختياري)</label>
                    <input type="text" id="name" name="name" placeholder="اسم المالك/المسؤول"
                           value="{{ old('name', $prefill['name'] ?? '') }}">
                </div>
                <div class="form-field">
                    <label for="mobile">رقم الجوال</label>
                    <input type="text" id="mobile" name="mobile" placeholder="05xxxxxxxx"
                           value="{{ old('mobile', $prefill['mobile'] ?? '') }}" required>
                    <span class="hint">مطلوب لدى Paylink لإصدار رابط الدفع. مثال: 05xxxxxxxx</span>
                </div>
            </div>

            @if(! $renewal && ! empty($turnstileSiteKey))
                <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" style="margin-top:14px"></div>
            @endif

            @if(! $renewal)
                <p class="note" style="margin-top:14px">
                    بعد إتمام الدفع سنجهّز نادياً خاصاً بك تلقائياً، وسيصلك <strong>بريد إلكتروني ترحيبي</strong>
                    فيه رابط ناديك ورابط تعيين كلمة المرور وتفاصيل خطتك.
                </p>
            @endif

            <div class="subscribe-errors alert error hidden" role="alert"></div>
            <div class="subscribe-success alert success hidden" role="status"></div>

            <div class="form-actions">
                <button type="submit" class="btn primary lg" id="subscribe-submit">
                    {{ $renewal ? 'تجديد الاشتراك والدفع' : 'متابعة إلى صفحة الدفع' }}
                </button>
                <a href="{{ $marketingUrl }}" class="btn">العودة للموقع الرئيسي</a>
            </div>
        </form>
    </div>
@endif

@push('scripts')
@if(! $renewal && ! empty($turnstileSiteKey))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
<script>
(function () {
    var subdomainState = { available: null, checking: false, slug: '' };

    function setupEmailVerification(form) {
        var box = form.querySelector('#email-verify-box');
        var state = { required: !!box, verified: false, email: '' };
        if (!box) return state;

        var emailInput = form.querySelector('#email');
        var codeInput = form.querySelector('#email_code');
        var sendBtn = form.querySelector('#email-code-send');
        var verifyBtn = form.querySelector('#email-code-verify');
        var statusEl = form.querySelector('#email-verify-status');
        var csrf = (form.querySelector('input[name="_token"]') || {}).value || '';

        function say(text, kind) {
            statusEl.textContent = text;
            statusEl.style.color = kind === 'error' ? '#b42318' : (kind === 'ok' ? '#067647' : '');
        }

        function post(url, fields) {
            var body = new FormData();
            body.append('_token', csrf);
            Object.keys(fields).forEach(function (k) { body.append(k, fields[k]); });
            return fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: body
            }).then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (json) {
                    return { ok: res.ok, status: res.status, json: json };
                });
            });
        }

        function errorText(r) {
            if (r.status === 429) return 'محاولات كثيرة. انتظر قليلاً ثم أعد المحاولة.';
            if (r.json && r.json.errors) return Object.values(r.json.errors).flat().join('، ');
            return (r.json && (r.json.error || r.json.message)) || 'تعذر تنفيذ الطلب.';
        }

        emailInput.addEventListener('input', function () {
            if (state.verified && emailInput.value.trim().toLowerCase() !== state.email) {
                state.verified = false;
                say('تم تغيير البريد. أعد التحقق منه.', 'error');
            }
        });

        sendBtn.addEventListener('click', function () {
            var email = emailInput.value.trim();
            if (!email) { say('أدخل بريدك الإلكتروني أولاً.', 'error'); return; }
            var tokenInput = form.querySelector('input[name="cf-turnstile-response"]');
            sendBtn.disabled = true;
            say('جاري إرسال الرمز...', '');
            post(form.getAttribute('data-email-code-url'), {
                email: email,
                website: (form.querySelector('input[name="website"]') || {}).value || '',
                'cf-turnstile-response': tokenInput ? tokenInput.value : ''
            }).then(function (r) {
                if (r.ok) {
                    say(r.json.message || 'تم إرسال الرمز.', 'ok');
                    codeInput.focus();
                    setTimeout(function () { sendBtn.disabled = false; }, 30000);
                } else {
                    sendBtn.disabled = false;
                    say(errorText(r), 'error');
                    if (r.json && r.json.login_url) {
                        var a = document.createElement('a');
                        a.href = r.json.login_url;
                        a.textContent = ' تسجيل الدخول';
                        statusEl.appendChild(a);
                    }
                }
                if (window.turnstile) { try { window.turnstile.reset(); } catch (e) {} }
            }).catch(function () {
                sendBtn.disabled = false;
                say('تعذر الاتصال بالخادم. حاول لاحقاً.', 'error');
            });
        });

        verifyBtn.addEventListener('click', function () {
            var email = emailInput.value.trim();
            var code = codeInput.value.replace(/\D/g, '');
            if (!email || code.length !== 6) { say('أدخل الرمز المكوّن من 6 أرقام.', 'error'); return; }
            verifyBtn.disabled = true;
            post(form.getAttribute('data-email-verify-url'), { email: email, code: code }).then(function (r) {
                verifyBtn.disabled = false;
                if (r.ok && r.json.verified) {
                    state.verified = true;
                    state.email = email.toLowerCase();
                    say('تم التحقق من بريدك الإلكتروني ✓', 'ok');
                } else {
                    state.verified = false;
                    say(errorText(r), 'error');
                }
            }).catch(function () {
                verifyBtn.disabled = false;
                say('تعذر الاتصال بالخادم. حاول لاحقاً.', 'error');
            });
        });

        return state;
    }

    function debounce(fn, ms) {
        var timer;
        return function () {
            var args = arguments;
            var ctx = this;
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(ctx, args); }, ms);
        };
    }

    function normalizeSlug(value) {
        return String(value || '').toLowerCase().replace(/[^a-z0-9-]/g, '');
    }

    function setupSubdomainCheck(input) {
        var statusEl = document.getElementById('subdomain-status');
        var submitBtn = document.getElementById('subscribe-submit');
        var checkUrl = input.getAttribute('data-subdomain-check');
        if (!checkUrl || !statusEl) return;

        function setStatus(state, message) {
            statusEl.classList.remove('hidden', 'checking', 'available', 'taken', 'invalid', 'reserved', 'empty');
            statusEl.classList.add(state || 'checking');
            statusEl.textContent = message || '';
            input.classList.remove('subdomain-valid', 'subdomain-invalid');
            if (state === 'available') input.classList.add('subdomain-valid');
            if (state === 'taken' || state === 'invalid' || state === 'reserved') input.classList.add('subdomain-invalid');
            if (submitBtn) submitBtn.disabled = state !== 'available';
        }

        var runCheck = debounce(function () {
            var slug = normalizeSlug(input.value);
            if (input.value !== slug) input.value = slug;
            if (!slug) {
                subdomainState = { available: false, checking: false, slug: '' };
                setStatus('empty', '');
                if (submitBtn) submitBtn.disabled = false;
                return;
            }
            subdomainState.checking = true;
            setStatus('checking', 'جاري التحقق من توفر السب-دومين...');
            fetch(checkUrl + '?subdomain=' + encodeURIComponent(slug), { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    subdomainState = {
                        available: !!data.available,
                        checking: false,
                        slug: data.subdomain || slug
                    };
                    setStatus(data.state || (data.available ? 'available' : 'invalid'), data.message || '');
                })
                .catch(function () {
                    subdomainState = { available: null, checking: false, slug: slug };
                    setStatus('invalid', 'تعذر التحقق الآن. حاول مرة أخرى.');
                    if (submitBtn) submitBtn.disabled = false;
                });
        }, 450);

        input.addEventListener('input', runCheck);
        input.addEventListener('blur', runCheck);
        if (input.value.trim()) runCheck();
    }

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
        var subdomainInput = form.querySelector('#subdomain[data-subdomain-check]');
        if (subdomainInput) setupSubdomainCheck(subdomainInput);
        var emailState = setupEmailVerification(form);

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            var errorsBox = form.querySelector('.subscribe-errors');
            var successBox = form.querySelector('.subscribe-success');
            errorsBox.classList.add('hidden');
            successBox.classList.add('hidden');
            errorsBox.textContent = '';
            successBox.textContent = '';

            if (subdomainInput && subdomainState.slug && subdomainState.available === false) {
                errorsBox.textContent = 'السب-دومين غير متاح. اختر اسماً آخر قبل المتابعة.';
                errorsBox.classList.remove('hidden');
                return;
            }

            if (emailState.required && !emailState.verified) {
                errorsBox.textContent = 'تحقق من بريدك الإلكتروني أولاً: اضغط «إرسال الرمز» ثم أدخل الرمز واضغط «تأكيد».';
                errorsBox.classList.remove('hidden');
                return;
            }

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
                    var loginUrl = '';
                    if (resp.status === 429) {
                        message = 'محاولات كثيرة. انتظر قليلاً ثم أعد المحاولة.';
                    } else if (contentType.includes('application/json')) {
                        var j = await resp.json();
                        if (j.errors) {
                            message = Object.values(j.errors).flat().join('، ');
                        } else {
                            message = j.error || j.message || '';
                        }
                        loginUrl = j.login_url || '';
                    } else {
                        message = await resp.text();
                    }
                    errorsBox.textContent = message || ('حدث خطأ غير متوقع (رمز ' + resp.status + ')');
                    if (loginUrl) {
                        var link = document.createElement('a');
                        link.href = loginUrl;
                        link.textContent = ' تسجيل الدخول';
                        errorsBox.appendChild(link);
                    }
                    errorsBox.classList.remove('hidden');
                    if (window.turnstile) { try { window.turnstile.reset(); } catch (err) {} }
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
                errorsBox.textContent = 'تعذر الاتصال بالخادم. حاول لاحقاً.';
                errorsBox.classList.remove('hidden');
            }
        });
    });
})();
</script>
@endpush
