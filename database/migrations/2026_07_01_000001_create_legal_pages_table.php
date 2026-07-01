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
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('page_type')->default('custom')->index();
            $table->string('status')->default('draft')->index();
            $table->boolean('allow_indexing')->default(true);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('last_reviewed_at')->nullable();

            $table->string('title_en');
            $table->string('title_ar');
            $table->string('title_fr');
            $table->longText('content_en');
            $table->longText('content_ar');
            $table->longText('content_fr');
            $table->text('summary_en')->nullable();
            $table->text('summary_ar')->nullable();
            $table->text('summary_fr')->nullable();
            $table->string('seo_title_en')->nullable();
            $table->string('seo_title_ar')->nullable();
            $table->string('seo_title_fr')->nullable();
            $table->text('seo_description_en')->nullable();
            $table->text('seo_description_ar')->nullable();
            $table->text('seo_description_fr')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};
