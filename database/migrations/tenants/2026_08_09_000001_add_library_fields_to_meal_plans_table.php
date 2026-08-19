<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_plans', function (Blueprint $table) {
            $table->string('source', 40)->default('custom')->after('user_id')->index();
            $table->string('external_id', 80)->nullable()->after('source');
            $table->string('name_en')->nullable()->after('name');
            $table->text('description_en')->nullable()->after('description');
            $table->text('ingredients_en')->nullable()->after('ingredients');
            $table->text('instructions_en')->nullable()->after('instructions');
            $table->json('ingredients_json')->nullable()->after('ingredients_en');
            $table->boolean('nutrition_is_estimated')->default(false)->after('fats');
            $table->string('nutrition_source', 60)->nullable()->after('nutrition_is_estimated');
            $table->string('image_attribution')->nullable()->after('image');
            $table->string('image_attribution_url')->nullable()->after('image_attribution');

            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('meal_plans', function (Blueprint $table) {
            $table->dropUnique(['source', 'external_id']);
            $table->dropColumn([
                'source',
                'external_id',
                'name_en',
                'description_en',
                'ingredients_en',
                'instructions_en',
                'ingredients_json',
                'nutrition_is_estimated',
                'nutrition_source',
                'image_attribution',
                'image_attribution_url',
            ]);
        });
    }
};
