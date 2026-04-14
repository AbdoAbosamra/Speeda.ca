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
        if (!Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('title_ar');
                $table->string('title_en');
                $table->string('title_fr');
                $table->text('message_ar');
                $table->text('message_en');
                $table->text('message_fr');
                $table->enum('target_type', ['provider_only'])->default('provider_only');
                $table->unsignedBigInteger('created_by');
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
