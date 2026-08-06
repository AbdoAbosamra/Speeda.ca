<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Admin-authored testimonials about Speeda, collected from real service
     * providers and entered from the dashboard. The public home page shows
     * these as cards (exactly 3 active, or the section is hidden).
     */
    public function up(): void
    {
        Schema::create('site_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('provider_name');
            $table->string('provider_title')->nullable(); // e.g. company / profession
            $table->unsignedTinyInteger('rating')->default(5); // 1..5 stars
            $table->text('testimonial_text');
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_testimonials');
    }
};
