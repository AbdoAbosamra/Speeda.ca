<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Local Offers: admin-managed deals attributed to real service providers.
 *
 *  - offers          one row per deal; copy in en/ar/fr, dates drive status
 *  - offer_sections  the single settings row for the home page section
 *  - offer_events    privacy-safe event log behind the offer analytics
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 160)->unique();

            // percent | fixed | free | custom
            $table->string('deal_type', 20)->default('percent');
            $table->decimal('deal_value', 10, 2)->nullable();
            $table->string('badge_text_en', 40)->nullable();
            $table->string('badge_text_ar', 40)->nullable();
            $table->string('badge_text_fr', 40)->nullable();

            $table->string('title_en', 120);
            $table->string('title_ar', 120)->nullable();
            $table->string('title_fr', 120)->nullable();
            $table->string('summary_en', 200)->nullable();
            $table->string('summary_ar', 200)->nullable();
            $table->string('summary_fr', 200)->nullable();
            $table->text('details_en')->nullable();
            $table->text('details_ar')->nullable();
            $table->text('details_fr')->nullable();
            $table->text('terms_en')->nullable();
            $table->text('terms_ar')->nullable();
            $table->text('terms_fr')->nullable();

            $table->string('image_path')->nullable();

            // whatsapp | profile | url
            $table->string('cta_type', 20)->default('whatsapp');
            $table->string('cta_url', 500)->nullable();

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(true);
            $table->unsignedInteger('sort_order')->default(1);

            // Denormalised counters for list screens; the event log is the source of truth.
            $table->unsignedInteger('views_count')->default(0);
            // "Get this offer on WhatsApp" clicks.
            $table->unsignedInteger('claims_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index(['is_featured', 'sort_order']);
        });

        Schema::create('offer_sections', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('title_en', 120)->nullable();
            $table->string('title_ar', 120)->nullable();
            $table->string('title_fr', 120)->nullable();
            $table->string('subtitle_en', 255)->nullable();
            $table->string('subtitle_ar', 255)->nullable();
            $table->string('subtitle_fr', 255)->nullable();
            $table->unsignedTinyInteger('max_items')->default(8);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('offer_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('service_provider_id')->index();
            // impression | view | claim | reopen | code_copy | share | verify | redeem
            $table->string('event', 20);
            $table->string('source', 30)->nullable();
            $table->string('session_hash', 64)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('device_type', 10)->nullable();
            $table->string('locale', 5)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['offer_id', 'event', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_events');
        Schema::dropIfExists('offer_sections');
        Schema::dropIfExists('offers');
    }
};
