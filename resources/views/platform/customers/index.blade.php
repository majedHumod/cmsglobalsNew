<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-extrabold mb-2">عملاء المنصة (الأندية والمدربون)</h1>
        <p class="text-gray-600 mb-6">عرض الاشتراكات وحالة الوصول. المحتوى لا يُحذف تلقائياً.</p>
        <div class="overflow-x-auto bg-white shadow rounded-xl">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-right">النادي</th>
                        <th class="p-3 text-right">الدومين</th>
                        <th class="p-3 text-right">البريد</th>
                        <th class="p-3 text-right">الخطة</th>
                        <th class="p-3 text-right">نهاية الفترة</th>
                        <th class="p-3 text-right">الوصول</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="border-t">
                            <td class="p-3">{{ $row['tenant']->name }}</td>
                            <td class="p-3">{{ $row['tenant']->domain }}</td>
                            <td class="p-3">{{ $row['tenant']->email }}</td>
                            <td class="p-3">{{ $row['plan'] ?: '—' }}</td>
                            <td class="p-3">{{ $row['period_end']?->format('Y-m-d') ?: '—' }}</td>
                            <td class="p-3">{{ $row['access'] }}</td>
                        </tr>
                    @empty
                        <tr><td class="p-3" colspan="6">لا يوجد عملاء بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-guest-layout>
