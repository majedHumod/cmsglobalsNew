@component('mail::message')
# دعوة للانضمام إلى الفريق

@if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
إذا لم يكن لديك حساب، يمكنك إنشاء حساب من الزر أدناه. بعد إنشاء الحساب، استخدم زر قبول الدعوة في هذا البريد:

@component('mail::button', ['url' => route('register')])
إنشاء حساب
@endcomponent
@endif

@if ($team->hasUserWithEmail($email))
تمت دعوتك للانضمام إلى فريق **{{ $team->name }}** على {{ config('app.name') }}.
@else
تمت دعوتك للانضمام إلى فريق **{{ $team->name }}** على {{ config('app.name') }}.
@endif

@component('mail::button', ['url' => $acceptUrl])
قبول الدعوة
@endcomponent

إذا لم تكن تتوقع هذه الدعوة، يمكنك تجاهل هذا البريد.

مع التحية،<br>
{{ config('app.name') }}
@endcomponent
