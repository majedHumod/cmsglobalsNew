@extends('layouts.public-page')

@section('title', 'دفع هايبر باي')

@section('content')
<section class="py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">إتمام الدفع</h1>
            <p class="text-gray-600 mb-6">الخطة: <strong>{{ $membership->subscriptionPlan->name }}</strong></p>
            <form action="{{ $returnUrl }}" class="paymentWidgets" data-brands="{{ $widget['brands'] ?? 'VISA MASTER MADA' }}"></form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script
        src="{{ $widget['script_url'] }}"
        @if(!empty($widget['integrity'])) integrity="{{ $widget['integrity'] }}" crossorigin="anonymous" @endif
    ></script>
@endpush
