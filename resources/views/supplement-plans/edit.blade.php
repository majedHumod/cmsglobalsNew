@extends('layouts.admin')

@section('title', 'تعديل: ' . $supplementPlan->name)

@section('header', 'تعديل مكمل غذائي')

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
            <h2 class="text-lg font-medium text-gray-900">{{ $supplementPlan->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">تعديل بيانات المكمل الغذائي.</p>
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

        <form method="POST" action="{{ route('supplement-plans.update', $supplementPlan) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        اسم المكمل <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $supplementPlan->name) }}" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        نوع المكمل <span class="text-red-500">*</span>
                    </label>
                    <select name="supplement_type" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach(['protein'=>'بروتين','vitamins'=>'فيتامينات','minerals'=>'معادن','pre_workout'=>'ما قبل التمرين','post_workout'=>'ما بعد التمرين','omega'=>'أوميغا','general'=>'عام'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('supplement_type', $supplementPlan->supplement_type)==$val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        توقيت الأخذ <span class="text-red-500">*</span>
                    </label>
                    <select name="timing" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach(['morning'=>'الصباح','pre_workout'=>'قبل التمرين','post_workout'=>'بعد التمرين','night'=>'قبل النوم','with_meal'=>'مع الوجبة'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('timing', $supplementPlan->timing)==$val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الجرعة</label>
                    <input type="text" name="dosage" value="{{ old('dosage', $supplementPlan->dosage) }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">العلامة التجارية</label>
                    <input type="text" name="brand" value="{{ old('brand', $supplementPlan->brand) }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $supplementPlan->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الاستخدام</label>
                    <textarea name="instructions" rows="3"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('instructions', $supplementPlan->instructions) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">تحذيرات وملاحظات</label>
                    <textarea name="warnings" rows="2"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('warnings', $supplementPlan->warnings) }}</textarea>
                </div>

                {{-- الصورة --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">صورة المكمل</label>
                    @if($supplementPlan->image)
                    <div class="mb-3 flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <img src="{{ Storage::url($supplementPlan->image) }}" alt="{{ $supplementPlan->name }}"
                             class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                        <div>
                            <p class="text-sm font-medium text-gray-700">الصورة الحالية</p>
                            <p class="text-xs text-gray-400 mt-1">ارفع صورة جديدة للاستبدال</p>
                        </div>
                    </div>
                    @endif
                    <input type="file" name="image" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — حجم أقصى 2MB</p>
                    @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الجمهور المستهدف</label>
                    <select name="audience_gender"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all"    @selected(old('audience_gender', $supplementPlan->audience_gender)=='all')>الجميع</option>
                        <option value="male"   @selected(old('audience_gender', $supplementPlan->audience_gender)=='male')>رجال فقط</option>
                        <option value="female" @selected(old('audience_gender', $supplementPlan->audience_gender)=='female')>نساء فقط</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الترتيب</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $supplementPlan->sort_order) }}" min="0"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                @if($membershipTypes->count())
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        العضويات المطلوبة
                        <span class="text-gray-400 font-normal text-xs">(اتركها فارغة للظهور للجميع)</span>
                    </label>
                    <div class="flex flex-wrap gap-4">
                        @foreach($membershipTypes as $type)
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="required_membership_types[]" value="{{ $type->id }}"
                                   @checked(in_array($type->id, old('required_membership_types', $supplementPlan->required_membership_types ?? [])))
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ $type->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               @checked(old('is_active', $supplementPlan->is_active))
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        نشط (مرئي للعملاء)
                    </label>
                </div>

            </div>

            <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                    تحديث المكمل
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
