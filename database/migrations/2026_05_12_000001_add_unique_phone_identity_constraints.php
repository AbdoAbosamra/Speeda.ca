<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'mobile')) {
            $this->assertNoDuplicateValues('users', 'mobile');
            $this->dropIndexIfExists('users', 'users_mobile_index');

            Schema::table('users', function (Blueprint $table) {
                $table->unique('mobile', 'users_mobile_unique');
            });
        }

        if (Schema::hasColumn('service_providers', 'whatsapp_number')) {
            $this->assertNoDuplicateValues('service_providers', 'whatsapp_number');
            $this->dropIndexIfExists('service_providers', 'service_providers_whatsapp_number_index');

            Schema::table('service_providers', function (Blueprint $table) {
                $table->unique('whatsapp_number', 'service_providers_whatsapp_number_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_providers', 'whatsapp_number')) {
            $this->dropIndexIfExists('service_providers', 'service_providers_whatsapp_number_unique');

            Schema::table('service_providers', function (Blueprint $table) {
                $table->index('whatsapp_number', 'service_providers_whatsapp_number_index');
            });
        }

        if (Schema::hasColumn('users', 'mobile')) {
            $this->dropIndexIfExists('users', 'users_mobile_unique');

            Schema::table('users', function (Blueprint $table) {
                $table->index('mobile', 'users_mobile_index');
            });
        }
    }

    private function assertNoDuplicateValues(string $table, string $column): void
    {
        $duplicates = DB::table($table)
            ->select($column, DB::raw('COUNT(*) as total'))
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->having('total', '>', 1)
            ->pluck($column)
            ->all();

        if (! empty($duplicates)) {
            throw new RuntimeException(sprintf(
                'Cannot add unique index on %s.%s because duplicate values already exist: %s',
                $table,
                $column,
                implode(', ', array_slice($duplicates, 0, 10))
            ));
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($index) {
                $table->dropIndex($index);
            });
        } catch (Throwable) {
            // Index names differ across environments; absence is safe here.
        }
    }
};
