@php
    $selectedMembershipTypes = old('required_membership_types', $selectedMembershipTypes ?? ($model->required_membership_types ?? []));
    if (is_string($selectedMembershipTypes)) {
        $selectedMembershipTypes = json_decode($selectedMembershipTypes, true) ?? [];
    }

    $audienceFieldsWrapperClass = $audienceFieldsWrapperClass ?? 'border-b border-gray-200 py-6';
    $audienceHeading = $audienceHeading ?? 'استهداف المحتوى';
    $audienceIntro = $audienceIntro ?? 'يمكنك جعل المحتوى مشتركاً أو تخصيصه حسب الجنس أو مسار المتدرب.';
    $membershipBlockId = $membershipBlockId ?? null;
    $membershipPathsLabel = $membershipPathsLabel ?? 'المسارات المسموح لها';
    $membershipPathsHint = $membershipPathsHint ?? 'اتركها فارغة لعرض المحتوى لكل المسارات.';

    try {
        $audienceMembershipTypes = \App\Models\MembershipType::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    } catch (\Throwable $e) {
        $audienceMembershipTypes = collect();
    }
@endphp

@if($audienceFieldsWrapperClass !== '')
<div class="{{ $audienceFieldsWrapperClass }}">
@endif
    <h3 class="text-lg font-medium text-gray-900">{{ $audienceHeading }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ $audienceIntro }}</p>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="audience_gender" class="block text-sm font-medium text-gray-700">الجمهور حسب الجنس</label>
            <select name="audience_gender" id="audience_gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="all" {{ old('audience_gender', $model->audience_gender ?? 'all') === 'all' ? 'selected' : '' }}>الجميع</option>
                <option value="male" {{ old('audience_gender', $model->audience_gender ?? 'all') === 'male' ? 'selected' : '' }}>رجال</option>
                <option value="female" {{ old('audience_gender', $model->audience_gender ?? 'all') === 'female' ? 'selected' : '' }}>نساء</option>
            </select>
            @error('audience_gender')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-6"@if($membershipBlockId) id="{{ $membershipBlockId }}"@endif>
        <label class="block text-sm font-medium text-gray-700">{{ $membershipPathsLabel }}</label>
        <p class="mt-1 text-sm text-gray-500">{{ $membershipPathsHint }}</p>

        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @forelse($audienceMembershipTypes as $membershipType)
                <label class="flex items-center rounded-lg border border-gray-200 p-3">
                    <input
                        type="checkbox"
                        name="required_membership_types[]"
                        value="{{ $membershipType->id }}"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        {{ in_array($membershipType->id, $selectedMembershipTypes, true) ? 'checked' : '' }}
                    >
                    <span class="mr-3 text-sm text-gray-700">{{ $membershipType->name }}</span>
                </label>
            @empty
                <p class="text-sm text-gray-500">لا توجد مسارات مفعلة حالياً.</p>
            @endforelse
        </div>

        @error('required_membership_types')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
@if($audienceFieldsWrapperClass !== '')
</div>
@endif
