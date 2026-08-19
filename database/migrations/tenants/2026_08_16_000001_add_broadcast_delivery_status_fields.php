<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('message_broadcasts')) {
            Schema::table('message_broadcasts', function (Blueprint $table) {
                if (! Schema::hasColumn('message_broadcasts', 'status')) {
                    $table->string('status', 32)->default('queued')->after('recipients_count');
                }
                if (! Schema::hasColumn('message_broadcasts', 'delivered_count')) {
                    $table->unsignedInteger('delivered_count')->default(0)->after('status');
                }
                if (! Schema::hasColumn('message_broadcasts', 'failed_count')) {
                    $table->unsignedInteger('failed_count')->default(0)->after('delivered_count');
                }
                if (! Schema::hasColumn('message_broadcasts', 'started_at')) {
                    $table->timestamp('started_at')->nullable()->after('sent_at');
                }
                if (! Schema::hasColumn('message_broadcasts', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('started_at');
                }
                if (! Schema::hasColumn('message_broadcasts', 'error_message')) {
                    $table->text('error_message')->nullable()->after('completed_at');
                }
            });
        }

        if (Schema::hasTable('message_broadcast_recipients')) {
            Schema::table('message_broadcast_recipients', function (Blueprint $table) {
                if (! Schema::hasColumn('message_broadcast_recipients', 'status')) {
                    $table->string('status', 32)->default('pending')->after('conversation_id');
                }
                if (! Schema::hasColumn('message_broadcast_recipients', 'error_message')) {
                    $table->text('error_message')->nullable()->after('read_at');
                }
                if (! Schema::hasColumn('message_broadcast_recipients', 'message_id')) {
                    $table->unsignedBigInteger('message_id')->nullable()->after('conversation_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('message_broadcasts')) {
            Schema::table('message_broadcasts', function (Blueprint $table) {
                foreach (['status', 'delivered_count', 'failed_count', 'started_at', 'completed_at', 'error_message'] as $column) {
                    if (Schema::hasColumn('message_broadcasts', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('message_broadcast_recipients')) {
            Schema::table('message_broadcast_recipients', function (Blueprint $table) {
                foreach (['status', 'error_message', 'message_id'] as $column) {
                    if (Schema::hasColumn('message_broadcast_recipients', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
