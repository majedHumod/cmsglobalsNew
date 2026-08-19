<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('message_templates')) {
            Schema::create('message_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->string('category', 80)->default('general');
                $table->text('body');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('message_broadcasts')) {
            Schema::create('message_broadcasts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title')->nullable();
                $table->text('body');
                $table->string('segment_type', 80)->default('all_clients');
                $table->json('segment_filters')->nullable();
                $table->unsignedInteger('recipients_count')->default(0);
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('message_broadcast_recipients')) {
            Schema::create('message_broadcast_recipients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('broadcast_id')->constrained('message_broadcasts')->cascadeOnDelete();
                $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('conversation_id')->nullable()->constrained('conversations')->nullOnDelete();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('message_broadcast_recipients')) {
            $indexes = collect(DB::select('SHOW INDEX FROM `message_broadcast_recipients`'))
                ->pluck('Key_name')
                ->unique()
                ->all();

            if (! in_array('msg_br_recipient_uq', $indexes, true)) {
                Schema::table('message_broadcast_recipients', function (Blueprint $table) {
                    $table->unique(['broadcast_id', 'recipient_user_id'], 'msg_br_recipient_uq');
                });
            }

            if (! in_array('msg_br_rec_rd_idx', $indexes, true)) {
                Schema::table('message_broadcast_recipients', function (Blueprint $table) {
                    $table->index(['recipient_user_id', 'read_at'], 'msg_br_rec_rd_idx');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_broadcast_recipients');
        Schema::dropIfExists('message_broadcasts');
        Schema::dropIfExists('message_templates');
    }
};
