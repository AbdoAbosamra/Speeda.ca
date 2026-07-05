<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // Add hierarchical support and metadata columns
            $table->string('country')->nullable()->after('city');
            $table->string('area')->nullable()->after('country');
            $table->decimal('latitude', 10, 8)->nullable()->after('area');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('meta_title')->nullable()->after('image');
            $table->string('meta_description')->nullable()->after('meta_title');

            // Add indexes for performance
            $table->index('is_active');
            $table->index('country');
            $table->index('area');
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'])) {
                $table->fullText(['city', 'country', 'area']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['country']);
            $table->dropIndex(['area']);
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'])) {
                $table->dropFullText(['city', 'country', 'area']);
            }

            $table->dropColumn([
                'country',
                'area',
                'latitude',
                'longitude',
                'meta_title',
                'meta_description',
            ]);
        });
    }
};
