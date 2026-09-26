<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            if (! Schema::hasColumn('user_memberships', 'payment_channel')) {
                $table->string('payment_channel')->nullable()->after('payment_reference');
            }

            if (! Schema::hasColumn('user_memberships', 'gateway_reference')) {
                $table->string('gateway_reference')->nullable()->after('payment_channel');
            }

            if (! Schema::hasColumn('user_memberships', 'transfer_reference')) {
                $table->string('transfer_reference')->nullable()->after('gateway_reference');
            }

            if (! Schema::hasColumn('user_memberships', 'transfer_receipt')) {
                $table->string('transfer_receipt')->nullable()->after('transfer_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            foreach (['transfer_receipt', 'transfer_reference', 'gateway_reference', 'payment_channel'] as $column) {
                if (Schema::hasColumn('user_memberships', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
