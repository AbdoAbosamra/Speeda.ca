<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visitor engagement on offers.
 *
 *  - offer_likes      anyone; a guest is one salted session hash
 *  - offer_comments   anyone; held as "pending" until an admin approves
 *  - offer_favorites  client accounts only, listed on their favorites page
 *
 * Counters on offers keep cards and lists cheap to render; the rows above
 * are the source of truth.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['offer_id', 'user_id']);
            $table->index(['offer_id', 'session_hash']);
        });

        Schema::create('offer_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name', 60);
            $table->text('body');
            // pending | approved | rejected
            $table->string('status', 12)->default('pending');
            $table->string('locale', 5)->nullable();
            $table->string('session_hash', 64)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['offer_id', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('offer_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'offer_id']);
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->unsignedInteger('likes_count')->default(0)->after('claims_count');
            $table->unsignedInteger('comments_count')->default(0)->after('likes_count');
            $table->unsignedInteger('favorites_count')->default(0)->after('comments_count');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['likes_count', 'comments_count', 'favorites_count']);
        });
        Schema::dropIfExists('offer_favorites');
        Schema::dropIfExists('offer_comments');
        Schema::dropIfExists('offer_likes');
    }
};
