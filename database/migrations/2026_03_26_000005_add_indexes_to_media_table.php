<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            // Used by the admin Provider Activity Monitor to count gallery images per provider.
            $table->index(
                ['model_type', 'collection_name', 'model_id'],
                'media_model_type_collection_name_model_id_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_model_type_collection_name_model_id_idx');
        });
    }
};

