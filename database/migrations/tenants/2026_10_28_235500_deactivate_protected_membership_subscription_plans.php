<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $protectedIds = DB::table('membership_types')
            ->where('is_protected', true)
            ->pluck('id');

        if ($protectedIds->isEmpty()) {
            return;
        }

        DB::table('subscription_plans')
            ->whereIn('membership_type_id', $protectedIds)
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        // لا نعيد تفعيل الخطط الإدارية تلقائياً لأنها ليست للبيع.
    }
};
