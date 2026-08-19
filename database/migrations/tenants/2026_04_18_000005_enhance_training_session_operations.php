<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('training_sessions', 'session_type')) {
                $table->enum('session_type', ['online', 'in_person', 'hybrid'])
                    ->default('in_person')
                    ->after('duration_hours');
            }

            if (! Schema::hasColumn('training_sessions', 'capacity')) {
                $table->unsignedInteger('capacity')->default(1)->after('session_type');
            }

            if (! Schema::hasColumn('training_sessions', 'location')) {
                $table->string('location')->nullable()->after('capacity');
            }
        });

        Schema::table('session_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('session_bookings', 'attendance_status')) {
                $table->enum('attendance_status', ['scheduled', 'attended', 'missed', 'late_cancelled'])
                    ->default('scheduled')
                    ->after('payment_status');
            }

            if (! Schema::hasColumn('session_bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('attendance_status');
            }

            if (! Schema::hasColumn('session_bookings', 'cancelled_by_user_id')) {
                $table->foreignId('cancelled_by_user_id')
                    ->nullable()
                    ->after('cancelled_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('session_bookings', 'rescheduled_from_booking_id')) {
                $table->foreignId('rescheduled_from_booking_id')
                    ->nullable()
                    ->after('cancelled_by_user_id')
                    ->constrained('session_bookings')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('session_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('session_bookings', 'rescheduled_from_booking_id')) {
                $table->dropForeign(['rescheduled_from_booking_id']);
                $table->dropColumn('rescheduled_from_booking_id');
            }

            if (Schema::hasColumn('session_bookings', 'cancelled_by_user_id')) {
                $table->dropForeign(['cancelled_by_user_id']);
                $table->dropColumn('cancelled_by_user_id');
            }

            if (Schema::hasColumn('session_bookings', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }

            if (Schema::hasColumn('session_bookings', 'attendance_status')) {
                $table->dropColumn('attendance_status');
            }
        });

        Schema::table('training_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('training_sessions', 'location')) {
                $table->dropColumn('location');
            }

            if (Schema::hasColumn('training_sessions', 'capacity')) {
                $table->dropColumn('capacity');
            }

            if (Schema::hasColumn('training_sessions', 'session_type')) {
                $table->dropColumn('session_type');
            }
        });
    }
};
