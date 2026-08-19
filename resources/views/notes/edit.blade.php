@extends('layouts.admin')

@section('title', 'تعديل الملاحظة')
@section('header', 'تعديل الملاحظة')

@section('header_actions')
<a href="{{ route('notes.index') }}" class="inline-flex items-center rounded-tremor-default border border-tremor-border bg-white px-3 py-2 text-sm font-medium text-tremor-content-emphasis shadow-tremor-input hover:bg-tremor-background-muted">
    العودة للقائمة
</a>
@endsection

@section('content')
<div class="mx-auto max-w-3xl space-y-4">
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

    <form action="{{ route('notes.update', $note) }}" method="POST" class="admin-card p-5 sm:p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <h2 class="text-sm font-semibold text-tremor-content-strong">تعديل الملاحظة</h2>
            <p class="mt-1 text-sm text-tremor-content">حدّث العنوان والمحتوى ثم احفظ.</p>
            <div class="mt-4">
                <label for="title" class="block text-sm font-medium text-tremor-content-emphasis">عنوان الملاحظة *</label>
                <input type="text" name="title" id="title" value="{{ old('title', $note->title) }}" required
                    class="mt-1 block w-full rounded-tremor-default border-tremor-border shadow-tremor-input focus:border-orange-400 focus:ring-orange-400">
                @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="content" class="block text-sm font-medium text-tremor-content-emphasis mb-2">محتوى الملاحظة *</label>
            <div class="border border-tremor-border rounded-t-tremor-default bg-tremor-background-muted p-2 flex flex-wrap gap-1" id="editor-toolbar">
                <button type="button" onclick="formatText('bold')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="غامق"><strong>B</strong></button>
                <button type="button" onclick="formatText('italic')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="مائل"><em>I</em></button>
                <button type="button" onclick="formatText('underline')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle" title="تسطير"><u>U</u></button>
                <div class="border-l border-tremor-border mx-1"></div>
                <button type="button" onclick="formatText('insertUnorderedList')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle text-xs">• قائمة</button>
                <button type="button" onclick="formatText('insertOrderedList')" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle text-xs">1. قائمة</button>
                <div class="border-l border-tremor-border mx-1"></div>
                <button type="button" onclick="insertLink()" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle text-xs">رابط</button>
                <button type="button" onclick="toggleSourceCode()" class="px-3 py-1 bg-white border border-tremor-border rounded-tremor-small hover:bg-tremor-background-subtle text-xs">&lt;/&gt; كود</button>
            </div>
            <div id="editor-container" class="border-x border-b border-tremor-border rounded-b-tremor-default overflow-hidden">
                <div id="editor" contenteditable="true" class="min-h-48 p-4 focus:outline-none focus:ring-2 focus:ring-orange-300" style="direction: rtl;">{!! old('content', $note->content) !!}</div>
                <textarea name="content" id="content-textarea" class="hidden w-full min-h-48 p-4 border-0 focus:outline-none focus:ring-2 focus:ring-orange-300" style="direction: rtl;">{!! old('content', $note->content) !!}</textarea>
            </div>
            @error('content')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="rounded-tremor-default border border-tremor-border bg-tremor-background-muted px-4 py-3">
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-tremor-content-subtle">تاريخ الإنشاء</dt>
                    <dd class="font-medium text-tremor-content-strong">{{ $note->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-tremor-content-subtle">آخر تحديث</dt>
                    <dd class="font-medium text-tremor-content-strong">{{ $note->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-tremor-content-subtle">المؤلف</dt>
                    <dd class="font-medium text-tremor-content-strong">{{ $note->user->name ?? 'مستخدم محذوف' }}</dd>
                </div>
                <div>
                    <dt class="text-tremor-content-subtle">عدد الكلمات</dt>
                    <dd class="font-medium text-tremor-content-strong">{{ str_word_count(strip_tags($note->content)) }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('notes.index') }}" class="inline-flex items-center rounded-tremor-default border border-tremor-border bg-white px-4 py-2 text-sm font-medium text-tremor-content-emphasis hover:bg-tremor-background-muted">إلغاء</a>
            <button type="submit" class="admin-btn-brand">تحديث الملاحظة</button>
        </div>
    </form>
</div>

<script>
    let isSourceMode = false;
    const editor = document.getElementById('editor');
    const textarea = document.getElementById('content-textarea');
    editor.addEventListener('input', function () {
        if (!isSourceMode) textarea.value = editor.innerHTML;
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
        if (url) formatText('createLink', url);
    }
    function toggleSourceCode() {
        isSourceMode = !isSourceMode;
        if (isSourceMode) {
            textarea.value = editor.innerHTML;
            editor.style.display = 'none';
            textarea.style.display = 'block';
            textarea.classList.remove('hidden');
        } else {
            editor.innerHTML = textarea.value;
            editor.style.display = 'block';
            textarea.style.display = 'none';
            textarea.classList.add('hidden');
        }
    }
    document.querySelector('form').addEventListener('submit', function () {
        if (isSourceMode) editor.innerHTML = textarea.value;
        else textarea.value = editor.innerHTML;
    });
    editor.addEventListener('paste', function (e) {
        e.preventDefault();
        document.execCommand('insertText', false, e.clipboardData.getData('text/plain'));
    });
</script>
@endsection
