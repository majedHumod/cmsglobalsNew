@extends('layouts.admin')

@section('title', 'إعدادات الموقع')

@section('header', 'إعدادات الموقع')

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">إعدادات الموقع</h2>
            <p class="mt-1 text-sm text-gray-500">قم بتخصيص إعدادات موقعك من هنا.</p>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <ul class="flex flex-wrap -mb-px" id="settingsTabs" role="tablist">
                <li class="ml-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-indigo-600 rounded-t-lg text-indigo-600 active" id="general-tab" data-tabs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                        <svg class="w-5 h-5 ml-2 inline-block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                        </svg>
                        عام
                    </button>
                </li>
                <li class="ml-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="contact-tab" data-tabs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
                        <svg class="w-5 h-5 ml-2 inline-block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                        </svg>
                        معلومات الاتصال
                    </button>
                </li>
                <li class="ml-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="social-tab" data-tabs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false">
                        <svg class="w-5 h-5 ml-2 inline-block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"></path>
                        </svg>
                        التواصل الاجتماعي
                    </button>
                </li>
                <li role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="app-tab" data-tabs-target="#app" type="button" role="tab" aria-controls="app" aria-selected="false">
                        <svg class="w-5 h-5 ml-2 inline-block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        إعدادات التطبيق
                    </button>
                </li>
                <li role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="homepage-tab" data-tabs-target="#homepage" type="button" role="tab" aria-controls="homepage" aria-selected="false">
                        <svg class="w-5 h-5 ml-2 inline-block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        إعدادات الصفحة الرئيسية
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div id="settingsTabContent">
            <!-- General Settings -->
            <div class="block" id="general" role="tabpanel" aria-labelledby="general-tab">
                <form action="{{ route('admin.settings.update-general') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Site Name -->
                        <div>
                            <label for="site_name" class="block text-sm font-medium text-gray-700 text-right">اسم الموقع</label>
                            <input type="text" name="site_name" id="site_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right" value="{{ old('site_name', \App\Models\SiteSetting::get('site_name', config('app.name'))) }}">
                            <p class="mt-1 text-sm text-gray-500 text-right">اسم موقعك الذي سيظهر في العنوان والهيدر.</p>
                        </div>
                        
                        <!-- Site Description -->
                        <div>
                            <label for="site_description" class="block text-sm font-medium text-gray-700 text-right">وصف الموقع</label>
                            <input type="text" name="site_description" id="site_description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right" value="{{ old('site_description', \App\Models\SiteSetting::get('site_description')) }}">
                            <p class="mt-1 text-sm text-gray-500 text-right">وصف قصير للموقع يظهر في محركات البحث.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Site Logo -->
                        <div>
                            <label for="site_logo" class="block text-sm font-medium text-gray-700 text-right">شعار الموقع</label>
                            @if(\App\Models\SiteSetting::get('site_logo'))
                                <div class="mt-2 mb-4 text-right">
                                    <img src="{{ Storage::url(\App\Models\SiteSetting::get('site_logo')) }}" alt="Site Logo" class="h-16 object-contain">
                                    <p class="mt-1 text-xs text-gray-500">الشعار الحالي</p>
                                </div>
                            @endif
                            <input type="file" name="site_logo" id="site_logo" class="mt-1 block w-full text-sm text-gray-500 file:ml-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-sm text-gray-500 text-right">يفضل أن يكون الشعار بصيغة PNG أو SVG بخلفية شفافة.</p>
                        </div>
                        
                        <!-- Site Favicon -->
                        <div>
                            <label for="site_favicon" class="block text-sm font-medium text-gray-700 text-right">أيقونة التبويب (Favicon)</label>
                            @if(\App\Models\SiteSetting::get('site_favicon'))
                                <div class="mt-2 mb-4 text-right">
                                    <img src="{{ Storage::url(\App\Models\SiteSetting::get('site_favicon')) }}" alt="Site Favicon" class="h-8 w-8 object-contain">
                                    <p class="mt-1 text-xs text-gray-500">الأيقونة الحالية</p>
                                </div>
                            @endif
                            <input type="file" name="site_favicon" id="site_favicon" class="mt-1 block w-full text-sm text-gray-500 file:ml-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-sm text-gray-500 text-right">يفضل أن تكون الأيقونة مربعة بأبعاد 32×32 أو 64×64 بكسل.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Primary Color -->
                        <div>
                            <label for="primary_color" class="block text-sm font-medium text-gray-700 text-right">اللون الرئيسي</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" name="primary_color_text" id="primary_color_text" class="flex-1 rounded-r-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right" value="{{ old('primary_color', \App\Models\SiteSetting::get('primary_color', '#6366f1')) }}" readonly>
                                <input type="color" name="primary_color" id="primary_color" class="h-10 w-10 border-gray-300 rounded-l-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('primary_color', \App\Models\SiteSetting::get('primary_color', '#6366f1')) }}">
                            </div>
                            <p class="mt-1 text-sm text-gray-500 text-right">اللون الرئيسي للموقع (الأزرار، الروابط، إلخ).</p>
                        </div>
                        
                        <!-- Secondary Color -->
                        <div>
                            <label for="secondary_color" class="block text-sm font-medium text-gray-700 text-right">اللون الثانوي</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" name="secondary_color_text" id="secondary_color_text" class="flex-1 rounded-r-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right" value="{{ old('secondary_color', \App\Models\SiteSetting::get('secondary_color', '#10b981')) }}" readonly>
                                <input type="color" name="secondary_color" id="secondary_color" class="h-10 w-10 border-gray-300 rounded-l-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('secondary_color', \App\Models\SiteSetting::get('secondary_color', '#10b981')) }}">
                            </div>
                            <p class="mt-1 text-sm text-gray-500 text-right">اللون الثانوي للموقع (العناصر المساعدة).</p>
                        </div>
                    </div>
                    
                    <!-- Footer Text -->
                    <div>
                        <label for="footer_text" class="block text-sm font-medium text-gray-700 text-right">نص التذييل</label>
                        <textarea name="footer_text" id="footer_text" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right">{{ old('footer_text', \App\Models\SiteSetting::get('footer_text', '© ' . date('Y') . ' ' . config('app.name') . '. جميع الحقوق محفوظة.')) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500 text-right">النص الذي سيظهر في تذييل الصفحة.</p>
                    </div>
                    
                    <div class="flex justify-start">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            حفظ الإعدادات العامة
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Contact Settings -->
            <div class="hidden" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                <form action="{{ route('admin.settings.update-contact') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Contact Email -->
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 text-right">البريد الإلكتروني</label>
                            <input type="email" name="contact_email" id="contact_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right" value="{{ old('contact_email', \App\Models\SiteSetting::get('contact_email')) }}">
                            <p class="mt-1 text-sm text-gray-500 text-right">البريد الإلكتروني الرئيسي للاتصال.</p>
                        </div>
                        
                        <!-- Contact Phone -->
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700 text-right">رقم الجوال</label>
                            <input type="text" name="contact_phone" id="contact_phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('contact_phone', \App\Models\SiteSetting::get('contact_phone', '+966541221765')) }}" dir="ltr">
                            <p class="mt-1 text-sm text-gray-500 text-right">رقم الجوال بالصيغة الدولية (مثال: +966541221765).</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- WhatsApp Number -->
                        <div>
                            <label for="contact_whatsapp" class="block text-sm font-medium text-gray-700 text-right">رقم واتساب</label>
                            <input type="text" name="contact_whatsapp" id="contact_whatsapp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('contact_whatsapp', \App\Models\SiteSetting::get('contact_whatsapp')) }}" dir="ltr">
                            <p class="mt-1 text-sm text-gray-500 text-right">رقم واتساب بالصيغة الدولية (مثال: +966541221765).</p>
                        </div>
                        
                        <!-- Telegram Username -->
                        <div>
                            <label for="contact_telegram" class="block text-sm font-medium text-gray-700 text-right">معرف تليجرام</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" name="contact_telegram" id="contact_telegram" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('contact_telegram', \App\Models\SiteSetting::get('contact_telegram')) }}" dir="ltr">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">@</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 text-right">معرف تليجرام بدون علامة @ (مثال: cmsglobal).</p>
                        </div>
                    </div>
                    
                    <!-- Physical Address -->
                    <div>
                        <label for="contact_address" class="block text-sm font-medium text-gray-700 text-right">العنوان الفعلي</label>
                        <textarea name="contact_address" id="contact_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right">{{ old('contact_address', \App\Models\SiteSetting::get('contact_address')) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500 text-right">العنوان الفعلي للشركة أو المؤسسة.</p>
                    </div>
                    
                    <!-- Google Maps Link -->
                    <div>
                        <label for="contact_map_link" class="block text-sm font-medium text-gray-700 text-right">رابط خرائط جوجل</label>
                        <input type="url" name="contact_map_link" id="contact_map_link" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('contact_map_link', \App\Models\SiteSetting::get('contact_map_link')) }}" dir="ltr">
                        <p class="mt-1 text-sm text-gray-500 text-right">رابط موقعك على خرائط جوجل.</p>
                    </div>
                    
                    <div class="flex justify-start">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            حفظ معلومات الاتصال
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Social Media Settings -->
            <div class="hidden" id="social" role="tabpanel" aria-labelledby="social-tab">
                <form action="{{ route('admin.settings.update-social') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Facebook -->
                        <div>
                            <label for="social_facebook" class="block text-sm font-medium text-gray-700 text-right">فيسبوك</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="url" name="social_facebook" id="social_facebook" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('social_facebook', \App\Models\SiteSetting::get('social_facebook')) }}" dir="ltr">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 text-right">رابط صفحة الفيسبوك.</p>
                        </div>
                        
                        <!-- Twitter -->
                        <div>
                            <label for="social_twitter" class="block text-sm font-medium text-gray-700 text-right">تويتر</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="url" name="social_twitter" id="social_twitter" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('social_twitter', \App\Models\SiteSetting::get('social_twitter')) }}" dir="ltr">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 text-right">رابط حساب تويتر.</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-start">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            حفظ روابط التواصل
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- App Settings -->
            <div class="hidden" id="app" role="tabpanel" aria-labelledby="app-tab">
                <form action="{{ route('admin.settings.update-app') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Maintenance Mode -->
                        <div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="maintenance_mode" id="maintenance_mode" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ \App\Models\SiteSetting::get('maintenance_mode') ? 'checked' : '' }}>
                                </div>
                                <div class="mr-3 text-sm">
                                    <label for="maintenance_mode" class="font-medium text-gray-700">وضع الصيانة</label>
                                    <p class="text-gray-500">تفعيل وضع الصيانة لإغلاق الموقع مؤقتاً.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Enable Registration -->
                        <div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="enable_registration" id="enable_registration" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ \App\Models\SiteSetting::get('enable_registration', true) ? 'checked' : '' }}>
                                </div>
                                <div class="mr-3 text-sm">
                                    <label for="enable_registration" class="font-medium text-gray-700">تفعيل التسجيل</label>
                                    <p class="text-gray-500">السماح للمستخدمين بإنشاء حسابات جديدة.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-start">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            حفظ إعدادات التطبيق
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Homepage Settings -->
            <div class="hidden" id="homepage" role="tabpanel" aria-labelledby="homepage-tab">
                <form action="{{ route('admin.settings.update-homepage') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <!-- Training Sessions Settings -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">إعدادات جلسات التدريب الخاصة</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Training Sessions Title -->
                            <div>
                                <label for="training_sessions_title" class="block text-sm font-medium text-gray-700 text-right">عنوان القسم</label>
                                <input type="text" name="training_sessions_title" id="training_sessions_title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right" value="{{ old('training_sessions_title', \App\Models\SiteSetting::get('training_sessions_title', 'مدربونا الخبراء')) }}">
                                <p class="mt-1 text-sm text-gray-500 text-right">عنوان قسم جلسات التدريب في الصفحة الرئيسية.</p>
                            </div>
                            
                            <!-- Number of Sessions -->
                            <div>
                                <label for="training_sessions_count" class="block text-sm font-medium text-gray-700 text-right">عدد الجلسات المعروضة</label>
                                <select name="training_sessions_count" id="training_sessions_count" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="2" {{ \App\Models\SiteSetting::get('training_sessions_count', 4) == 2 ? 'selected' : '' }}>2 جلسات</option>
                                    <option value="3" {{ \App\Models\SiteSetting::get('training_sessions_count', 4) == 3 ? 'selected' : '' }}>3 جلسات</option>
                                    <option value="4" {{ \App\Models\SiteSetting::get('training_sessions_count', 4) == 4 ? 'selected' : '' }}>4 جلسات</option>
                                    <option value="6" {{ \App\Models\SiteSetting::get('training_sessions_count', 4) == 6 ? 'selected' : '' }}>6 جلسات</option>
                                    <option value="8" {{ \App\Models\SiteSetting::get('training_sessions_count', 4) == 8 ? 'selected' : '' }}>8 جلسات</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500 text-right">عدد جلسات التدريب المعروضة في الصفحة الرئيسية.</p>
                            </div>
                        </div>
                        
                        <!-- Training Sessions Description -->
                        <div class="mt-6">
                            <label for="training_sessions_description" class="block text-sm font-medium text-gray-700 text-right">وصف القسم</label>
                            <textarea name="training_sessions_description" id="training_sessions_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right">{{ old('training_sessions_description', \App\Models\SiteSetting::get('training_sessions_description', 'تعرف على مدربينا المعتمدين المتخصصين في إرشادك خلال رحلتك مع الدعم الشخصي والتعليمات الواعية وممارسات العافية الشاملة')) }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 text-right">وصف قسم جلسات التدريب في الصفحة الرئيسية.</p>
                        </div>
                        
                        <!-- Enable Training Sessions -->
                        <div class="mt-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="training_sessions_enabled" id="training_sessions_enabled" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ \App\Models\SiteSetting::get('training_sessions_enabled', true) ? 'checked' : '' }}>
                                </div>
                                <div class="mr-3 text-sm">
                                    <label for="training_sessions_enabled" class="font-medium text-gray-700">تفعيل قسم جلسات التدريب</label>
                                    <p class="text-gray-500">إظهار قسم جلسات التدريب في الصفحة الرئيسية.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonials Settings -->
                    <div class="py-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">إعدادات قصص النجاح</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Testimonials Title -->
                            <div>
                                <label for="testimonials_title" class="block text-sm font-medium text-gray-700 text-right">عنوان القسم</label>
                                <input type="text" name="testimonials_title" id="testimonials_title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right" value="{{ old('testimonials_title', \App\Models\SiteSetting::get('testimonials_title', 'ماذا يقول عملاؤنا')) }}">
                                <p class="mt-1 text-sm text-gray-500 text-right">عنوان قسم قصص النجاح في الصفحة الرئيسية.</p>
                            </div>
                            
                            <!-- Number of Testimonials -->
                            <div>
                                <label for="testimonials_count" class="block text-sm font-medium text-gray-700 text-right">عدد القصص المعروضة</label>
                                <select name="testimonials_count" id="testimonials_count" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1" {{ \App\Models\SiteSetting::get('testimonials_count', 3) == 1 ? 'selected' : '' }}>قصة واحدة</option>
                                    <option value="2" {{ \App\Models\SiteSetting::get('testimonials_count', 3) == 2 ? 'selected' : '' }}>قصتان</option>
                                    <option value="3" {{ \App\Models\SiteSetting::get('testimonials_count', 3) == 3 ? 'selected' : '' }}>3 قصص</option>
                                    <option value="4" {{ \App\Models\SiteSetting::get('testimonials_count', 3) == 4 ? 'selected' : '' }}>4 قصص</option>
                                    <option value="5" {{ \App\Models\SiteSetting::get('testimonials_count', 3) == 5 ? 'selected' : '' }}>5 قصص</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500 text-right">عدد قصص النجاح المعروضة في الصفحة الرئيسية.</p>
                            </div>
                        </div>
                        
                        <!-- Testimonials Description -->
                        <div class="mt-6">
                            <label for="testimonials_description" class="block text-sm font-medium text-gray-700 text-right">وصف القسم</label>
                            <textarea name="testimonials_description" id="testimonials_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right">{{ old('testimonials_description', \App\Models\SiteSetting::get('testimonials_description', 'اكتشف تجارب عملائنا الحقيقية وكيف ساعدتهم خدماتنا في تحقيق أهدافهم وتحسين حياتهم بطرق مذهلة ومؤثرة.')) }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 text-right">وصف قسم قصص النجاح في الصفحة الرئيسية.</p>
                        </div>
                        
                        <!-- Enable Testimonials -->
                        <div class="mt-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="testimonials_enabled" id="testimonials_enabled" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ \App\Models\SiteSetting::get('testimonials_enabled', true) ? 'checked' : '' }}>
                                </div>
                                <div class="mr-3 text-sm">
                                    <label for="testimonials_enabled" class="font-medium text-gray-700">تفعيل قسم قصص النجاح</label>
                                    <p class="text-gray-500">إظهار قسم قصص النجاح في الصفحة الرئيسية.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">إعدادات قسم المقالات</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="articles_count" class="block text-sm font-medium text-gray-700 text-right">عدد المقالات المعروضة</label>
                                <select name="articles_count" id="articles_count" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="2" {{ \App\Models\SiteSetting::get('articles_count', 3) == 2 ? 'selected' : '' }}>2 مقالات</option>
                                    <option value="3" {{ \App\Models\SiteSetting::get('articles_count', 3) == 3 ? 'selected' : '' }}>3 مقالات</option>
                                    <option value="4" {{ \App\Models\SiteSetting::get('articles_count', 3) == 4 ? 'selected' : '' }}>4 مقالات</option>
                                    <option value="6" {{ \App\Models\SiteSetting::get('articles_count', 3) == 6 ? 'selected' : '' }}>6 مقالات</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500 text-right">عدد المقالات التي تظهر في الصفحة الرئيسية.</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="articles_enabled" id="articles_enabled" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ \App\Models\SiteSetting::get('articles_enabled', true) ? 'checked' : '' }}>
                                </div>
                                <div class="mr-3 text-sm">
                                    <label for="articles_enabled" class="font-medium text-gray-700">تفعيل قسم المقالات</label>
                                    <p class="text-gray-500">إظهار أو إخفاء المقالات في الصفحة الرئيسية.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-start">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            حفظ إعدادات الصفحة الرئيسية
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tabs functionality
        const tabButtons = document.querySelectorAll('[role="tab"]');
        const tabPanels = document.querySelectorAll('[role="tabpanel"]');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Hide all tab panels
                tabPanels.forEach(panel => {
                    panel.classList.add('hidden');
                });
                
                // Show the selected tab panel
                const panelId = button.getAttribute('data-tabs-target').substring(1);
                document.getElementById(panelId).classList.remove('hidden');
                
                // Update active state for tab buttons
                tabButtons.forEach(btn => {
                    btn.setAttribute('aria-selected', 'false');
                    btn.classList.remove('border-indigo-600', 'text-indigo-600');
                    btn.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                });
                
                button.setAttribute('aria-selected', 'true');
                button.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                button.classList.add('border-indigo-600', 'text-indigo-600');
            });
        });
        
        // Color picker sync
        const primaryColor = document.getElementById('primary_color');
        const primaryColorText = document.getElementById('primary_color_text');
        const secondaryColor = document.getElementById('secondary_color');
        const secondaryColorText = document.getElementById('secondary_color_text');
        
        if (primaryColor && primaryColorText) {
            primaryColor.addEventListener('input', () => {
                primaryColorText.value = primaryColor.value;
            });
            
            primaryColorText.addEventListener('input', () => {
                primaryColor.value = primaryColorText.value;
            });
        }
        
        if (secondaryColor && secondaryColorText) {
            secondaryColor.addEventListener('input', () => {
                secondaryColorText.value = secondaryColor.value;
            });
            
            secondaryColorText.addEventListener('input', () => {
                secondaryColor.value = secondaryColorText.value;
            });
        }
    });
</script>
@endsection