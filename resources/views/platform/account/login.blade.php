<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="text-xl font-extrabold text-center mb-1">دخول المدرب / النادي</h1>
        <p class="text-sm text-gray-600 text-center mb-5">استخدم بريد الاشتراك وكلمة مرور حسابك على سب-دومين ناديك.</p>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">{{ $value }}</div>
        @endsession

        <form method="POST" action="{{ route('platform.account.login.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="redirect" value="{{ $redirect }}">

            <div>
                <x-label for="email" value="البريد الإلكتروني" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div>
                <div class="flex items-center justify-between gap-3">
                    <x-label for="password" value="كلمة المرور" />
                    <a href="{{ route('platform.account.forgot') }}" class="text-sm font-medium text-teal-700">نسيت كلمة المرور؟</a>
                </div>
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <x-button class="w-full justify-center">تسجيل الدخول</x-button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-5">
            ليس لديك مركز بعد؟
            <a href="{{ $subscribeUrl }}" class="font-medium text-teal-700">اشترك الآن</a>
        </p>
        <p class="text-center text-xs text-gray-500 mt-3">
            متدربو النادي يسجّلون الدخول من رابط النادي مباشرة، وليس من هذه الصفحة.
        </p>
    </x-authentication-card>
</x-guest-layout>
