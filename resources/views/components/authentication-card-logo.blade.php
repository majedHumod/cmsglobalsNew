@php
    $siteLogo = \App\Models\SiteSetting::get('site_logo');
    $siteName = \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel'));
@endphp

<a href="{{ route('home') }}" class="flex flex-col items-center justify-center gap-2 text-center no-underline" title="{{ $siteName }}">
    @if($siteLogo)
        <img
            src="{{ \Illuminate\Support\Facades\Storage::url($siteLogo) }}"
            alt="{{ $siteName }}"
            title="{{ $siteName }}"
            class="h-16 w-auto max-w-[14rem] object-contain"
        >
    @else
        <span class="text-2xl font-bold text-gray-800" title="{{ $siteName }}">{{ $siteName }}</span>
    @endif
</a>
