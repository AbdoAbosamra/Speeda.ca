<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropLegacyFlags('service_providers');
        $this->dropLegacyFlags('service_provider_profiles');
    }

    public function down(): void
    {
        $this->restoreLegacyFlags('service_providers');
        $this->restoreLegacyFlags('service_provider_profiles');
    }

    private function dropLegacyFlags(string $table): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $columns = collect(['is_verified', 'is_featured'])
            ->filter(fn (string $column) => Schema::hasColumn($table, $column))
            ->values()
            ->all();

        if ($columns === []) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns) {
            $blueprint->dropColumn($columns);
        });
    }

    private function restoreLegacyFlags(string $table): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (!Schema::hasColumn($table, 'is_verified')) {
                $blueprint->boolean('is_verified')->default(false);
            }

            if (!Schema::hasColumn($table, 'is_featured')) {
                $blueprint->boolean('is_featured')->default(false);
            }
        });
    }
};
