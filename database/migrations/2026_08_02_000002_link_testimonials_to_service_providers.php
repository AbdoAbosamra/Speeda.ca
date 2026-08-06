<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Link testimonials to a real service provider instead of a typed-in name.
 *
 * Admins previously retyped the provider's name as free text, so the home page
 * could not show the provider's photo or city and the name could drift from the
 * real record. The testimonial now points at a service provider and the display
 * details are derived from it.
 *
 * provider_name / provider_title are kept (nullable) as the fallback for rows
 * created before this change, and for the rare case where a quote comes from
 * someone without a provider profile.
 *
 * Also moves sort_order to a 1-based sequence — a "0th" position reads wrong in
 * the admin UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_testimonials', function (Blueprint $table) {
            $table->foreignId('service_provider_id')
                ->nullable()
                ->after('id')
                ->constrained('service_providers')
                ->nullOnDelete();
        });

        Schema::table('site_testimonials', function (Blueprint $table) {
            $table->string('provider_name')->nullable()->change();
            $table->unsignedInteger('sort_order')->default(1)->change();
        });

        // Existing rows start at 0; shift them into the 1-based sequence.
        DB::table('site_testimonials')->where('sort_order', 0)->update(['sort_order' => 1]);
    }

    public function down(): void
    {
        DB::table('site_testimonials')->whereNull('provider_name')->update(['provider_name' => '']);

        Schema::table('site_testimonials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_provider_id');
        });

        Schema::table('site_testimonials', function (Blueprint $table) {
            $table->string('provider_name')->nullable(false)->change();
            $table->unsignedInteger('sort_order')->default(0)->change();
        });
    }
};
