@extends('layouts.admin')

@section('title', 'تعديل السؤال الشائع')

@section('header', 'تعديل السؤال الشائع')

@section('header_actions')
    <x-admin.button :href="route('admin.faqs.index')" variant="secondary">العودة للقائمة</x-admin.button>
@endsection

@section('content')
<x-admin.validation-errors title="تعذر تحديث السؤال:" />

<x-admin.card>
    <x-admin.section-heading
        title="تعديل بيانات السؤال"
        description="التغييرات تظهر للجمهور بعد الحفظ حسب حالة التفعيل."
    />

    <form action="{{ route('admin.faqs.update', $faq) }}" method="POST" class="space-y-6" id="faq-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-admin.label for="question" value="السؤال *" />
                <x-admin.input type="text" name="question" id="question" :value="old('question', $faq->question)" required />
                <x-admin.field-error name="question" />
            </div>

            <div>
                <x-admin.label for="category" value="التصنيف *" />
                <x-admin.select name="category" id="category" required>
                    @foreach(['عام', 'العضويات', 'الدفع', 'الحساب', 'المحتوى', 'الدعم الفني'] as $category)
                        <option value="{{ $category }}" @selected(old('category', $faq->category) === $category)>{{ $category }}</option>
                    @endforeach
                </x-admin.select>
                <x-admin.field-error name="category" />
            </div>

            <div>
                <x-admin.label for="sort_order" value="ترتيب العرض" />
                <x-admin.input type="number" name="sort_order" id="sort_order" min="0" :value="old('sort_order', $faq->sort_order)" />
                <x-admin.field-error name="sort_order" />
            </div>
        </div>

        <div>
            <x-admin.label for="answer" value="الإجابة *" />

            <div class="mt-1 flex flex-wrap gap-1 rounded-t-tremor-default border border-tremor-border bg-tremor-background-muted p-2" id="editor-toolbar">
                <button type="button" onclick="formatText('bold')" class="rounded-tremor-small border border-tremor-border bg-white px-3 py-1 hover:bg-tremor-background-subtle" title="غامق"><strong>B</strong></button>
                <button type="button" onclick="formatText('italic')" class="rounded-tremor-small border border-tremor-border bg-white px-3 py-1 hover:bg-tremor-background-subtle" title="مائل"><em>I</em></button>
                <button type="button" onclick="formatText('underline')" class="rounded-tremor-small border border-tremor-border bg-white px-3 py-1 hover:bg-tremor-background-subtle" title="تسطير"><u>U</u></button>
                <div class="mx-1 border-l border-tremor-border"></div>
                <button type="button" onclick="formatText('insertUnorderedList')" class="rounded-tremor-small border border-tremor-border bg-white px-3 py-1 text-xs hover:bg-tremor-background-subtle">• قائمة</button>
                <button type="button" onclick="formatText('insertOrderedList')" class="rounded-tremor-small border border-tremor-border bg-white px-3 py-1 text-xs hover:bg-tremor-background-subtle">1. قائمة</button>
                <div class="mx-1 border-l border-tremor-border"></div>
                <button type="button" onclick="formatText('justifyRight')" class="rounded-tremor-small border border-tremor-border bg-white px-3 py-1 hover:bg-tremor-background-subtle" title="محاذاة يمين">→</button>
                <button type="button" onclick="formatText('justifyCenter')" class="rounded-tremor-small border border-tremor-border bg-white px-3 py-1 hover:bg-tremor-background-subtle" title="محاذاة وسط">↔</button>
                <button type="button" onclick="formatText('justifyLeft')" class="rounded-tremor-small border border-tremor-border bg-white px-3 py-1 hover:bg-tremor-background-subtle" title="محاذاة يسار">←</button>
                <div class="mx-1 border-l border-tremor-border"></div>
                <button type="button" onclick="insertLink()" class="rounded-tremor-small border border-tremor-border bg-white px-3 py-1 text-xs hover:bg-tremor-background-subtle">رابط</button>
            </div>

            <div id="editor-container" class="rounded-b-tremor-default border-x border-b border-tremor-border">
                <div id="editor" contenteditable="true" class="min-h-32 p-4 focus:outline-none focus:ring-2 focus:ring-tremor-brand" style="direction: rtl;">
                    {!! old('answer', $faq->answer) !!}
                </div>
                <textarea name="answer" id="answer-textarea" class="hidden min-h-32 w-full border-0 p-4 focus:outline-none focus:ring-2 focus:ring-tremor-brand" style="direction: rtl;" required>{!! old('answer', $faq->answer) !!}</textarea>
            </div>
            <x-admin.field-error name="answer" />
        </div>

        <label class="flex items-start gap-3">
            <input
                type="checkbox"
                name="is_active"
                id="is_active"
                value="1"
                class="mt-1 rounded border-tremor-border text-tremor-brand shadow-tremor-input focus:border-tremor-brand focus:ring-tremor-brand"
                @checked(old('is_active', $faq->is_active))
            >
            <span class="text-sm text-tremor-content-emphasis">تفعيل السؤال (جعله مرئي للجمهور)</span>
        </label>

        <x-admin.form-actions
            :cancel-href="route('admin.faqs.index')"
            submit-label="تحديث السؤال"
        />
    </form>
</x-admin.card>

<script>
    let isSourceMode = false;
    const editor = document.getElementById('editor');
    const textarea = document.getElementById('answer-textarea');

    editor.addEventListener('input', function () {
        if (!isSourceMode) {
            textarea.value = editor.innerHTML;
        }
    });

    function formatText(command, value = null) {
        if (isSourceMode) return;
        document.execCommand(command, false, value);
        editor.focus();
        textarea.value = editor.innerHTML;
    }

    function insertLink() {
        if (isSourceMode) return;
        const url = prompt('أدخل رابط URL:');
        if (url) {
            formatText('createLink', url);
        }
    }

    document.getElementById('faq-form').addEventListener('submit', function () {
        textarea.value = editor.innerHTML;
        textarea.classList.remove('hidden');
    });

    editor.addEventListener('paste', function (e) {
        e.preventDefault();
        const text = e.clipboardData.getData('text/plain');
        document.execCommand('insertText', false, text);
    });
</script>
@endsection
