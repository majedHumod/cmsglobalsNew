<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-5 text-sm leading-6 text-gray-600 text-right">
            نسيت كلمة المرور؟ لا مشكلة. أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.
        </div>

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600 text-right">
                {{ $value }}
            </div>
        @endsession

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <x-label for="email" value="البريد الإلكتروني" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div>
                <x-button class="btn-brand w-full justify-center border-transparent hover:opacity-95">
                    إرسال رابط إعادة التعيين
                </x-button>
            </div>

            <p class="text-center text-sm text-gray-600">
                تذكرت كلمة المرور؟
                <a href="{{ route('login') }}" class="link-brand font-medium focus:outline-none focus:underline">
                    تسجيل الدخول
                </a>
            </p>
        </form>
    </x-authentication-card>
</x-guest-layout>
