<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. service_providers table
        if (Schema::hasTable('service_providers')) {
            if (!$this->indexExists('service_providers', 'idx_sp_active_category_location')) {
                DB::statement('CREATE INDEX idx_sp_active_category_location ON service_providers (is_verified, category_id, location_id) ALGORITHM=INPLACE LOCK=NONE');
            }
        }

        // 2. categories table
        if (Schema::hasTable('categories')) {
            if (!$this->indexExists('categories', 'idx_cat_section_active_parent')) {
                DB::statement('CREATE INDEX idx_cat_section_active_parent ON categories (is_section, is_active, parent_id) ALGORITHM=INPLACE LOCK=NONE');
            }
        }

        // 3. analytics table
        if (Schema::hasTable('analytics')) {
            if (!$this->indexExists('analytics', 'idx_analytics_provider_action_date')) {
                DB::statement('CREATE INDEX idx_analytics_provider_action_date ON analytics (provider_id, action_type, created_at) ALGORITHM=INPLACE LOCK=NONE');
            }
            if (!$this->indexExists('analytics', 'idx_analytics_session_hash')) {
                DB::statement('CREATE INDEX idx_analytics_session_hash ON analytics (session_hash) ALGORITHM=INPLACE LOCK=NONE');
            }
        }

        // 4. service_provider_reviews table
        if (Schema::hasTable('service_provider_reviews')) {
            if (!$this->indexExists('service_provider_reviews', 'idx_reviews_active_date')) {
                DB::statement('CREATE INDEX idx_reviews_active_date ON service_provider_reviews (is_active, created_at) ALGORITHM=INPLACE LOCK=NONE');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if ($this->indexExists('service_providers', 'idx_sp_active_category_location')) {
                $table->dropIndex('idx_sp_active_category_location');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if ($this->indexExists('categories', 'idx_cat_section_active_parent')) {
                $table->dropIndex('idx_cat_section_active_parent');
            }
        });

        Schema::table('analytics', function (Blueprint $table) {
            if ($this->indexExists('analytics', 'idx_analytics_provider_action_date')) {
                $table->dropIndex('idx_analytics_provider_action_date');
            }
            if ($this->indexExists('analytics', 'idx_analytics_session_hash')) {
                $table->dropIndex('idx_analytics_session_hash');
            }
        });

        Schema::table('service_provider_reviews', function (Blueprint $table) {
            if ($this->indexExists('service_provider_reviews', 'idx_reviews_active_date')) {
                $table->dropIndex('idx_reviews_active_date');
            }
        });
    }

    /**
     * Helper to safely check if an index exists.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEXES FROM {$table}");
        foreach ($indexes as $index) {
            if ($index->Key_name === $indexName) {
                return true;
            }
        }
        return false;
    }
};
