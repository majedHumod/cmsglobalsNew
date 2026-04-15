@extends('layouts.admin')

@section('title', 'تعديل المقال')

@section('header', 'تعديل المقال: ' . $article->title)

@section('header_actions')
<div class="flex space-x-2">
    @if($article->is_published)
    <a href="{{ route('articles.public.show', $article) }}" class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100" target="_blank">
        <svg class="-ml-1 mr-2 h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        استعراض المنشور
    </a>
    @endif
    <a href="{{ route('articles.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        العودة للقائمة
    </a>
</div>
@endsection

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">تعديل المقال</h2>
            <p class="mt-1 text-sm text-gray-500">قم بتعديل محتوى ومعلومات المقال.</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <strong class="font-bold">خطأ في البيانات!</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('articles.update', $article) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- معلومات المقال الأساسية -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900">معلومات المقال الأساسية</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل المعلومات الأساسية للمقال.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- عنوان المقال -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">عنوان المقال *</label>
                        <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('title', $article->title) }}" required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- صورة المقال -->
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">صورة المقال</label>
                        @if($article->image)
                            <div class="mb-2">
                                <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="w-32 h-32 object-cover rounded">
                                <p class="text-sm text-gray-500 mt-1">الصورة الحالية</p>
                            </div>
                        @endif
                        <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- محتوى المقال -->
            <div class="border-b border-gray-200 py-6">
                <h3 class="text-lg font-medium text-gray-900">محتوى المقال</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل محتوى المقال.</p>
                
                <div class="mt-6">
                    <label for="content" class="block text-sm font-medium text-gray-700">محتوى المقال *</label>
                    <textarea name="content" id="content" rows="12" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('content', $article->content) }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- معلومات المقال -->
            <div class="py-6">
                <h3 class="text-lg font-medium text-gray-900">معلومات المقال</h3>
                <p class="mt-1 text-sm text-gray-500">معلومات إضافية عن المقال.</p>
                
                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">تاريخ الإنشاء</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $article->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">آخر تحديث</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $article->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">المؤلف</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $article->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">عدد الكلمات</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ str_word_count(strip_tags($article->content)) }} كلمة</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900">إعدادات النشر</h3>
                <div class="mt-4 flex items-center">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" id="is_published" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                    <label for="is_published" class="mr-2 block text-sm text-gray-700">نشر المقال</label>
                </div>
                <p class="mt-1 text-xs text-gray-500">عند إلغاء هذا الخيار سيتم إخفاء المقال من الموقع العام.</p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" id="preview-article-btn" class="inline-flex items-center px-4 py-2 border border-indigo-300 text-sm font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                    استعراض المقال
                </button>
                <a href="{{ route('articles.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    إلغاء
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    تحديث المقال
                </button>
            </div>
        </form>
    </div>
</div>

<div id="article-preview-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-start justify-center bg-black/50 px-4 py-8">
        <div class="w-full max-w-3xl rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-4 py-3">
                <h3 class="text-lg font-semibold text-gray-900">استعراض المقال</h3>
                <button type="button" id="close-preview-modal" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <div class="p-6">
                <h2 id="preview-title" class="mb-4 text-2xl font-bold text-gray-900"></h2>
                <div id="preview-image-wrap" class="mb-4 {{ $article->image ? '' : 'hidden' }}">
                    <img id="preview-image" src="{{ $article->image ? Storage::url($article->image) : '' }}" alt="معاينة صورة المقال" class="h-64 w-full rounded object-cover">
                </div>
                <p id="preview-content" class="whitespace-pre-line text-gray-700"></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const previewBtn = document.getElementById('preview-article-btn');
    const closeBtn = document.getElementById('close-preview-modal');
    const modal = document.getElementById('article-preview-modal');
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    const imageInput = document.getElementById('image');
    const previewTitle = document.getElementById('preview-title');
    const previewContent = document.getElementById('preview-content');
    const previewImageWrap = document.getElementById('preview-image-wrap');
    const previewImage = document.getElementById('preview-image');

    function closeModal() {
        modal.classList.add('hidden');
    }

    previewBtn?.addEventListener('click', function () {
        previewTitle.textContent = titleInput.value || 'بدون عنوان';
        previewContent.textContent = contentInput.value || 'لا يوجد محتوى للمعاينة';

        const file = imageInput.files && imageInput.files[0];
        if (file) {
            previewImage.src = URL.createObjectURL(file);
            previewImageWrap.classList.remove('hidden');
        } else if (!previewImage.src) {
            previewImageWrap.classList.add('hidden');
        }

        modal.classList.remove('hidden');
    });

    closeBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
});
</script>
@endsection