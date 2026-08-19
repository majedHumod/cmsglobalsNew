<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('message_templates') && ! Schema::hasColumn('message_templates', 'description')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->string('description')->nullable()->after('name');
            });
        }

        if (Schema::hasTable('message_broadcasts') && ! Schema::hasColumn('message_broadcasts', 'template_id')) {
            Schema::table('message_broadcasts', function (Blueprint $table) {
                $table->foreignId('template_id')->nullable()->after('sender_user_id')->constrained('message_templates')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->json('preferences')->nullable();
                $table->timestamps();
                $table->unique('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('message_broadcasts') && Schema::hasColumn('message_broadcasts', 'template_id')) {
            Schema::table('message_broadcasts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('template_id');
            });
        }

        if (Schema::hasTable('message_templates') && Schema::hasColumn('message_templates', 'description')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }

        Schema::dropIfExists('notification_preferences');
    }
};
