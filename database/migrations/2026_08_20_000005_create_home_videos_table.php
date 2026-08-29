<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The admin-managed video section on the home page.
 *
 * A single-row table rather than a generic key/value settings store: every
 * field here is typed, validated and translated, and a key/value store would
 * turn all of that into string juggling for no gain. If a second settings
 * group ever appears, it gets its own table for the same reason.
 *
 * Two ways to supply the video, because they fail differently:
 *   - embed: a YouTube/Vimeo URL. Costs no storage or bandwidth, adapts
 *     quality to the viewer's connection.
 *   - upload: a file on our own disk. Full control and no third-party
 *     branding, but bounded by PHP's upload limit and our bandwidth.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_videos', function (Blueprint $table) {
            $table->id();

            // The section is hidden unless this is true AND a usable video
            // exists — see HomeVideo::forHomePage().
            $table->boolean('is_active')->default(false);

            $table->string('source_type', 16)->default('embed'); // embed | upload
            $table->string('video_url')->nullable();  // embed source
            $table->string('video_path')->nullable(); // upload source, on the public disk
            $table->string('poster_path')->nullable();

            // Same per-locale column layout as legal_pages, so the fallback
            // rules stay identical across the site.
            $table->string('title_en')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('title_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_fr')->nullable();

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_videos');
    }
};
