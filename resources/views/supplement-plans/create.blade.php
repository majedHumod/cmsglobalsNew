@extends('layouts.admin')

@section('title', 'إضافة مكمل غذائي')

@section('header', 'إضافة مكمل غذائي')

@section('header_actions')
<a href="{{ route('supplement-plans.index') }}"
   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    العودة للقائمة
</a>
@endsection

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">بيانات المكمل الغذائي</h2>
            <p class="mt-1 text-sm text-gray-500">أدخل تفاصيل المكمل الغذائي وطريقة استخدامه.</p>
        </div>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <strong class="font-bold">خطأ في البيانات!</strong>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('supplement-plans.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- الاسم --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        اسم المكمل <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- النوع --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        نوع المكمل <span class="text-red-500">*</span>
                    </label>
                    <select name="supplement_type" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="protein"      @selected(old('supplement_type')=='protein')>بروتين</option>
                        <option value="vitamins"     @selected(old('supplement_type')=='vitamins')>فيتامينات</option>
                        <option value="minerals"     @selected(old('supplement_type')=='minerals')>معادن</option>
                        <option value="pre_workout"  @selected(old('supplement_type')=='pre_workout')>ما قبل التمرين</option>
                        <option value="post_workout" @selected(old('supplement_type')=='post_workout')>ما بعد التمرين</option>
                        <option value="omega"        @selected(old('supplement_type')=='omega')>أوميغا</option>
                        <option value="general"      @selected(old('supplement_type','general')=='general')>عام</option>
                    </select>
                </div>

                {{-- التوقيت --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        توقيت الأخذ <span class="text-red-500">*</span>
                    </label>
                    <select name="timing" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="morning"      @selected(old('timing','morning')=='morning')>الصباح</option>
                        <option value="pre_workout"  @selected(old('timing')=='pre_workout')>قبل التمرين</option>
                        <option value="post_workout" @selected(old('timing')=='post_workout')>بعد التمرين</option>
                        <option value="night"        @selected(old('timing')=='night')>قبل النوم</option>
                        <option value="with_meal"    @selected(old('timing')=='with_meal')>مع الوجبة</option>
                    </select>
                </div>

                {{-- الجرعة --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الجرعة</label>
                    <input type="text" name="dosage" value="{{ old('dosage') }}" placeholder="مثال: 30 غرام"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- العلامة التجارية --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">العلامة التجارية</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" placeholder="مثال: Optimum Nutrition"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- الوصف --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                </div>

                {{-- التعليمات --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الاستخدام</label>
                    <textarea name="instructions" rows="3"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('instructions') }}</textarea>
                </div>

                {{-- التحذيرات --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">تحذيرات وملاحظات</label>
                    <textarea name="warnings" rows="2"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('warnings') }}</textarea>
                </div>

                {{-- الصورة --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">صورة المكمل</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="text-sm text-gray-600">
                                <label for="image-upload" class="relative cursor-pointer text-indigo-600 hover:text-indigo-500 font-medium">
                                    <span>اختر صورة</span>
                                    <input id="image-upload" name="image" type="file" accept="image/*" class="sr-only">
                                </label>
                                <span class="text-gray-400"> أو اسحب وأفلت</span>
                            </div>
                            <p class="text-xs text-gray-500">JPG, PNG, WebP — حجم أقصى 2MB</p>
                        </div>
                    </div>
                    @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- الجمهور المستهدف --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الجمهور المستهدف</label>
                    <select name="audience_gender"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all"    @selected(old('audience_gender','all')=='all')>الجميع</option>
                        <option value="male"   @selected(old('audience_gender')=='male')>رجال فقط</option>
                        <option value="female" @selected(old('audience_gender')=='female')>نساء فقط</option>
                    </select>
                </div>

                {{-- الترتيب --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الترتيب</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- أنواع العضوية --}}
                @if($membershipTypes->count())
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        العضويات المطلوبة
                        <span class="text-gray-400 font-normal text-xs">(اتركها فارغة للظهور للجميع)</span>
                    </label>
                    <div class="flex flex-wrap gap-4">
                        @foreach($membershipTypes as $type)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="required_membership_types[]" value="{{ $type->id }}"
                                   @checked(in_array($type->id, old('required_membership_types', [])))
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ $type->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- نشط --}}
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        نشط (مرئي للعملاء)
                    </label>
                </div>

            </div>

            <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                    حفظ المكمل
                </button>
                <a href="{{ route('supplement-plans.index') }}"
                   class="px-6 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-md shadow-sm text-sm">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
