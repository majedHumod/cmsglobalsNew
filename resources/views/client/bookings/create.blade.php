@extends('layouts.client')

@section('title', 'حجز جلسة')

@section('content')
<div class="space-y-4" x-data="clientBookingCreate({
    sessions: @json($sessions->map(fn ($s) => ['id' => $s->id, 'title' => $s->title, 'price' => $s->price])),
    selectedSessionId: {{ $selectedSessionId ?: 'null' }},
    selectedDate: @json($selectedDate),
    slotsUrlTemplate: @json(url('/client/training-sessions/__ID__/slots')),
    csrfToken: @json(csrf_token()),
})" x-init="init()">
    <h1 class="text-lg font-semibold text-slate-900">حجز جلسة جديدة</h1>

    <form method="POST" action="{{ route('client.bookings.store') }}" class="space-y-4">
        @csrf
        <div class="rounded-2xl bg-white p-5 shadow-sm space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">الجلسة</label>
                <select name="training_session_id" x-model="sessionId" @change="loadSlots()" required class="w-full rounded-xl border-slate-300">
                    <option value="">اختر الجلسة</option>
                    <template x-for="session in sessions" :key="session.id">
                        <option :value="session.id" x-text="session.title + (session.price > 0 ? ' — ' + session.price + ' ر.س' : ' — مجاني')"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">التاريخ</label>
                <input type="date" name="booking_date" x-model="date" @change="loadSlots()" required class="w-full rounded-xl border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">الوقت</label>
                <select name="booking_time" required class="w-full rounded-xl border-slate-300">
                    <option value="">اختر الوقت</option>
                    <template x-for="slot in availableSlots" :key="slot.time">
                        <option :value="slot.time" x-text="slot.label" :disabled="!slot.available"></option>
                    </template>
                </select>
                <p class="text-xs text-slate-500 mt-1" x-show="loadingSlots">جاري تحميل المواعيد...</p>
                <p class="text-xs text-rose-600 mt-1" x-text="slotsError" x-show="slotsError"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ملاحظات</label>
                <textarea name="notes" rows="2" class="w-full rounded-xl border-slate-300"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white rounded-xl py-3 font-medium">تأكيد الحجز</button>
        </div>
    </form>
</div>
@endsection

@push('head')
<script>
document.addEventListener('alpine:init', () => {
    window.Alpine.data('clientBookingCreate', (config) => ({
        sessions: config.sessions,
        sessionId: config.selectedSessionId ? String(config.selectedSessionId) : '',
        date: config.selectedDate,
        slots: [],
        loadingSlots: false,
        slotsError: null,
        init() {
            if (this.sessionId && this.date) {
                this.loadSlots();
            }
        },
        get availableSlots() {
            return this.slots.filter((slot) => slot.available);
        },
        async loadSlots() {
            this.slots = [];
            this.slotsError = null;
            if (!this.sessionId || !this.date) {
                return;
            }
            this.loadingSlots = true;
            try {
                const url = config.slotsUrlTemplate.replace('__ID__', this.sessionId) + '?date=' + encodeURIComponent(this.date);
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const data = await response.json();
                this.slots = data.slots ?? [];
            } catch (error) {
                this.slotsError = 'تعذر تحميل المواعيد المتاحة.';
            } finally {
                this.loadingSlots = false;
            }
        },
    }));
});
</script>
@endpush
