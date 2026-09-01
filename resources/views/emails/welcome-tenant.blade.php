@component('mail::message')
# مرحباً {{ $contactName ?? $tenantName }}

تم إنشاء منصة **{{ $tenantName }}** بنجاح. يمكنك الآن إدارة ناديك أو برنامجك التدريبي من لوحة التحكم.

**رابط منصتك:** [{{ $tenantDomainUrl }}]({{ $tenantDomainUrl }})

@if($plan)
**الخطة:** {{ $plan['name'] ?? $plan['code'] ?? '—' }}@if(!empty($plan['interval'])) ({{ $plan['interval'] === 'yearly' ? 'سنوي' : 'شهري' }})@endif
@endif

@if($passwordResetUrl)
لإنشاء كلمة مرورك الأولى، اضغط الزر أدناه (صالح لفترة محدودة):

@component('mail::button', ['url' => $passwordResetUrl])
تعيين كلمة المرور
@endcomponent
@endif

**البريد المسجل:** {{ $contactEmail }}

مع التحية،<br>
{{ $tenantName }}
@endcomponent
