<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('habits')) {
            Schema::create('habits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->string('unit', 50)->nullable();
                $table->unsignedInteger('target_value')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['client_user_id', 'is_active']);
            });
        }

        if (! Schema::hasTable('habit_logs')) {
            Schema::create('habit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('habit_id')->constrained('habits')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('logged_on');
                $table->unsignedInteger('value')->default(1);
                $table->boolean('is_completed')->default(true);
                $table->timestamps();

                $table->unique(['habit_id', 'logged_on']);
                $table->index(['user_id', 'logged_on']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('habit_logs');
        Schema::dropIfExists('habits');
    }
};
