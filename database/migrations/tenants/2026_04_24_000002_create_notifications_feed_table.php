<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications_feed')) {
            Schema::create('notifications_feed', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type', 80);
                $table->string('title');
                $table->text('body')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['user_id', 'read_at']);
                $table->index(['type', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_feed');
    }
};
