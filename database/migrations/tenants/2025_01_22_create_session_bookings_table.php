<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
          # إنشاء جدول حجوزات جلسات التدريب

          1. الجداول الجديدة
            - `session_bookings`
              - `id` (bigint, primary key)
              - `training_session_id` (bigint, foreign key)
              - `user_id` (bigint, foreign key)
              - `booking_date` (date, تاريخ الحجز)
              - `booking_time` (time, وقت الحجز)
              - `status` (enum, حالة الحجز)
              - `payment_status` (enum, حالة الدفع)
              - `payment_amount` (decimal, مبلغ الدفع)
              - `payment_reference` (string, مرجع الدفع)
              - `stripe_payment_intent_id` (string, معرف Stripe)
              - `notes` (text, ملاحظات)
              - `created_at` (timestamp)
              - `updated_at` (timestamp)

          2. الأمان
            - مفاتيح خارجية للجلسة والمستخدم
            - فهرسة على الحقول المهمة
        */

        Schema::create('session_bookings', function (Blueprint $table) {
            $table->id();
            // تجنّب FK مباشر بسبب ترتيب الميجريشن، سيضاف لاحقاً إن لزم
            $table->unsignedBigInteger('training_session_id'); // ربط بالجلسة
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // المستخدم الذي حجز
            $table->date('booking_date'); // تاريخ الحجز
            $table->time('booking_time'); // وقت الحجز
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending'); // حالة الحجز
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending'); // حالة الدفع
            $table->decimal('payment_amount', 10, 2)->default(0); // مبلغ الدفع
            $table->string('payment_reference')->nullable(); // مرجع الدفع
            $table->string('stripe_payment_intent_id')->nullable(); // معرف Stripe
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();
            
            // فهارس للأداء
            $table->index(['user_id', 'status']);
            $table->index(['booking_date', 'booking_time']);
            $table->index(['payment_status', 'status']);
            
            // قيد فريد لمنع الحجز المزدوج
            $table->unique(['training_session_id', 'booking_date', 'booking_time'], 'session_booking_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_bookings');
    }
};