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
        Schema::table('categories', function (Blueprint $table) {
            // Add multi-language name columns if they don't exist
            if (!Schema::hasColumn('categories', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (!Schema::hasColumn('categories', 'name_en')) {
                $table->string('name_en')->nullable()->after('name_ar');
            }
            if (!Schema::hasColumn('categories', 'name_fr')) {
                $table->string('name_fr')->nullable()->after('name_en');
            }

            // Add multi-language description columns if they don't exist
            if (!Schema::hasColumn('categories', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
            if (!Schema::hasColumn('categories', 'description_en')) {
                $table->text('description_en')->nullable()->after('description_ar');
            }
            if (!Schema::hasColumn('categories', 'description_fr')) {
                $table->text('description_fr')->nullable()->after('description_en');
            }
        });

        // Migrate existing data: copy current name/description to all language columns
        $this->migrateExistingData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $columns = [
                'name_ar', 'name_en', 'name_fr',
                'description_ar', 'description_en', 'description_fr'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Migrate existing data to new language columns
     */
    private function migrateExistingData(): void
    {
        try {
            $categories = \App\Models\Category::all();

            foreach ($categories as $category) {
                $updateData = [];

                // Only update if new columns are empty and old columns have data
                if (empty($category->name_ar) && !empty($category->name)) {
                    $updateData['name_ar'] = $category->name;
                    $updateData['name_en'] = $category->name;
                    $updateData['name_fr'] = $category->name;
                }

                if (empty($category->description_ar) && !empty($category->description)) {
                    $updateData['description_ar'] = $category->description;
                    $updateData['description_en'] = $category->description;
                    $updateData['description_fr'] = $category->description;
                }

                if (!empty($updateData)) {
                    $category->update($updateData);
                }
            }

            \Illuminate\Support\Facades\Log::info('Multi-language category migration completed successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to migrate existing category data: ' . $e->getMessage());
        }
    }
};
