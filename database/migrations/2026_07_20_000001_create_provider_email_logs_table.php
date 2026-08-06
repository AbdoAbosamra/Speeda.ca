<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the provider_email_logs table that tracks every automated
     * onboarding email sent to each service provider. This powers:
     *   - State machine: which step each provider is at
     *   - Resend logic:  when to send follow-up emails
     *   - Admin panel:   full visibility into the email journey per provider
     */
    public function up(): void
    {
        Schema::create('provider_email_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_provider_id')
                ->constrained('service_providers')
                ->cascadeOnDelete();

            // Email type constants mirror ProviderEmailJourneyService::EMAIL_* constants
            $table->string('email_type', 50);
            // welcome | photo | services | bio | experience | gallery | service_areas | complete | reviews

            // Which attempt is this? (1 = first send, 2 = first resend, 3 = second resend …)
            $table->unsignedTinyInteger('attempt_number')->default(1);

            // When the email was dispatched
            $table->timestamp('sent_at');

            // When the next resend should fire (null = no more resends scheduled)
            $table->timestamp('next_send_at')->nullable();

            // Filled when the provider completes the step this email was nudging them to do
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Fast lookups for the scheduler
            $table->index(['service_provider_id', 'email_type'], 'idx_provider_email_type');
            $table->index('next_send_at', 'idx_next_send');
            $table->index(['email_type', 'sent_at'], 'idx_email_type_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_email_logs');
    }
};
