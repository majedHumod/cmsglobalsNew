<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfNeeded('push_subscriptions', 'push_last_seen_idx', function (Blueprint $table) {
            $table->index(['last_seen_at'], 'push_last_seen_idx');
        });

        $this->addIndexIfNeeded('community_posts', 'community_posts_user_created_idx', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'community_posts_user_created_idx');
        });

        $this->addIndexIfNeeded('community_comments', 'community_comments_user_created_idx', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'community_comments_user_created_idx');
        });

        $this->addIndexIfNeeded('challenge_participants', 'challenge_progress_idx', function (Blueprint $table) {
            $table->index(['is_completed', 'progress_value'], 'challenge_progress_idx');
        });

        $this->addIndexIfNeeded('follow_up_campaign_runs', 'followup_status_sent_idx', function (Blueprint $table) {
            $table->index(['status', 'sent_at'], 'followup_status_sent_idx');
        });
    }

    public function down(): void
    {
        $this->dropIndexIfNeeded('follow_up_campaign_runs', 'followup_status_sent_idx');
        $this->dropIndexIfNeeded('challenge_participants', 'challenge_progress_idx');
        $this->dropIndexIfNeeded('community_comments', 'community_comments_user_created_idx');
        $this->dropIndexIfNeeded('community_posts', 'community_posts_user_created_idx');
        $this->dropIndexIfNeeded('push_subscriptions', 'push_last_seen_idx');
    }

    private function addIndexIfNeeded(string $tableName, string $indexName, \Closure $callback): void
    {
        if (! Schema::hasTable($tableName) || $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, $callback);
    }

    private function dropIndexIfNeeded(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::connection('tenant')->getDatabaseName();
        if (! $database) {
            return false;
        }

        return collect(DB::connection('tenant')->select(
            'select index_name from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ? limit 1',
            [$database, $tableName, $indexName]
        ))->isNotEmpty();
    }
};
