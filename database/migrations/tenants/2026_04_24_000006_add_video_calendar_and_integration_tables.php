<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('training_sessions', 'video_meeting_url')) {
                $table->string('video_meeting_url')->nullable()->after('location');
            }
        });

        Schema::table('session_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('session_bookings', 'video_meeting_url')) {
                $table->string('video_meeting_url')->nullable()->after('booking_time');
            }
            if (! Schema::hasColumn('session_bookings', 'calendar_uid')) {
                $table->string('calendar_uid', 120)->nullable()->after('video_meeting_url');
            }
        });

        if (! Schema::hasTable('integration_webhook_logs')) {
            Schema::create('integration_webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 60);
                $table->string('event_type', 100)->nullable();
                $table->json('payload')->nullable();
                $table->unsignedSmallInteger('status_code')->nullable();
                $table->string('reference', 120)->nullable();
                $table->timestamps();

                $table->index(['provider', 'created_at'], 'integration_provider_idx');
            });
        }

        if (! Schema::hasTable('follow_up_campaign_runs')) {
            Schema::create('follow_up_campaign_runs', function (Blueprint $table) {
                $table->id();
                $table->string('campaign_key', 80);
                $table->unsignedBigInteger('user_id');
                $table->string('status', 30)->default('sent');
                $table->json('meta')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index(['campaign_key', 'user_id'], 'followup_campaign_user_idx');
                $table->index(['campaign_key', 'sent_at'], 'followup_campaign_sent_idx');
                $table->index('user_id', 'followup_campaign_user_only_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_campaign_runs');
        Schema::dropIfExists('integration_webhook_logs');

        Schema::table('session_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('session_bookings', 'calendar_uid')) {
                $table->dropColumn('calendar_uid');
            }
            if (Schema::hasColumn('session_bookings', 'video_meeting_url')) {
                $table->dropColumn('video_meeting_url');
            }
        });

        Schema::table('training_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('training_sessions', 'video_meeting_url')) {
                $table->dropColumn('video_meeting_url');
            }
        });
    }
};
