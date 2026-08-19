<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_plans', 'compare_at_price')) {
                // List / "was" price. Shown only when greater than the payable `price`.
                $table->decimal('compare_at_price', 10, 2)
                    ->nullable()
                    ->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_plans', 'compare_at_price')) {
                $table->dropColumn('compare_at_price');
            }
        });
    }
};
