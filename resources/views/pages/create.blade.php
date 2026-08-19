@extends('layouts.admin')

@section('title', 'إضافة صفحة جديدة')

@section('header', 'إضافة صفحة جديدة')

@section('header_actions')
<a href="{{ route('pages.index') }}" class="inline-flex items-center rounded-tremor-default border border-tremor-border bg-white px-3 py-2 text-sm font-medium text-tremor-content-emphasis shadow-tremor-input hover:bg-tremor-background-muted">
    العودة للقائمة
</a>
@endsection

@section('content')
<div class="mx-auto max-w-5xl space-y-4">
        @if ($errors->any())
            <div class="rounded-tremor-default border border-gray-300 bg-gray-100 px-4 py-3 text-sm text-gray-900">
                <div class="font-semibold mb-1">تعذّر الحفظ</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pages.store') }}" method="POST" enctype="multipart/form-data" class="admin-card p-5 sm:p-6 space-y-6">
            @csrf
            
            <!-- معلومات الصفحة الأساسية -->
            <div class="border-b border-tremor-border pb-6">
                <h3 class="text-sm font-semibold text-tremor-content-strong">معلومات الصفحة الأساسية</h3>
                <p class="mt-1 text-sm text-tremor-content">أدخل المعلومات الأساسية للصفحة.</p>
                
                <div class="mt-6">
                    <!-- العنوان -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-tremor-content-emphasis mb-2">عنوان الصفحة *</label>
                        <input type="text" name="title" id="title" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" value="{{ old('title') }}" required>
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- محتوى الصفحة -->
            <div class="border-b border-tremor-border py-6">
                <h3 class="text-sm font-semibold text-tremor-content-strong">محتوى الصفحة</h3>
                <p class="mt-1 text-sm text-tremor-content">أدخل محتوى الصفحة مع إمكانيات التنسيق المتقدمة.</p>
                
                <div class="mt-6">
                        
                        <!-- المحتوى مع المحرر المجاني -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-tremor-content-emphasis mb-2">محتوى الصفحة *</label>
                            
                            <!-- أدوات التنسيق -->
                            <div class="border border-tremor-border rounded-t-tremor-default bg-tremor-background-muted p-2 flex flex-wrap gap-1" id="editor-toolbar">
                                <button type="button" onclick="formatText('bold')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="غامق">
                                    <strong>B</strong>
                                </button>
                                <button type="button" onclick="formatText('italic')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="مائل">
                                    <em>I</em>
                                </button>
                                <button type="button" onclick="formatText('underline')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="تسطير">
                                    <u>U</u>
                                </button>
                                <div class="border-l border-tremor-border mx-1"></div>
                                <button type="button" onclick="formatText('insertUnorderedList')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="قائمة نقطية">
                                    • قائمة
                                </button>
                                <button type="button" onclick="formatText('insertOrderedList')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="قائمة مرقمة">
                                    1. قائمة
                                </button>
                                <div class="border-l border-tremor-border mx-1"></div>
                                <button type="button" onclick="formatText('justifyLeft')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="محاذاة يسار">
                                    ←
                                </button>
                                <button type="button" onclick="formatText('justifyCenter')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="محاذاة وسط">
                                    ↔
                                </button>
                                <button type="button" onclick="formatText('justifyRight')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="محاذاة يمين">
                                    →
                                </button>
                                <div class="border-l border-tremor-border mx-1"></div>
                                <button type="button" onclick="insertLink()" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="إدراج رابط">
                                    🔗 رابط
                                </button>
                                <button type="button" onclick="insertImage()" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="إدراج صورة">
                                    🖼️ صورة
                                </button>
                                <div class="border-l border-tremor-border mx-1"></div>
                                <select onchange="formatHeading(this.value)" class="px-2 py-1 bg-white border border-gray-300 rounded text-sm">
                                    <option value="">العناوين</option>
                                    <option value="h1">عنوان رئيسي</option>
                                    <option value="h2">عنوان فرعي</option>
                                    <option value="h3">عنوان صغير</option>
                                    <option value="p">نص عادي</option>
                                </select>
                                <div class="border-l border-tremor-border mx-1"></div>
                                <button type="button" onclick="toggleSourceCode()" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="عرض الكود">
                                    &lt;/&gt; كود
                                </button>
                            </div>

                            <!-- منطقة المحرر -->
                            <div id="editor-container" class="border-l border-r border-b border-gray-300 rounded-b-md">
                                <div id="editor" contenteditable="true" class="min-h-96 p-4 focus:outline-none focus:ring-2 focus:ring-orange-400" style="direction: rtl;">
                                    {{ old('content') }}
                                </div>
                                <textarea name="content" id="content-textarea" class="hidden w-full min-h-96 p-4 border-0 focus:outline-none focus:ring-2 focus:ring-orange-400" style="direction: rtl;">{{ old('content') }}</textarea>
                            </div>
                            
                            @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">استخدم أدوات التنسيق أعلاه لتنسيق المحتوى</p>
                        </div>
                </div>
            </div>
            
            <!-- معلومات إضافية -->
            <div class="border-b border-tremor-border py-6">
                <h3 class="text-sm font-semibold text-tremor-content-strong">معلومات إضافية</h3>
                <p class="mt-1 text-sm text-tremor-content">أضف معلومات إضافية للصفحة.</p>
                
                <div class="mt-6">
                    <!-- المقتطف -->
                    <div class="mb-6">
                        <label for="excerpt" class="block text-sm font-medium text-tremor-content-emphasis mb-2">مقتطف قصير</label>
                        <textarea name="excerpt" id="excerpt" rows="3" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" placeholder="وصف مختصر للصفحة">{{ old('excerpt') }}</textarea>
                        @error('excerpt')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- عنوان SEO -->
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-tremor-content-emphasis mb-2">عنوان SEO</label>
                            <input type="text" name="meta_title" id="meta_title" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" value="{{ old('meta_title') }}" placeholder="عنوان محرك البحث">
                            @error('meta_title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- الصورة المميزة -->
                        <div>
                            <label for="featured_image" class="block text-sm font-medium text-tremor-content-emphasis mb-2">الصورة المميزة</label>
                            <input type="file" name="featured_image" id="featured_image" accept="image/*" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400">
                            @error('featured_image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- وصف SEO -->
                    <div class="mt-6">
                        <label for="meta_description" class="block text-sm font-medium text-tremor-content-emphasis mb-2">وصف SEO</label>
                        <textarea name="meta_description" id="meta_description" rows="2" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" placeholder="وصف الصفحة لمحركات البحث (160 حرف كحد أقصى)">{{ old('meta_description') }}</textarea>
                        @error('meta_description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- إعدادات الوصول -->
            <div class="border-b border-tremor-border py-6">
                <h3 class="text-sm font-semibold text-tremor-content-strong">إعدادات الوصول والصلاحيات</h3>
                <p class="mt-1 text-sm text-tremor-content">حدد من يستطيع الوصول لهذه الصفحة.</p>
                
                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="access_level" class="block text-sm font-medium text-tremor-content-emphasis mb-2">مستوى الوصول *</label>
                                    <select name="access_level" id="access_level" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" required>
                                        <option value="public" {{ old('access_level', 'public') == 'public' ? 'selected' : '' }}>🌍 عام للجميع</option>
                                        <option value="authenticated" {{ old('access_level') == 'authenticated' ? 'selected' : '' }}>🔐 أي مستخدم مسجّل الدخول</option>
                                        <option value="user" {{ old('access_level') == 'user' ? 'selected' : '' }}>👤 متدربون (أدوار user أو client)</option>
                                        <option value="page_manager" {{ old('access_level') == 'page_manager' ? 'selected' : '' }}>📝 مديرو الصفحات فقط</option>
                                        <option value="admin" {{ old('access_level') == 'admin' ? 'selected' : '' }}>👑 المديرون فقط</option>
                                       <option value="membership" {{ old('access_level') == 'membership' ? 'selected' : '' }}>💎 أعضاء العضويات المدفوعة (حسب المسار)</option>
                                    </select>
                                    @error('access_level')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-2">هذا المستوى مستقل عن صلاحيات لوحة إدارة الصفحات (Spatie). المدربون يرون عادةً مستوى «مسجّل» فما فوق؛ «متدربون» يقتصر على أدوار user/client في النظام.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-tremor-content-emphasis mb-2">نوع المحتوى</label>
                                    <div class="flex items-center">
                                        <input type="hidden" name="is_premium" value="0">
                                        <input type="checkbox" name="is_premium" id="is_premium" value="1" class="rounded border-gray-300 text-tremor-brand shadow-sm focus:border-orange-400 focus:ring-orange-400" {{ old('is_premium') ? 'checked' : '' }}>
                                        <label for="is_premium" class="ml-2 block text-sm text-gray-700">💎 محتوى مدفوع</label>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">سيتم تطبيق هذا لاحقاً مع نظام العضويات</p>
                                </div>
                            </div>

                            @include('partials.audience-fields', [
                                'model' => new \App\Models\Page(),
                                'audienceFieldsWrapperClass' => '',
                                'audienceHeading' => 'استهداف الجمهور (الجنس والمسارات)',
                                'audienceIntro' => 'فلتر إضافي فوق مستوى الوصول. عند اختيار «أعضاء العضويات المدفوعة» يجب تحديد مسار واحد على الأقل. عند أي مستوى آخر تُجاهَل المسارات عند الحفظ.',
                                'membershipBlockId' => 'membership-types-section',
                                'membershipPathsLabel' => 'مسارات العضوية',
                                'membershipPathsHint' => 'يُستخدم بشكل إلزامي فقط عند مستوى «أعضاء العضويات المدفوعة»؛ وإلا يُفرغ تلقائياً عند الحفظ.',
                            ])
                </div>
            </div>

            <!-- إعدادات النشر -->
            <div class="py-6">
                <h3 class="text-sm font-semibold text-tremor-content-strong">إعدادات النشر</h3>
                <p class="mt-1 text-sm text-tremor-content">حدد إعدادات النشر والعرض للصفحة.</p>
                
                <div class="mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- ترتيب القائمة -->
                        <div>
                            <label for="menu_order" class="block text-sm font-medium text-tremor-content-emphasis mb-2">ترتيب القائمة</label>
                            <input type="number" name="menu_order" id="menu_order" min="0" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" value="{{ old('menu_order', 0) }}">
                            @error('menu_order')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- تاريخ النشر -->
                        <div>
                            <label for="published_at" class="block text-sm font-medium text-tremor-content-emphasis mb-2">تاريخ النشر</label>
                            <input type="datetime-local" name="published_at" id="published_at" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" value="{{ old('published_at') }}">
                            @error('published_at')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- خيارات النشر -->
                    <div class="mt-6 space-y-4">
                        <div class="flex items-center">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" id="is_published" value="1" class="rounded border-gray-300 text-tremor-brand shadow-sm focus:border-orange-400 focus:ring-orange-400" {{ old('is_published', true) ? 'checked' : '' }}>
                            <label for="is_published" class="ml-2 block text-sm text-gray-700">نشر الصفحة</label>
                        </div>

                        <div class="flex items-center">
                            <input type="hidden" name="show_in_menu" value="0">
                            <input type="checkbox" name="show_in_menu" id="show_in_menu" value="1" class="rounded border-gray-300 text-tremor-brand shadow-sm focus:border-orange-400 focus:ring-orange-400" {{ old('show_in_menu') ? 'checked' : '' }}>
                            <label for="show_in_menu" class="ml-2 block text-sm text-gray-700">إظهار في قائمة التنقل</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('pages.index') }}" class="inline-flex items-center rounded-tremor-default border border-tremor-border bg-white px-3 py-2 text-sm font-medium text-tremor-content-emphasis shadow-tremor-input hover:bg-tremor-background-muted">
                    إلغاء
                </a>
                <button type="submit" class="admin-btn-brand">
                    حفظ الصفحة
                </button>
            </div>
        </form>
</div>

    <!-- محرر مجاني بالكامل -->
    <script>
        let isSourceMode = false;
        const editor = document.getElementById('editor');
        const textarea = document.getElementById('content-textarea');

        // تحديث المحتوى في الـ textarea عند التغيير
        editor.addEventListener('input', function() {
            if (!isSourceMode) {
                textarea.value = editor.innerHTML;
            }
        });

        // دالة تنسيق النص
        function formatText(command, value = null) {
            if (isSourceMode) return;
            
            document.execCommand(command, false, value);
            editor.focus();
            textarea.value = editor.innerHTML;
        }

        // دالة تنسيق العناوين
        function formatHeading(tag) {
            if (isSourceMode || !tag) return;
            
            formatText('formatBlock', tag);
        }

        // دالة إدراج رابط
        function insertLink() {
            if (isSourceMode) return;
            
            const url = prompt('أدخل رابط URL:');
            if (url) {
                formatText('createLink', url);
            }
        }

        // دالة إدراج صورة
        function insertImage() {
            if (isSourceMode) return;
            
            const url = prompt('أدخل رابط الصورة:');
            if (url) {
                formatText('insertImage', url);
            }
        }

        // تبديل وضع عرض الكود
        function toggleSourceCode() {
            isSourceMode = !isSourceMode;
            
            if (isSourceMode) {
                // التبديل إلى وضع الكود
                textarea.value = editor.innerHTML;
                editor.style.display = 'none';
                textarea.style.display = 'block';
                textarea.classList.remove('hidden');
            } else {
                // التبديل إلى وضع المحرر
                editor.innerHTML = textarea.value;
                editor.style.display = 'block';
                textarea.style.display = 'none';
                textarea.classList.add('hidden');
            }
        }

        // تحديث المحتوى قبل إرسال النموذج
        document.querySelector('form').addEventListener('submit', function() {
            if (isSourceMode) {
                editor.innerHTML = textarea.value;
            } else {
                textarea.value = editor.innerHTML;
            }
        });

        // تحسين تجربة المستخدم
        editor.addEventListener('paste', function(e) {
            e.preventDefault();
            const text = e.clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
        });

        // إضافة أنماط CSS للمحرر
        const style = document.createElement('style');
        style.textContent = `
            #editor {
                line-height: 1.6;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            #editor h1 { font-size: 2em; font-weight: bold; margin: 0.5em 0; }
            #editor h2 { font-size: 1.5em; font-weight: bold; margin: 0.5em 0; }
            #editor h3 { font-size: 1.2em; font-weight: bold; margin: 0.5em 0; }
            #editor p { margin: 0.5em 0; }
            #editor ul, #editor ol { margin: 0.5em 0; padding-right: 2em; }
            #editor li { margin: 0.2em 0; }
            #editor a { color: #3b82f6; text-decoration: underline; }
            #editor img { max-width: 100%; height: auto; margin: 0.5em 0; }
            #editor blockquote { 
                border-right: 4px solid #e5e7eb; 
                padding-right: 1em; 
                margin: 1em 0; 
                font-style: italic; 
                background: #f9fafb; 
                padding: 1em; 
            }
        `;
        document.head.appendChild(style);
    </script>

    <script>
        // إظهار/إخفاء قسم العضويات المطلوبة بناءً على مستوى الوصول
        document.addEventListener('DOMContentLoaded', function() {
            const accessLevelSelect = document.getElementById('access_level');
            const membershipTypesSection = document.getElementById('membership-types-section');
            const membershipCheckboxes = document.querySelectorAll('input[name="required_membership_types[]"]');
            
            function updateMembershipSection() {
                if (accessLevelSelect.value === 'membership') {
                    membershipTypesSection.style.display = 'block';
                    // Make at least one checkbox required when membership is selected
                    membershipCheckboxes.forEach(checkbox => {
                        checkbox.setAttribute('required', 'required');
                    });
                } else {
                    membershipTypesSection.style.display = 'none';
                    // Remove required attribute when membership is not selected
                    membershipCheckboxes.forEach(checkbox => {
                        checkbox.removeAttribute('required');
                    });
                }
            }
            
            if (accessLevelSelect && membershipTypesSection) {
                accessLevelSelect.addEventListener('change', function() {
                    updateMembershipSection();
                });
                
                // Run once on page load
                updateMembershipSection();
            }
            
            // Make checkboxes behave as a group for the required attribute
            if (membershipCheckboxes.length > 0) {
                membershipCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const anyChecked = Array.from(membershipCheckboxes).some(cb => cb.checked);
                        if (anyChecked) {
                            membershipCheckboxes.forEach(cb => cb.removeAttribute('required'));
                        } else if (accessLevelSelect.value === 'membership') {
                            membershipCheckboxes.forEach(cb => cb.setAttribute('required', 'required'));
                        }
                    });
                });
            }
        });
    </script>
@endsection