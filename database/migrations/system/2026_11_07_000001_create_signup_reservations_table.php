<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('signup_reservations')) {
            return;
        }

        Schema::create('signup_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('slug', 63);
            $table->string('email');
            $table->string('mobile_e164', 32)->nullable()->index();
            $table->string('plan_code', 64)->nullable();
            // pending (invoice created) → paid → provisioned | released (expired/cancelled) | conflict (paid but could not be honoured)
            $table->string('status', 24)->default('pending')->index();
            // Set only while the reservation is "live"; the UNIQUE indexes make concurrent claims on the same slug/email impossible.
            $table->string('active_slug', 63)->nullable()->unique();
            $table->string('active_email')->nullable()->unique();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signup_reservations');
    }
};
