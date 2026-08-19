<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-label for="email" value="البريد الإلكتروني" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div>
                <div class="flex items-center justify-between gap-3" dir="rtl">
                    <x-label for="password" value="كلمة المرور" class="!inline-block" />
                    @if (Route::has('password.request'))
                        <a
                            href="{{ route('password.request') }}"
                            class="link-brand shrink-0 text-sm font-medium focus:outline-none focus:underline"
                        >
                            نسيت كلمة المرور؟
                        </a>
                    @endif
                </div>
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div>
                <label for="remember_me" class="inline-flex items-center gap-2">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="text-sm text-gray-600">تذكرني</span>
                </label>
            </div>

            <div>
                <x-button class="btn-brand w-full justify-center border-transparent hover:opacity-95">
                    تسجيل الدخول
                </x-button>
            </div>

            @if (Route::has('register'))
                <p class="text-center text-sm text-gray-600">
                    ليس لديك حساب؟
                    <a href="{{ route('register') }}" class="link-brand font-medium focus:outline-none focus:underline">
                        إنشاء حساب
                    </a>
                </p>
            @endif
        </form>
    </x-authentication-card>
</x-guest-layout>
