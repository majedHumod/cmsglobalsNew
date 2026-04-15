@if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $discount ? route('nutrition-discounts.update', $discount) : route('nutrition-discounts.store') }}" 
      method="POST" enctype="multipart/form-data">
    @csrf
    @if($discount)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Name Field -->
        <div class="md:col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                اسم المركز <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="{{ old('name', $discount->name ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                   required>
        </div>

        <!-- Discount Percentage -->
        <div>
            <label for="discount_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                نسبة الخصم <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="number" 
                       id="discount_percentage" 
                       name="discount_percentage" 
                       value="{{ old('discount_percentage', $discount->discount_percentage ?? '') }}"
                       min="1" 
                       max="100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                       required>
                <span class="absolute left-3 top-2 text-gray-500">%</span>
            </div>
        </div>

        <!-- Start Date -->
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                تاريخ البداية <span class="text-red-500">*</span>
            </label>
            <input type="date" 
                   id="start_date" 
                   name="start_date" 
                   value="{{ old('start_date', $discount?->start_date?->format('Y-m-d')) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                   required>
        </div>

        <!-- End Date -->
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                تاريخ النهاية <span class="text-red-500">*</span>
            </label>
            <input type="date" 
                   id="end_date" 
                   name="end_date" 
                   value="{{ old('end_date', $discount?->end_date?->format('Y-m-d')) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                   required>
        </div>

        <!-- Image Upload -->
        <div class="md:col-span-2">
            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                صورة المركز
            </label>
            <input type="file" 
                   id="image" 
                   name="image" 
                   accept="image/*"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-sm text-gray-500 mt-1 text-right">ارفع صورة (JPEG, PNG, JPG, GIF) - الحد الأقصى 2 ميجابايت</p>
            
            @if($discount && $discount->image)
                <div class="mt-4">
                    <p class="text-sm text-gray-700 mb-2 text-right">الصورة الحالية:</p>
                    <img src="{{ $discount->image_url }}" alt="{{ $discount->name }}" 
                         class="w-32 h-32 object-cover rounded-lg border">
                </div>
            @endif
        </div>

        <!-- Active Status -->
        <div class="md:col-span-2">
            <div class="flex items-center">
                <input type="checkbox" 
                       id="is_active" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', $discount->is_active ?? true) ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="mr-2 block text-sm text-gray-700">
                    خصم نشط
                </label>
            </div>
            <p class="text-sm text-gray-500 mt-1 text-right">قم بإلغاء التحديد لإلغاء تفعيل هذا الخصم</p>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="flex justify-end space-x-4 mt-8">
        <a href="{{ route('nutrition-discounts.index') }}" 
           class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
            إلغاء
        </a>
        <button type="submit" 
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
            {{ $discount ? 'تحديث الخصم' : 'إنشاء الخصم' }}
        </button>
    </div>
</form>
