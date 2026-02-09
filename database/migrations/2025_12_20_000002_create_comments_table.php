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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // Foreign keys - Polymorphic relationship for comments on reviews/listings
            $table->string('commentable_type'); // Class name: Review, ServiceProviderProfile, etc
            $table->unsignedBigInteger('commentable_id');

            // User who wrote the comment
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Comment content and moderation
            $table->text('content');
            $table->boolean('is_active')->default(false); // Must be approved by admin before showing
            $table->boolean('is_flagged')->default(false); // Flagged for review

            // Admin approval trail
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Rejection reason if applicable
            $table->string('rejection_reason')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes(); // Soft delete to preserve history

            // Indexes for performance
            $table->index(['commentable_type', 'commentable_id']);
            $table->index('user_id');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
