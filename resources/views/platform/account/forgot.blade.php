<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="text-xl font-extrabold text-center mb-1">استعادة كلمة المرور</h1>
        <p class="text-sm text-gray-600 text-center mb-5">سنرسل الرابط إلى بريد مالك النادي، وسيفتح على موقع السب-دومين.</p>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">{{ $value }}</div>
        @endsession

        <form method="POST" action="{{ route('platform.account.forgot.store') }}" class="space-y-5">
            @csrf
            <div>
                <x-label for="email" value="البريد الإلكتروني" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>
            <x-button class="w-full justify-center">إرسال رابط الاستعادة</x-button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-5">
            <a href="{{ $loginUrl }}" class="font-medium text-teal-700">العودة لتسجيل الدخول</a>
        </p>
    </x-authentication-card>
</x-guest-layout>
