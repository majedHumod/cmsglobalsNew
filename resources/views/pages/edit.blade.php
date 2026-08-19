@extends('layouts.admin')

@section('title', 'تعديل الصفحة')

@section('header', 'تعديل الصفحة: ' . $page->title)

@section('header_actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="inline-flex items-center rounded-tremor-default border border-tremor-border bg-white px-3 py-2 text-sm font-medium text-tremor-content-emphasis shadow-tremor-input hover:bg-tremor-background-muted">عرض الصفحة</a>
    <a href="{{ route('pages.index') }}" class="inline-flex items-center rounded-tremor-default border border-tremor-border bg-white px-3 py-2 text-sm font-medium text-tremor-content-emphasis shadow-tremor-input hover:bg-tremor-background-muted">العودة للقائمة</a>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-5xl space-y-4">
        <form action="{{ route('pages.update', $page) }}" method="POST" enctype="multipart/form-data" class="admin-card p-5 sm:p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <!-- معلومات الصفحة الأساسية -->
            <div class="border-b border-tremor-border pb-6">
                <h3 class="text-sm font-semibold text-tremor-content-strong">معلومات الصفحة الأساسية</h3>
                <p class="mt-1 text-sm text-tremor-content">تعديل المعلومات الأساسية للصفحة.</p>
                
                <div class="mt-6">
                    <!-- العنوان -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-tremor-content-emphasis mb-2">عنوان الصفحة *</label>
                        <input type="text" name="title" id="title" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" value="{{ old('title', $page->title) }}" required>
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- محتوى الصفحة -->
            <div class="border-b border-tremor-border py-6">
                <h3 class="text-sm font-semibold text-tremor-content-strong">محتوى الصفحة</h3>
                <p class="mt-1 text-sm text-tremor-content">تعديل محتوى الصفحة مع إمكانيات التنسيق المتقدمة.</p>
                
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
                                    {!! old('content', $page->content) !!}
                                </div>
                                <textarea name="content" id="content-textarea" class="hidden w-full min-h-96 p-4 border-0 focus:outline-none focus:ring-2 focus:ring-orange-400" style="direction: rtl;">{!! old('content', $page->content) !!}</textarea>
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
                <p class="mt-1 text-sm text-tremor-content">تعديل المعلومات الإضافية للصفحة.</p>
                
                <div class="mt-6">
                    <!-- المقتطف -->
                    <div class="mb-6">
                        <label for="excerpt" class="block text-sm font-medium text-tremor-content-emphasis mb-2">مقتطف قصير</label>
                        <textarea name="excerpt" id="excerpt" rows="3" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" placeholder="وصف مختصر للصفحة">{{ old('excerpt', $page->excerpt) }}</textarea>
                        @error('excerpt')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- عنوان SEO -->
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-tremor-content-emphasis mb-2">عنوان SEO</label>
                            <input type="text" name="meta_title" id="meta_title" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" value="{{ old('meta_title', $page->meta_title) }}" placeholder="عنوان محرك البحث">
                            @error('meta_title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- الصورة المميزة -->
                        <div>
                            <label for="featured_image" class="block text-sm font-medium text-tremor-content-emphasis mb-2">الصورة المميزة</label>
                            @if($page->featured_image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($page->featured_image) }}" alt="{{ $page->title }}" class="w-32 h-32 object-cover rounded">
                                    <p class="text-sm text-tremor-content mt-1">الصورة الحالية</p>
                                </div>
                            @endif
                            <input type="file" name="featured_image" id="featured_image" accept="image/*" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400">
                            @error('featured_image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- وصف SEO -->
                    <div class="mt-6">
                        <label for="meta_description" class="block text-sm font-medium text-tremor-content-emphasis mb-2">وصف SEO</label>
                        <textarea name="meta_description" id="meta_description" rows="2" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" placeholder="وصف الصفحة لمحركات البحث (160 حرف كحد أقصى)">{{ old('meta_description', $page->meta_description) }}</textarea>
                        @error('meta_description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- إعدادات الوصول -->
            <div class="border-b border-tremor-border py-6">
                <h3 class="text-sm font-semibold text-tremor-content-strong">إعدادات الوصول والصلاحيات</h3>
                <p class="mt-1 text-sm text-tremor-content">تعديل إعدادات الوصول والصلاحيات للصفحة.</p>
                
                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="access_level" class="block text-sm font-medium text-tremor-content-emphasis mb-2">مستوى الوصول *</label>
                                    <select name="access_level" id="access_level" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" required>
                                        <option value="public" {{ old('access_level', $page->access_level) == 'public' ? 'selected' : '' }}>🌍 عام للجميع</option>
                                        <option value="authenticated" {{ old('access_level', $page->access_level) == 'authenticated' ? 'selected' : '' }}>🔐 أي مستخدم مسجّل الدخول</option>
                                        <option value="user" {{ old('access_level', $page->access_level) == 'user' ? 'selected' : '' }}>👤 متدربون (أدوار user أو client)</option>
                                        <option value="page_manager" {{ old('access_level', $page->access_level) == 'page_manager' ? 'selected' : '' }}>📝 مديرو الصفحات فقط</option>
                                        <option value="admin" {{ old('access_level', $page->access_level) == 'admin' ? 'selected' : '' }}>👑 المديرون فقط</option>
                                       <option value="membership" {{ old('access_level', $page->access_level) == 'membership' ? 'selected' : '' }}>💎 أعضاء العضويات المدفوعة (حسب المسار)</option>
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
                                        <input type="checkbox" name="is_premium" id="is_premium" value="1" class="rounded border-gray-300 text-tremor-brand shadow-sm focus:border-orange-400 focus:ring-orange-400" {{ old('is_premium', $page->is_premium) ? 'checked' : '' }}>
                                        <label for="is_premium" class="ml-2 block text-sm text-gray-700">💎 محتوى مدفوع</label>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">سيتم تطبيق هذا لاحقاً مع نظام العضويات</p>
                                </div>
                            </div>

                            @include('partials.audience-fields', [
                                'model' => $page,
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
                <p class="mt-1 text-sm text-tremor-content">تعديل إعدادات النشر والعرض للصفحة.</p>
                
                <div class="mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- ترتيب القائمة -->
                        <div>
                            <label for="menu_order" class="block text-sm font-medium text-tremor-content-emphasis mb-2">ترتيب القائمة</label>
                            <input type="number" name="menu_order" id="menu_order" min="0" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" value="{{ old('menu_order', $page->menu_order) }}">
                            @error('menu_order')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- تاريخ النشر -->
                        <div>
                            <label for="published_at" class="block text-sm font-medium text-tremor-content-emphasis mb-2">تاريخ النشر</label>
                            <input type="datetime-local" name="published_at" id="published_at" class="w-full border-tremor-border rounded-tremor-default shadow-tremor-input focus:border-orange-400 focus:ring-orange-400" value="{{ old('published_at', $page->published_at ? $page->published_at->format('Y-m-d\TH:i') : '') }}">
                            @error('published_at')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- خيارات النشر -->
                    <div class="mt-6 space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_published" id="is_published" class="rounded border-gray-300 text-tremor-brand shadow-sm focus:border-orange-400 focus:ring-orange-400" {{ old('is_published', $page->is_published) ? 'checked' : '' }}>
                            <label for="is_published" class="ml-2 block text-sm text-gray-700">نشر الصفحة</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="show_in_menu" id="show_in_menu" class="rounded border-gray-300 text-tremor-brand shadow-sm focus:border-orange-400 focus:ring-orange-400" {{ old('show_in_menu', $page->show_in_menu) ? 'checked' : '' }}>
                            <label for="show_in_menu" class="ml-2 block text-sm text-gray-700">إظهار في قائمة التنقل</label>
                        </div>
                    </div>
                    
                    <!-- معلومات الصفحة -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">معلومات الصفحة</h4>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">تاريخ الإنشاء</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $page->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">آخر تحديث</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $page->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">المؤلف</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $page->user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">عدد الكلمات</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ str_word_count(strip_tags($page->content)) }} كلمة</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('pages.index') }}" class="inline-flex items-center rounded-tremor-default border border-tremor-border bg-white px-3 py-2 text-sm font-medium text-tremor-content-emphasis shadow-tremor-input hover:bg-tremor-background-muted">
                    إلغاء
                </a>
                <button type="submit" class="admin-btn-brand">
                    تحديث الصفحة
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
            
            function setMembershipGroupRequired(required) {
                if (!membershipCheckboxes.length) return;
                membershipCheckboxes.forEach(cb => cb.removeAttribute('required'));
                if (required) {
                    // اجعل أول عنصر فقط required ليعمل التحقق كمجموعة (واحد على الأقل)
                    membershipCheckboxes[0].setAttribute('required', 'required');
                }
            }

            function updateMembershipSection() {
                if (accessLevelSelect.value === 'membership') {
                    membershipTypesSection.style.display = 'block';
                    const anyChecked = Array.from(membershipCheckboxes).some(cb => cb.checked);
                    setMembershipGroupRequired(!anyChecked);
                } else {
                    membershipTypesSection.style.display = 'none';
                    setMembershipGroupRequired(false);
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
                        setMembershipGroupRequired(accessLevelSelect.value === 'membership' && !anyChecked);
                    });
                });
            }
        });
    </script>
@endsection