<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('provider_id')
                ->constrained('service_providers')
                ->cascadeOnDelete();

            $table->string('action_type', 30);
            $table->string('ip_address', 45);

            $table->timestamps(); // created_at + updated_at

            // Critical performance index for dashboard + ranges
            $table->index(['provider_id', 'created_at'], 'analytics_provider_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};

