<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->after('id')->constrained('users')->nullOnDelete();

            $table->string('title_ar')->nullable()->after('title');
            $table->string('title_en')->nullable()->after('title_ar');
            $table->string('title_fr')->nullable()->after('title_en');

            $table->longText('content_ar')->nullable()->after('content');
            $table->longText('content_en')->nullable()->after('content_ar');
            $table->longText('content_fr')->nullable()->after('content_en');

            $table->text('excerpt_ar')->nullable()->after('excerpt');
            $table->text('excerpt_en')->nullable()->after('excerpt_ar');
            $table->text('excerpt_fr')->nullable()->after('excerpt_en');

            $table->string('featured_image')->nullable()->after('image');
            $table->string('featured_image_alt_ar')->nullable()->after('featured_image');
            $table->string('featured_image_alt_en')->nullable()->after('featured_image_alt_ar');
            $table->string('featured_image_alt_fr')->nullable()->after('featured_image_alt_en');

            $table->string('seo_title_ar')->nullable()->after('is_published');
            $table->string('seo_title_en')->nullable()->after('seo_title_ar');
            $table->string('seo_title_fr')->nullable()->after('seo_title_en');

            $table->text('seo_description_ar')->nullable()->after('seo_title_fr');
            $table->text('seo_description_en')->nullable()->after('seo_description_ar');
            $table->text('seo_description_fr')->nullable()->after('seo_description_en');

            $table->text('seo_keywords_ar')->nullable()->after('seo_description_fr');
            $table->text('seo_keywords_en')->nullable()->after('seo_keywords_ar');
            $table->text('seo_keywords_fr')->nullable()->after('seo_keywords_en');

            $table->string('og_title_ar')->nullable()->after('seo_keywords_fr');
            $table->string('og_title_en')->nullable()->after('og_title_ar');
            $table->string('og_title_fr')->nullable()->after('og_title_en');

            $table->text('og_description_ar')->nullable()->after('og_title_fr');
            $table->text('og_description_en')->nullable()->after('og_description_ar');
            $table->text('og_description_fr')->nullable()->after('og_description_en');
            $table->string('og_image')->nullable()->after('og_description_fr');

            $table->string('twitter_title_ar')->nullable()->after('og_image');
            $table->string('twitter_title_en')->nullable()->after('twitter_title_ar');
            $table->string('twitter_title_fr')->nullable()->after('twitter_title_en');

            $table->text('twitter_description_ar')->nullable()->after('twitter_title_fr');
            $table->text('twitter_description_en')->nullable()->after('twitter_description_ar');
            $table->text('twitter_description_fr')->nullable()->after('twitter_description_en');
            $table->string('twitter_image')->nullable()->after('twitter_description_fr');

            $table->string('status')->default('draft')->after('twitter_image');
            $table->timestamp('published_at')->nullable()->after('status');
            $table->boolean('is_featured')->default(false)->after('published_at');
            $table->boolean('allow_indexing')->default(true)->after('is_featured');
            $table->string('canonical_url')->nullable()->after('allow_indexing');
            $table->string('meta_robots')->nullable()->after('canonical_url');
            $table->unsignedInteger('reading_time_minutes')->nullable()->after('meta_robots');

            $table->index(['status', 'published_at'], 'posts_status_published_at_index');
            $table->index('author_id', 'posts_author_id_index');
            $table->index('is_featured', 'posts_is_featured_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_status_published_at_index');
            $table->dropIndex('posts_author_id_index');
            $table->dropIndex('posts_is_featured_index');
            $table->dropConstrainedForeignId('author_id');

            $table->dropColumn([
                'title_ar',
                'title_en',
                'title_fr',
                'content_ar',
                'content_en',
                'content_fr',
                'excerpt_ar',
                'excerpt_en',
                'excerpt_fr',
                'featured_image',
                'featured_image_alt_ar',
                'featured_image_alt_en',
                'featured_image_alt_fr',
                'seo_title_ar',
                'seo_title_en',
                'seo_title_fr',
                'seo_description_ar',
                'seo_description_en',
                'seo_description_fr',
                'seo_keywords_ar',
                'seo_keywords_en',
                'seo_keywords_fr',
                'og_title_ar',
                'og_title_en',
                'og_title_fr',
                'og_description_ar',
                'og_description_en',
                'og_description_fr',
                'og_image',
                'twitter_title_ar',
                'twitter_title_en',
                'twitter_title_fr',
                'twitter_description_ar',
                'twitter_description_en',
                'twitter_description_fr',
                'twitter_image',
                'status',
                'published_at',
                'is_featured',
                'allow_indexing',
                'canonical_url',
                'meta_robots',
                'reading_time_minutes',
            ]);
        });
    }
};
