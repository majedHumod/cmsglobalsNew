<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'access_status')) {
                $table->string('access_status', 32)->default('active')->after('status');
            }
            if (! Schema::hasColumn('tenants', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            }
            if (! Schema::hasColumn('tenants', 'grace_ends_at')) {
                $table->timestamp('grace_ends_at')->nullable()->after('subscription_ends_at');
            }
            if (! Schema::hasColumn('tenants', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('grace_ends_at');
            }
            if (! Schema::hasColumn('tenants', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('suspended_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $columns = ['access_status', 'subscription_ends_at', 'grace_ends_at', 'suspended_at', 'archived_at'];
            $existing = array_values(array_filter($columns, fn (string $col) => Schema::hasColumn('tenants', $col)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
