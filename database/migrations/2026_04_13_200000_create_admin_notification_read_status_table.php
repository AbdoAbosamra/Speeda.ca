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
        if (!Schema::hasTable('admin_notification_user')) {
            Schema::create('admin_notification_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('admin_notification_id')->constrained()->onDelete('cascade');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'admin_notification_id'], 'user_notification_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notification_user');
    }
};
