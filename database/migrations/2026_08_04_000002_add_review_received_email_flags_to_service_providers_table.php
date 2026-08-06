<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds send-once guards for the service provider review engagement emails:
     *   - first_review_received_email_sent_at : motivational email when provider receives 1st approved review
     *   - fifth_review_received_email_sent_at : milestone email when provider receives 5th approved review
     */
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->timestamp('first_review_received_email_sent_at')->nullable()->after('endorsement_count');
            $table->timestamp('fifth_review_received_email_sent_at')->nullable()->after('first_review_received_email_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn(['first_review_received_email_sent_at', 'fifth_review_received_email_sent_at']);
        });
    }
};
