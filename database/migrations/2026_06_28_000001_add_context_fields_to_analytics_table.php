<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WHATSAPP CLICK INTELLIGENCE — context enrichment.
     *
     * PRODUCTION-SAFE: Additive only. All new columns are nullable so existing
     * rows and existing writes are unaffected. No data is reset or removed.
     * No IP tracking is (re)introduced.
     *
     * Adds the dimensions needed to break WhatsApp clicks (and views) down by
     * category, location, source page, locale and device type, plus the indexes
     * that keep those aggregations fast at scale.
     */
    public function up(): void
    {
        Schema::table('analytics', function (Blueprint $table) {
            if (! Schema::hasColumn('analytics', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('provider_id');
            }
            if (! Schema::hasColumn('analytics', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('category_id');
            }
            if (! Schema::hasColumn('analytics', 'source_page')) {
                $table->string('source_page', 50)->nullable()->after('action_type');
            }
            if (! Schema::hasColumn('analytics', 'locale')) {
                $table->string('locale', 5)->nullable()->after('source_page');
            }
            if (! Schema::hasColumn('analytics', 'device_type')) {
                $table->string('device_type', 20)->nullable()->after('locale');
            }
        });

        // Indexes for the admin WhatsApp analytics aggregations.
        Schema::table('analytics', function (Blueprint $table) {
            $this->addIndexIfMissing($table, ['action_type', 'created_at'], 'analytics_action_created_idx');
            $this->addIndexIfMissing($table, ['category_id', 'action_type', 'created_at'], 'analytics_category_action_created_idx');
            $this->addIndexIfMissing($table, ['location_id', 'action_type', 'created_at'], 'analytics_location_action_created_idx');
        });

        // Mark the schema as context-aware so the tracking actions enrich payloads.
        Cache::forever('analytics_has_context', true);
        Cache::forget('analytics_table_columns');
    }

    public function down(): void
    {
        Cache::forget('analytics_has_context');
        Cache::forget('analytics_table_columns');

        Schema::table('analytics', function (Blueprint $table) {
            foreach ([
                'analytics_action_created_idx',
                'analytics_category_action_created_idx',
                'analytics_location_action_created_idx',
            ] as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Throwable $e) {
                    // Index may not exist; ignore.
                }
            }

            $table->dropColumn(['category_id', 'location_id', 'source_page', 'locale', 'device_type']);
        });
    }

    private function addIndexIfMissing(Blueprint $table, array $columns, string $name): void
    {
        try {
            $indexes = Schema::getIndexes('analytics');
            foreach ($indexes as $index) {
                if (($index['name'] ?? null) === $name) {
                    return;
                }
            }
        } catch (\Throwable $e) {
            // Older driver without getIndexes(); fall through and attempt creation.
        }

        try {
            $table->index($columns, $name);
        } catch (\Throwable $e) {
            // Index already exists; ignore.
        }
    }
};
