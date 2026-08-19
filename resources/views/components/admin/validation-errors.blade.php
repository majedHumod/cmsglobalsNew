@props([
    'title' => null,
])

@if ($errors->any())
    <x-admin.alert type="error" :title="$title ?? 'تعذر حفظ البيانات:'">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-admin.alert>
@endif
