<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female'])->nullable()->after('email');
            }
        });

        Schema::table('user_memberships', function (Blueprint $table) {
            if (! Schema::hasColumn('user_memberships', 'subscription_plan_id')) {
                $table->foreignId('subscription_plan_id')
                    ->nullable()
                    ->after('membership_type_id')
                    ->constrained('subscription_plans')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('user_memberships', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('payment_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            if (Schema::hasColumn('user_memberships', 'subscription_plan_id')) {
                $table->dropForeign(['subscription_plan_id']);
                $table->dropColumn('subscription_plan_id');
            }

            if (Schema::hasColumn('user_memberships', 'stripe_payment_intent_id')) {
                $table->dropColumn('stripe_payment_intent_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
};
