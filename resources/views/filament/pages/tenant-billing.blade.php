<x-filament-panels::page>
    <div class="space-y-6">
        @unless ($this->tenantInfo)
            <div class="rounded-xl bg-white p-6 text-sm text-gray-600 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-300 dark:ring-white/10">
                لم يتم تحديد المستأجر الحالي.
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="text-sm text-gray-500">المستأجر</div>
                    <div class="mt-1 text-lg font-semibold">{{ $this->tenantInfo['name'] }}</div>
                    @if ($this->tenantInfo['domain'])
                        <div class="mt-1 text-sm text-gray-500">{{ $this->tenantInfo['domain'] }}</div>
                    @endif
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="text-sm text-gray-500">الخطة الحالية</div>
                    @if ($this->planInfo)
                        <div class="mt-1 text-lg font-semibold">{{ $this->planInfo['name'] }}</div>
                        <div class="mt-1 text-sm text-gray-500">
                            {{ $this->planInfo['price'] }} · {{ $this->planInfo['interval'] }} · {{ $this->planInfo['code'] }}
                        </div>
                    @else
                        <div class="mt-1 text-sm text-gray-500">لا توجد خطة مرتبطة.</div>
                    @endif

                    @if ($this->subscriptionInfo)
                        <div class="mt-3 text-sm">
                            الحالة: <span class="font-medium">{{ $this->subscriptionInfo['status'] ?? '—' }}</span>
                            <div class="mt-1 text-gray-500">
                                الفترة: {{ $this->subscriptionInfo['starts_at'] ?? '—' }} → {{ $this->subscriptionInfo['ends_at'] ?? '—' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-200 dark:ring-amber-400/20">
                تغيير الخطة وتحديث وسيلة الدفع قريباً — هذه الصفحة للعرض حالياً.
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-200 px-4 py-3 text-sm font-semibold dark:border-white/10">آخر الفواتير</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-2 text-right">الرقم</th>
                                    <th class="px-4 py-2 text-right">المبلغ</th>
                                    <th class="px-4 py-2 text-right">الحالة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                @forelse ($this->invoices as $invoice)
                                    <tr>
                                        <td class="px-4 py-2">{{ $invoice['number'] }}</td>
                                        <td class="px-4 py-2">{{ $invoice['amount'] }}</td>
                                        <td class="px-4 py-2">{{ $invoice['status'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">لا توجد فواتير.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-200 px-4 py-3 text-sm font-semibold dark:border-white/10">آخر المدفوعات</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-2 text-right">المبلغ</th>
                                    <th class="px-4 py-2 text-right">الحالة</th>
                                    <th class="px-4 py-2 text-right">التاريخ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                @forelse ($this->payments as $payment)
                                    <tr>
                                        <td class="px-4 py-2">{{ $payment['amount'] }}</td>
                                        <td class="px-4 py-2">{{ $payment['status'] }}</td>
                                        <td class="px-4 py-2">{{ $payment['paid_at'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">لا توجد مدفوعات.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endunless
    </div>
</x-filament-panels::page>
