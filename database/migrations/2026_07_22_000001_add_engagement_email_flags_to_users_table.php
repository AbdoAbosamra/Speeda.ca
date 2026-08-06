<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds send-once guards for the client engagement emails:
     *   - first_review_email_sent_at : motivational email at the 1st approved review
     *   - fifth_review_email_sent_at : milestone email at the 5th approved review
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('first_review_email_sent_at')->nullable()->after('is_active');
            $table->timestamp('fifth_review_email_sent_at')->nullable()->after('first_review_email_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_review_email_sent_at', 'fifth_review_email_sent_at']);
        });
    }
};
