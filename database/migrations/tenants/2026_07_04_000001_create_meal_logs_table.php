<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meal_logs')) {
            return;
        }

        Schema::create('meal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->date('logged_on');
            $table->string('meal_slot', 30)->default('lunch');
            $table->unsignedTinyInteger('adherence_score')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'logged_on', 'meal_slot']);
            $table->index(['user_id', 'logged_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_logs');
    }
};
