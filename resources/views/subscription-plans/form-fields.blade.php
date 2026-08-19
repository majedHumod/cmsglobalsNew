@php
    $plan = $subscriptionPlan ?? null;
    $features = old('features', $plan?->features ?? ['']);
    if (! is_array($features) || count($features) === 0) {
        $features = [''];
    }
    $selectedMembershipTypeId = old(
        'membership_type_id',
        $plan?->membership_type_id ?? ($selectedMembershipTypeId ?? null)
    );
@endphp

<div class="space-y-6">
    <x-admin.alert type="info">
        خطة الاشتراك هي العرض التجاري (السعر والمدة والمميزات). مسار العضوية يحدد فقط صلاحية الوصول للمحتوى بعد الاشتراك.
    </x-admin.alert>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <x-admin.label for="membership_type_id" value="مسار العضوية *" />
            <x-admin.select name="membership_type_id" id="membership_type_id" required>
                @foreach($membershipTypes as $membershipType)
                    <option value="{{ $membershipType->id }}" @selected((string) $selectedMembershipTypeId === (string) $membershipType->id)>
                        {{ $membershipType->name }}
                    </option>
                @endforeach
            </x-admin.select>
            <x-admin.field-error name="membership_type_id" />
        </div>

        <div>
            <x-admin.label for="name" value="اسم الخطة *" />
            <x-admin.input type="text" name="name" id="name" :value="old('name', $plan?->name)" required />
            <x-admin.field-error name="name" />
        </div>
    </div>

    <div>
        <x-admin.label for="description" value="الوصف" />
        <x-admin.textarea name="description" id="description" rows="3">{{ old('description', $plan?->description) }}</x-admin.textarea>
        <x-admin.field-error name="description" />
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">
        <div>
            <x-admin.label for="duration_days" value="المدة بالأيام *" />
            <x-admin.input type="number" name="duration_days" id="duration_days" min="1" max="100000" :value="old('duration_days', $plan?->duration_days ?? 30)" required />
            <x-admin.field-error name="duration_days" />
        </div>

        <div>
            <x-admin.label for="compare_at_price" value="السعر قبل الخصم" />
            <x-admin.input type="number" step="0.01" name="compare_at_price" id="compare_at_price" min="0" :value="old('compare_at_price', $plan?->compare_at_price)" placeholder="اختياري" />
            <p class="mt-1 text-xs text-tremor-content-subtle">يظهر مشطوباً للعميل إذا كان أعلى من سعر البيع.</p>
            <x-admin.field-error name="compare_at_price" />
        </div>

        <div>
            <x-admin.label for="price" value="سعر البيع بعد الخصم *" />
            <x-admin.input type="number" step="0.01" name="price" id="price" min="0" :value="old('price', $plan?->price ?? 0)" required />
            <p class="mt-1 text-xs text-tremor-content-subtle">هذا هو المبلغ الذي يدفعه العميل فعلياً.</p>
            <x-admin.field-error name="price" />
        </div>

        <div>
            <x-admin.label for="gender_scope" value="نطاق الجنس *" />
            <x-admin.select name="gender_scope" id="gender_scope" required>
                <option value="all" @selected(old('gender_scope', $plan?->gender_scope ?? 'all') === 'all')>الجميع</option>
                <option value="male" @selected(old('gender_scope', $plan?->gender_scope) === 'male')>رجال</option>
                <option value="female" @selected(old('gender_scope', $plan?->gender_scope) === 'female')>نساء</option>
            </x-admin.select>
            <x-admin.field-error name="gender_scope" />
        </div>

        <div>
            <x-admin.label for="sort_order" value="الترتيب" />
            <x-admin.input type="number" name="sort_order" id="sort_order" min="0" :value="old('sort_order', $plan?->sort_order ?? 0)" />
            <x-admin.field-error name="sort_order" />
        </div>
    </div>

    <div>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <x-admin.label value="المزايا" />
                <p class="mt-1 text-sm text-tremor-content">أضف أي عدد من المزايا التي تظهر للعميل في بطاقة الخطة.</p>
            </div>
            <x-admin.button type="button" id="add-feature" variant="secondary" size="sm">إضافة ميزة</x-admin.button>
        </div>

        <div id="features-container" class="mt-3 space-y-3">
            @foreach($features as $index => $feature)
                <div class="feature-item flex items-center gap-2">
                    <input
                        type="text"
                        name="features[]"
                        value="{{ $feature }}"
                        class="mt-0 block w-full flex-1 rounded-tremor-default border-tremor-border shadow-tremor-input focus:border-tremor-brand focus:ring-tremor-brand"
                        placeholder="مثال: متابعة أسبوعية مع المدرب"
                    >
                    <x-admin.button
                        type="button"
                        variant="danger"
                        size="sm"
                        class="remove-feature {{ count($features) === 1 ? 'hidden' : '' }}"
                    >
                        حذف
                    </x-admin.button>
                </div>
            @endforeach
        </div>
        <x-admin.field-error name="features" />
    </div>

    <label class="flex items-start gap-3">
        <input type="hidden" name="is_active" value="0">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            class="mt-1 rounded border-tremor-border text-tremor-brand shadow-tremor-input focus:border-tremor-brand focus:ring-tremor-brand"
            @checked(old('is_active', $plan?->is_active ?? true))
        >
        <span class="text-sm text-tremor-content-emphasis">الخطة مفعلة</span>
    </label>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('features-container');
        const addButton = document.getElementById('add-feature');

        if (!container || !addButton) {
            return;
        }

        const inputClass = 'mt-0 block w-full flex-1 rounded-tremor-default border-tremor-border shadow-tremor-input focus:border-tremor-brand focus:ring-tremor-brand';
        const removeClass = 'remove-feature inline-flex items-center justify-center gap-2 rounded-tremor-default border border-transparent bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500';

        const updateRemoveButtons = () => {
            const items = container.querySelectorAll('.feature-item');
            items.forEach((item) => {
                const removeButton = item.querySelector('.remove-feature');
                if (!removeButton) {
                    return;
                }
                removeButton.classList.toggle('hidden', items.length <= 1);
            });
        };

        addButton.addEventListener('click', function () {
            const item = document.createElement('div');
            item.className = 'feature-item flex items-center gap-2';
            item.innerHTML = `
                <input type="text" name="features[]" value="" class="${inputClass}" placeholder="أدخل ميزة جديدة">
                <button type="button" class="${removeClass}">حذف</button>
            `;
            container.appendChild(item);
            item.querySelector('input')?.focus();
            updateRemoveButtons();
        });

        container.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-feature');
            if (!removeButton) {
                return;
            }

            const item = removeButton.closest('.feature-item');
            if (item && container.querySelectorAll('.feature-item').length > 1) {
                item.remove();
                updateRemoveButtons();
            }
        });

        updateRemoveButtons();
    });
</script>
