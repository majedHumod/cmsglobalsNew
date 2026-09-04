<!-- Site Footer with Contact Info and Social Media -->
<footer class="bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto py-12 px-4 overflow-hidden sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Logo and About -->
            <div class="col-span-1 md:col-span-1">
                <div class="flex items-center">
                    @php
                        $siteLogo = \App\Models\SiteSetting::get('site_logo');
                        $siteName = \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel'));
                    @endphp
                    @if($siteLogo)
                        <img class="h-10 w-auto" src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}" title="{{ $siteName }}">
                    @else
                        <span class="text-xl font-bold text-indigo-600">{{ $siteName }}</span>
                    @endif
                </div>
                <p class="mt-4 text-gray-500 text-sm">
                    {{ \App\Models\SiteSetting::get('site_description', 'نظام إدارة محتوى متكامل يوفر حلول متقدمة لإدارة المحتوى الرقمي.') }}
                </p>
            </div>
            
            <!-- Quick Links -->
            <div class="col-span-1">
                <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">روابط سريعة</h3>
                <ul class="mt-4 space-y-4">
                    <li>
                        <a href="{{ auth()->check() && auth()->user()->hasAnyRole(['admin', 'coach']) ? url('/admin-cms') : route('dashboard') }}" class="text-base text-gray-500 hover:text-indigo-600">
                            الرئيسية
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pages.public') }}" class="text-base text-gray-500 hover:text-indigo-600">
                            الصفحات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('meal-plans.public') }}" class="text-base text-gray-500 hover:text-indigo-600">
                            الجداول الغذائية
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faqs.index') }}" class="text-base text-gray-500 hover:text-indigo-600">
                            الأسئلة الشائعة
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="col-span-1">
                <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">معلومات الاتصال</h3>
                <ul class="mt-4 space-y-4">
                    @php
                        $contactPhone = \App\Models\SiteSetting::get('contact_phone');
                    @endphp
                    @if($contactPhone)
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-gray-500">{{ $contactPhone }}</span>
                        </li>
                    @endif
                    
                    @php
                        $contactWhatsapp = \App\Models\SiteSetting::get('contact_whatsapp');
                    @endphp
                    @if($contactWhatsapp)
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-400 ml-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                            </svg>
                            <span class="text-gray-500">{{ $contactWhatsapp }}</span>
                        </li>
                    @endif
                    
                    @php
                        $contactEmail = \App\Models\SiteSetting::get('contact_email');
                    @endphp
                    @if($contactEmail)
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-500">{{ $contactEmail }}</span>
                        </li>
                    @endif
                    
                    @php
                        $contactAddress = \App\Models\SiteSetting::get('contact_address');
                    @endphp
                    @if($contactAddress)
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-gray-500">{{ $contactAddress }}</span>
                        </li>
                    @endif
                </ul>
            </div>
            
            <!-- Mobile Apps -->
            <div class="col-span-1">
                <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">تطبيقات الجوال</h3>
                <div class="mt-4 space-y-4">
                    @php
                        $appAndroid = \App\Models\SiteSetting::get('app_android');
                    @endphp
                    @if($appAndroid)
                        <a href="{{ $appAndroid }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.523 15.34c-.5 0-.909-.409-.909-.909s.409-.909.91-.909.908.409.908.909-.409.909-.909.909m-10.136 0c-.5 0-.91-.409-.91-.909s.41-.909.91-.909.908.409.908.909-.409.909-.909.909m10.91-4.318H5.703l-.909 1.818v6.82h3.182v-2.727h8.182v2.727h3.182v-6.82l-.91-1.818zm-13.863.909l1.33-2.727 1.5-2.727h8.863l1.5 2.727 1.33 2.727h-14.523zm13.863-6.82h-2.272v-.91h2.272v.91zm-4.09 0h-2.273v-.91h2.273v.91z"></path>
                            </svg>
                            تطبيق أندرويد
                        </a>
                    @endif
                    
                    @php
                        $appIos = \App\Models\SiteSetting::get('app_ios');
                    @endphp
                    @if($appIos)
                        <a href="{{ $appIos }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.462 8.293c-.057-5.177 4.243-7.691 4.433-7.812-2.413-3.531-6.172-4.013-7.516-4.069-3.195-.323-6.239 1.877-7.854 1.877-1.633 0-4.14-1.831-6.812-1.781-3.504.05-6.731 2.034-8.535 5.165-3.644 6.319-.943 15.664 2.616 20.787 1.736 2.507 3.804 5.324 6.516 5.221 2.621-.104 3.608-1.693 6.771-1.693 3.144 0 4.043 1.693 6.806 1.638 2.812-.047 4.595-2.553 6.312-5.071 1.989-2.904 2.807-5.717 2.857-5.861-.062-.028-5.482-2.104-5.539-8.348zm-5.184-15.291c1.446-1.751 2.421-4.185 2.155-6.602-2.079.085-4.593 1.38-6.082 3.122-1.336 1.547-2.507 4.023-2.192 6.394 2.317.179 4.682-1.165 6.119-2.914z"></path>
                            </svg>
                            تطبيق آيفون
                        </a>
                    @endif
                </div>
                
                <!-- Social Media Icons -->
                <div class="mt-8">
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">تابعنا</h3>
                    <div class="mt-4 flex space-x-6">
                        @php
                            $socialFacebook = \App\Models\SiteSetting::get('social_facebook');
                        @endphp
                        @if($socialFacebook)
                            <a href="{{ $socialFacebook }}" target="_blank" class="text-gray-400 hover:text-indigo-600">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        @endif
                        
                        @php
                            $socialTwitter = \App\Models\SiteSetting::get('social_twitter');
                        @endphp
                        @if($socialTwitter)
                            <a href="{{ $socialTwitter }}" target="_blank" class="text-gray-400 hover:text-indigo-600">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"></path>
                                </svg>
                            </a>
                        @endif
                        
                        @php
                            $socialInstagram = \App\Models\SiteSetting::get('social_instagram');
                        @endphp
                        @if($socialInstagram)
                            <a href="{{ $socialInstagram }}" target="_blank" class="text-gray-400 hover:text-indigo-600">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        @endif
                        
                        @php
                            $socialYoutube = \App\Models\SiteSetting::get('social_youtube');
                        @endphp
                        @if($socialYoutube)
                            <a href="{{ $socialYoutube }}" target="_blank" class="text-gray-400 hover:text-indigo-600">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="mt-12 border-t border-gray-200 pt-8">
            <p class="text-base text-gray-400 text-center">
                {{ \App\Models\SiteSetting::get('footer_text', '© ' . date('Y') . ' ' . $siteName . '. جميع الحقوق محفوظة.') }}
            </p>
        </div>
    </div>
</footer>