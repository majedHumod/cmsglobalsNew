<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <x-label for="name" value="الاسم الكامل" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div>
                <x-label for="email" value="البريد الإلكتروني" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div>
                <x-label for="gender" value="الجنس" />
                <select
                    id="gender"
                    name="gender"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="" disabled {{ old('gender') ? '' : 'selected' }}>اختر الجنس</option>
                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>رجال</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>نساء</option>
                </select>
            </div>

            <div>
                <x-label for="password" value="كلمة المرور" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div>
                <x-label for="password_confirmation" value="تأكيد كلمة المرور" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div>
                    <label for="terms" class="inline-flex items-start gap-2">
                        <x-checkbox name="terms" id="terms" required class="mt-1" />
                        <span class="text-sm text-gray-600 text-right">
                            أوافق على
                            <a target="_blank" href="{{ route('terms.show') }}" class="link-brand font-medium focus:outline-none focus:underline">شروط الخدمة</a>
                            و
                            <a target="_blank" href="{{ route('policy.show') }}" class="link-brand font-medium focus:outline-none focus:underline">سياسة الخصوصية</a>
                        </span>
                    </label>
                </div>
            @endif

            <div>
                <x-button class="btn-brand w-full justify-center border-transparent hover:opacity-95">
                    إنشاء حساب
                </x-button>
            </div>

            <p class="text-center text-sm text-gray-600">
                لديك حساب بالفعل؟
                <a href="{{ route('login') }}" class="link-brand font-medium focus:outline-none focus:underline">
                    تسجيل الدخول
                </a>
            </p>
        </form>
    </x-authentication-card>
</x-guest-layout>
