<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('endpoint', 1024);
                $table->text('public_key')->nullable();
                $table->text('auth_token')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'endpoint'], 'push_user_endpoint_uq');
                $table->index(['user_id', 'last_seen_at'], 'push_user_seen_idx');
            });
        }

        if (! Schema::hasTable('community_posts')) {
            Schema::create('community_posts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->text('content');
                $table->boolean('is_visible')->default(true);
                $table->timestamps();

                $table->index(['is_visible', 'created_at'], 'community_post_vis_idx');
                $table->index('user_id', 'community_post_user_idx');
            });
        }

        if (! Schema::hasTable('community_reactions')) {
            Schema::create('community_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('community_posts')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->string('reaction', 30)->default('like');
                $table->timestamps();

                $table->unique(['post_id', 'user_id'], 'community_reaction_uq');
                $table->index('user_id', 'community_reaction_user_idx');
            });
        }

        if (! Schema::hasTable('community_comments')) {
            Schema::create('community_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('community_posts')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->text('content');
                $table->timestamps();

                $table->index(['post_id', 'created_at'], 'community_comment_idx');
                $table->index('user_id', 'community_comment_user_idx');
            });
        }

        if (! Schema::hasTable('gamification_badges')) {
            Schema::create('gamification_badges', function (Blueprint $table) {
                $table->id();
                $table->string('code', 80)->unique();
                $table->string('name');
                $table->string('description')->nullable();
                $table->unsignedInteger('points')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->foreignId('badge_id')->constrained('gamification_badges')->cascadeOnDelete();
                $table->timestamp('awarded_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'badge_id'], 'user_badge_uq');
                $table->index('user_id', 'user_badges_user_idx');
            });
        }

        if (! Schema::hasTable('weekly_challenges')) {
            Schema::create('weekly_challenges', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('challenge_type', 50)->default('habit_completion');
                $table->unsignedInteger('target_value')->default(5);
                $table->date('starts_on');
                $table->date('ends_on');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('challenge_participants')) {
            Schema::create('challenge_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('challenge_id')->constrained('weekly_challenges')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->unsignedInteger('progress_value')->default(0);
                $table->boolean('is_completed')->default(false);
                $table->timestamps();

                $table->unique(['challenge_id', 'user_id'], 'challenge_user_uq');
                $table->index('user_id', 'challenge_participant_user_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_participants');
        Schema::dropIfExists('weekly_challenges');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('gamification_badges');
        Schema::dropIfExists('community_comments');
        Schema::dropIfExists('community_reactions');
        Schema::dropIfExists('community_posts');
        Schema::dropIfExists('push_subscriptions');
    }
};
