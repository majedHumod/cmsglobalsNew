<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('supplement_plans')) {
            Schema::create('supplement_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('supplement_type', [
                    'protein',
                    'vitamins',
                    'minerals',
                    'pre_workout',
                    'post_workout',
                    'omega',
                    'general',
                ])->default('general');
                $table->string('dosage')->nullable();
                $table->enum('timing', [
                    'morning',
                    'pre_workout',
                    'post_workout',
                    'night',
                    'with_meal',
                ])->default('morning');
                $table->string('brand')->nullable();
                $table->string('image')->nullable();
                $table->text('instructions')->nullable();
                $table->text('warnings')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('audience_gender')->default('all');
                $table->json('required_membership_types')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['is_active', 'supplement_type']);
                $table->index(['user_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplement_plans');
    }
};
