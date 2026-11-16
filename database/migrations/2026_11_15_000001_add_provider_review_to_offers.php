<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            // Existing, admin-created offers keep their current visibility.
            $table->string('review_status', 20)->default('approved')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['review_status']);
            $table->dropColumn(['review_status', 'submitted_at', 'reviewed_at', 'reviewed_by', 'rejection_reason']);
        });
    }
};
